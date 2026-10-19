<?php
/**
 * Contact Form Backend Processor
 * Validates submissions and persists records into MySQL `contact_messages`.
 * Reusable across Home, Services, and About pages.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$feedback = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['contact_submit']) || (isset($_POST['email']) && !isset($_POST['job_application_submit'])))) {
    $is_ajax = isset($_POST['is_ajax']);
    $response = ['success' => false, 'message' => ''];

    // 1. Anti-spam honeypot check
    if (!empty($_POST['email_type_name'])) {
        $response['message'] = 'Spam submission detected.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        $feedback = ['type' => 'error', 'text' => $response['message']];
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
            $feedback = ['type' => 'error', 'text' => $response['message']];
        } else {
            // 3. Sanitize and validate inputs
            $name    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $company = trim(filter_input(INPUT_POST, 'company', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $email   = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
            $phone   = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? ($company ? "Inquiry from {$company}" : 'General Website Inquiry'));
            $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

            if (empty($name)) {
                $response['message'] = 'Please enter your name.';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = 'Please enter a valid email address.';
            } elseif (empty($message)) {
                $response['message'] = 'Please enter your message.';
            } else {
                try {
                    $db = get_db_connection();
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

                    $stmt = $db->prepare('
                        INSERT INTO contact_messages (name, email, phone, subject, message, status, ip_address, user_agent, created_at)
                        VALUES (?, ?, ?, ?, ?, "new", ?, ?, NOW())
                    ');
                    $stmt->execute([$name, $email, $phone ?: null, $subject ?: null, $message, $ip, $ua]);

                    $response['success'] = true;
                    $response['message'] = 'Thank you! Your message has been sent successfully. Our team will contact you shortly.';

                } catch (Exception $e) {
                    error_log('Contact form DB error: ' . $e->getMessage());
                    $response['message'] = 'Unable to save your message due to a system error. Please contact us directly at info@mowinton.com.';
                }
            }

            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            $feedback = [
                'type' => $response['success'] ? 'success' : 'error',
                'text' => $response['message']
            ];
        }
    }
}
