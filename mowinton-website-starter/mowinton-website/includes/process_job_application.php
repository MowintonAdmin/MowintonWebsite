<?php
/**
 * Job Application Form Backend Processor
 * Validates submissions, safely stores resumes, and persists records into MySQL `job_applications`.
 * Reusable across Careers, Submit Job Application, and Job Detail pages.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$app_feedback = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_application_submit'])) {
    $is_ajax = isset($_POST['is_ajax']);
    $response = ['success' => false, 'message' => ''];

    // 1. Anti-spam honeypot
    if (!empty($_POST['email_type_name'])) {
        $response['message'] = 'Spam submission detected.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        $app_feedback = ['type' => 'error', 'text' => $response['message']];
    } else {
        // 2. Validate CSRF token
        $submitted_token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $submitted_token)) {
            $response['message'] = 'Security validation failed. Please refresh the page and try again.';
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            $app_feedback = ['type' => 'error', 'text' => $response['message']];
        } else {
            // 3. Sanitize inputs
            $first_name  = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $last_name   = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $email       = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
            $phone       = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $position    = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $location    = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $education   = trim(filter_input(INPUT_POST, 'education', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $experience  = trim(filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $salary      = trim(filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $declaration = isset($_POST['declaration']);

            $full_name = trim("{$first_name} {$last_name}");
            if ($full_name === '' && !empty($_POST['name'])) {
                $full_name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            }

            // Determine application type (job vs internship)
            $is_internship = (
                stripos($position, 'intern') !== false ||
                (isset($_POST['application_type']) && $_POST['application_type'] === 'internship')
            );
            $application_type = $is_internship ? 'internship' : 'job';

            if (empty($full_name) || empty($first_name) || empty($last_name)) {
                $response['message'] = 'Please enter your full name (both First and Last name).';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = 'Please enter a valid email address.';
            } elseif (empty($location) && empty($phone)) {
                $response['message'] = 'Please enter your current location or phone number.';
            } elseif (!$declaration && isset($_POST['has_declaration'])) {
                $response['message'] = 'Please check the declaration box before submitting.';
            } else {
                // 4. File Upload Handling
                $upload_ok = true;
                $uploaded_filename = '';
                $original_filename = '';
                $file_size = 0;

                if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $file_error = $_FILES['resume']['error'];
                    if ($file_error !== UPLOAD_ERR_OK) {
                        $upload_ok = false;
                        $response['message'] = 'File upload error occurred. Please try again.';
                    } else {
                        $file_size = (int)$_FILES['resume']['size'];
                        $file_tmp  = $_FILES['resume']['tmp_name'];
                        $original_filename = (string)$_FILES['resume']['name'];
                        $file_ext  = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                        $allowed_exts = ['pdf', 'doc', 'docx'];

                        // Max 5MB
                        if ($file_size > 5 * 1024 * 1024) {
                            $upload_ok = false;
                            $response['message'] = 'The uploaded file is too large. Maximum allowed size is 5MB.';
                        } elseif (!in_array($file_ext, $allowed_exts, true)) {
                            $upload_ok = false;
                            $response['message'] = 'Invalid file format. Please upload a PDF, DOC, or DOCX file.';
                        } else {
                            $target_dir = __DIR__ . '/../uploads/resumes/';
                            if (!is_dir($target_dir)) {
                                mkdir($target_dir, 0755, true);
                            }
                            $safe_filename = 'cv_' . bin2hex(random_bytes(16)) . '_' . time() . '.' . $file_ext;
                            $target_file = $target_dir . $safe_filename;
                            if (move_uploaded_file($file_tmp, $target_file)) {
                                $uploaded_filename = $safe_filename;
                            } else {
                                $upload_ok = false;
                                $response['message'] = 'Failed to save uploaded resume. Please try again.';
                            }
                        }
                    }
                } else {
                    if (isset($_POST['resume_required']) && $_POST['resume_required'] === '1') {
                        $upload_ok = false;
                        $response['message'] = 'Please upload your CV / Resume.';
                    }
                }

                // 5. Persist to MySQL
                if ($upload_ok) {
                    try {
                        $db = get_db_connection();
                        $refId = 'MOW-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
                        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
                        $contact_phone = $phone ?: $location;

                        $stmt = $db->prepare('
                            INSERT INTO job_applications (
                                reference_id, full_name, email, phone, position, application_type,
                                education, experience, expected_salary, resume_filename,
                                resume_original_name, resume_filesize, status, ip_address, user_agent, created_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "new", ?, ?, NOW())
                        ');

                        $stmt->execute([
                            $refId,
                            $full_name,
                            $email,
                            $contact_phone,
                            $position ?: 'General Application',
                            $application_type,
                            $education ?: null,
                            $experience ?: null,
                            $salary ?: null,
                            $uploaded_filename,
                            $original_filename,
                            $file_size,
                            $ip,
                            $ua,
                        ]);

                        $response['success'] = true;
                        $response['reference_id'] = $refId;
                        $response['message'] = "Thank you! Your application has been successfully submitted (Reference ID: {$refId}). Our recruitment team will review your application and contact shortlisted candidates.";

                    } catch (Exception $e) {
                        error_log('Job Application DB error: ' . $e->getMessage());
                        $response['message'] = 'Unable to save your application due to a system error. Please contact us directly at info@mowinton.com.';
                    }
                }
            }

            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            $app_feedback = [
                'type' => $response['success'] ? 'success' : 'error',
                'text' => $response['message']
            ];
        }
    }
}
