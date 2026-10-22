<?php
/**
 * Mowinton Administrator Authentication & Security Library
 * Session handling, role-based authorization, rate limiting, and CSRF protection.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Configure and start secure PHP session
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_start();
}

/**
 * Retrieves the configured admin session timeout in seconds from system settings.
 */
function get_session_timeout_seconds(): int {
    $minutes = (int)(get_system_setting('session_timeout_minutes', '30') ?? 30);
    $minutes = max(1, min(1440, $minutes));
    return $minutes * 60;
}

/**
 * Checks for session inactivity timeout based on dynamic system settings.
 */
function check_session_timeout(?int $timeoutSeconds = null): void {
    if (isset($_SESSION['admin_user_id'])) {
        if ($timeoutSeconds === null) {
            $timeoutSeconds = get_session_timeout_seconds();
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutSeconds)) {
            // Logged out due to inactivity
            $adminId = (int)$_SESSION['admin_user_id'];
            admin_logout();
            header('Location: /admin/login.php?expired=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}

// Enforce timeout check on every request using dynamic system setting
check_session_timeout();

/**
 * Checks if current request is authenticated as admin.
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_user_id']) && !empty($_SESSION['admin_role']);
}

/**
 * Requires admin authentication. Redirects to login if not logged in.
 */
function require_admin_login(): array {
    if (!is_admin_logged_in()) {
        $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/admin/');
        header("Location: /admin/login.php?return_to={$returnUrl}");
        exit;
    }

    return [
        'id'        => (int)$_SESSION['admin_user_id'],
        'username'  => (string)$_SESSION['admin_username'],
        'full_name' => (string)($_SESSION['admin_full_name'] ?? 'Admin'),
        'email'     => (string)($_SESSION['admin_email'] ?? ''),
        'role'      => (string)$_SESSION['admin_role'],
    ];
}

/**
 * Requires specific role (e.g. 'super_admin'). Returns 403 Forbidden if unauthorized.
 */
function require_role($roles): void {
    $user = require_admin_login();
    $allowed = is_array($roles) ? $roles : [$roles];

    if (!in_array($user['role'], $allowed, true)) {
        http_response_code(403);
        require_once __DIR__ . '/../admin/admin-header.php';
        echo '<div class="admin-card" style="margin: 40px auto; max-width: 600px; text-align: center;">';
        echo '<h2 style="color: #dc2626; margin-bottom: 12px;">403 — Unauthorized Action</h2>';
        echo '<p style="color: #475569; margin-bottom: 24px;">You do not have the required permissions (' . implode(', ', $allowed) . ') to access this resource or perform this action.</p>';
        echo '<a href="/admin/" class="admin-btn admin-btn-primary">Return to Dashboard</a>';
        echo '</div>';
        require_once __DIR__ . '/../admin/admin-footer.php';
        exit;
    }
}

/**
 * Checks if current admin has role.
 */
function admin_has_role(string $role): bool {
    return is_admin_logged_in() && ($_SESSION['admin_role'] === $role);
}

/**
 * Gets currently logged in admin user data.
 */
function current_admin_user(): ?array {
    if (!is_admin_logged_in()) {
        return null;
    }
    return [
        'id'        => (int)$_SESSION['admin_user_id'],
        'username'  => (string)$_SESSION['admin_username'],
        'full_name' => (string)($_SESSION['admin_full_name'] ?? 'Admin'),
        'email'     => (string)($_SESSION['admin_email'] ?? ''),
        'role'      => (string)$_SESSION['admin_role'],
    ];
}

/**
 * Rate Limiting for Admin Login (prevents brute-force attacks).
 * Allows max $maxAttempts failures in $lockoutMinutes.
 */
function check_login_rate_limit(string $username, string $ip, int $maxAttempts = 5, int $lockoutMinutes = 15): array {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM login_rate_limits
            WHERE (ip_address = ? OR username = ?)
              AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ');
        $stmt->execute([$ip, $username, $lockoutMinutes]);
        $attempts = (int)$stmt->fetchColumn();

        if ($attempts >= $maxAttempts) {
            return [
                'allowed'   => false,
                'remaining' => 0,
                'message'   => "Too many failed login attempts. Please wait {$lockoutMinutes} minutes before trying again.",
            ];
        }

        return [
            'allowed'   => true,
            'remaining' => $maxAttempts - $attempts,
            'message'   => '',
        ];
    } catch (Exception $e) {
        error_log('Rate limit check error: ' . $e->getMessage());
        return ['allowed' => true, 'remaining' => 5, 'message' => ''];
    }
}

/**
 * Records a failed login attempt.
 */
function record_failed_login(string $username, string $ip): void {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare('INSERT INTO login_rate_limits (ip_address, username, attempt_time) VALUES (?, ?, NOW())');
        $stmt->execute([$ip, $username]);
    } catch (Exception $e) {
        error_log('Failed to record login attempt: ' . $e->getMessage());
    }
}

/**
 * Clears rate limit entries upon successful login.
 */
function clear_rate_limits(string $username, string $ip): void {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare('DELETE FROM login_rate_limits WHERE ip_address = ? OR username = ?');
        $stmt->execute([$ip, $username]);
    } catch (Exception $e) {
        error_log('Failed to clear rate limits: ' . $e->getMessage());
    }
}

