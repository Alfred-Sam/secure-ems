-- Secure Employee Management System (EMS) Database Schema
-- Database: `secure_ems`

CREATE DATABASE IF NOT EXISTS `secure_ems` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `secure_ems`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default administrator credentials:
-- Username: admin
-- Password: admin123
INSERT INTO `users` (`username`, `password_hash`) 
VALUES ('admin', '$2y$10$ZNieMGOcYF7xTLh72TnbDOOffOo16sJaossOi3yvt9M8v94N9BWSu')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- --------------------------------------------------------
-- Table structure for table `employees`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `emp_code` VARCHAR(20) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `position` VARCHAR(100) NOT NULL,
    `avatar_path` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `login_attempts`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL UNIQUE,
    `attempts` INT NOT NULL DEFAULT 1,
    `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
