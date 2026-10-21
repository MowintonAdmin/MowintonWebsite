-- ==============================================================================
-- Mowinton Consulting — Production & Staging Database Schema (Hostinger MySQL)
-- Version: 1.0.0
-- Target Engine: MySQL 5.7+ / 8.0+ / MariaDB 10+ (Hostinger Cloud / Web Hosting)
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
--
-- IMPORTANT FOR HOSTINGER IMPORT:
-- 1. In phpMyAdmin, FIRST select your designated database (e.g. u981051719_mow_stg).
-- 2. Click the "Import" tab, choose this file, and click "Import".
-- 3. This script does NOT contain CREATE DATABASE or USE statements, ensuring
--    it executes safely within your assigned hosting privileges without error #1044.
-- ==============================================================================

-- 1. Administrator Accounts Table
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('super_admin', 'staff') NOT NULL DEFAULT 'staff',
    `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admin_username` (`username`),
    INDEX `idx_admin_role` (`role`),
    INDEX `idx_admin_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Login Rate Limiting Table (Brute-Force Protection)
CREATE TABLE IF NOT EXISTS `login_rate_limits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `attempt_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rate_limit` (`ip_address`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `subject` VARCHAR(150) NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new', 'read', 'resolved') NOT NULL DEFAULT 'new',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_contact_status` (`status`),
    INDEX `idx_contact_created` (`created_at`),
    INDEX `idx_contact_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Job & Internship Applications Table
CREATE TABLE IF NOT EXISTS `job_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reference_id` VARCHAR(32) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `position` VARCHAR(100) NOT NULL,
    `application_type` ENUM('job', 'internship') NOT NULL DEFAULT 'job',
    `education` VARCHAR(255) NULL,
    `experience` TEXT NULL,
    `expected_salary` VARCHAR(50) NULL,
    `resume_filename` VARCHAR(255) NOT NULL,
    `resume_original_name` VARCHAR(255) NOT NULL,
    `resume_filesize` INT NOT NULL,
    `status` ENUM('new', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected') NOT NULL DEFAULT 'new',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_app_ref` (`reference_id`),
    INDEX `idx_app_status` (`status`),
    INDEX `idx_app_type` (`application_type`),
    INDEX `idx_app_created` (`created_at`),
    INDEX `idx_app_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Application Internal Notes Table
CREATE TABLE IF NOT EXISTS `application_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `admin_user_id` INT NOT NULL,
    `note` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `job_applications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE,
    INDEX `idx_note_app` (`application_id`),
    INDEX `idx_note_admin` (`admin_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Audit Logs Table (Administrative Traceability)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `target_type` VARCHAR(50) NULL,
    `target_id` INT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_admin` (`admin_user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. System Settings Table
CREATE TABLE IF NOT EXISTS `system_settings` (
    `setting_key` VARCHAR(64) PRIMARY KEY,
    `setting_value` TEXT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default System Settings (Safe initial baseline)
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Mowinton Consulting'),
('notification_email', 'info@mowinton.com'),
('smtp_enabled', '0'),
('smtp_host', 'smtp.hostinger.com'),
('smtp_port', '465'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_secure', 'ssl'),
('session_timeout_minutes', '30'),
('rate_limit_max_attempts', '5'),
('rate_limit_lockout_minutes', '15')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;
