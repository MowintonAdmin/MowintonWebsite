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
  - CSRF token validation, honeypot protection, file upload validation (PDF, DOC, DOCX up to 5MB saved to `uploads/resumes/` with sanitized timestamped filenames), error handling, and JSON/POST responses (`includes/process_job_application.php`).
- [x] WhatsApp and social integrations tested.
- [x] Google Reviews carousel interactive slider (sliding animations, track positioning, mobile touch friendly) in `js/main.js`.
- [x] Verified zero exposure of real user or customer data.

## Phase 5 — Deployment and Handover
- [ ] Set up staging Hostinger Web Hosting environment separate from Website Builder production site.
- [ ] Configure GitHub deployment only after verifying hosting plan and safe target directory.
- [ ] Test deployment, backups, rollback, SSL, redirects and form delivery on Hostinger PHP environment.
- [ ] Obtain owner approval before changing live domain.
- [ ] Document maintenance workflow: Antigravity/VS Code -> GitHub -> staging -> production.

---

## Verification Evidence & Test Results
- **Full Route Crawl (14/14 Pages):** Tested all 14 pages on local PHP server `http://127.0.0.1:8000/`. All returned HTTP 200 with zero PHP errors, warnings, or notices.
- **Form Submissions Tested:**
  - Contact Form: Verified with `test_submit.py` -> HTTP 200, JSON success message.
  - Job Application Form: Verified with `test_job_submit.py` -> HTTP 200, resume file safely validated and stored in `uploads/resumes/`.
- **Visual Benchmarking & Screenshots:** Captured full desktop and mobile screenshots for all reconstructed pages in `scratch/`.
