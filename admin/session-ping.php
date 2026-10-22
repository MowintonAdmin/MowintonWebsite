<?php
/**
 * Admin Session Activity Ping Endpoint
 * Allows client-side keepalive while the admin is actively interacting with the page.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// check_session_timeout() has already verified and updated last_activity
echo json_encode([
    'success'         => true,
    'timeout_seconds' => get_session_timeout_seconds(),
    'last_activity'   => $_SESSION['last_activity'] ?? time(),
]);
