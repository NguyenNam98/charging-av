-- Create database
DROP DATABASE IF EXISTS easyev;


CREATE DATABASE IF NOT EXISTS easyev;
USE easyev;

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

-- demo1@gmail.com - user - 12345678@Sa
-- demo2@gmail.com - user - 12345678@Sa

-- insert charging locations

INSERT INTO charging_locations (location_id, description, num_stations, cost_per_hour, created_at, updated_at) VALUES (1, 'Downtown Parking Garage', 2, 12.60, '2025-05-06 14:30:35', '2025-05-13 14:34:57');
INSERT INTO charging_locations (location_id, description, num_stations, cost_per_hour, created_at, updated_at) VALUES (2, 'Shopping Mall East', 8, 10.00, '2025-05-06 14:30:35', '2025-05-06 14:30:35');
INSERT INTO charging_locations (location_id, description, num_stations, cost_per_hour, created_at, updated_at) VALUES (3, 'City Center Plaza', 3, 15.00, '2025-05-06 14:30:35', '2025-05-06 14:30:35');
INSERT INTO charging_locations (location_id, description, num_stations, cost_per_hour, created_at, updated_at) VALUES (4, 'North Business Park', 10, 8.75, '2025-05-06 14:30:35', '2025-05-06 14:30:35');
INSERT INTO charging_locations (location_id, description, num_stations, cost_per_hour, created_at, updated_at) VALUES (5, 'West Side Community Center', 2, 9.99, '2025-05-06 14:30:35', '2025-05-06 14:30:35');
INSERT INTO charging_locations (location_id, description, num_stations, cost_per_hour, created_at, updated_at) VALUES (6, 'Parking 1', 12, 100.00, '2025-05-13 14:16:39', '2025-05-13 14:16:50');


-- insert users

INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (1, 'Admin User', 'admin@com', '123-456-7890', '$2y$10$FvVSZNLCwEzQkR8Xr3LgMOb19JWD5TDzeJg8xHbNZhTbL7fZgbf0y', 'Administrator', '2025-05-06 14:30:35');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (2, 'John Doe', 'john@example.com', '987-654-3210', '$2y$10$RKQ3wC7rSH0.FxDtT0o2..wqQf3P7BJ/mN7cZ5Y34X2JKFudj0Tem', 'User', '2025-05-06 14:30:35');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (3, 'Jane Smith', 'jane@example.com', '555-123-4567', '$2y$10$RKQ3wC7rSH0.FxDtT0o2..wqQf3P7BJ/mN7cZ5Y34X2JKFudj0Tem', 'User', '2025-05-06 14:30:35');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (4, 'Nguyen Nam', 'michalnam98@gmail.com', '0492911759', '$2y$12$5MFEM99/r0DNKPyIj1ZFoO86vPDlBCfCxthUHIWS2ChG58XmK2dKO', 'User', '2025-05-06 14:48:11');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (5, 'nguyen', 'michalnam97@gmail.com', '0492911759', '$2y$12$rWn86LETCnTMl8O7.6iL8.SuZ/XZJRnjDRsl7mlYYfTxIRILx.eXW', 'User', '2025-05-06 15:58:50');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (6, 'Nguyen Nam', 'michalnam2@gmail.com', '0492911759', '$2y$12$QGu0GK5/3shbRPus/A4sBeshwK8kbxhRELaDpCVYA827tomwxfPn6', 'Administrator', '2025-05-11 16:36:03');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (12, 'Nguyen Nam', 'test124@gmail.com', '0492911759', '$2y$12$cA8qC1o3/KG1LRdKIHCvX.HEOZU7t6Un3QCQkpXZAwR/csycmZDiK', 'User', '2025-05-13 11:50:49');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (13, 'Nguyen Nam', 'test456@gmail.com', '0492911759', '$2y$12$VrWMbn8Bt7qeCtvW0nUmaOaqXqu24H/DkA0aZdB4J9pj9gwlKAzEO', 'User', '2025-05-13 12:36:16');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (14, 'Nguyen Nam', 'admin1@gmail.com', '0492911759', '$2y$12$fJqL2xpi933Ilyt3TUfRvOdiLVLN9eSJKO5N5ZbjqoaTwzJIg07Uq', 'Administrator', '2025-05-13 12:40:56');
INSERT INTO users (user_id, name, email, phone, password, user_type, created_at) VALUES (15, 'Nguyen Nam', 'michalnam90@gmail.com', '0492911759', '$2y$12$qkE6qCzMA/gMj1x6MW8lUOY6DDchibOQ36j/SmtDGyaR86b05rKia', 'User', '2025-05-20 16:04:34');

-- insert charging sessions
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (1, 2, 1, '2023-05-01 09:00:00', '2023-05-01 11:30:00', 31.25, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (2, 3, 2, '2023-05-02 14:00:00', '2023-05-02 15:45:00', 17.50, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (3, 2, 3, '2023-05-03 08:30:00', '2023-05-03 10:00:00', 22.50, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (4, 3, 1, '2023-05-04 12:00:00', null, null, 'active');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (5, 4, 1, '2025-05-06 15:21:32', '2025-05-06 15:22:57', 124.58, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (6, 4, 1, '2025-05-06 15:23:14', '2025-05-06 15:23:39', 124.79, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (7, 4, 1, '2025-05-06 15:24:28', '2025-05-06 15:50:37', 5.42, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (8, 4, 4, '2025-05-06 15:50:51', '2025-05-06 15:50:57', 0.00, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (9, 5, 1, '2025-05-06 15:59:07', '2025-05-06 17:26:54', 18.13, 'completed');
INSERT INTO charging_sessions (session_id, user_id, location_id, check_in_time, check_out_time, total_cost, status) VALUES (10, 4, 2, '2025-05-06 16:23:06', '2025-05-06 16:23:16', 0.00, 'completed');
