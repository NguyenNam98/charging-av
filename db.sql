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

-- Insert sample data for testing
-- Sample administrators
INSERT INTO users (name, email, phone, password, user_type) VALUES
('Admin User', 'admin@easyev.com', '123-456-7890', '$2y$10$FvVSZNLCwEzQkR8Xr3LgMOb19JWD5TDzeJg8xHbNZhTbL7fZgbf0y', 'Administrator');
-- Note: password is 'admin123' hashed with bcrypt

-- Sample regular users
INSERT INTO users (name, email, phone, password, user_type) VALUES
('John Doe', 'john@example.com', '987-654-3210', '$2y$10$RKQ3wC7rSH0.FxDtT0o2..wqQf3P7BJ/mN7cZ5Y34X2JKFudj0Tem', 'User'),
('Jane Smith', 'jane@example.com', '555-123-4567', '$2y$10$RKQ3wC7rSH0.FxDtT0o2..wqQf3P7BJ/mN7cZ5Y34X2JKFudj0Tem', 'User');
-- Note: password is 'user123' hashed with bcrypt

-- Sample charging locations
INSERT INTO charging_locations (description, num_stations, cost_per_hour) VALUES
('Downtown Parking Garage', 5, 12.50),
('Shopping Mall East', 8, 10.00),
('City Center Plaza', 3, 15.00),
('North Business Park', 10, 8.75),
('West Side Community Center', 2, 9.99);

-- Sample charging sessions
INSERT INTO charging_sessions (user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES
(2, 1, '2023-05-01 09:00:00', '2023-05-01 11:30:00', 31.25, 'completed'),
(3, 2, '2023-05-02 14:00:00', '2023-05-02 15:45:00', 17.50, 'completed'),
(2, 3, '2023-05-03 08:30:00', '2023-05-03 10:00:00', 22.50, 'completed'),
(3, 1, '2023-05-04 12:00:00', NULL, NULL, 'active');