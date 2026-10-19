<?php
/**
 * Secure Resume Streaming Endpoint
 * Authenticates administrator, checks record existence, prevents path traversal,
 * and streams file safely without exposing web URLs.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// 1. Enforce Authentication
$currentUser = require_admin_login();

// 2. Validate Application ID parameter
$appId = (int)($_GET['id'] ?? 0);
if ($appId <= 0) {
    http_response_code(400);
    exit('400 Bad Request: Missing or invalid application ID.');
}

// 3. Query Application Record
try {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, reference_id, resume_filename, resume_original_name, resume_filesize FROM job_applications WHERE id = ?');
    $stmt->execute([$appId]);
    $app = $stmt->fetch();

    if (!$app || empty($app['resume_filename'])) {
        http_response_code(404);
        exit('404 Not Found: Resume record not found.');
    }

    // 4. Secure Path Traversal Prevention
    $safeFilename = basename($app['resume_filename']);
    $storageDir = realpath(__DIR__ . '/../uploads/resumes');
    $filePath = realpath($storageDir . DIRECTORY_SEPARATOR . $safeFilename);

    // Verify the file is strictly within the allowed directory
    if ($filePath === false || !str_starts_with($filePath, $storageDir) || !file_exists($filePath)) {
        http_response_code(404);
        exit('404 Not Found: The requested resume file does not exist on disk.');
    }

    // Sanitize download filename for the browser
    $downloadName = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $app['resume_original_name'] ?: 'resume.pdf');

    // 5. Audit Log Download
    record_audit_log('download_resume', 'job_applications', $appId, "Downloaded resume for {$app['reference_id']}");

    // 6. Send Protected Streaming Headers
    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: private, no-store, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');

    readfile($filePath);
    exit;

} catch (Exception $e) {
    error_log('Resume download error: ' . $e->getMessage());
    http_response_code(500);
    exit('500 Internal Server Error.');
}
