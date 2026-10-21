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
                <img src="/img/4fb4414e-52aaaaaaad-3-300x113.webp" alt="Mowinton Consulting" onerror="this.onerror=null; this.src='/img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg';">
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
                <div class="password-input-wrap">
                    <input type="password" id="password" name="password" class="admin-form-input" 
                           required placeholder="••••••••">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Show password" title="Show password">
                        <svg class="eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="eye-closed" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
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

<script>
function togglePasswordVisibility(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    var eyeOpen = btn.querySelector('.eye-open');
    var eyeClosed = btn.querySelector('.eye-closed');
    if (input.type === 'password') {
        input.type = 'text';
        if (eyeOpen) eyeOpen.style.display = 'none';
        if (eyeClosed) eyeClosed.style.display = 'block';
        btn.setAttribute('aria-label', 'Hide password');
        btn.setAttribute('title', 'Hide password');
    } else {
        input.type = 'password';
        if (eyeOpen) eyeOpen.style.display = 'block';
        if (eyeClosed) eyeClosed.style.display = 'none';
        btn.setAttribute('aria-label', 'Show password');
        btn.setAttribute('title', 'Show password');
    }
}
</script>
</body>
</html>
