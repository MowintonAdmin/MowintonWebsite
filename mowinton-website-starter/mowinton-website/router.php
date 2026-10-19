<?php
/**
 * Local Development Server Router for PHP Built-in Server (`php -S`)
 * Enforces security rules, protects private uploads and internal files,
 * and replicates Apache/LiteSpeed .htaccess behavior during local development.
 *
 * Usage:
 *   php -S 127.0.0.1:8000 router.php
 */

declare(strict_types=1);

$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$path   = parse_url($rawUri, PHP_URL_PATH);
$path   = urldecode($path ?: '/');

// 1. Strict Security Block: Sensitive Directories & Files
$blockedPrefixes = [
    '/uploads/resumes',
    '/storage/resumes',
    '/includes',
    '/database',
    '/scripts',
];

$blockedExtensions = [
    'env',
    'sql',
    'log',
    'htaccess',
    'md',
];

// Block direct access to protected directories
foreach ($blockedPrefixes as $prefix) {
    if (str_starts_with($path, $prefix)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "403 Forbidden: Direct access to protected directory '{$prefix}' is strictly prohibited.";
        exit;
    }
}

// Block access to sensitive file extensions (e.g., .env, .sql)
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if (in_array($ext, $blockedExtensions, true) || str_contains($path, '.env')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "403 Forbidden: Access to configuration or source files is prohibited.";
    exit;
}

$fullPath = __DIR__ . $path;

// 2. Directory Routing: If path is a directory, serve index.php inside it
if (is_dir($fullPath)) {
    $indexPath = rtrim($fullPath, '/\\') . DIRECTORY_SEPARATOR . 'index.php';
    if (file_exists($indexPath)) {
        require $indexPath;
        exit;
    }
}

// 3. Static Files: Allow normal public static assets (CSS, JS, images, fonts)
if ($path !== '/' && file_exists($fullPath) && !is_dir($fullPath)) {
    return false; // Tell PHP built-in server to serve static file
}

// 4. Default: Run root index.php
require __DIR__ . '/index.php';
