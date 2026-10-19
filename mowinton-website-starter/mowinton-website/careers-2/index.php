<?php
$page_title = 'Career - Mowinton Consulting';
$current_page = 'careers';
$root_path = '..';

require_once __DIR__ . '/../includes/process_job_application.php';
require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
    <!-- Section 1: Career Top Title & Hero Card -->
    <section class="career-page">
        <div class="career-title">
            <h1>Career</h1>
            <p>
                Discover exciting career opportunities and become part of a team dedicated to innovation,
                professional growth, and delivering impactful solutions.
            </p>
        </div>

        <div class="career-hero-card">
            <div class="career-main">
                <div class="career-text">
                    <span class="career-badge">Career Opportunities</span>
                    <h2>Join Our Team</h2>
                    <div class="career-line"></div>
                    <p>
                        At Mowinton Consulting, we are always looking for passionate professionals,
                        fresh graduates, and talented individuals ready to contribute to innovative
                        projects, recruitment solutions, and digital transformation initiatives.
                    </p>
                    <a href="<?php echo $base_url; ?>/submit-job-application/" class="career-btn">
                        Apply Now <span>→</span>
                    </a>
                </div>

                <div class="career-photo-area">
                    <div class="career-photo-card">
                        <img src="<?php echo $base_url; ?>/img/placeholders/careers-team-group.svg" 
                             alt="Mowinton Consulting Team" 
                             width="500" 
                             height="280" 
                             loading="lazy">
                    </div>
                </div>
            </div>

            <!-- Bottom 4 Benefits -->
            <div class="career-benefits">
                <div class="benefit-item">
                    <div class="benefit-icon">👥</div>
                    <div>
                        <h3>Great Team</h3>
                        <p>Work with talented and supportive professionals.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">📈</div>
                    <div>
                        <h3>Growth &amp; Learning</h3>
                        <p>Continuous learning and career advancement.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">♡</div>
                    <div>
                        <h3>Work-Life Balance</h3>
                        <p>We value your well-being and personal time.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🚀</div>
                    <div>
                        <h3>Impactful Work</h3>
                        <p>Contribute to meaningful projects and solutions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Job Openings Section -->
    <section class="mowinton-job-section">
        <div class="mowinton-job-container">
            <div class="mowinton-job-header">
                <p class="mowinton-job-badge">Career Opportunities</p>
                <h2>Job Openings</h2>
                <p>
                    Explore our latest job listings and discover opportunities to grow your career with Mowinton Consulting.
                </p>
            </div>

            <div class="mowinton-job-layout">
                <!-- Left Culture Card -->
                <div class="mowinton-job-image">
                    <img src="<?php echo $base_url; ?>/img/placeholders/careers-work-culture.svg" 
                         alt="Mowinton Careers" 
                         width="656" 
                         height="470" 
                         loading="lazy">
                    <div class="mowinton-job-overlay"></div>
                    <div class="mowinton-job-image-text">
                        <p>Join Mowinton</p>
                        <h3>Build Your Future With Us</h3>
                        <p>
                            Work with professionals, contribute to meaningful projects, and grow in a collaborative consulting environment.
                        </p>
                    </div>
                </div>

                <!-- Right 2x3 Grid of Jobs -->
                <div class="mowinton-job-grid">
                    <!-- Job 1 -->
                    <article class="mowinton-job-card">
                        <div>
                            <p class="type">Full Time</p>
                            <h3>Project Manager</h3>
                            <p class="desc">Lead smart factory and IT project initiatives across technical teams.</p>
                        </div>
                        <a href="<?php echo $base_url; ?>/project-manager-jd/">View Details →</a>
                    </article>

                    <!-- Job 2 -->
                    <article class="mowinton-job-card">
                        <div>
                            <p class="type">Full Time</p>
                            <h3>Software Engineer</h3>
                            <p class="desc">Develop software tools and improve technical workflows.</p>
                        </div>
                        <a href="<?php echo $base_url; ?>/software-engineer-jd/">View Details →</a>
                    </article>

                    <!-- Job 3 -->
                    <article class="mowinton-job-card">
                        <div>
                            <p class="type">Full Time</p>
                            <h3>Technical Lead</h3>
                            <p class="desc">Guide technical teams and deliver scalable digital solutions.</p>
                        </div>
                        <a href="<?php echo $base_url; ?>/technical-lead/">View Details →</a>
                    </article>

                    <!-- Job 4 -->
                    <article class="mowinton-job-card">
                        <div>
                            <p class="type">Full Time</p>
                            <h3>QA Engineer</h3>
                            <p class="desc">Execute software testing, automation, SIT and UAT activities.</p>
                        </div>
                        <a href="<?php echo $base_url; ?>/qa-engineer-jd/">View Details →</a>
                    </article>

                    <!-- Job 5 -->
                    <article class="mowinton-job-card">
                        <div>
                            <p class="type">Full Time</p>
                            <h3>Business Analyst</h3>
                            <p class="desc">Bridge business requirements and technical implementation.</p>
                        </div>
                        <a href="<?php echo $base_url; ?>/business-analyst-jd/">View Details →</a>
                    </article>

                    <!-- Job 6 -->
                    <article class="mowinton-job-card">
                        <div>
                            <p class="type">Internship</p>
                            <h3>Intern – Digital Marketing</h3>
                            <p class="desc">Support campaigns, content creation, analytics and social media.</p>
                        </div>
                        <a href="<?php echo $base_url; ?>/intern-digital-marketing/">View Details →</a>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3: Career Application Section -->
    <section class="career-section" id="apply-form">
        <div class="career-header">
            <div class="career-badge">Career Opportunities</div>
            <h2>Career</h2>
            <p>
                We are always looking for talented individuals who are passionate about technology,
                consulting, recruitment, and innovation. Submit your application and take the next
                step in your career with Mowinton Consulting.
            </p>
        </div>

        <div class="career-grid">
            <!-- Left Info Card -->
            <div class="career-info-card">
                <p class="small-title">Join Mowinton</p>
                <h2>Build Your Career With Us</h2>
                <p class="info-text">
                    Grow your skills, gain real industry experience, and work with a professional team
                    in technology, consulting, recruitment, and project solutions.
                </p>

                <div class="info-list">
                    <div>
                        <span>✓</span>
                        <p><strong>Professional Growth</strong><br>Opportunities to learn and improve your career skills.</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p><strong>Industry Exposure</strong><br>Work with experienced professionals and real projects.</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p><strong>Supportive Team</strong><br>Be part of a collaborative and friendly work environment.</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p><strong>Career Pathway</strong><br>Develop your potential and grow with the company.</p>
                    </div>
                </div>

                <div class="contact-box">
                    <p><strong>Need Assistance?</strong></p>
                    <p>Email us at <a href="mailto:info@mowinton.com" style="color:#67e8f9;">info@mowinton.com</a></p>
                </div>
            </div>

            <!-- Right Form Card -->
            <div class="career-form-card">
                <p class="small-title blue">Submit Application</p>
                <h2>Application Form</h2>
                <p>
                    Complete the form below and upload your latest CV/Resume. Our team will review
                    your application and contact shortlisted candidates.
                </p>

                <?php if ($app_feedback): ?>
                    <div class="form-alert is-<?php echo htmlspecialchars($app_feedback['type']); ?>" style="display:block;margin-bottom:20px;">
                        <?php echo htmlspecialchars($app_feedback['text']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#apply-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="job_application_submit" value="1">
                    <input type="hidden" name="resume_required" value="1">

                    <!-- Anti-spam Honeypot -->
                    <div class="form-honeypot" aria-hidden="true">
                        <label for="email_type_name">email Type name</label>
                        <input type="text" id="email_type_name" name="email_type_name" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="app-form-group">
                        <label class="app-form-label">Full Name <span class="form-required">*</span></label>
                        <div class="app-form-grid-2">
                            <div>
                                <input type="text" name="first_name" class="app-form-control" placeholder="First" required autocomplete="given-name">
                                <span class="app-form-label-sub">First</span>
                            </div>
                            <div>
                                <input type="text" name="last_name" class="app-form-control" placeholder="Last" required autocomplete="family-name">
                                <span class="app-form-label-sub">Last</span>
                            </div>
                        </div>
                    </div>

                    <div class="app-form-group">
                        <label class="app-form-label" for="app_email">Email Address <span class="form-required">*</span></label>
                        <input type="email" id="app_email" name="email" class="app-form-control" placeholder="name@example.com" required autocomplete="email">
                    </div>

                    <div class="app-form-group">
                        <label class="app-form-label" for="app_position">Position Applying For</label>
                        <input type="text" id="app_position" name="position" class="app-form-control" placeholder="e.g. Software Engineer, Project Manager">
                    </div>

                    <div class="app-form-group">
                        <label class="app-form-label" for="app_location">Current Location <span class="form-required">*</span></label>
                        <input type="text" id="app_location" name="location" class="app-form-control" placeholder="City, State, Country" required>
                    </div>

                    <div class="app-form-group">
                        <label class="app-form-label" for="app_education">Highest Education Level</label>
                        <input type="text" id="app_education" name="education" class="app-form-control" placeholder="Diploma, Bachelor's, Master's, etc.">
                    </div>

                    <div class="app-form-group">
                        <label class="app-form-label" for="app_experience">Years of Working Experience</label>
                        <input type="text" id="app_experience" name="experience" class="app-form-control" placeholder="Fresh Graduate, 1-3 years, etc.">
                    </div>

                    <div class="app-form-group">
                        <label class="app-form-label" for="app_resume">Upload CV / Resume <span class="form-required">*</span></label>
                        <input type="file" id="app_resume" name="resume" class="app-form-file" accept=".pdf,.doc,.docx" required>
                    </div>

                    <button type="submit" class="app-btn-submit">
                        Submit Application
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
