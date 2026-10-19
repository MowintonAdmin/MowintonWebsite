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

// Normalize path for consistent, case-insensitive security checks (prevents Windows case bypass)
$normalizedPath = strtolower(str_replace('\\', '/', $path));

// 1. Strict Security Block: Sensitive Directories & Private Uploads
$blockedPrefixes = [
    '/uploads/resumes',
    '/storage/resumes',
    '/storage',
    '/includes',
    '/database',
    '/scripts',
    '/.git',
];

foreach ($blockedPrefixes as $prefix) {
    if (str_starts_with($normalizedPath, $prefix)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "403 Forbidden: Direct access to protected path '{$prefix}' is strictly prohibited.";
        exit;
    }
}

// 2. Strict Security Block: Sensitive Extensions, Backup Archives, and Hidden Files
$blockedExtensions = [
    'env',
    'sql',
    'log',
    'htaccess',
    'md',
    'zip',
    'tar',
    'gz',
    'tgz',
    'bak',
    'backup',
    'ini',
    'conf',
    'sh',
    'bat',
    'cmd',
];

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if (
    in_array($ext, $blockedExtensions, true) ||
    str_contains($normalizedPath, '.env') ||
    preg_match('#/\.[a-zA-Z0-9]#', $normalizedPath)
) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "403 Forbidden: Access to configuration, backup, or source files is prohibited.";
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
