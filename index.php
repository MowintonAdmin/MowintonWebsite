<?php
require_once __DIR__ . '/includes/process_contact.php';

$page_title = 'Mowinton Consulting - IT Consulting & Talent Solutions';
$page_description = 'At Mowinton Consulting, we specialize in IT consulting, offering tailored solutions in Program and Project Management, Talent Specialist & Recruitment to elevate your business to new heights.';
$active_nav = 'home';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <p class="hero-tag">IT Consulting &amp; Talent Solutions</p>
                <h1 class="hero-title">Mowinton<br>Consulting</h1>
                <p class="hero-desc">
                    At Mowinton Consulting, we specialize in IT consulting, offering tailored solutions in Program and Project Management, Talent Specialist &amp; Recruitment to elevate your business to new heights.
                </p>
                <p class="hero-desc">
                    With over three decades of experience delivering projects and solutions internationally, we bring proven practices and professional value to clients across Asia Pacific and worldwide.
                </p>
                <div class="hero-cta-group">
                    <a href="<?php echo $base_url; ?>/services/" class="btn-primary">Explore Services</a>
                    <a href="<?php echo $base_url; ?>/careers-2/" class="btn-secondary">Join Our Team</a>
                </div>
            </div>
            <div class="hero-image-wrap">
                <div class="hero-circle-deco" aria-hidden="true"></div>
                <img src="<?php echo $base_url; ?>/img/office-building-7-290x300.avif" 
                     alt="Mowinton Consulting Office Building" 
                     class="hero-img"
                     width="480" 
                     height="480">
            </div>
        </div>
    </section>

    <!-- Professional IT Services Section -->
    <section class="services-section">
        <div class="services-container">
            <h2 class="services-title">Professional IT Services</h2>
            <div class="services-grid">
                <!-- Service 1: Technical Consulting -->
                <article class="service-card">
                    <figure>
                        <img src="<?php echo $base_url; ?>/img/service-technical-consulting.jpg" 
                             alt="Technical Consulting" 
                             width="656" 
                             height="499" 
                             loading="lazy">
                    </figure>
                    <h3 class="service-card-title">Technical Consulting</h3>
                    <p class="service-card-desc">
                        We partner with clients to bridge strategy and execution, reconcile competing priorities, and translate bold vision into pragmatic results through strategic business and IT consulting.
                    </p>
                </article>

                <!-- Service 2: IT Outsourcing -->
                <article class="service-card">
                    <figure>
                        <img src="<?php echo $base_url; ?>/img/service-it-outsourcing.jpg" 
                             alt="IT Outsourcing" 
                             width="656" 
                             height="499" 
                             loading="lazy">
                    </figure>
                    <h3 class="service-card-title">IT Outsourcing</h3>
                    <p class="service-card-desc">
                        Full-Suite Outsourced IT Project Management that delivers results while your team owns the horizon.
                    </p>
                </article>

                <!-- Service 3: AI Solutions Integration -->
                <article class="service-card">
                    <figure>
                        <img src="<?php echo $base_url; ?>/img/photo-1718241905462-56e7b9f722f3-300x228.avif" 
                             alt="AI Solutions Integration" 
                             width="656" 
                             height="499" 
                             loading="lazy">
                    </figure>
                    <h3 class="service-card-title">AI Solutions Integration</h3>
                    <p class="service-card-desc">
                        We learn about your business workflow before proposing the best AI tools. Whether B2B or B2C, we fill your operational gaps with embedded intelligence so you unlock efficiency gains without heavy lifting.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <!-- About Mowinton Section -->
    <section class="about-mowinton">
        <div class="about-wrap">
            <div class="about-content">
                <span class="about-tag">About Mowinton</span>
                <h2>Your Trusted IT<br>Consulting Partner</h2>
                <p class="about-text">
                    Our team brings over 20 years of experience delivering high-profile, large-scale technology projects internationally for government agencies, multinational corporations, and Fortune 500 companies. We help organizations transform through strategic consulting, project delivery, and digital innovation.
                </p>
                <div class="about-stats">
                    <div class="stat-card">
                        <div class="stat-number">80+</div>
                        <div class="stat-label">Projects<br>Delivered</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$100M+</div>
                        <div class="stat-label">Project<br>Value</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">13</div>
                        <div class="stat-label">Countries<br>Served</div>
                    </div>
                </div>
            </div>
            <figure class="about-image-wrap">
                <img src="<?php echo $base_url; ?>/img/about-team.jpg" 
                     alt="Mowinton Consulting Team" 
                     width="861" 
                     height="637" 
                     loading="lazy">
            </figure>
        </div>
    </section>

    <!-- Industry Expertise Section -->
    <section class="industry-section">
        <div class="industry-container">
            <div class="industry-header">
                <p>Industries We Serve</p>
                <h2>Industry Expertise</h2>
                <span>
                    Delivering specialized consulting and technology solutions across multiple industries, helping organizations innovate, transform, and grow.
                </span>
            </div>
            <div class="industry-grid">
                <!-- Industry 1: Banking -->
                <article class="industry-card">
                    <div class="industry-img-wrap">
                        <img src="<?php echo $base_url; ?>/img/bank-building-AE07VZB14Wu9NZ4j-300x225.webp" 
                             alt="Banking" 
                             width="768" 
                             height="513" 
                             loading="lazy">
                    </div>
                    <div class="industry-content">
                        <h3>Banking</h3>
                        <p>Supporting financial institutions with reliable IT consulting, digital transformation, and operational excellence solutions.</p>
                    </div>
                </article>

                <!-- Industry 2: Healthcare -->
                <article class="industry-card">
                    <div class="industry-img-wrap">
                        <img src="<?php echo $base_url; ?>/img/healthcare-YZ9EPJD58ZCppBgw-300x170.webp" 
                             alt="Healthcare" 
                             width="768" 
                             height="513" 
                             loading="lazy">
                    </div>
                    <div class="industry-content">
                        <h3>Healthcare</h3>
                        <p>Helping healthcare providers improve patient services, operational efficiency, and technology adoption.</p>
                    </div>
                </article>

                <!-- Industry 3: Manufacturing -->
                <article class="industry-card">
                    <div class="industry-img-wrap">
                        <img src="<?php echo $base_url; ?>/img/manufacturing-ALp2VazNkriMOkXv-300x200.webp" 
                             alt="Manufacturing" 
                             width="768" 
                             height="513" 
                             loading="lazy">
                    </div>
                    <div class="industry-content">
                        <h3>Manufacturing</h3>
                        <p>Driving automation, process optimization, and operational excellence across manufacturing environments.</p>
                    </div>
                </article>

                <!-- Industry 4: Semiconductor -->
                <article class="industry-card">
                    <div class="industry-img-wrap">
                        <img src="<?php echo $base_url; ?>/img/semiconductor-photo-essay_part-1_main-2-YNqM5xVZeBi2QBLN-300x200.webp" 
                             alt="Semiconductor" 
                             width="768" 
                             height="513" 
                             loading="lazy">
                    </div>
                    <div class="industry-content">
                        <h3>Semiconductor</h3>
                        <p>Supporting semiconductor organizations with smart factory, automation, and engineering solutions.</p>
                    </div>
                </article>

                <!-- Industry 5: Telecommunications -->
                <article class="industry-card">
                    <div class="industry-img-wrap">
                        <img src="<?php echo $base_url; ?>/img/telecommunication-industry-mePge80LNkspa1vg-300x169.webp" 
                             alt="Telecommunications" 
                             width="768" 
                             height="513" 
                             loading="lazy">
                    </div>
                    <div class="industry-content">
                        <h3>Telecommunications</h3>
                        <p>Delivering technology expertise that strengthens connectivity, infrastructure, and digital communications.</p>
                    </div>
                </article>

                <!-- Industry 6: Security -->
                <article class="industry-card">
                    <div class="industry-img-wrap">
                        <img src="<?php echo $base_url; ?>/img/security-dOqZ9L3R4rILE1ZZ-300x165.webp" 
                             alt="Security" 
                             width="768" 
                             height="513" 
                             loading="lazy">
                    </div>
                    <div class="industry-content">
                        <h3>Security</h3>
                        <p>Providing innovative solutions that enhance monitoring, safety, and technology-driven security operations.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
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
                        <label class="form-label" for="contact_name">Enter your name <span class="form-required">*</span></label>
                        <input type="text" id="contact_name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_company">Enter company name</label>
                        <input type="text" id="contact_company" name="company" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_email">Enter email address <span class="form-required">*</span></label>
                        <input type="email" id="contact_email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_message">Type your message here</label>
                        <textarea id="contact_message" name="message" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
