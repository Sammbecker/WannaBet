<?php
require_once __DIR__ . '/app/config/db.php';

try {
    $db = getDB();
    
    // Create users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        trust_score INT DEFAULT 100,
        last_score_update TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Create bets table
    $db->exec("CREATE TABLE IF NOT EXISTS bets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        creator_id INT NOT NULL,
        opponent_id INT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        stake_type ENUM('money', 'favor') NOT NULL,
        stake_amount DECIMAL(10,2),
        stake_description VARCHAR(255),
        status ENUM('pending', 'accepted', 'completed', 'cancelled') DEFAULT 'pending',
        winner_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (creator_id) REFERENCES users(id),
        FOREIGN KEY (opponent_id) REFERENCES users(id),
        FOREIGN KEY (winner_id) REFERENCES users(id)
    )");

    // Create bet_participants table
    $db->exec("CREATE TABLE IF NOT EXISTS bet_participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bet_id INT NOT NULL,
        user_id INT NOT NULL,
        status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (bet_id) REFERENCES bets(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Create bet_outcomes table
    $db->exec("CREATE TABLE IF NOT EXISTS bet_outcomes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bet_id INT NOT NULL,
        outcome_description TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bet_id) REFERENCES bets(id)
    )");

    // Create payments table
    $db->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bet_id INT NOT NULL,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (bet_id) REFERENCES bets(id),
        FOREIGN KEY (sender_id) REFERENCES users(id),
        FOREIGN KEY (receiver_id) REFERENCES users(id)
    )");

    // Create notifications table
    $db->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        bet_id INT,
        type ENUM('bet_invite', 'bet_accepted', 'bet_declined', 'bet_completed', 'payment_received', 'payment_sent') NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (bet_id) REFERENCES bets(id)
    )");

    echo "Database tables created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 