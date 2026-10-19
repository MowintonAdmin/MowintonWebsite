# Mowinton Consulting Website — Reconstruction & Development Guide

A clean, production-grade reconstruction of [https://mowinton.com/](https://mowinton.com/) built with modern semantic HTML5, responsive CSS3, vanilla JavaScript, and plain PHP 8+ with MySQL / MariaDB database persistence.

---

## Table of Contents
1. [Project Overview](#1-project-overview)
2. [System Requirements](#2-system-requirements)
3. [Clone the Repository](#3-clone-the-repository)
4. [Install & Configure Dependencies](#4-install--configure-dependencies)
5. [Environment Configuration](#5-environment-configuration)
6. [Database Setup & Migration](#6-database-setup--migration)
7. [Create Administrator Account](#7-create-administrator-account)
8. [Run the Website Locally (with Security Router)](#8-run-the-website-locally-with-security-router)
9. [Verification & Testing Checklist](#9-verification--testing-checklist)
10. [Project Directory Structure](#10-project-directory-structure)
11. [Git & GitHub Collaboration Workflow](#11-git--github-collaboration-workflow)
12. [Hostinger Production Deployment Guide (Staging, Backups & Rollback)](#12-hostinger-production-deployment-guide-staging-backups--rollback)
13. [Troubleshooting Guide](#13-troubleshooting-guide)
14. [Security Architecture & Guidelines](#14-security-architecture--guidelines)
15. [Documentation Index](#15-documentation-index)

---

## 1. Project Overview

### Purpose
This repository reconstructs the complete corporate website of **Mowinton Consulting** from the ground up as a maintainable, self-hosted source-code project. It eliminates dependence on proprietary website builder platforms while preserving 100% of the original visual design, layout structure, responsive behaviors, and functional interactions.

### Implemented Features
- **Public Corporate Website (14 Pages):**
  - **Homepage (`/`):** Hero section, Professional IT Services cards, About Mowinton statistics, Industry Expertise (6 sectors), Contact dual-card form, and global footer.
  - **About Us (`/about/`):** Company capabilities, Google Maps office embed, accordion FAQs, and Lark consultation booking widget.
  - **Services (`/services/`):** IT consulting, Workforce Solutions 3-card grid, Tailored Solutions, interactive Google Reviews carousel (57 client reviews), and inquiry form.
  - **Careers Portal (`/careers-2/`):** Culture highlights, company perks, 6 active job openings, and embedded job application form.
  - **Internship Program (`/internship-program/`):** Student program overview, benefits, 6 internship disciplines, 5-step application process, and application CTA.
  - **Interview Preparation Guide (`/interview-guide/`):** 5 timeline preparation step cards with illustration placeholders, common interview questions, and tips.
  - **Submit Job Application (`/submit-job-application/`):** Dedicated applicant portal with URL position pre-filling and resume upload.
  - **News & Insights (`/news-insights/`):** Industry thought-leadership articles and expert consultation booking CTA.
  - **6 Dedicated Job Description Pages:**
    - Project Manager (`/project-manager-jd/`)
    - Software Engineer (`/software-engineer-jd/`)
    - Technical Lead (`/technical-lead/`)
    - QA Engineer (`/qa-engineer-jd/`)
    - Business Analyst (`/business-analyst-jd/`)
    - Intern – Digital Marketing (`/intern-digital-marketing/`)
- **Backend Functionality (PHP 8 + MySQL PDO):**
  - Contact Form validation, anti-spam honeypot, CSRF token verification, and persistence in `contact_messages`.
  - Job & Internship application processor with binary file validation (PDF, DOC, DOCX up to 10MB), automatic Reference ID generation (`MOW-YYYYMM-XXXXXX`), and persistence in `job_applications`.
  - Secure resume file storage with direct HTTP access blocked (HTTP 403 Forbidden).
- **Administrative Management Portal (`/admin/`):**
  - Secure authentication with Bcrypt password hashing (`password_hash`), 30-minute session inactivity expiration, and login rate limiting (5 attempts / 15 minutes).
  - Role-based authorization (`super_admin` vs `staff`).
  - Real-time Dashboard Overview querying database metrics via PDO prepared statements.
  - Contact Messages manager with status toggles (`new`, `read`, `resolved`) and search/filtering.
  - Job Applications manager with candidate review, interview status workflow, and internal reviewer notes.
  - Authenticated CV streaming endpoint (`/admin/download-resume.php`) with directory traversal protection and audit logging.
  - System settings and staff account provisioning panel.

### Technology Stack
| Layer | Technologies |
| :--- | :--- |
| **Backend & Routing** | Pure PHP 8.0+ (No Laravel, No WordPress, No heavy frameworks) |
| **Database** | MySQL 5.7+ / 8.0+ or MariaDB 10.3+ / 13.0+ |
| **Frontend Structure** | Semantic HTML5 |
| **Styling** | Modern CSS3 (CSS Variables, Flexbox, CSS Grid, clamp() fluid typography) |
| **Interactivity** | Vanilla JavaScript (ES6+, zero third-party framework dependencies) |

---

## 2. System Requirements

Before setting up the project locally, verify that your environment meets these requirements:

### Operating System
- Windows 10 / 11, macOS 12+, or modern Linux (Ubuntu 20.04+, Debian 11+, CentOS 8+)

### Software Prerequisites
- **Git:** Git 2.30+ installed.
- **PHP:** PHP 8.0, 8.1, 8.2, or 8.3 (tested on PHP 8.3.16).
- **Database Server:** MariaDB Server 10.3+ / 13.0+ or Oracle MySQL 5.7+ / 8.0+.
- **Composer:** **Not required.** The project is built with zero external third-party PHP packages for maximum portability.

### Required PHP Extensions
The following PHP extensions must be enabled in your `php.ini`:
- `pdo_mysql` — Database connection via PDO prepared statements.
- `fileinfo` — Server-side MIME type verification for uploaded resumes.
- `curl` — HTTP client utility functions.
- `mbstring` — Multi-byte character string handling.
- `openssl` — Cryptographically secure token generation.

---

## 3. Clone the Repository

### Step 1: Open Terminal or Shell
Open PowerShell / Command Prompt on Windows, or Terminal on macOS/Linux.

### Step 2: Clone the Project
```bash
git clone https://github.com/<your-username>/MowintonWebsite.git
cd MowintonWebsite
```

### Step 3: Open in Your Code Editor
Open the repository in your preferred editor (VS Code, Google Antigravity, or PhpStorm):
```bash
code .
```

### Step 4: Navigate to the Website Application Folder
The root repository contains the overall project. The runnable web application lives inside `mowinton-website-starter/mowinton-website`:
```bash
cd mowinton-website-starter/mowinton-website
```
*(All remaining commands in this guide are executed from inside this directory.)*

---

## 4. Install & Configure Dependencies

### 4.1 Verify PHP and Active Extensions
Confirm that PHP is accessible from your terminal:
```bash
php -v
```

Check that required extensions are active:
```bash
php -m | grep -E "pdo_mysql|fileinfo|curl|mbstring|openssl"
```
*(On Windows PowerShell, replace `grep -E` with `Select-String`):*
```powershell
php -m | Select-String "pdo_mysql", "fileinfo", "curl", "mbstring", "openssl"
```

### 4.2 How to Enable Missing PHP Extensions
If any extension is missing, locate your active `php.ini` file:
```bash
php --ini
```
Open `php.ini` in a text editor and uncomment the corresponding extension lines (remove the leading `;`):
```ini
extension=pdo_mysql
extension=fileinfo
extension=curl
extension=mbstring
extension=openssl
```
Save the file and verify with `php -m`.

---

## 5. Environment Configuration

### Step 1: Create a Dedicated Least-Privilege Database User
For both local development and production security, **never configure your application to use the `root` administrative account with an empty password**.

Open your MySQL or MariaDB command client and run:
```sql
-- 1. Create the database
CREATE DATABASE IF NOT EXISTS `mowinton_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Create a dedicated application user
CREATE USER 'mowinton_app'@'localhost' IDENTIFIED BY 'ChangeThisToYourSecurePassword!';

-- 3. Grant access strictly to mowinton_db
GRANT ALL PRIVILEGES ON `mowinton_db`.* TO 'mowinton_app'@'localhost';
FLUSH PRIVILEGES;
```

### Step 2: Create Your Local `.env` File
In `mowinton-website-starter/mowinton-website/`, copy `.env.example` to create `.env`:

**Windows (PowerShell):**
```powershell
Copy-Item .env.example .env
```

**macOS / Linux:**
```bash
cp .env.example .env
```

### Step 3: Configure Environment Variables
Open `.env` in your editor and configure your dedicated credentials:

```ini
# Application Environment (local or production)
APP_ENV=local
APP_URL=http://127.0.0.1:8000

# Database Connection (Dedicated Application User)
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=mowinton_db
DB_USER=mowinton_app
DB_PASS=ChangeThisToYourSecurePassword!
DB_CHARSET=utf8mb4

# Administrative & Notification Settings (Optional)
MAIL_TO=admin@mowinton.com
```

| Variable | Description | Recommended Setting | Required? |
| :--- | :--- | :--- | :---: |
| `APP_ENV` | Application environment (`local` or `production`) | `local` | Recommended |
| `APP_URL` | Base canonical URL of the application | `http://127.0.0.1:8000` | Recommended |
| `DB_HOST` | Database server hostname or IP | `127.0.0.1` | **Yes** |
| `DB_PORT` | Database server port | `3306` | **Yes** |
| `DB_NAME` | Database schema name | `mowinton_db` | **Yes** |
| `DB_USER` | Dedicated application user | `mowinton_app` | **Yes** |
| `DB_PASS` | Application user password | *(Your chosen password)* | **Yes** |
| `DB_CHARSET`| Database character set | `utf8mb4` | **Yes** |
| `MAIL_TO` | Notification recipient email | `admin@mowinton.com` | Optional |

---

## 6. Database Setup & Migration

### Step 1: Start Your MySQL or MariaDB Service
- **Standalone MariaDB / MySQL (Windows):** Ensure service is running, or launch via Service Manager.
- **XAMPP / WampServer:** Open the control panel and click **Start** next to MySQL.
- **macOS (Homebrew):** `brew services start mariadb` or `brew services start mysql`
- **Linux:** `sudo systemctl start mariadb` or `sudo systemctl start mysql`

### Step 2: Run Automated Migration Script
Execute the project's migration script from `mowinton-website-starter/mowinton-website/`:
```bash
php scripts/migrate.php
```

#### What `migrate.php` Does:
1. Connects to your database server using credentials from `.env`.
2. Verifies the existence of `mowinton_db`.
3. Executes `database/schema.sql` safely (`CREATE TABLE IF NOT EXISTS`).
4. Verifies the existence of all 7 core tables and reports table row counts.

### Step 3: Verify Database Status
Run the health check utility:
```bash
php scripts/check_status.php
```
You should see:
```text
1. MariaDB Connection: SUCCESSFUL (Database: mowinton_db)
2. Tables in Database (7):
   - admin_users
   - application_notes
   - audit_logs
   - contact_messages
   - job_applications
   - login_rate_limits
   - system_settings
```

---

## 7. Create Administrator Account

Because Mowinton Consulting uses a strictly protected administrative area, **there is no public registration form**. Initial administrative accounts are provisioned securely via CLI.

### Run Account Creation Tool Interactively
```bash
php scripts/create_admin.php
```

### Interactive Prompts:
1. **Admin Username:** Enter desired username (e.g., `admin`).
2. **Admin Email:** Enter valid email (e.g., `admin@mowinton.com`).
3. **Full Name:** Enter reviewer display name (e.g., `Site Administrator`).
4. **Select Role:** Enter `1` for `super_admin` (Full control) or `2` for `staff` (Reviewer).
5. **Password:** Type a secure password (minimum 8 characters).

> [!IMPORTANT]
> **Password Privacy Notice:** Always enter your administrator password interactively. Avoid passing passwords as command-line arguments, as CLI flags leave sensitive passwords in cleartext within terminal history files (`.bash_history`, `ConsoleHost_history.txt`) and operating system process listings.

The script hashes the password using PHP's standard Bcrypt algorithm (`PASSWORD_DEFAULT`) and writes the record to `admin_users`.

---

## 8. Run the Website Locally (with Security Router)

### Why Use `router.php`?
PHP's built-in development server (`php -S`) does not read or enforce Apache/LiteSpeed `.htaccess` rules. Without a router script, requests for files on disk (such as uploaded CVs) could be served statically. 

To guarantee that private resume uploads and internal scripts are **100% blocked with HTTP 403 Forbidden** during local development just as they are in production, always launch the development server with the included `router.php`.

### Step 1: Launch Development Server
From `mowinton-website-starter/mowinton-website/`, run:
```bash
php -S 127.0.0.1:8000 router.php
```

### Step 2: Access Endpoints in Browser
- **Public Homepage:** [http://127.0.0.1:8000/](http://127.0.0.1:8000/)
- **Services Page:** [http://127.0.0.1:8000/services/](http://127.0.0.1:8000/services/)
- **Careers Portal:** [http://127.0.0.1:8000/careers-2/](http://127.0.0.1:8000/careers-2/)
- **Job Application Form:** [http://127.0.0.1:8000/submit-job-application/](http://127.0.0.1:8000/submit-job-application/)
- **Admin Login:** [http://127.0.0.1:8000/admin/login.php](http://127.0.0.1:8000/admin/login.php)
- **Admin Dashboard:** [http://127.0.0.1:8000/admin/](http://127.0.0.1:8000/admin/)

### Step 3: Verify Router Security Block
Open an incognito browser tab or terminal and test direct file access:
```bash
curl -I http://127.0.0.1:8000/uploads/resumes/
curl -I http://127.0.0.1:8000/.env
```
*(Both requests will return **HTTP/1.1 403 Forbidden**).*

### Step 4: Stopping the Server
Press `Ctrl + C` in the terminal to cleanly terminate the server process.

---

## 9. Verification & Testing Checklist

After starting the server, verify the complete workflow using synthetic test data:

### 1. Public Site & Navigation Check
- [ ] Visit `http://127.0.0.1:8000/` and verify header logo, typography, hero banner, and footer.
- [ ] Test the **Careers** dropdown menu and mobile navigation drawer.
- [ ] Visit public pages (`/about/`, `/services/`, `/interview-guide/`, `/internship-program/`, `/news-insights/`).

### 2. Contact Form Verification
- [ ] Scroll to the Contact section on `http://127.0.0.1:8000/#contact-us`.
- [ ] Fill out the form with synthetic data (Name: `Alex Tan`, Email: `alex@example.com`, Subject: `Consulting Inquiry`).
- [ ] Click **Send Message** and confirm the green success notice appears.

### 3. Admin Inquiries Verification
- [ ] Log in at `http://127.0.0.1:8000/admin/login.php` with your administrator credentials.
- [ ] Open **Contact Messages** (`/admin/messages.php`).
- [ ] Confirm Alex Tan's submission appears with a blue **NEW** badge.
- [ ] Click **View** to inspect details, and **Mark Resolved** to update its status.

### 4. Job Application & Resume Streaming Verification
- [ ] Visit `http://127.0.0.1:8000/submit-job-application/?job=Operations%20Manager`.
- [ ] Confirm position is automatically pre-filled.
- [ ] Fill candidate fields and attach a sample `.pdf` file.
- [ ] Submit and note the generated Reference ID (e.g., `MOW-YYYYMM-XXXXXX`).
- [ ] Open **Job Applications** (`/admin/applications.php`) and click **View Details**.
- [ ] Click **Download Resume** to stream the PDF securely.
- [ ] Try direct URL access to the file path — verify it returns **403 Forbidden**.
- [ ] Add an internal reviewer note and change candidate status to **Shortlisted**.

### 5. Automated Regression Test Suite
Run the automated Python end-to-end verification suite:
```bash
python scripts/test_admin_e2e.py
```
*(All 26 automated tests will execute and confirm 100% success).*

---

## 10. Project Directory Structure

```text
MowintonWebsite/
├── .gitignore                                 # Git ignore rules for root repository
├── .env.example                               # Root environment configuration template
├── README.md                                  # Master repository documentation (this file)
├── img/
│   ├── mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg # Official Mowinton logo asset
│   └── mowinton-consulting-AGBbZjJ9kpuxp8Gb.avif
└── mowinton-website-starter/
    └── mowinton-website/                      # Web application document root
        ├── router.php                         # Local development server security router
        ├── index.php                          # Reconstructed Homepage
        ├── about/index.php                    # About Us, Google Maps embed & FAQs
        ├── services/index.php                 # Services & Google Reviews carousel
        ├── careers-2/index.php                # Careers listing & application form
        ├── internship-program/index.php       # Internship Program & roadmap
        ├── interview-guide/index.php          # Interview Preparation Guide
        ├── submit-job-application/index.php   # Standalone Job Application portal
        ├── news-insights/index.php            # Thought leadership articles
        ├── project-manager-jd/index.php       # Project Manager JD
        ├── software-engineer-jd/index.php     # Software Engineer JD
        ├── technical-lead/index.php           # Technical Lead JD
        ├── qa-engineer-jd/index.php           # QA Engineer JD
        ├── business-analyst-jd/index.php      # Business Analyst JD
        ├── intern-digital-marketing/index.php # Intern – Digital Marketing JD
        ├── admin/                             # Protected Admin Dashboard Portal
        │   ├── index.php                      # Dashboard Overview metrics
        │   ├── login.php                      # Secure login interface
        │   ├── logout.php                     # Session termination endpoint
        │   ├── messages.php                   # Contact inquiries management
        │   ├── applications.php               # Job applications review list
        │   ├── application-details.php        # Applicant file, notes & status
        │   ├── settings.php                   # Staff accounts & audit logs
        │   ├── download-resume.php            # Authenticated CV streaming
        │   ├── admin-header.php               # Admin navigation & sidebar
        │   └── admin-footer.php               # Admin scripts & modals
        ├── includes/                          # Shared PHP Backend Components
        │   ├── db.php                         # PDO database connection & logger
        │   ├── auth.php                       # Session, RBAC, CSRF & rate limiter
        │   ├── header.php                     # Public site global header
        │   ├── footer.php                     # Public site global footer
        │   ├── process_contact.php            # Contact form handler
        │   └── process_job_application.php    # Job application & upload handler
        ├── database/
        │   └── schema.sql                     # Full MySQL schema definitions (7 tables)
        ├── scripts/                           # CLI Management & Test Utilities
        │   ├── migrate.php                    # Safe database migration runner
        │   ├── create_admin.php               # CLI administrator account creator
        │   ├── check_status.php               # Health check for DB & admin users
        │   └── test_admin_e2e.py              # 26 automated end-to-end tests
        ├── css/
        │   ├── style.css                      # Master public stylesheet
        │   └── admin.css                      # Admin dashboard stylesheet
        ├── js/
        │   └── main.js                        # Navigation, mobile drawer, reviews carousel
        ├── img/
        │   ├── mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg # In-app logo reference
        │   └── placeholders/                  # Neutral SVG placeholders (22 assets)
        ├── uploads/
        │   └── resumes/                       # Private CV storage directory
        │       ├── .htaccess                  # Apache / LiteSpeed 403 Forbidden block
        │       └── index.php                  # Fallback 403 Forbidden header
        ├── .env.example                       # Application environment template
        ├── .gitignore                         # Application-level git ignore rules
        ├── ADMIN_GUIDE.md                     # Administrator operations manual
        ├── BACKEND_SETUP.md                   # Database schema & technical guide
        ├── IMAGE_REPLACEMENT_GUIDE.md         # Image replacement inventory
        ├── VISUAL_REVIEW_NOTES.md             # Visual & responsive review notes
        ├── PHASE6_TEST_REPORT.md              # Test execution evidence report
        ├── PROJECT_REQUIREMENTS.md            # Reconstruction project specifications
        └── TASKS.md                           # Development & verification checklist
```

---

## 11. Git & GitHub Collaboration Workflow

To contribute or maintain this codebase safely, follow this standard Git workflow:

### Step 1: Create a Feature Branch
Never commit directly to `main`:
```bash
git checkout -b feature/your-feature-name
```

### Step 2: Make Changes & Test Locally
Before committing, verify that all automated regression tests pass:
```bash
python scripts/test_admin_e2e.py
```

### Step 3: Check Status & Stage Files
```bash
git status
git add <specific-files>
```

### Step 4: Commit with Clear Messages
```bash
git commit -m "Brief description of changes made"
```

### Files That Must NEVER Be Committed:
The `.gitignore` is pre-configured to strictly exclude sensitive assets. Ensure you never commit:
- `.env` files containing live credentials.
- Uploaded candidate CVs (`uploads/resumes/*.pdf`, `*.docx`).
- Production database SQL dumps.
- Server error logs or debug transcripts.

---

## 12. Hostinger Production Deployment Guide (Staging, Backups & Rollback)

When transitioning from local development to **Hostinger PHP Web Hosting**, follow this deployment plan:

> [!IMPORTANT]
> Production deployment must be explicitly scheduled and approved. Do not modify the live website until staging testing is complete.

### Step 1: Verify Hostinger PHP Configuration
1. Log in to **Hostinger hPanel**.
2. Navigate to **Advanced > PHP Configuration**.
3. Select **PHP 8.1, 8.2, or 8.3**.
4. Confirm extensions `pdo_mysql`, `fileinfo`, `curl`, `mbstring`, and `openssl` are enabled.

### Step 2: Set Up Staging Subdomain First
Before touching production, create a staging subdomain in hPanel (e.g., `staging.mowinton.com`):
1. Create a dedicated staging MySQL database and application user.
2. Deploy the code to `staging.mowinton.com` and run the verification checklist.
3. Validate forms, resume uploads, and admin authentication in the staging environment.

### Step 3: Pre-Deployment Backup Strategy
Before performing live cutover on production:
1. **Database Backup:** In hPanel, go to **Databases > phpMyAdmin**, select database, and click **Export** (or run `mysqldump`). Save the SQL file locally.
2. **File System Snapshot:** In hPanel **File Manager**, select the existing `public_html/` folder and create a ZIP archive named `backup_pre_deploy_YYYYMMDD.zip`.

### Step 4: Production Rollback Procedure
If any fatal issue occurs during production cutover, execute this immediate rollback:
1. Delete the failed files from `public_html/`.
2. Extract `backup_pre_deploy_YYYYMMDD.zip` back into `public_html/`.
3. In phpMyAdmin, drop the tables and import the pre-deployment database SQL backup.
4. Verify the site restores to its previous operational state.

### Step 5: Protect Internal Files via Root `.htaccess`
Hostinger uses **LiteSpeed Web Server**, which natively parses and enforces Apache `.htaccess` directives. Create or verify `.htaccess` in `public_html/` to prevent external access to internal files and directories:

```apache
# Block direct access to configuration and migration files
<FilesMatch "^\.env|schema\.sql">
    Require all denied
</FilesMatch>

# Block direct access to internal utility directories
RedirectMatch 403 ^/(scripts|database|includes)/
```

### Step 6: Import Database Schema & Configure Credentials
1. Open **phpMyAdmin** for your production database.
2. Import `database/schema.sql`.
3. Create `.env` in `public_html/` with production database credentials.

### Step 7: Directory Permissions & Private Resume Storage
1. Ensure `uploads/resumes/` permission is set to `755` (writable by PHP).
2. Confirm `uploads/resumes/.htaccess` contains `Require all denied`.
3. *Alternative Production Pattern:* You may also move the resume storage directory one level above `public_html/` (e.g., `../storage/resumes/`) and define its path in `.env` (`RESUME_STORAGE_PATH=../storage/resumes/`) so resumes reside physically outside the web root.

### Step 8: Provision Production Super Admin
Run `scripts/create_admin.php` via SSH command line to create the production Super Administrator account interactively.

### Step 9: Enable SSL / HTTPS
1. In hPanel, activate **Let's Encrypt SSL**.
2. Enable **Force HTTPS** in hPanel.

---

## 13. Troubleshooting Guide

| Issue / Error | Probable Cause | Solution |
| :--- | :--- | :--- |
| **`php: command not found`** | PHP binary is not in system PATH | Add PHP directory (e.g., `C:\php` or `/usr/bin/php`) to system Environment Variables. |
| **`could not find driver`** | `pdo_mysql` extension disabled | Open `php.ini`, remove the `;` before `extension=pdo_mysql`, and restart terminal. |
| **`Connection refused [2002]`** | MariaDB/MySQL is not running | Start your MariaDB or MySQL service (or start MySQL in XAMPP control panel). |
| **`Access denied for user`** | Incorrect username or password | Check `.env` and verify `DB_USER` and `DB_PASS` match your local MySQL configuration. |
| **`Base table or view not found`** | Migration has not been executed | Run `php scripts/migrate.php` to initialize the database schema. |
| **`Failed to open stream`** | Running command from wrong directory | Always run commands from `mowinton-website-starter/mowinton-website/`. |
| **`Address already in use: 8000`**| Another process using port 8000 | Run on another port: `php -S 127.0.0.1:8080 router.php`. |
| **`File upload failed`** | Permissions or file size limit | Ensure `uploads/resumes/` exists and is writable (`chmod 755`). Check `upload_max_filesize` in `php.ini`. |
| **`403 Forbidden` on resume** | Expected security behavior | Resumes cannot be accessed via direct URL. Download via Admin Details page. |

---

## 14. Security Architecture & Guidelines

1. **Defense in Depth Authentication:**
   - Password hashing uses standard PHP `password_hash()` with auto-salting Bcrypt.
   - Session identifiers are regenerated upon login (`session_regenerate_id(true)`).
   - Inactivity timeout automatically invalidates sessions after 30 minutes.
   - HttpOnly and SameSite (`Strict`) cookie flags prevent client-side script interception.
2. **Brute-Force Rate Limiting:**
   - Failed administrator login attempts are logged by IP in `login_rate_limits`.
   - Exceeding 5 failures within 15 minutes triggers a temporary IP block.
3. **Cross-Site Request Forgery (CSRF):**
   - Every public and administrative form embeds a cryptographically random token verified on POST submission.
4. **Anti-Spam Honeypots:**
   - Public forms contain hidden honeypot fields that trap automated spam bots without burdening genuine users.
5. **Private Resume Storage (Local & Production):**
   - Uploaded applicant CVs are stored in `uploads/resumes/`.
   - In local development, `router.php` actively intercepts and returns **403 Forbidden** on any direct requests to `/uploads/resumes/` (including direct `.pdf` filenames).
   - In production, LiteSpeed/Apache `.htaccess` enforces `Require all denied`, and storage can also be configured outside `public_html/`.
   - Resumes are served exclusively through the authenticated streaming controller (`/admin/download-resume.php`), which prevents directory traversal attacks and records download events in `audit_logs`.
6. **SQL Injection Defense:**
   - 100% of database interactions use PDO prepared statements with bound parameters. Zero dynamic string concatenation in SQL queries.

---

## 15. Documentation Index

The following project manuals are available within the repository:
- **Admin Operations Manual:** [`ADMIN_GUIDE.md`](mowinton-website-starter/mowinton-website/ADMIN_GUIDE.md)
- **Database & Backend Architecture Guide:** [`BACKEND_SETUP.md`](mowinton-website-starter/mowinton-website/BACKEND_SETUP.md)
- **Image Replacement Specifications:** [`IMAGE_REPLACEMENT_GUIDE.md`](mowinton-website-starter/mowinton-website/IMAGE_REPLACEMENT_GUIDE.md)
- **Visual & Typography Review Notes:** [`VISUAL_REVIEW_NOTES.md`](mowinton-website-starter/mowinton-website/VISUAL_REVIEW_NOTES.md)
- **Phase 6 Verification Report:** [`PHASE6_TEST_REPORT.md`](mowinton-website-starter/mowinton-website/PHASE6_TEST_REPORT.md)
- **Project Requirements & Scope:** [`PROJECT_REQUIREMENTS.md`](mowinton-website-starter/mowinton-website/PROJECT_REQUIREMENTS.md)
- **Task Verification Checklist:** [`TASKS.md`](mowinton-website-starter/mowinton-website/TASKS.md)
