# Mowinton Website Rebuild — Project Requirements

## Goal
Rebuild https://mowinton.com/ as a maintainable source-code project, visually and functionally matching the existing published website as closely as possible. Keep the live Website Builder site unchanged until a tested replacement is approved.

## Technology
- PHP 8+ (plain PHP, no Laravel), HTML5, CSS3, vanilla JavaScript.
- Reusable PHP templates/partials for navigation, footer, SEO and page layouts.
- Responsive layouts for desktop, tablet and mobile.
- GitHub private repository; later deployment to compatible Hostinger Web Hosting (not Website Builder).
- Database and email services only if required after feature audit.

## Source of truth and limitations
- Primary reference: https://mowinton.com/ and its accessible internal pages.
- Collect desktop/mobile screenshots, original logos and media, fonts, color values, menus, links, form configurations and any custom code from authorized Hostinger access.
- Published HTML is not the Website Builder source project. Do not assume hidden workflows, submissions or integrations can be recovered from the public site.
- When an asset or behavior is uncertain, record it as TODO / NEEDS CONFIRMATION rather than inventing it.
- Preserve text, layout, hierarchy, image cropping, spacing, typography and interactions as faithfully as practical. Avoid arbitrary redesign.

## Initial page inventory (verify and expand)
- Home: / — hero, IT services, about/company metrics, industry expertise, contact section.
- Services: /services/ — consulting, outsourcing, AI integration, workforce solutions.
- About: /about/ — company introduction, capabilities, office/contact, FAQ and consultation.
- Careers: /careers-2/ — opportunities, job cards, application form and CV upload.
- Internship: /internship-program/ — benefits, internship areas and application flow.
- Interview guide: /interview-guide/ — preparation steps and FAQs.
- Job detail pages: e.g. /project-manager-jd/, /software-engineer-jd/, /technical-lead/, /qa-engineer-jd/, /business-analyst-jd/, /intern-digital-marketing/.
- News & Insights and other navigation targets: discover and inventory before implementing.
- Preserve URL slugs where feasible; add redirects if changed.

## Functionality requirements
1. All navigation links, dropdowns, buttons, footer links, anchor links and mobile menu must work.
2. Rebuild contact/inquiry forms with validation, safe server-side processing, anti-spam measures, clear success/error states, and authorized notification routing.
3. Rebuild careers/application flow and resume upload with file-type/size restrictions, safe storage outside public web root where possible, access controls and retention policy. Confirm exact fields and destinations with the site owner.
4. Recreate any booking, WhatsApp, social, map or external integrations only after confirming existing behavior and destination URLs.
5. Preserve accessibility basics: semantic HTML, keyboard navigation, labels, alt text and visible focus.
6. Preserve SEO basics: page titles, meta descriptions, canonical URLs, headings, sitemap and robots directives where appropriate.
7. No real production submissions or emails during development tests without authorization.

## Security and source control
- Never commit .env, API keys, credentials, real applicant data, form submissions, private files, database dumps or production logs.
- Include .gitignore and .env.example with placeholders only.
- Validate and sanitize input; escape HTML output; use CSRF protection for forms; use prepared statements if database is introduced.
- Do not overwrite the production website or domain before approval and backup.
- Keep deployment credentials in secure GitHub Actions secrets or Hostinger configuration, never in code.

## Database & Admin Portal Specifications (Phase 5)
- **Database Engine:** MySQL / MariaDB (PDO prepared statements, UTF-8mb4).
- **Architecture:** Plain PHP 8+ without external frameworks (No Laravel, No WordPress).
- **Core Tables (7):** `admin_users`, `contact_messages`, `job_applications`, `application_notes`, `audit_logs`, `system_settings`, `login_rate_limits`.
- **Security:** Bcrypt password hashing, session timeout (30m), strict cookies, login rate limiting (5 attempts / 15m), CSRF tokens.
- **RBAC:** Super Admin (full control, staff creation, settings, deletion) vs Staff (message/candidate review).
- **CV Storage:** Blocked from direct HTTP requests (403 Forbidden via `.htaccess`); streamed only to authenticated admins via `/admin/download-resume.php` with directory traversal protection and audit logging.

## UI Refinement & Responsiveness (Phase 6)
- **Typography Standards:** Standardized CSS variables (`--font-family-primary`, fluid `clamp()` heading scales, line heights, letter spacing).
- **Responsiveness:** Validated across 6 viewports (375px, 390px, 768px, 1024px, 1440px, 1920px) across all 14 public routes with 0 horizontal overflows.
- **Admin Usability:** Touch-friendly data table horizontal scroll (`-webkit-overflow-scrolling: touch`), accessible `:focus-visible` focus outlines.
- **Regression Testing:** 100% pass on 26 automated end-to-end tests after all UI refinements.

## Acceptance criteria
- Every inventoried page renders and closely matches approved desktop and mobile reference screenshots.
- Navigation and interactive behavior work; no broken assets or console errors.
- Forms and integrations pass agreed test cases with non-production test data.
- Site can run locally with documented setup and deploy to a staging environment.
- Owner approves side-by-side review before cutover.

## Working instructions for Antigravity
- Read this document and TASKS.md before changing files.
- Work on one phase at a time. Start with inventory and the homepage; do not claim full migration until every feature is tested.
- Record unknowns, completed work and verification evidence in TASKS.md.
- Ask before introducing frameworks, paid services, database schemas or modifying production systems.
