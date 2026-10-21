<?php
$page_title = 'Interview Guide - Mowinton Consulting';
$page_description = 'Prepare confidently for your next interview with professional tips, step-by-step guidance, and practical advice from Mowinton Consulting.';
$active_nav = 'interview';
$current_page = 'interview';
$root_path = '..';

require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
    <section class="interview-page">
        <div class="interview-page-title">
            <h1>Interview Preparation</h1>
            <p>
                Prepare confidently for your next interview with professional tips, step-by-step guidance, and practical advice from Mowinton Consulting.
            </p>
        </div>

        <div class="interview-hero">
            <div class="interview-badge">Career Resources</div>
            <h2>Interview Preparation Guide</h2>
            <p>
                Prepare confidently for your next interview with professional tips, step-by-step guidance, and practical advice from Mowinton Consulting.
            </p>
        </div>

        <div class="section-title">
            <h2>Your Interview Journey</h2>
            <p>Follow these steps to prepare, perform, and follow up professionally.</p>
        </div>

        <div class="timeline">
            <!-- Step 1 -->
            <div class="step-card">
                <div class="step-content">
                    <div class="step-number">1</div>
                    <h3>Research Before the Interview</h3>
                    <p>Understand the company, services, job role, and industry background before attending the interview.</p>
                    <ul>
                        <li>Read about the company background</li>
                        <li>Understand the job description</li>
                        <li>Prepare questions for the interviewer</li>
                    </ul>
                </div>
                <figure>
                    <img src="<?php echo $base_url; ?>/img/interview-office-prep.jpg" alt="Office preparation" loading="lazy">
                </figure>
            </div>

            <!-- Step 2 -->
            <div class="step-card reverse">
                <figure>
                    <img src="<?php echo $base_url; ?>/img/interview-resume-prep.jpg" alt="Resume preparation" loading="lazy">
                </figure>
                <div class="step-content">
                    <div class="step-number">2</div>
                    <h3>Prepare Your Resume</h3>
                    <p>Your resume should be simple, clean, and focused on your most relevant skills, experience, and achievements.</p>
                    <ul>
                        <li>Use clear formatting</li>
                        <li>Highlight relevant skills</li>
                        <li>Save your resume as PDF</li>
                    </ul>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-card">
                <div class="step-content">
                    <div class="step-number">3</div>
                    <h3>Practice Common Questions</h3>
                    <p>Prepare answers for common interview questions so you can respond clearly and confidently.</p>
                    <ul>
                        <li>Tell us about yourself</li>
                        <li>Why do you want this role?</li>
                        <li>What are your strengths?</li>
                    </ul>
                </div>
                <figure>
                    <img src="<?php echo $base_url; ?>/img/interview-practice.jpg" alt="Interview practice" loading="lazy">
                </figure>
            </div>

            <!-- Step 4 -->
            <div class="step-card reverse">
                <figure>
                    <img src="<?php echo $base_url; ?>/img/interview-professional.jpg" alt="Professional interview" loading="lazy">
                </figure>
                <div class="step-content">
                    <div class="step-number">4</div>
                    <h3>Attend Professionally</h3>
                    <p>During the interview, stay calm, answer clearly, and show genuine interest in the company and role.</p>
                    <ul>
                        <li>Be punctual</li>
                        <li>Dress professionally</li>
                        <li>Use real examples in your answers</li>
                    </ul>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="step-card">
                <div class="step-content">
                    <div class="step-number">5</div>
                    <h3>Follow Up After Interview</h3>
                    <p>After the interview, follow up politely and remain professional while waiting for the next update.</p>
                    <ul>
                        <li>Send a thank-you message</li>
                        <li>Check your email regularly</li>
                        <li>Prepare for the next stage</li>
                    </ul>
                </div>
                <figure>
                    <img src="<?php echo $base_url; ?>/img/interview-followup.jpg" alt="Online follow up" loading="lazy">
                </figure>
            </div>
        </div>

        <div class="section-title">
            <h2>Common Interview Questions</h2>
            <p>Use these examples to prepare simple and confident answers.</p>
        </div>

        <div class="question-grid">
            <div class="question-card">
                <h3>Tell us about yourself.</h3>
                <p>Briefly introduce your background, education, experience, and career interest.</p>
            </div>

            <div class="question-card">
                <h3>Why do you want this role?</h3>
                <p>Explain how the position matches your skills, goals, and professional interests.</p>
            </div>

            <div class="question-card">
                <h3>What are your strengths?</h3>
                <p>Mention relevant strengths such as teamwork, communication, leadership, or problem-solving.</p>
            </div>

            <div class="question-card">
                <h3>Describe a challenge you faced.</h3>
                <p>Use a real example and explain how you solved the issue professionally.</p>
            </div>
        </div>

        <div class="cta-box">
            <h2>Looking for Career Opportunities?</h2>
            <p>Explore current openings or submit your resume to Mowinton Consulting.</p>
            <a href="<?php echo $base_url; ?>/careers-2/">View Careers</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
