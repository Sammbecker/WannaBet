-- Add trust_score and last_score_update columns to users table
ALTER TABLE users
ADD COLUMN trust_score DECIMAL(5,2) DEFAULT 70.00,
ADD COLUMN last_score_update TIMESTAMP NULL; 