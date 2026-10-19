<?php
require_once __DIR__ . '/../includes/process_contact.php';

$page_title = 'Services - Mowinton Consulting';
$current_page = 'services';
$root_path = '..';

require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
    <!-- Top Header & Intro -->
    <section class="services-page-header">
        <h1 class="services-page-title">Services</h1>
        <p class="services-page-subtitle">
            Delivering innovative IT consulting, recruitment, outsourcing and AI-powered solutions to support business growth and transformation.
        </p>
    </section>

    <!-- Section 1: Professional IT Services Box -->
    <section class="services-container">
        <div class="services-feature-box">
            <p class="services-feature-badge">OUR SERVICES</p>
            <h2 class="services-feature-title">Professional IT<br>Services</h2>
            <p class="services-feature-desc">
                Delivering innovative IT consulting, project management, recruitment, outsourcing, and AI-driven solutions that help businesses achieve sustainable growth and operational excellence.
            </p>

            <div class="services-cards-grid">
                <!-- Service 1: Technical Consulting -->
                <article class="service-glass-card">
                    <img src="<?php echo $base_url; ?>/img/placeholders/services-technical-consulting.svg" 
                         alt="Technical Consulting" 
                         width="900" 
                         height="600" 
                         loading="lazy">
                    <h3>Technical Consulting</h3>
                    <p>Expert technology guidance to improve systems, enhance operations, support digital transformation, and align IT strategies with business goals.</p>
                </article>

                <!-- Service 2: IT Outsourcing -->
                <article class="service-glass-card">
                    <img src="<?php echo $base_url; ?>/img/placeholders/services-it-outsourcing.svg" 
                         alt="IT Outsourcing" 
                         width="900" 
                         height="600" 
                         loading="lazy">
                    <h3>IT Outsourcing</h3>
                    <p>Reliable outsourcing support that helps organizations access skilled IT professionals, reduce operational workload, and improve service delivery.</p>
                </article>

                <!-- Service 3: AI Solutions Integration -->
                <article class="service-glass-card">
                    <img src="<?php echo $base_url; ?>/img/placeholders/services-ai-solutions.svg" 
                         alt="AI Solutions Integration" 
                         width="900" 
                         height="600" 
                         loading="lazy">
                    <h3>AI Solutions Integration</h3>
                    <p>Practical AI-driven solutions designed to automate workflows, improve decision-making, streamline processes, and unlock new business opportunities.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Section 2: Workforce Solutions -->
    <section class="workforce-section">
        <div class="workforce-header">
            <div class="workforce-badge">Workforce Solutions</div>
            <h2>Flexible, Trained &amp; Reliable Workforce Support</h2>
            <p>
                We provide skilled and dependable manpower solutions to help businesses manage workloads, support operations, and complete projects efficiently.
            </p>
        </div>

        <div class="workforce-grid">
            <!-- Workforce Card 1 -->
            <article class="workforce-card">
                <img src="<?php echo $base_url; ?>/img/placeholders/services-workforce-flexible.svg" 
                     alt="Flexible Workforce" 
                     width="1200" 
                     height="800" 
                     loading="lazy">
                <div class="workforce-content">
                    <h3>Flexible Workforce</h3>
                    <p>Scale your workforce quickly based on operational needs, seasonal demand, or project requirements.</p>
                    <div class="workforce-benefits">
                        <span>✓ Flexible working arrangements</span>
                        <span>✓ Fast deployment</span>
                        <span>✓ Cost-effective solution</span>
                    </div>
                </div>
            </article>

            <!-- Workforce Card 2 -->
            <article class="workforce-card">
                <img src="<?php echo $base_url; ?>/img/placeholders/services-workforce-trained.svg" 
                     alt="Trained Personnel" 
                     width="1200" 
                     height="800" 
                     loading="lazy">
                <div class="workforce-content">
                    <h3>Trained Personnel</h3>
                    <p>Access experienced and trained workers who are ready to contribute from day one.</p>
                    <div class="workforce-benefits">
                        <span>✓ Skilled workers</span>
                        <span>✓ Industry-ready personnel</span>
                        <span>✓ Higher productivity</span>
                    </div>
                </div>
            </article>

            <!-- Workforce Card 3 -->
            <article class="workforce-card">
                <img src="<?php echo $base_url; ?>/img/placeholders/services-workforce-operational.svg" 
                     alt="Operational Support" 
                     width="1200" 
                     height="800" 
                     loading="lazy">
                <div class="workforce-content">
                    <h3>Operational Support</h3>
                    <p>Reliable manpower support for daily operations, projects, and business growth.</p>
                    <div class="workforce-benefits">
                        <span>✓ Short &amp; long-term support</span>
                        <span>✓ Scalable workforce</span>
                        <span>✓ Reduced hiring burden</span>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <!-- Section 3: Tailored IT Solutions -->
    <section class="tailored-section">
        <h2 class="tailored-title">Tailored IT Solutions</h2>
        <div class="tailored-grid">
            <article class="tailored-card">
                <figure>
                    <img src="<?php echo $base_url; ?>/img/placeholders/services-tailored-pm.svg" 
                         alt="Expert Project Management" 
                         width="340" 
                         height="340" 
                         loading="lazy">
                </figure>
                <h3>Expert Project Management</h3>
                <p>Our project management services ensure timely delivery and optimal resource allocation, helping your business achieve its goals efficiently and effectively.</p>
            </article>

            <article class="tailored-card">
                <figure>
                    <img src="<?php echo $base_url; ?>/img/placeholders/services-tailored-ai.svg" 
                         alt="AI Integration Services" 
                         width="340" 
                         height="340" 
                         loading="lazy">
                </figure>
                <h3>AI Integration Services</h3>
                <p>We specialize in integrating AI solutions tailored to your business needs, enhancing productivity and driving innovation across your operations.</p>
            </article>

            <article class="tailored-card">
                <figure>
                    <img src="<?php echo $base_url; ?>/img/placeholders/services-tailored-talent.svg" 
                         alt="Talent Acquisition Experts" 
                         width="340" 
                         height="340" 
                         loading="lazy">
                </figure>
                <h3>Talent Acquisition Experts</h3>
                <p>Our recruitment services focus on identifying and attracting top talent, ensuring your team is equipped with the skills necessary for success.</p>
            </article>
        </div>
    </section>

    <!-- Section 4: Google Reviews Widget -->
    <section class="reviews-section">
        <div class="reviews-wrapper">
            <p class="reviews-tag">Google Reviews</p>
            <h2 class="reviews-title">What Our Clients Say</h2>
            <p class="reviews-desc">
                See what our clients and partners have to say about working with Mowinton Consulting.
            </p>

            <div class="reviews-widget-grid">
                <!-- Left Rating Summary -->
                <div class="reviews-summary-card">
                    <span class="reviews-rating-word">EXCELLENT</span>
                    <div class="reviews-stars" aria-label="5 out of 5 stars">★★★★★</div>
                    <span class="reviews-count">Based on <strong>57 reviews</strong></span>
                    <svg class="reviews-google-logo" viewBox="0 0 272 92" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Google">
                        <path d="M115.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18C71.25 34.32 81.24 25 93.5 25s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44S80.99 39.2 80.99 47.18c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z" fill="#EA4335"/>
                        <path d="M163.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18c0-12.85 9.99-22.18 22.25-22.18s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44s-12.51 5.46-12.51 13.44c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z" fill="#FBBC05"/>
                        <path d="M209.75 26.34v39.82c0 16.38-9.66 23.07-21.08 23.07-10.75 0-17.22-7.19-19.66-13.07l8.48-3.53c1.51 3.61 5.21 7.87 11.17 7.87 7.31 0 11.84-4.51 11.84-13.01v-3.19h-.34c-2.18 2.69-6.38 5.04-11.68 5.04-11.09 0-21.25-9.66-21.25-22.09 0-12.52 10.16-22.35 21.25-22.35 5.29 0 9.49 2.35 11.68 4.96h.34v-3.53h9.25zm-8.82 20.92c0-7.81-5.21-13.52-11.84-13.52-6.72 0-12.35 5.71-12.35 13.52 0 7.73 5.63 13.36 12.35 13.36 6.63 0 11.84-5.63 11.84-13.36z" fill="#4285F4"/>
                        <path d="M225 3v65.04h-9.5V3h9.5z" fill="#34A853"/>
                        <path d="M262.02 54.49l7.56 5.04c-2.44 3.61-8.32 9.83-18.48 9.83-12.6 0-22.01-9.74-22.01-22.18 0-13.19 9.49-22.18 20.92-22.18 11.51 0 17.14 9.16 18.99 14.11l1.01 2.52-29.65 12.27c2.27 4.45 5.8 6.72 10.75 6.72 4.96 0 8.4-2.44 10.9-6.13zm-19.32-8.06l19.82-8.23c-1.09-2.77-4.37-4.7-8.32-4.7-4.95 0-11.93 4.36-11.5 12.93z" fill="#EA4335"/>
                        <path d="M35.29 41.41V30.34h31.95c.31 1.64.47 3.5.47 5.68 0 6.78-1.85 15.22-7.89 21.26-5.89 6.13-13.48 9.43-24.53 9.43C16.03 66.71 0 51.76 0 33.36 0 14.95 16.03 0 35.29 0c10.63 0 18.23 4.15 23.94 9.58l-6.72 6.72c-4.08-3.87-9.58-6.8-17.22-6.8-14.11 0-25.12 11.43-25.12 25.54 0 14.11 11.01 25.54 25.12 25.54 9.16 0 14.37-3.69 17.73-7.06 1.81-1.81 3.03-4.37 3.53-7.9H35.29v-4.21z" fill="#4285F4"/>
                    </svg>
                </div>

                <!-- Right Slider Container -->
                <div class="reviews-carousel-container">
                    <div class="reviews-slider-track" id="reviewsTrack">
                        <!-- Review 1 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#8b5cf6;">S</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">Shattika Das</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">Best service</p>
                        </div>

                        <!-- Review 2 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#0ea5e9;">J</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">JE tech zone</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">Best service.</p>
                        </div>

                        <!-- Review 3 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#10b981;">T</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">Tanvir Hasan</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">Overall experience was excellent.</p>
                        </div>

                        <!-- Review 4 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#ec4899;">Z</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">Zinia Sultana</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">Great people and great future ahead.</p>
                        </div>

                        <!-- Review 5 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#f59e0b;">M</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">Md Foysal Hridoy</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">I had a great experience with this company. The staff were professional, friendly, and very helpful working with.</p>
                        </div>

                        <!-- Review 6 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#6366f1;">H</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">hr rahman</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">I had a great experience with this company. The staff were professional, friendly, and very helpful working with.</p>
                        </div>

                        <!-- Review 7 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#3b82f6;">A</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">Arham Ahmed</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">this is one of the best Consultancy Group in Malaysia ,their Service is well organised 💙</p>
                        </div>

                        <!-- Review 8 -->
                        <div class="review-slide-card">
                            <div class="review-card-header">
                                <div class="review-avatar" style="background:#14b8a6;">M</div>
                                <div class="review-author-info">
                                    <div class="review-author-name">milhat rahman</div>
                                    <div class="review-date">4 months ago</div>
                                </div>
                                <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" class="review-google-badge">
                            </div>
                            <div class="review-stars-row">
                                <span class="stars">★★★★★</span>
                                <span class="review-verified-badge">✔ Verified</span>
                            </div>
                            <p class="review-text">Their service really amazing and appritiable.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reviews-carousel-nav">
                <button type="button" class="reviews-nav-btn" id="prevReviewBtn" aria-label="Previous review">❮</button>
                <button type="button" class="reviews-nav-btn" id="nextReviewBtn" aria-label="Next review">❯</button>
            </div>

            <div style="margin-top:20px;text-align:right;">
                <span class="reviews-trustindex-badge">Verified by Trustindex ⓘ</span>
            </div>
        </div>
    </section>

    <!-- Section 5: Contact Section -->
    <section class="contact-section" id="contact">
        <div class="contact-container">
            <!-- Left Info Card -->
            <div class="contact-info-card">
                <span class="contact-badge">Contact Mowinton</span>
                <h2 class="contact-info-title">Let’s Build Your Next Solution Together</h2>
                <p class="contact-info-desc">
                    Have a question about IT consulting, project management, recruitment, or digital transformation? Send us your inquiry and our team will get back to you shortly.
                </p>
                <div class="contact-features">
                    <div class="contact-feature-item">
                        <div class="feature-icon-box" aria-hidden="true">✉️</div>
                        <div class="feature-details">
                            <strong>Email Us</strong>
                            <span>info@mowinton.com</span>
                        </div>
                    </div>
                    <div class="contact-feature-item">
                        <div class="feature-icon-box" aria-hidden="true">💼</div>
                        <div class="feature-details">
                            <strong>Services</strong>
                            <span>IT Consulting, Recruitment &amp; Project Solutions</span>
                        </div>
                    </div>
                    <div class="contact-feature-item">
                        <div class="feature-icon-box" aria-hidden="true">⚡</div>
                        <div class="feature-details">
                            <strong>Fast Response</strong>
                            <span>We aim to respond as soon as possible.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form Card -->
            <div class="contact-form-card">
                <p class="form-tag">Send Inquiry</p>
                <h2 class="form-title">Contact Us</h2>
                <p class="form-subtitle">Fill in the form below and our team will contact you soon.</p>

                <!-- Alert Message Container -->
                <div id="formAlert" class="form-alert <?php echo $feedback ? 'is-' . $feedback['type'] : ''; ?>" 
                     style="<?php echo $feedback ? 'display: block;' : ''; ?>">
                    <?php if ($feedback): ?>
                        <?php echo htmlspecialchars($feedback['text']); ?>
                    <?php endif; ?>
                </div>

                <form id="mowintonContactForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#contact">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <!-- Honeypot anti-spam field -->
                    <div class="form-honeypot" aria-hidden="true">
                        <label for="email_type_name">email Type name</label>
                        <input type="text" id="email_type_name" name="email_type_name" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_name">
                            Enter your name <span class="form-required" aria-hidden="true">*</span>
                        </label>
                        <input type="text" 
                               id="contact_name" 
                               name="name" 
                               class="form-control" 
                               required 
                               autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_company">Enter company name</label>
                        <input type="text" 
                               id="contact_company" 
                               name="company" 
                               class="form-control" 
                               autocomplete="organization">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_email">
                            Enter email address <span class="form-required" aria-hidden="true">*</span>
                        </label>
                        <input type="email" 
                               id="contact_email" 
                               name="email" 
                               class="form-control" 
                               required 
                               autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_message">Type your message here</label>
                        <textarea id="contact_message" 
                                  name="message" 
                                  class="form-control" 
                                  rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
// Reviews Slider Interaction
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('reviewsTrack');
    const prevBtn = document.getElementById('prevReviewBtn');
    const nextBtn = document.getElementById('nextReviewBtn');
    if (!track || !prevBtn || !nextBtn) return;

    let currentIndex = 0;
    const cards = track.querySelectorAll('.review-slide-card');
    const totalCards = cards.length;

    function getVisibleCount() {
        return window.innerWidth <= 1024 ? 1 : 2;
    }

    function updateTrack() {
        const visible = getVisibleCount();
        const maxIndex = Math.max(0, totalCards - visible);
        if (currentIndex > maxIndex) currentIndex = maxIndex;
        if (currentIndex < 0) currentIndex = 0;

        const cardWidth = cards[0].offsetWidth;
        const gap = 20;
        const moveAmount = (cardWidth + gap) * currentIndex;
        track.style.transform = `translateX(-${moveAmount}px)`;
    }

    prevBtn.addEventListener('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            updateTrack();
        }
    });

    nextBtn.addEventListener('click', function() {
        const visible = getVisibleCount();
        if (currentIndex < totalCards - visible) {
            currentIndex++;
            updateTrack();
        }
    });

    window.addEventListener('resize', updateTrack);
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
