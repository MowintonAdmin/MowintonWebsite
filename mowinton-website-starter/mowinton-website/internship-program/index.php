<?php
$page_title = 'Internship Program - Mowinton Consulting';
$page_description = 'Build real-world experience, develop professional skills, and start your career journey with Mowinton Consulting.';
$active_nav = 'internship';
$current_page = 'internship';
$root_path = '..';

require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
    <div class="internship-page-header" style="max-width: 760px; margin: 60px auto 40px; text-align: center; padding: 0 20px;">
        <h1 style="font-size: clamp(36px, 5.5vw, 56px); font-weight: 800; color: #132033; margin: 0 0 15px; letter-spacing: -1px; line-height: 1.2;">Internship</h1>
        <p style="font-size: 18px; line-height: 1.8; color: #475569; margin: 0;">
            Build real-world experience, develop professional skills, and start your career journey with Mowinton Consulting.
        </p>
    </div>

    <section class="internship-page">
        <div class="internship-hero">
            <div class="internship-badge">Internship Program</div>
            <h2>Kickstart Your Career With Mowinton</h2>
            <p>
                Gain real-world experience, work with professionals, and build practical skills through internship opportunities in IT, consulting, recruitment, and business support.
            </p>
            <a class="internship-btn" href="<?php echo $base_url; ?>/submit-job-application/?position=Internship">Apply Now</a>
        </div>

        <div class="section-title internship-section-title">
            <h2>Why Join Our Internship Program?</h2>
            <p>
                Our internship program is designed to help students and fresh graduates develop professional confidence, workplace skills, and industry knowledge.
            </p>
        </div>

        <div class="benefit-grid">
            <div class="benefit-card">
                <div class="benefit-icon benefit-icon-emoji">🚀</div>
                <h3>Hands-On Experience</h3>
                <p>Work on real tasks and gain exposure to actual business operations and project environments.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon benefit-icon-emoji">🤝</div>
                <h3>Mentorship</h3>
                <p>Learn directly from experienced professionals and supportive project teams.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon benefit-icon-emoji">📈</div>
                <h3>Skill Development</h3>
                <p>Improve communication, problem-solving, teamwork, and technical skills.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon benefit-icon-emoji">🎯</div>
                <h3>Career Growth</h3>
                <p>Build a strong foundation for your future career and professional journey.</p>
            </div>
        </div>

        <div class="area-section">
            <div class="section-title internship-section-title" style="margin-top: 0;">
                <h2>Internship Areas</h2>
                <p>Explore internship opportunities across multiple business and technology functions.</p>
            </div>

            <div class="area-grid">
                <div class="area-card">💻 Software Development</div>
                <div class="area-card">📊 Business Analysis</div>
                <div class="area-card">📁 Project Management</div>
                <div class="area-card">🛠 IT Support</div>
                <div class="area-card">📢 Digital Marketing</div>
                <div class="area-card">👥 HR &amp; Recruitment</div>
            </div>
        </div>

        <div class="section-title internship-section-title">
            <h2>Application Process</h2>
            <p>Simple steps to begin your internship journey with us.</p>
        </div>

        <div class="process-grid">
            <div class="process-card">
                <div class="process-number">1</div>
                <h3>Submit Application</h3>
            </div>

            <div class="process-card">
                <div class="process-number">2</div>
                <h3>Screening</h3>
            </div>

            <div class="process-card">
                <div class="process-number">3</div>
                <h3>Interview</h3>
            </div>

            <div class="process-card">
                <div class="process-number">4</div>
                <h3>Offer</h3>
            </div>

            <div class="process-card">
                <div class="process-number">5</div>
                <h3>Start Internship</h3>
            </div>
        </div>

        <div class="cta-box internship-cta-box">
            <h2>Ready to Apply?</h2>
            <p>Submit your resume and start your professional journey with Mowinton Consulting.</p>
            <a class="internship-btn" href="<?php echo $base_url; ?>/submit-job-application/?position=Internship">Apply Internship</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
