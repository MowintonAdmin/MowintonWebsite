# Mowinton Consulting Website — Reconstruction Project

This project is a source-code reconstruction of [https://mowinton.com/](https://mowinton.com/) built with clean, standards-compliant HTML5, CSS3, vanilla JavaScript, and plain PHP 8+. It is optimized for local development and direct deployment to Hostinger PHP Web Hosting.

---

## Tech Stack
- **Backend / Templating:** PHP 8+ (Clean directory routing with reusable `includes/header.php`, `includes/footer.php`)
- **Structure:** Semantic HTML5
- **Styling:** Modular CSS3 with responsive grid, flexbox, fluid clamp typography, and custom variables (`css/style.css`)
- **Interactivity:** Vanilla JavaScript (`js/main.js`): mobile navigation drawer, dropdown accessibility, Google Reviews carousel, and AJAX form handling
- **No external heavy frameworks:** No WordPress, no Laravel, no React.

---

## Website Pages Inventory (14 Pages Reconstructed)

All publicly accessible pages from the original site have been reconstructed with faithful copy, hierarchy, styling, and responsiveness:

1. **Homepage:** [`index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/index.php) — Hero, Professional IT Services, About Mowinton, Industry Expertise (6 cards), Contact dual-card section, and Footer.
2. **About Us:** [`about/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/about/index.php) — Company overview, Google Maps office embed, accordion FAQs, and Lark consultation scheduler.
3. **Services:** [`services/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/services/index.php) — IT consulting hero banner, Workforce Solutions grid, Tailored Solutions cards, Google Reviews carousel (57 reviews, 5.0 score), and contact form.
4. **Careers:** [`careers-2/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/careers-2/index.php) — Hero card, 4 Perks, 6 Job Openings grid with culture card, and embedded job application form.
5. **Internship Program:** [`internship-program/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/internship-program/index.php) — Hero card, 4 Benefits, 6 Internship Areas, 5 Process steps, and Apply CTA.
6. **Interview Guide:** [`interview-guide/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/interview-guide/index.php) — 5 timeline step cards with SVG illustrations, 4 common interview questions, and careers CTA.
7. **Submit Job Application:** [`submit-job-application/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/submit-job-application/index.php) — Standalone application page with `#ece7de` warm background, position autofill query parameter, and secure resume upload.
8. **News & Insights:** [`news-insights/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/news-insights/index.php) — 3 insight article cards (*Digital Transformation*, *Project Delivery*, *Technology Roadmap*) and Lark consultation CTA.
9. **Project Manager JD:** [`project-manager-jd/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/project-manager-jd/index.php)
10. **Software Engineer JD:** [`software-engineer-jd/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/software-engineer-jd/index.php)
11. **Technical Lead JD:** [`technical-lead/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/technical-lead/index.php)
12. **QA Engineer JD:** [`qa-engineer-jd/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/qa-engineer-jd/index.php)
13. **Business Analyst JD:** [`business-analyst-jd/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/business-analyst-jd/index.php)
14. **Intern – Digital Marketing JD:** [`intern-digital-marketing/index.php`](file:///c:/Users/USER/Desktop/Jack/Documents/GitHub/MowintonWebsite/mowinton-website-starter/mowinton-website/intern-digital-marketing/index.php)

---

## Project Structure
```text
mowinton-website/
├── index.php                          # Reconstructed Homepage
├── about/index.php                    # About Us, Maps & FAQs
├── services/index.php                 # Services & Google Reviews slider
├── careers-2/index.php                # Careers listing & application form
├── internship-program/index.php       # Internship Program
├── interview-guide/index.php          # Interview Preparation Guide
├── submit-job-application/index.php   # Standalone Job Application
├── news-insights/index.php            # News & Insights articles
├── project-manager-jd/index.php       # Project Manager JD
├── software-engineer-jd/index.php     # Sr / Software Engineer JD
├── technical-lead/index.php           # Tech Lead JD
├── qa-engineer-jd/index.php           # QA Engineer JD
├── business-analyst-jd/index.php      # Sr / Business Analyst JD
├── intern-digital-marketing/index.php # Intern – Digital Marketing JD
├── includes/
│   ├── header.php                     # Global header & navigation bar
│   ├── footer.php                     # Global footer & social links
│   ├── process_contact.php            # Contact form backend processor (CSRF, honeypot)
│   └── process_job_application.php    # Job application backend & resume upload handling
├── css/
│   └── style.css                      # Master stylesheet (all pages & responsive queries)
├── js/
│   └── main.js                        # Navigation, mobile drawer, reviews carousel & AJAX forms
├── img/
│   ├── mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg  # Original company logo
│   └── placeholders/                  # Neutral light-gray SVG placeholders
│       ├── hero-office-building.svg
│       ├── service-technical-consulting.svg
│       ├── service-it-outsourcing.svg
│       ├── service-ai-solutions.svg
│       ├── about-team.svg
│       ├── industry-banking.svg
│       ├── industry-healthcare.svg
│       ├── industry-manufacturing.svg
│       ├── industry-semiconductor.svg
│       ├── industry-telecommunications.svg
│       ├── industry-security.svg
│       ├── careers-team-group.svg
│       ├── careers-work-culture.svg
│       ├── interview-office-prep.svg
│       ├── interview-resume-prep.svg
│       ├── interview-practice.svg
│       ├── interview-professional.svg
│       └── interview-followup.svg
├── uploads/
│   └── resumes/                       # Local resume upload directory (gitignored)
├── IMAGE_REPLACEMENT_GUIDE.md         # Image inventory & replacement specifications
├── TASKS.md                           # Development & verification checklist
└── README.md                          # Project documentation
```

---

## How to Run Locally

Run the project with PHP's built-in web server:

```bash
php -S localhost:8000
```

Then visit:
[http://localhost:8000](http://localhost:8000)

---

## Security & Form Handling
- **CSRF Protection:** Every form generates and verifies secure session tokens.
- **Honeypot Anti-Spam:** Hidden spam trap fields filter automated bot submissions silently.
- **File Upload Security:** Resume uploads validate allowed MIME types (`.pdf`, `.doc`, `.docx`), enforce a 5MB size limit, sanitize filenames with timestamp prefixes, and store files safely in `uploads/resumes/`.
