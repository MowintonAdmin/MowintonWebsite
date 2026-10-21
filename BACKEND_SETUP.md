# Mowinton Consulting — Backend & Database Setup Guide

This guide provides deep technical instructions for configuring, managing, and maintaining the backend database infrastructure for the Mowinton Consulting website.

---

## 1. Architecture Overview

- **Language Runtime:** PHP 8.0+ (Tested on PHP 8.3.16)
- **Database Engine:** MySQL 5.7+ / 8.0+ or MariaDB 10.3+ / 13.0+
- **Driver:** PHP Data Objects (`PDO_MYSQL`) with Prepared Statements
- **Charset:** `utf8mb4` with `utf8mb4_unicode_ci` collation
- **Configuration Pattern:** Environment variables loaded via `.env` or system environment

---

## 2. Database Schema (7 Core Tables)

All tables are defined in [`database/schema.sql`](database/schema.sql) (for local development & CLI migration) and [`database/schema_hostinger.sql`](database/schema_hostinger.sql) (for Hostinger/cPanel phpMyAdmin import without `CREATE DATABASE` statements):

### 2.1 `admin_users`
Stores authenticated staff and super administrators.
- `id` (INT, Primary Key, Auto Increment)
- `username` (VARCHAR 50, Unique)
- `email` (VARCHAR 100, Unique)
- `password_hash` (VARCHAR 255) — Generated with `password_hash($pass, PASSWORD_DEFAULT)` (Bcrypt)
- `full_name` (VARCHAR 100)
- `role` (ENUM: `'super_admin'`, `'staff'`)
- `status` (ENUM: `'active'`, `'suspended'`)
- `last_login_at` (DATETIME, Nullable)
- `created_at` (DATETIME)
- `updated_at` (DATETIME)

### 2.2 `contact_messages`
Stores inquiries submitted from public contact forms (`/` and `/services/`).
- `id` (INT, Primary Key, Auto Increment)
- `name` (VARCHAR 100)
- `email` (VARCHAR 100)
- `phone` (VARCHAR 30, Nullable)
- `subject` (VARCHAR 200, Nullable)
- `message` (TEXT)
- `status` (ENUM: `'new'`, `'read'`, `'resolved'`)
- `ip_address` (VARCHAR 45, Nullable)
- `user_agent` (VARCHAR 255, Nullable)
- `created_at` (DATETIME)
- `updated_at` (DATETIME)

### 2.3 `job_applications`
Stores candidate job and internship applications (`/careers-2/` and `/submit-job-application/`).
- `id` (INT, Primary Key, Auto Increment)
- `reference_id` (VARCHAR 30, Unique) — Format: `MOW-YYYYMM-XXXXXX`
- `position_applied` (VARCHAR 100)
- `full_name` (VARCHAR 100)
- `email` (VARCHAR 100)
- `phone` (VARCHAR 30)
- `education` (TEXT, Nullable)
- `experience` (TEXT, Nullable)
- `expected_salary` (VARCHAR 50, Nullable)
- `resume_file_path` (VARCHAR 255) — Sanitized relative path in `uploads/resumes/`
- `original_filename` (VARCHAR 255) — User's original file upload name
- `status` (ENUM: `'new'`, `'under_review'`, `'shortlisted'`, `'interview'`, `'accepted'`, `'rejected'`)
- `created_at` (DATETIME)
- `updated_at` (DATETIME)

### 2.4 `application_notes`
Internal reviewer commentary appended to candidate records.
- `id` (INT, Primary Key, Auto Increment)
- `application_id` (INT, Foreign Key to `job_applications.id`)
- `admin_user_id` (INT, Foreign Key to `admin_users.id`)
- `note` (TEXT)
- `created_at` (DATETIME)

### 2.5 `audit_logs`
Security compliance ledger tracking administrative actions.
- `id` (INT, Primary Key, Auto Increment)
- `admin_user_id` (INT, Nullable)
- `action` (VARCHAR 50) — e.g. `'login'`, `'download_resume'`, `'status_change'`
- `target_type` (VARCHAR 50, Nullable) — e.g. `'job_applications'`, `'admin_users'`
- `target_id` (INT, Nullable)
- `details` (TEXT, Nullable)
- `ip_address` (VARCHAR 45, Nullable)
- `created_at` (DATETIME)

### 2.6 `system_settings`
Key-value configuration store for notification and security preferences.
- `setting_key` (VARCHAR 50, Primary Key)
- `setting_value` (TEXT, Nullable)
- `updated_at` (DATETIME)

### 2.7 `login_rate_limits`
Tracks failed login attempts by IP to block brute-force attacks (5 attempts / 15 minutes).
- `ip_address` (VARCHAR 45)
- `attempt_type` (VARCHAR 30) — e.g. `'admin_login'`
- `attempt_count` (INT)
- `first_attempt_at` (DATETIME)
- `last_attempt_at` (DATETIME)
- `locked_until` (DATETIME, Nullable)

---

## 3. Migration & Verification Commands

To run or re-verify database migration from the project root:

```bash
# Run migration from the repository root (safe, uses CREATE TABLE IF NOT EXISTS)
php scripts/migrate.php

# Check database tables, record counts and admin users
php scripts/check_status.php
```

### 3.1 Hostinger / Shared Hosting Import via phpMyAdmin
On shared hosting such as Hostinger, database creation is restricted by the hosting provider. Importing `database/schema.sql` will fail with `MySQL Error #1044 (Access denied for CREATE DATABASE)`.

**To import into Hostinger staging or production:**
1. Open phpMyAdmin from hPanel.
2. Select your designated database (e.g. `u981051719_mow_stg`) in the left sidebar.
3. Click the **Import** tab.
4. Select `database/schema_hostinger.sql` and click **Import**.
5. This builds all 7 core tables and default `system_settings` directly in the active database without running `CREATE DATABASE` or `USE`.

---

## 4. Dedicated Least-Privilege Database User Setup

For both local security and production readiness, **never connect your application as `root` with an empty password**. Always create a dedicated application user with access restricted strictly to `mowinton_db`:

```sql
-- Run in MySQL/MariaDB client:
CREATE USER 'mowinton_app'@'localhost' IDENTIFIED BY 'ChangeThisToYourSecurePassword!';
GRANT ALL PRIVILEGES ON mowinton_db.* TO 'mowinton_app'@'localhost';
FLUSH PRIVILEGES;
```

---

## 5. Environment Variables Reference

Create a `.env` file in the repository root by copying `.env.example`:

```ini
APP_ENV=local
APP_URL=http://127.0.0.1:8000

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=mowinton_db
DB_USER=mowinton_app
DB_PASS=ChangeThisToYourSecurePassword!
DB_CHARSET=utf8mb4

# Notifications
MAIL_TO=admin@mowinton.com
```

If `.env` is omitted, `includes/db.php` will default to `127.0.0.1:3306`, database `mowinton_db`, user `root`, empty password for quick local development testing.