/**
 * Authenticates user credentials.
 */
function authenticate_admin(string $usernameOrEmail, string $password): array {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // 1. Check Rate Limit
    $rateLimit = check_login_rate_limit($usernameOrEmail, $ip);
    if (!$rateLimit['allowed']) {
        return ['success' => false, 'error' => $rateLimit['message']];
    }

    try {
        $db = get_db_connection();
        $stmt = $db->prepare('
            SELECT id, username, email, password_hash, full_name, role, status
            FROM admin_users
            WHERE (username = ? OR email = ?)
            LIMIT 1
        ');
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();

        if (!$user) {
            record_failed_login($usernameOrEmail, $ip);
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'This account has been suspended. Please contact a Super Administrator.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            record_failed_login($usernameOrEmail, $ip);
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        // Needs rehash if algorithm cost changed
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $rehashStmt = $db->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
            $rehashStmt->execute([$newHash, $user['id']]);
        }

        // Successful authentication: Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['admin_user_id']   = (int)$user['id'];
        $_SESSION['admin_username']  = (string)$user['username'];
        $_SESSION['admin_email']     = (string)$user['email'];
        $_SESSION['admin_full_name'] = (string)$user['full_name'];
        $_SESSION['admin_role']      = (string)$user['role'];
        $_SESSION['last_activity']   = time();

        // Clear rate limits
        clear_rate_limits($usernameOrEmail, $ip);

        // Update last login timestamp in DB
        $updateStmt = $db->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?');
        $updateStmt->execute([$user['id']]);

        // Audit log
        record_audit_log('login', 'admin_users', (int)$user['id'], 'Admin logged in successfully');

        return ['success' => true, 'user' => $user];

    } catch (Exception $e) {
        error_log('Authentication error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'A system error occurred during authentication. Please try again.'];
    }
}

/**
 * Destroys session and logs admin out.
 */
function admin_logout(): void {
    if (isset($_SESSION['admin_user_id'])) {
        record_audit_log('logout', 'admin_users', (int)$_SESSION['admin_user_id'], 'Admin logged out');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Generates or retrieves the Admin CSRF token.
 */
function admin_csrf_token(): string {
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf_token'];
}

/**
 * Validates the Admin CSRF token.
 */
function admin_verify_csrf(?string $token): bool {
    if (empty($token) || empty($_SESSION['admin_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['admin_csrf_token'], $token);
}

/**
 * HTML Helper for CSRF hidden input.
 */
function admin_csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
