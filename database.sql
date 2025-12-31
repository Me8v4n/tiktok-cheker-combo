CREATE DATABASE tiktok_checker;
USE tiktok_checker;

CREATE TABLE combos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_or_user VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'checking', 'valid', 'invalid') DEFAULT 'pending',
    checked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
