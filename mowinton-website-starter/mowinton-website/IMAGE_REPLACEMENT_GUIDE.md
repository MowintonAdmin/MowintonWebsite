# Mowinton Website — Image Replacement Guide

This guide documents all images and placeholders used across the entire reconstructed website, including their exact locations, dimensions, visual styling, original URLs, and replacement instructions.

---

## 1. Company Logo (Official Asset Provided)

| Property | Details |
| :--- | :--- |
| **Project Path** | `img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg` (and `img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.avif`) |
| **Dimensions** | 375 × 141 px (Rendered size in header: ~115 × 43 px) |
| **Usage** | Global Header navigation bar (`includes/header.php`) |
| **Status** | **Original Asset In Use** (Do not replace or rename) |

---

## 2. Homepage Image Placeholders

All placeholders below use clean, neutral light-gray SVGs (`#E2E8F0` / `#CBD5E1`) with descriptive labels and exact aspect ratios.

### 2.1 Hero Section
| Property | Details |
| :--- | :--- |
| **Placeholder File** | `img/placeholders/hero-office-building.svg` |
| **Label** | Hero Image — Office Building (480x480) |
| **Dimensions / Ratio** | 480 × 480 px (1:1 square) |
| **Rendered Style** | Circular (`border-radius: 50%`), `border: 8px solid white`, shadow `0 25px 60px rgba(15,23,42,.16)`, `object-fit: cover` |
| **Original Reference** | `https://mowinton.com/wp-content/uploads/2025/09/office-building-6.avif` |
| **How to Replace** | Save replacement photo as `img/hero-office-building.jpg` and update `src` in `index.php`. |

### 2.2 Professional IT Services
| Service Card | Placeholder File | Dimensions | Original Asset File |
| :--- | :--- | :--- | :--- |
| **1. Technical Consulting** | `img/placeholders/service-technical-consulting.svg` | 656 × 499 px | Unsplash `photo-1520607162513-77705c0f0d4a` |
| **2. IT Outsourcing** | `img/placeholders/service-it-outsourcing.svg` | 656 × 499 px | Unsplash `photo-1507679799987-c73779587ccf` |
| **3. AI Solutions Integration** | `img/placeholders/service-ai-solutions.svg` | 656 × 499 px | Unsplash `photo-1718241905462-56e7b9f722f3` |

### 2.3 About Mowinton Section
| Property | Details |
| :--- | :--- |
| **Placeholder File** | `img/placeholders/about-team.svg` |
| **Label** | Team Image — About Mowinton (861x637) |
| **Dimensions / Ratio** | 861 × 637 px (Cover height 520px on desktop, 330px on tablet, 280px on mobile) |
| **Rendered Style** | Rounded corners (`border-radius: 30px`), shadow `0 25px 60px rgba(15,23,42,.18)`, `object-fit: cover` |
| **Original Reference** | Unsplash `photo-1544006658-5ed4d689eb5e` |
| **How to Replace** | Place replacement image in `img/` and update `src` in `index.php`. |

### 2.4 Industry Expertise Section
All 6 industry cards share uniform card sizing, hover zoom (`scale(1.06)`), and top border accent (`#2563eb`).

| Industry Card | Placeholder File | Dimensions | Original Asset File |
| :--- | :--- | :--- | :--- |
| **1. Banking** | `img/placeholders/industry-banking.svg` | 768 × 513 px | `bank-building-AE07VZB14Wu9NZ4j.jpg` |
| **2. Healthcare** | `img/placeholders/industry-healthcare.svg` | 768 × 513 px | `healthcare-YZ9EPJD58ZCppBgw.jpg` |
| **3. Manufacturing** | `img/placeholders/industry-manufacturing.svg` | 768 × 513 px | `manufacturing-ALp2VazNkriMOkXv.jpg` |
| **4. Semiconductor** | `img/placeholders/industry-semiconductor.svg` | 768 × 513 px | `semiconductor-photo-essay_part-1_main-2-YNqM5xVZeBi2QBLN.jpg` |
| **5. Telecommunications**| `img/placeholders/industry-telecommunications.svg` | 768 × 513 px | `telecommunication-industry-mePge80LNkspa1vg.jpg` |
| **6. Security** | `img/placeholders/industry-security.svg` | 768 × 513 px | `security-dOqZ9L3R4rILE1ZZ.jpg` |

---

## 3. Careers Page Image Placeholders (`careers-2/`)

| Location | Placeholder File | Dimensions | Description / Styling |
| :--- | :--- | :--- | :--- |
| **Top Hero Section** | `img/placeholders/careers-team-group.svg` | 500 × 333 px | Team collaboration photo, rounded corners (`border-radius: 20px`), shadow |
| **Culture & Openings Card** | `img/placeholders/careers-work-culture.svg` | 656 × 470 px | Large culture photo with gradient overlay (`rgba(19,32,51,0.85)`), height 470px |

---

## 4. Interview Guide Image Placeholders (`interview-guide/`)

| Step | Placeholder File | Dimensions | Description / Usage |
| :--- | :--- | :--- | :--- |
| **Step 1: Research** | `img/placeholders/interview-office-prep.svg` | 500 × 320 px | Office preparation visual (Unsplash `photo-1497366754035-f200968a6e72`) |
| **Step 2: Resume** | `img/placeholders/interview-resume-prep.svg` | 500 × 320 px | Resume preparation visual (Unsplash `photo-1450101499163-c8848c66ca85`) |
| **Step 3: Practice** | `img/placeholders/interview-practice.svg` | 500 × 320 px | Interview practice visual (Unsplash `photo-1551836022-d5d88e9218df`) |
| **Step 4: Attendance** | `img/placeholders/interview-professional.svg` | 500 × 320 px | Professional interview visual (Unsplash `photo-1556761175-b413da4baf72`) |
| **Step 5: Follow Up** | `img/placeholders/interview-followup.svg` | 500 × 320 px | Online follow up visual (Unsplash `photo-1516321318423-f06f85e504b3`) |

---

## 5. General Replacement Instructions

When replacing placeholder SVG images with production photographs:
1. Ensure images are saved in optimized formats (JPEG, WebP, or AVIF format) under 250KB for fast page speed.
2. Maintain the recommended aspect ratios specified above to avoid layout shifts or distortions.
3. Update the `src` attribute in the respective template file (`index.php`, `careers-2/index.php`, `interview-guide/index.php`).
4. Keep the descriptive `alt` attribute for accessibility and SEO.
