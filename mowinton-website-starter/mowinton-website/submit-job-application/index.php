<?php
$page_title = 'Submit Job Application - Mowinton Consulting';
$current_page = 'careers';
$root_path = '..';

require_once __DIR__ . '/../includes/process_job_application.php';

// Optional pre-selected position from query parameter (e.g. ?position=Software+Engineer)
$preselected_position = filter_input(INPUT_GET, 'position', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="submit-job-body" style="background:#ece7de;padding:40px 0 80px 0;">
    <main id="main-content">
        <section style="max-width:1200px;margin:30px auto 80px auto;padding:0 30px;font-family:var(--font-primary);">
            <div style="text-align:center;margin-bottom:50px;">
                <h1 style="font-size:clamp(36px, 5.5vw, 56px);font-weight:800;color:#132033;margin:0 0 15px;letter-spacing:-1px;">
                    Submit Job Application
                </h1>
                <p style="max-width:760px;margin:auto;font-size:18px;line-height:1.8;color:#475569;">
                    Complete the application form below and submit your details to join Mowinton Consulting. Our recruitment team will review your application and contact shortlisted candidates.
                </p>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:45px;align-items:start;" class="submit-job-grid-wrap">
                <!-- Left Dark Information Card -->
                <div style="background:linear-gradient(135deg,#132033,#1e3a5f);color:#fff;border-radius:28px;padding:45px 38px;box-shadow:0 22px 50px rgba(15,23,42,.18);">
                    <p style="margin:0 0 14px;color:#67e8f9;font-size:14px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">
                        JOIN OUR TEAM
                    </p>

                    <h2 style="margin:0 0 18px;font-size:clamp(28px, 4vw, 40px);line-height:1.15;font-weight:800;color:#ffffff;">
                        Start Your Career With Mowinton
                    </h2>

                    <p style="margin:0 0 32px;color:#dbeafe;font-size:16px;line-height:1.8;">
                        Submit your application and take the next step in your career with Mowinton Consulting. We welcome passionate individuals in IT, consulting, recruitment, and digital solutions.
                    </p>

                    <div style="display:grid;gap:22px;">
                        <div style="display:flex;gap:16px;align-items:flex-start;">
                            <div style="width:44px;height:44px;min-width:44px;border-radius:14px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:20px;">💼</div>
                            <div>
                                <strong style="display:block;margin-bottom:4px;font-size:17px;color:#ffffff;">Career Opportunities</strong>
                                <span style="color:#dbeafe;font-size:15px;line-height:1.6;">Explore roles in technology, consulting, recruitment, and project solutions.</span>
                            </div>
                        </div>

                        <div style="display:flex;gap:16px;align-items:flex-start;">
                            <div style="width:44px;height:44px;min-width:44px;border-radius:14px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:20px;">📄</div>
                            <div>
                                <strong style="display:block;margin-bottom:4px;font-size:17px;color:#ffffff;">Submit Your Details</strong>
                                <span style="color:#dbeafe;font-size:15px;line-height:1.6;">Fill in the form and provide your latest information for our recruitment team.</span>
                            </div>
                        </div>

                        <div style="display:flex;gap:16px;align-items:flex-start;">
                            <div style="width:44px;height:44px;min-width:44px;border-radius:14px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:20px;">✉️</div>
                            <div>
                                <strong style="display:block;margin-bottom:4px;font-size:17px;color:#ffffff;">Need Assistance?</strong>
                                <span style="color:#dbeafe;font-size:15px;line-height:1.6;">Contact us at <a href="mailto:jt@mowinton.com" style="color:#67e8f9;text-decoration:underline;">jt@mowinton.com</a></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Application Form Card -->
                <div style="background:#ffffff;border-radius:28px;padding:45px 42px;box-shadow:0 22px 50px rgba(15,23,42,.12);border:1px solid #e2e8f0;">
                    <p style="margin:0 0 10px;color:#2563eb;font-size:14px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">
                        APPLICATION FORM
                    </p>

                    <h2 style="margin:0 0 10px;color:#132033;font-size:clamp(28px, 4vw, 36px);line-height:1.2;font-weight:800;">
                        Submit Job Application
                    </h2>

                    <p style="margin:0 0 28px;color:#64748b;font-size:16px;line-height:1.7;">
                        Fill in the form below and our team will review your application. Shortlisted candidates will be contacted soon.
                    </p>

                    <?php if ($app_feedback): ?>
                        <div class="form-alert is-<?php echo htmlspecialchars($app_feedback['type']); ?>" style="display:block;margin-bottom:20px;">
                            <?php echo htmlspecialchars($app_feedback['text']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="job_application_submit" value="1">
                        <input type="hidden" name="resume_required" value="1">
                        <input type="hidden" name="has_declaration" value="1">

                        <!-- Honeypot -->
                        <div class="form-honeypot" aria-hidden="true">
                            <label for="email_type_name">email Type name</label>
                            <input type="text" id="email_type_name" name="email_type_name" tabindex="-1" autocomplete="off">
                        </div>

                        <!-- Full Name -->
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

                        <!-- Email Address -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="submit_email">Email Address <span class="form-required">*</span></label>
                            <input type="email" id="submit_email" name="email" class="app-form-control" placeholder="name@example.com" required autocomplete="email">
                        </div>

                        <!-- Position Applying For -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="submit_position">Position Applying For</label>
                            <input type="text" id="submit_position" name="position" class="app-form-control" 
                                   value="<?php echo htmlspecialchars($preselected_position); ?>" 
                                   placeholder="e.g. Software Engineer">
                        </div>

                        <!-- Current Location -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="submit_location">Current Location <span class="form-required">*</span></label>
                            <input type="text" id="submit_location" name="location" class="app-form-control" placeholder="e.g. Kuala Lumpur, Malaysia" required>
                        </div>

                        <!-- Highest Education Level -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="submit_education">Highest Education Level</label>
                            <input type="text" id="submit_education" name="education" class="app-form-control" placeholder="Diploma / Bachelor's / Master's Degree">
                        </div>

                        <!-- Years of Working Experience -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="submit_experience">Years of Working Experience</label>
                            <input type="text" id="submit_experience" name="experience" class="app-form-control" placeholder="Fresh Graduate / 1-3 years / 3-5 years">
                        </div>

                        <!-- Upload CV / Resume -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="submit_resume">Upload CV / Resume <span class="form-required">*</span></label>
                            <input type="file" id="submit_resume" name="resume" class="app-form-file" accept=".pdf,.doc,.docx" required>
                        </div>

                        <!-- Confirmation Radio / Checkbox -->
                        <div class="app-form-group">
                            <label class="app-form-label">Have you uploaded your Resume from the link above? <span class="form-required">*</span></label>
                            <div class="app-checkbox-group">
                                <label class="app-checkbox-label">
                                    <input type="radio" name="resume_confirm" value="yes" checked required>
                                    <span>Yes, I have uploaded my Resume</span>
                                </label>
                                <label class="app-checkbox-label">
                                    <input type="radio" name="resume_confirm" value="no" required>
                                    <span>I have not uploaded my Resume</span>
                                </label>
                            </div>
                        </div>

                        <!-- Expected Monthly Salary -->
                        <div class="app-form-group">
                            <label class="app-form-label" for="submit_salary">Expected Monthly Salary</label>
                            <input type="text" id="submit_salary" name="salary" class="app-form-control" placeholder="e.g. RM 4,500">
                        </div>

                        <!-- Declaration Checkbox -->
                        <div class="app-form-group" style="margin-top:25px;margin-bottom:30px;">
                            <label class="app-form-label">Declaration Checkbox <span class="form-required">*</span></label>
                            <label class="app-checkbox-label">
                                <input type="checkbox" name="declaration" value="1" required>
                                <span>I confirm that the information provided is accurate and complete.</span>
                            </label>
                        </div>

                        <button type="submit" class="app-btn-submit" style="background:#0274be;">
                            Submit Application
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<style>
@media (max-width: 900px) {
    .submit-job-grid-wrap {
        grid-template-columns: 1fr !important;
        gap: 35px !important;
    }
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
