CREATE DATABASE weather_dashboard;
USE weather_dashboard;
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP );
-- Favorites table
CREATE TABLE favorites (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
city VARCHAR(100) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
UNIQUE KEY unique_favorite (user_id, city)
);
-- Search History table
CREATE TABLE search_history (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
city VARCHAR(100) NOT NULL,
searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- Feedback table
CREATE TABLE feedback (
id INT AUTO_INCREMENT PRIMARY KEY,

user_id INT,
name VARCHAR(100),
email VARCHAR(100),
subject VARCHAR(255),
rating INT,
message TEXT NOT NULL,
submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);