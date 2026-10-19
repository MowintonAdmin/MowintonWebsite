# Mowinton Website Rebuild — Tasks

Status: [ ] todo, [x] verified complete. Do not mark complete merely because code was generated.

## Phase 0 — Discovery and safe setup
- [x] Confirm company authorization, original assets and access to Website Builder settings.
- [x] Capture desktop and mobile screenshots of original pages for visual benchmarking.
- [x] Inventory all pages, URLs, menus, links, images, fonts and reusable UI components.
- [x] Document form fields, recipients, validation, upload destinations, booking links and third-party integrations (without copying private submissions).
- [x] Identify any functionality inaccessible from the public site and flag NEEDS CONFIRMATION.
- [x] Initialize local PHP project, README, .gitignore, .env.example, and directory structure (`includes/`, `css/`, `js/`, `img/placeholders/`).

## Phase 1 — Shared design system and homepage
- [x] Implement reusable header, desktop navigation, dropdown menus, mobile navigation drawer, and footer.
- [x] Establish original typography (Poppins, Inter), brand colors, spacing, buttons, logo asset, image placeholders, and responsive breakpoints.
- [x] Recreate all homepage sections in exact original order with matching copy:
  - Hero section: IT Consulting & Talent Solutions, heading, copy, CTA buttons, circular hero image with accent circle
  - Professional IT Services: Technical Consulting, IT Outsourcing, AI Solutions Integration cards
  - About Mowinton: badge, heading, narrative copy, 3 stat cards (80+, $100M+, 13), team image
  - Industry Expertise: header, 6 industry cards (Banking, Healthcare, Manufacturing, Semiconductor, Telecommunications, Security)
  - Contact Section: dual-card layout (dark info card with highlights & icons; light inquiry card with contact form)
  - Footer: About Us address, Contacts Us hours/email/phone, social media links (Facebook, Instagram, WhatsApp, LinkedIn)
- [x] Check homepage at desktop, tablet, and mobile widths (1280px, 1024px, 768px, 480px, 375px) against headless screenshots.
- [x] Side-by-side visual comparison verified; matched typography, spacing, colors, input styling, and component alignment.

## Phase 2 / Phase 4 — Reconstruct All Remaining Pages
- [x] About Us, FAQ & Lark consultation scheduler (`/about/`):
  - Verified Google Maps iframe integration, office address card, expandable accordion FAQs, and Lark booking widget.
- [x] Services page (`/services/`):
  - Verified dark gradient box ("Empowering Business Through Technology"), Workforce Solutions 2x2 grid, Tailored IT Solutions 3-card grid, Google Reviews interactive carousel (57 reviews, 5.0 score, next/prev slide controls), and dual-card contact form.
- [x] Careers page (`/careers-2/`):
  - Verified hero card ("Join Our Team") with placeholder `careers-team-group.svg`, 4 Perk cards, 6 Job Openings grid with `careers-work-culture.svg` culture card, and embedded job application form.
- [x] Standalone Job Application (`/submit-job-application/`):
  - Reconstructed with `#ece7de` warm background, dual-card info & form, position autofill query parameter, and secure resume file upload.
- [x] Internship Program (`/internship-program/`):
  - Reconstructed with hero card ("Kickstart Your Career With Mowinton"), 4 Benefit cards, 6 Internship Area cards, 5 Application Process cards, and CTA apply card.
- [x] Interview Guide (`/interview-guide/`):
  - Reconstructed with 5 timeline step cards with SVG placeholders (`interview-office-prep.svg`, `interview-resume-prep.svg`, `interview-practice.svg`, `interview-professional.svg`, `interview-followup.svg`), 4 common interview questions cards, and careers CTA card.
- [x] News & Insights (`/news-insights/`):
  - Reconstructed with 3 article cards (*Digital Transformation*, *Project Delivery*, *Technology Roadmap*) and "Need Expert Advice?" Lark consultation booking CTA.
- [x] All 6 Job Description (JD) Pages:
  - [x] Project Manager (`/project-manager-jd/`)
  - [x] Software Engineer (`/software-engineer-jd/`)
  - [x] Technical Lead (`/technical-lead/`)
  - [x] QA Engineer (`/qa-engineer-jd/`)
  - [x] Business Analyst (`/business-analyst-jd/`)
  - [x] Intern – Digital Marketing (`/intern-digital-marketing/`)
