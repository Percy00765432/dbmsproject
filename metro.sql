
CREATE DATABASE metro;
USE metro;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('admin', 'operator', 'passenger') DEFAULT 'passenger',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Metro Cards table
CREATE TABLE metro_cards (
    card_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    card_number VARCHAR(20) UNIQUE NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    expiry_date DATE,
    issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Stations table
CREATE TABLE stations (
    station_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    zone VARCHAR(50)
);

-- Transactions table
CREATE TABLE transactions (
    trans_id INT AUTO_INCREMENT PRIMARY KEY,
    card_id INT,
    entry_station INT,
    exit_station INT,
    fare DECIMAL(10, 2),
    entry_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    exit_time TIMESTAMP NULL,
    FOREIGN KEY (card_id) REFERENCES metro_cards(card_id),
    FOREIGN KEY (entry_station) REFERENCES stations(station_id),
    FOREIGN KEY (exit_station) REFERENCES stations(station_id)
);

-- Fare Rules table
CREATE TABLE fare_rules (
    rule_id INT AUTO_INCREMENT PRIMARY KEY,
    start_zone VARCHAR(50),
    end_zone VARCHAR(50),
    fare DECIMAL(10, 2)
);

-- Recharge History table
CREATE TABLE recharges (
    recharge_id INT AUTO_INCREMENT PRIMARY KEY,
    card_id INT,
    amount DECIMAL(10, 2),
    recharge_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (card_id) REFERENCES metro_cards(card_id)
);

-- Penalties table
CREATE TABLE penalties (
    penalty_id INT AUTO_INCREMENT PRIMARY KEY,
    card_id INT,
    amount DECIMAL(10, 2),
    reason VARCHAR(255),
    penalty_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (card_id) REFERENCES metro_cards(card_id)
);

-- Analytics table
CREATE TABLE analytics (
    analytics_id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_rides INT,
    total_revenue DECIMAL(10, 2),
    avg_fare DECIMAL(10, 2)
);

-- Insert Admin User
INSERT INTO users (name, email, password, role) 
VALUES ('Admin', 'admin@metro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Stations
INSERT INTO stations (name, location, zone) VALUES 
('Kenegeri BUs Terminal', 'City Center', 'A'),
('Pattanagere', 'North District', 'B'),
('Jnanabharthi', 'South District', 'C'),
('Rajarajeshwari Nagar', 'East District', 'D'),
('Pantharapalya - Nayandahalli', 'West District', 'E');

-- Insert Sample Fare Rules
INSERT INTO fare_rules (start_zone, end_zone, fare) VALUES 
('A', 'B', 20.00),
('A', 'C', 30.00),
('A', 'D', 25.00),
('A', 'E', 35.00),
('B', 'C', 15.00),
('B', 'D', 30.00),
('B', 'E', 40.00),
('C', 'D', 20.00),
('C', 'E', 25.00),
('D', 'E', 15.00);
