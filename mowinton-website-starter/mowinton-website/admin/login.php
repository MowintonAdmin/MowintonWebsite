<?php
/**
 * Admin Login Page
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// Redirect if already authenticated
if (is_admin_logged_in()) {
    header('Location: /admin/');
    exit;
}

$error = '';
$info = '';

if (isset($_GET['expired'])) {
    $info = 'Your session has expired due to inactivity. Please log in again.';
} elseif (isset($_GET['logged_out'])) {
    $info = 'You have been logged out successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!admin_verify_csrf($submittedToken)) {
        $error = 'Security validation failed. Please refresh the page and try again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            $auth = authenticate_admin($username, $password);
            if ($auth['success']) {
                $returnTo = $_GET['return_to'] ?? '/admin/';
                // Sanitize redirect target to avoid open redirect vulnerability
                if (!str_starts_with($returnTo, '/admin/')) {
                    $returnTo = '/admin/';
                }
                header("Location: {$returnTo}");
                exit;
            } else {
                $error = $auth['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Mowinton Consulting</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-login-wrapper">
    <div class="admin-login-card">
        <div class="admin-login-logo">
            <a href="/" target="_blank" title="Mowinton Consulting">
                <img src="/img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg" alt="Mowinton Consulting">
            </a>
        </div>

        <h1>Admin Portal</h1>
        <p class="subtitle">Mowinton Consulting Staff &amp; Management</p>

        <?php if ($info !== ''): ?>
            <div class="admin-alert admin-alert-info">
                <span>ℹ</span>
                <span><?php echo htmlspecialchars($info); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="admin-alert admin-alert-danger">
                <span>⚠</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <?php echo admin_csrf_field(); ?>

            <div class="admin-form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="admin-form-input" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                       required autofocus placeholder="admin">
            </div>

            <div class="admin-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="admin-form-input" 
                       required placeholder="••••••••">
            </div>

            <div style="margin-top: 28px;">
                <button type="submit" class="admin-btn admin-btn-primary">
                    Sign In to Dashboard →
                </button>
            </div>
        </form>

        <div style="margin-top: 32px; font-size: 13px; color: var(--admin-text-muted);">
            <a href="/" style="color: var(--admin-blue); text-decoration: none; font-weight: 600;">
                ← Back to Mowinton Public Website
            </a>
        </div>
    </div>
</div>

</body>
</html>
