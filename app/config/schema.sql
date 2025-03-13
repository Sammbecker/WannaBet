-- Create the database
CREATE DATABASE IF NOT EXISTS betting_app;

USE betting_app;

-- Create the Users table
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    stripe_account_id VARCHAR(255) DEFAULT NULL,
    paystack_recipient_code VARCHAR(255) DEFAULT NULL,
    bank_account_name VARCHAR(255) DEFAULT NULL,
    bank_account_number VARCHAR(20) DEFAULT NULL,
    bank_code VARCHAR(10) DEFAULT NULL
);

-- Create the Friendships table
CREATE TABLE IF NOT EXISTS Friendships (
    friendship_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (friend_id) REFERENCES Users(user_id),
    UNIQUE KEY unique_friendship (user_id, friend_id)
);

-- Create the Bets table
CREATE TABLE IF NOT EXISTS Bets (
    bet_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    description TEXT NOT NULL,
    stake_type ENUM('money', 'favor') NOT NULL,
    stake_amount DECIMAL(10,2) DEFAULT NULL,
    stake_description TEXT DEFAULT NULL,
    deadline DATE NOT NULL,
    status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_required BOOLEAN DEFAULT FALSE,
    payment_status VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- Create the Notifications table
CREATE TABLE IF NOT EXISTS Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    bet_id INT NULL,
    friendship_id INT NULL,
    user_id INT,
    type ENUM('bet_invitation', 'friend_request', 'bet_response', 'friend_response') NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bet_id) REFERENCES Bets(bet_id) ON DELETE CASCADE,
    FOREIGN KEY (friendship_id) REFERENCES Friendships(friendship_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- Create the Transactions table
CREATE TABLE IF NOT EXISTS Transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    bet_id INT,
    amount DECIMAL(10, 2),
    transaction_type ENUM('bet', 'win', 'loss'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (bet_id) REFERENCES Bets(bet_id)
);

-- Create PaymentIntents table
CREATE TABLE IF NOT EXISTS PaymentIntents (
    payment_intent_id INT PRIMARY KEY AUTO_INCREMENT,
    bet_id INT NOT NULL,
    stripe_payment_intent_id VARCHAR(255) COMMENT 'Stores Paystack reference',
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    transfer_id VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    paystack_access_code VARCHAR(255) DEFAULT NULL,
    verification_status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (bet_id) REFERENCES Bets(bet_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create index on frequently accessed columns
CREATE INDEX idx_bets_user_id ON Bets(user_id);
CREATE INDEX idx_bets_status ON Bets(status);
CREATE INDEX idx_notifications_user_id ON Notifications(user_id);
CREATE INDEX idx_notifications_bet_id ON Notifications(bet_id);
CREATE INDEX idx_notifications_status ON Notifications(status);
CREATE INDEX idx_transactions_user_id ON Transactions(user_id);
CREATE INDEX idx_transactions_bet_id ON Transactions(bet_id);
CREATE INDEX idx_friendships_user_id ON Friendships(user_id);
CREATE INDEX idx_friendships_friend_id ON Friendships(friend_id);
CREATE INDEX idx_friendships_status ON Friendships(status);

-- Sample data for testing
INSERT INTO Users (username, password, email) VALUES
('john_doe', '$2y$10$5ZKdmGNQ0F.sKG8MlZQS3OQRUZJvvqGAuWQvXRw9H0C4iJW.3Svhm', 'john@example.com'),
('jane_smith', '$2y$10$5ZKdmGNQ0F.sKG8MlZQS3OQRUZJvvqGAuWQvXRw9H0C4iJW.3Svhm', 'jane@example.com');
-- Note: The password hash above corresponds to 'password123' 

-- Create test users
php app/utils/create_test_user.php
php app/utils/create_second_user.php