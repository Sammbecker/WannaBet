-- Add payment related columns to Bets table
ALTER TABLE Bets
ADD COLUMN payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
ADD COLUMN payment_date TIMESTAMP NULL,
ADD COLUMN creator_id INT NULL,
ADD COLUMN opponent_id INT NULL,
ADD COLUMN winner_id INT NULL,
ADD FOREIGN KEY (creator_id) REFERENCES users(user_id),
ADD FOREIGN KEY (opponent_id) REFERENCES users(user_id),
ADD FOREIGN KEY (winner_id) REFERENCES users(user_id); 