-- ====================================================================
-- Verity Anti-Buddy Punching System - Relational Database Schema
-- Target Database: MySQL 5.7+ / MariaDB 10.4+
-- File: verity_db.sql
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `verity_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `verity_db`;

-- Drop existing tables to ensure a clean deployment state
DROP TABLE IF EXISTS `vacant_queue`;
DROP TABLE IF EXISTS `scan_logs`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------------------
-- 1. Users Table (Supervisor & Admin Node Authentication)
-- --------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `node_id` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `role` VARCHAR(50) DEFAULT 'Supervisor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 2. Employees Table (Staff Roster, Shift Rules & Penalty Rates)
-- --------------------------------------------------------------------
CREATE TABLE `employees` (
    `id` VARCHAR(50) PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `schedule_hours` DECIMAL(4,1) DEFAULT 8.0,
    `actual_hours` DECIMAL(4,1) DEFAULT 8.0,
    `strikes` INT DEFAULT 0,
    `shift_start` TIME DEFAULT '08:00:00',
    `hourly_rate` DECIMAL(10,2) DEFAULT 150.00,
    `strike_penalty` DECIMAL(10,2) DEFAULT 200.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 3. Scan Logs Table (Real-Time Biometric Verification Audit Trail)
-- --------------------------------------------------------------------
CREATE TABLE `scan_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `log_id` VARCHAR(50) NOT NULL,
    `timestamp` DATETIME NOT NULL,
    `employee` VARCHAR(150) NOT NULL,
    `station` VARCHAR(100) NOT NULL,
    `method` VARCHAR(100) NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `status_color` VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 4. Vacant Queue Table (Active Unhandled Attendance Anomalies)
-- --------------------------------------------------------------------
CREATE TABLE `vacant_queue` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `time` VARCHAR(20) NOT NULL,
    `emp_name` VARCHAR(100) NOT NULL,
    `emp_id` VARCHAR(50) NOT NULL,
    `dept` VARCHAR(100) NOT NULL,
    `anomaly` VARCHAR(100) NOT NULL,
    `strikes` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================================================
-- SAMPLE SEED DATA INSERTION (FOR EVALUATION & SUBMISSION)
-- ====================================================================

-- Seed Supervisor Node Credentials
INSERT INTO `users` (`node_id`, `password`, `name`, `role`) VALUES 
('MGR-8802', 'verity123', 'Supervisor Node', 'Admin'),
('SUP-1001', 'verity123', 'Secondary Supervisor', 'Supervisor');

-- Seed Employee Profiles Across Departments
INSERT INTO `employees` (`id`, `name`, `department`, `schedule_hours`, `actual_hours`, `strikes`, `shift_start`, `hourly_rate`, `strike_penalty`) VALUES
('1023', 'Nurse Ezra Villamar', 'Medical', 8.0, 8.0, 0, '08:00:00', 150.00, 200.00),
('402B', 'Engr. Karl Vacaro', 'Engineering', 8.0, 7.5, 2, '08:00:00', 150.00, 200.00),
('5102', 'Tech. Jairo Curaming', 'IT Support', 8.0, 6.2, 2, '08:00:00', 150.00, 200.00),
('3088', 'Dr. Althea Santos', 'Emergency', 8.0, 8.0, 0, '07:00:00', 250.00, 300.00),
('2041', 'Pharm. Mark Dizon', 'Pharmacy', 8.0, 7.0, 1, '08:00:00', 175.00, 200.00);

-- Seed Historical Check-in Audit Logs
INSERT INTO `scan_logs` (`log_id`, `timestamp`, `employee`, `station`, `method`, `status`, `status_color`) VALUES
('LOG-9079', '2026-10-05 07:55:00', 'Nurse Ezra Villamar (1023)', 'Security Gate', 'Biometric Facial Scan', 'Verified', '#10b981'),
('LOG-9080', '2026-10-05 08:28:00', 'Tech. Jairo Curaming (5102)', 'ER Triage Desk', 'Biometric Facial Scan', 'Late (+28m)', '#f43f5e'),
('LOG-9081', '2026-10-05 07:58:00', 'Engr. Karl Vacaro (402B)', 'Main Pharmacy', 'Biometric Facial Scan', 'Verified', '#10b981'),
('LOG-9082', '2026-10-05 08:15:00', 'Pharm. Mark Dizon (2041)', 'Main Pharmacy', 'Biometric Facial Scan', 'Late (+15m)', '#f43f5e'),
('LOG-9083', '2026-10-05 06:58:00', 'Dr. Althea Santos (3088)', 'ER Triage Desk', 'Biometric Facial Scan', 'Verified', '#10b981'),
('LOG-8513', '2026-10-05 08:35:00', 'Engr. Karl Vacaro (402B)', 'Main Pharmacy', 'Manual Override', 'Late Check-in (+35m)', '#f43f5e');

-- Seed Active Vacant Queue Items
INSERT INTO `vacant_queue` (`time`, `emp_name`, `emp_id`, `dept`, `anomaly`, `strikes`) VALUES
('08:28 AM', 'Tech. Jairo Curaming', '5102', 'IT Support', 'Late (+28m)', 2),
('08:15 AM', 'Pharm. Mark Dizon', '2041', 'Pharmacy', 'Late (+15m)', 1);