<?php
/**
 * Change Current Admin / Staff User Password
 * Accessible to any authenticated administrator or staff member.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// Enforce authentication
$currentUser = require_admin_login();
$db = get_db_connection();

// Handle Password Change Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $returnUrl = (string)($_POST['return_url'] ?? '/admin/change-password.php');
    // Ensure return URL is safe (internal to /admin)
    if (!str_starts_with($returnUrl, '/admin') || str_contains($returnUrl, "\r") || str_contains($returnUrl, "\n")) {
        $returnUrl = '/admin/change-password.php';
    }

    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security validation failed. Please try again.';
    } else {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword     = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        // Fetch current hash from database
        $stmt = $db->prepare('SELECT password_hash, username FROM admin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$currentUser['id']]);
        $userRow = $stmt->fetch();

        if (!$userRow) {
            $_SESSION['flash_error'] = 'User account not found.';
        } elseif (!password_verify($currentPassword, $userRow['password_hash'])) {
            $_SESSION['flash_error'] = 'Incorrect current password. Please try again.';
        } elseif (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'New password must be at least 8 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'New password and confirmation do not match.';
        } elseif ($currentPassword === $newPassword) {
            $_SESSION['flash_error'] = 'New password must be different from your current password.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $db->prepare('UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
            $updateStmt->execute([$newHash, $currentUser['id']]);

            record_audit_log('change_password', 'admin_users', $currentUser['id'], "User '{$currentUser['username']}' successfully changed their password");
            $_SESSION['flash_success'] = 'Your password has been changed successfully.';
        }
    }

    header("Location: {$returnUrl}");
    exit;
}

$pageTitle = 'Change Password';
$currentAdminNav = 'change-password';

require_once __DIR__ . '/admin-header.php';
?>

<div class="admin-card" style="max-width: 600px; margin: 0 auto;">
    <div class="admin-card-header">
        <div>
            <h3 style="margin-bottom: 4px;">Change Your Password</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted); margin: 0;">
                Update your account password for <strong>@<?php echo htmlspecialchars($currentUser['username']); ?></strong>.
            </p>
        </div>
    </div>

    <form method="POST" action="/admin/change-password.php">
        <?php echo admin_csrf_field(); ?>
        <input type="hidden" name="return_url" value="/admin/change-password.php">

        <div class="admin-form-group">
            <label for="page_curr_password">Current Password</label>
            <div class="password-input-wrap">
                <input type="password" id="page_curr_password" name="current_password" class="admin-form-input" required placeholder="Enter current password">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('page_curr_password', this)" aria-label="Show password" title="Show password">
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

        <div class="admin-form-group">
            <label for="page_new_password">New Password (Minimum 8 Characters)</label>
            <div class="password-input-wrap">
                <input type="password" id="page_new_password" name="new_password" class="admin-form-input" minlength="8" required placeholder="Enter new password">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('page_new_password', this)" aria-label="Show password" title="Show password">
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

        <div class="admin-form-group">
            <label for="page_confirm_password">Confirm New Password</label>
            <div class="password-input-wrap">
                <input type="password" id="page_confirm_password" name="confirm_password" class="admin-form-input" minlength="8" required placeholder="Re-enter new password">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('page_confirm_password', this)" aria-label="Show password" title="Show password">
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

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <a href="/admin/" class="admin-btn admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn admin-btn-primary" style="width: auto;">Update Password</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
