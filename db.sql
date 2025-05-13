-- Create database
CREATE DATABASE IF NOT EXISTS easyev_charging;
USE easyev_charging;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('Administrator', 'User') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Charging locations table
CREATE TABLE IF NOT EXISTS charging_locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    num_stations INT NOT NULL,
    cost_per_hour DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Charging sessions table
CREATE TABLE IF NOT EXISTS charging_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location_id INT NOT NULL,
    check_in_time DATETIME NOT NULL,
    check_out_time DATETIME NULL,
    total_cost DECIMAL(10, 2) NULL,
    status ENUM('active', 'completed') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (location_id) REFERENCES charging_locations(location_id)
);
