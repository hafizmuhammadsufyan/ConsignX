-- ConsignX Database Schema
-- Database Name: consignx_db

CREATE DATABASE IF NOT EXISTS consignx_db;
USE consignx_db;

-- 1. Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (role_name) VALUES ('admin'), ('agent'), ('user');

-- 2. Courier Companies table
CREATE TABLE courier_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    company_email VARCHAR(255) NOT NULL UNIQUE,
    company_phone VARCHAR(50) NOT NULL,
    company_address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    company_id INT DEFAULT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (company_id) REFERENCES courier_companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Shipment Status table
CREATE TABLE shipment_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO shipment_status (status_name) VALUES 
('Pending'), 
('In Transit'), 
('Out for Delivery'), 
('Delivered'), 
('Cancelled'), 
('Returned');

-- 5. Shipments table
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    tracking_number VARCHAR(50) NOT NULL UNIQUE,
    sender_name VARCHAR(255) NOT NULL,
    sender_phone VARCHAR(50) NOT NULL,
    sender_address TEXT NOT NULL,
    receiver_name VARCHAR(255) NOT NULL,
    receiver_phone VARCHAR(50) NOT NULL,
    receiver_address TEXT NOT NULL,
    created_by INT NOT NULL,
    shipment_type ENUM('standard', 'express') DEFAULT 'standard',
    weight DECIMAL(10, 2),
    price DECIMAL(10, 2),
    current_status_id INT NOT NULL,
    expected_delivery_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES courier_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (current_status_id) REFERENCES shipment_status(id),
    INDEX (tracking_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Shipment Tracking History table
CREATE TABLE shipment_tracking_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    status_id INT NOT NULL,
    updated_by INT NOT NULL,
    remarks TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES shipment_status(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. SMS Logs table
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    company_id INT NOT NULL,
    sent_to VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    sent_by INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES courier_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default admin user (password: Admin@123)
-- Role ID 1 is Admin
INSERT INTO users (role_id, company_id, full_name, email, phone, password, status) 
VALUES (1, NULL, 'System Administrator', 'admin@consignx.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');
