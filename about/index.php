<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = '..';
$page_title = 'About – Mowinton Consulting';
$page_description = 'Learn more about Mowinton Consulting, our expertise, values, and commitment to delivering innovative IT consulting, recruitment, and workforce solutions.';
$active_nav = 'about';

require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
    <!-- Intro Header & Hero Banner -->
    <section class="about-hero-section">
        <div class="page-intro-header">
            <h1 class="page-intro-title">About</h1>
            <p class="page-intro-desc">
                Learn more about Mowinton Consulting, our expertise, values, and commitment to delivering innovative IT consulting, recruitment, and workforce solutions.
            </p>
        </div>

        <div class="about-banner-box">
            <div class="about-banner-grid">
                <div>
                    <span class="about-banner-tag">Mowinton Consulting</span>
                    <h2 class="about-banner-title">Elevate Your Business With Confidence</h2>
                    <p class="about-banner-text">
                        At Mowinton Consulting, we deliver professional IT consulting, project management, recruitment, and workforce solutions that help organisations achieve sustainable growth and operational excellence.
                    </p>
                    <a href="<?php echo $root_path; ?>/services/" class="btn-secondary" style="padding:16px 36px;font-size:16px;">
                        Explore Our Services
                    </a>
                </div>

                <div class="about-banner-images">
                    <img src="<?php echo $root_path; ?>/img/about-business-consulting.jpg" 
                         alt="Business Consulting" 
                         width="900" 
                         height="600" 
                         loading="lazy">
                    <img src="<?php echo $root_path; ?>/img/about-professional-services.jpg" 
                         alt="Professional Services" 
                         width="900" 
                         height="600" 
                         loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Why Businesses Choose Mowinton Section -->
    <section class="mowinton-section">
        <div class="mowinton-badge">Why Businesses Choose Mowinton</div>
        <h2 class="mowinton-title">Delivering Practical Solutions That Drive Results</h2>
        <p class="mowinton-subtitle">
            At Mowinton Consulting, we help organizations achieve sustainable growth through strategic IT consulting, structured project management, and business transformation solutions tailored to their unique objectives.
        </p>

        <div class="mowinton-grid">
            <div class="mowinton-card">
                <div class="mowinton-icon" aria-hidden="true">💼</div>
                <h3>IT Consulting</h3>
                <p>We provide expert technology guidance to help businesses optimize operations, improve efficiency, and align IT investments with long-term goals.</p>
            </div>

            <div class="mowinton-card">
                <div class="mowinton-icon" aria-hidden="true">📊</div>
                <h3>Project Management</h3>
                <p>Our structured approach ensures projects are delivered on time, within scope, and aligned with business objectives while minimizing risk.</p>
            </div>

            <div class="mowinton-card">
                <div class="mowinton-icon" aria-hidden="true">🚀</div>
                <h3>Business Transformation</h3>
                <p>We help organizations embrace innovation, modernize processes, and leverage technology to remain competitive in a rapidly evolving market.</p>
            </div>
        </div>
    </section>

    <!-- Office Location Section -->
    <section class="office-location-section">
        <div class="office-grid">
            <div class="info-card">
                <h2>Visit Our Office</h2>
                <p style="margin-bottom:30px;">
                    We welcome clients, partners, and businesses looking for professional IT consulting, project management, recruitment, and digital transformation solutions.
                </p>

                <div class="info-group">
                    <h3>📍 Office Address</h3>
                    <p>
                        No 54-2,<br>
                        Jalan Puteri 5/1,<br>
                        Bandar Puteri Puchong,<br>
                        47100 Puchong,<br>
                        Selangor, Malaysia
                    </p>
                </div>

                <div class="info-group">
                    <h3>🕒 Business Hours</h3>
                    <p>
                        Monday – Friday<br>
                        9:00 AM – 6:00 PM
                    </p>
                </div>

                <div class="info-group">
                    <h3>📞 Contact Information</h3>
                    <p>
                        <a href="tel:+60108282060" style="color:inherit;">+6010 8282 060</a><br>
                        <a href="mailto:info@mowinton.com" style="color:inherit;">info@mowinton.com</a>
                    </p>
                </div>
            </div>

            <div class="map-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.300540905222!2d101.61314867472967!3d3.0136533969623005!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cdb35999c939fb%3A0x4089d8a6e2ba168c!2sMowinton%20Consulting%20Sdn.%20Bhd.!5e0!3m2!1sen!2smy!4v1780025659075!5m2!1sen!2smy" 
                        width="100%" 
                        height="550" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        title="Mowinton Consulting Office Location"></iframe>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="mowinton-faq-section">
        <div class="mowinton-faq-badge">FAQ</div>
        <h2 class="mowinton-faq-title">Everything You Need To Know</h2>
        <p class="mowinton-faq-subtitle">
            Find answers to common questions about Mowinton Consulting, our services, project delivery process, and how we can support your business growth.
        </p>

        <div class="mowinton-faq-list">
            <details open>
                <summary>What is Mowinton Insights?</summary>
                <div class="mowinton-faq-content">
                    Mowinton Insights is our specialized research and advisory service that provides tailored market intelligence, business analysis, and strategic recommendations to help organizations make informed decisions.
                </div>
            </details>

            <details>
                <summary>How do I get started with Mowinton?</summary>
                <div class="mowinton-faq-content">
                    Simply contact our team through our website or schedule a consultation. We will discuss your requirements, understand your objectives, and recommend the most suitable solution for your business.
                </div>
            </details>

            <details>
                <summary>How do you gather insights?</summary>
                <div class="mowinton-faq-content">
                    We combine market research, stakeholder interviews, surveys, data analysis, and industry expertise to provide actionable insights that support strategic business decisions.
                </div>
            </details>

            <details>
                <summary>How fast is project delivery?</summary>
                <div class="mowinton-faq-content">
                    Delivery timelines vary depending on project scope and complexity. We establish clear milestones and timelines from the beginning to ensure transparency and efficient execution.
                </div>
            </details>

            <details>
                <summary>Do you provide recruitment services?</summary>
                <div class="mowinton-faq-content">
                    Yes. We assist organizations in identifying, attracting, and securing qualified talent to support business growth and long-term success.
                </div>
            </details>

            <details>
                <summary>Can I schedule a consultation online?</summary>
                <div class="mowinton-faq-content">
                    Absolutely. You can book a consultation directly through our online scheduling system and choose a time that best fits your availability.
                </div>
            </details>
        </div>
    </section>

    <!-- Consultation Section -->
    <section class="consultation-section">
        <div class="consultation-grid">
            <div>
                <p class="consultation-tag">Book a Meeting</p>
                <h2 class="consultation-title">Schedule a Consultation with Mowinton</h2>
                <p class="consultation-desc">
                    Connect with our team to discuss your business needs, project goals, IT consulting requirements, recruitment support, or partnership opportunities.
                </p>
                <ul class="consultation-list">
                    <li>✓ 30-minute consultation session</li>
                    <li>✓ Online meeting with JT</li>
                    <li>✓ Choose a time that suits your schedule</li>
                </ul>
                <p>
                    <a class="btn-primary" href="https://xjp60y91kpb5.jp.larksuite.com/scheduler/fe0265a819a3b1fd" target="_blank" rel="noopener noreferrer" style="padding:15px 34px;">
                        Open Booking Page
                    </a>
                </p>
            </div>

            <div class="consultation-card">
                <iframe src="https://xjp60y91kpb5.jp.larksuite.com/scheduler/fe0265a819a3b1fd" 
                        title="Schedule Online Meeting with Mowinton Consulting" 
                        width="100%" 
                        height="620"></iframe>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