- [x] Validate original URL structure, directory paths, and clean `$root_path` linking across all 14 pages.

## Phase 3 — Functional Implementation
- [x] Contact/inquiry form (`index.php`, `about/index.php`, `services/index.php`):
  - CSRF protection, honeypot anti-spam, required field validation, and AJAX/standard POST submission handling (`includes/process_contact.php`).
- [x] Job Application form & file upload handling (`careers-2/index.php`, `submit-job-application/index.php`):
  - CSRF token validation, honeypot protection, file upload validation (PDF, DOC, DOCX up to 10MB saved to `uploads/resumes/` with sanitized timestamped filenames), error handling, and JSON/POST responses (`includes/process_job_application.php`).
- [x] WhatsApp and social integrations tested.
- [x] Google Reviews carousel interactive slider (sliding animations, track positioning, mobile touch friendly) in `js/main.js`.
- [x] Verified zero exposure of real user or customer data.

## Phase 5 — Backend Database Integration & Admin Management System
- [x] MariaDB 13.0 database schema designed and migrated with 7 tables (`admin_users`, `contact_messages`, `job_applications`, `application_notes`, `audit_logs`, `system_settings`, `login_rate_limits`).
- [x] Super Admin and Staff account provisioning via secure CLI script (`scripts/create_admin.php`).
- [x] Admin authentication system: Bcrypt password hashing, session timeout (30m), strict cookies, login rate limiting (5 attempts / 15m), CSRF tokens.
- [x] Admin Dashboard overview (`/admin/`): Real-time metrics queried from MySQL via PDO prepared statements.
- [x] Customer inquiries management (`/admin/messages.php`): Filter by status, search, modal view, mark read/resolved, Super Admin delete.
- [x] Candidate applications management (`/admin/applications.php` & `/admin/application-details.php`): Search, status workflow, internal notes log, Job vs Internship tabs.
- [x] Secure CV storage & streaming download (`/admin/download-resume.php`): Direct HTTP access blocked (403 Forbidden), authenticated streaming with audit logging.
- [x] End-to-end automated test suite (`scripts/test_admin_e2e.py`): 26 / 26 tests passed (100% success).

## Phase 6 — Final UI Refinement, Responsiveness & Optimization
- [x] Git checkpoint created locally (commit `b5858c8`).
- [x] Standardized typography variables in CSS (`--font-family-primary`, fluid `clamp()` heading scales, line heights, letter spacing).
- [x] Responsive layout audit across 14 pages × 6 viewport sizes (375px, 390px, 768px, 1024px, 1440px, 1920px) — 84 / 84 tests passed with 0 horizontal scroll issues.
- [x] Admin dashboard UI optimization: touch-friendly table scrolling (`-webkit-overflow-scrolling: touch`), accessible `:focus-visible` indicators.
- [x] Backend regression testing executed: 26 / 26 Phase 5 tests re-verified with 100% success.
- [x] Documentation updated: `VISUAL_REVIEW_NOTES.md`, `PHASE6_TEST_REPORT.md`, `IMAGE_REPLACEMENT_GUIDE.md`, `PROJECT_REQUIREMENTS.md`.

## Phase 7 — Production Deployment & Handover (On User Approval)
- [ ] Set up staging Hostinger Web Hosting environment separate from Website Builder production site.
- [ ] Configure deployment only after verifying hosting plan and safe target directory.
- [ ] Test deployment, database import, SSL, redirects, and file permissions (755 for `uploads/resumes/`) on Hostinger.
- [ ] Obtain owner approval before changing live domain.
- [ ] Document production maintenance workflow.

---

## Verification Evidence & Test Results
- **Responsive Viewport Audit (84/84 Tests Passed):** Tested all 14 pages on 375px, 390px, 768px, 1024px, 1440px, and 1920px. 0 element overflow culprits.
- **Backend Regression Suite (26/26 Tests Passed):** All authentication, session, CSRF, database persistence, status flow, notes, and secure resume download tests passed.
- **Full Route Crawl (14/14 Public Pages + 5 Admin Pages):** Verified with zero PHP errors, notices, or warnings.
- **Visual Benchmarking:** Complete placeholder catalog in `IMAGE_REPLACEMENT_GUIDE.md` and audit notes in `VISUAL_REVIEW_NOTES.md`.
