<?php
/**
 * Admin System Settings & User Management
 * Restricted to Super Administrators.
 */

declare(strict_types=1);

$pageTitle = 'System Settings & Staff';
$currentAdminNav = 'settings';

require_once __DIR__ . '/../includes/auth.php';
$currentUser = require_admin_login();
require_role('super_admin');

$db = get_db_connection();

// 1. Handle Create Admin User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_admin_user') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security validation failed. Please try again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $name     = trim((string)($_POST['full_name'] ?? ''));
        $role     = (string)($_POST['role'] ?? 'staff');
        $password = (string)($_POST['password'] ?? '');

        if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,30}$/', $username)) {
            $_SESSION['flash_error'] = 'Username must be 3-30 alphanumeric characters, underscores, or dashes.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $_SESSION['flash_error'] = 'Password must be at least 8 characters long.';
        } elseif (!in_array($role, ['super_admin', 'staff'], true)) {
            $_SESSION['flash_error'] = 'Invalid role specified.';
        } else {
            // Check uniqueness
            $checkStmt = $db->prepare('SELECT id FROM admin_users WHERE username = ? OR email = ? LIMIT 1');
            $checkStmt->execute([$username, $email]);
            if ($checkStmt->fetch()) {
                $_SESSION['flash_error'] = 'An administrator with that username or email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $db->prepare('
                    INSERT INTO admin_users (username, email, password_hash, full_name, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, "active", NOW())
                ');
                $insertStmt->execute([$username, $email, $hash, $name ?: $username, $role]);
                record_audit_log('create_admin', 'admin_users', (int)$db->lastInsertId(), "Created admin account {$username} ({$role})");
                $_SESSION['flash_success'] = "Administrator account '{$username}' created successfully.";
            }
        }
    }
    header('Location: /admin/settings.php');
    exit;
}

// 2. Handle Toggle User Status (Suspend / Activate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_user_status') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security validation failed.';
    } else {
        $targetId = (int)($_POST['user_id'] ?? 0);
        $newStatus = (string)($_POST['new_status'] ?? '');

        if ($targetId === $currentUser['id']) {
            $_SESSION['flash_error'] = 'You cannot suspend your own account.';
        } elseif ($targetId > 0 && in_array($newStatus, ['active', 'suspended'], true)) {
            $stmt = $db->prepare('UPDATE admin_users SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$newStatus, $targetId]);
            record_audit_log('toggle_status', 'admin_users', $targetId, "User status changed to {$newStatus}");
            $_SESSION['flash_success'] = "Account status updated to {$newStatus}.";
        }
    }
    header('Location: /admin/settings.php');
    exit;
}

// 3. Handle Super Admin Reset Password for Staff / User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_staff_password') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security validation failed. Please try again.';
    } else {
        $targetId = (int)($_POST['target_user_id'] ?? 0);
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        // Fetch target user
        $targetStmt = $db->prepare('SELECT id, username FROM admin_users WHERE id = ? LIMIT 1');
        $targetStmt->execute([$targetId]);
        $targetUser = $targetStmt->fetch();

        if (!$targetUser) {
            $_SESSION['flash_error'] = 'Target user account not found.';
        } elseif (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'Password must be at least 8 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'New password and confirmation do not match.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$newHash, $targetId]);

            record_audit_log('reset_password', 'admin_users', $targetId, "Super Admin '{$currentUser['username']}' reset password for '{$targetUser['username']}'");
            $_SESSION['flash_success'] = "Password for '{$targetUser['username']}' has been updated successfully.";
        }
    }
    header('Location: /admin/settings.php');
    exit;
}

// 4. Handle Update System Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security validation failed.';
    } else {
        $notificationEmail = trim((string)($_POST['notification_email'] ?? ''));
        $smtpEnabled       = isset($_POST['smtp_enabled']) ? '1' : '0';
        $smtpHost          = trim((string)($_POST['smtp_host'] ?? ''));
        $smtpPort          = trim((string)($_POST['smtp_port'] ?? '465'));
        $smtpUser          = trim((string)($_POST['smtp_user'] ?? ''));
        $smtpPass          = trim((string)($_POST['smtp_pass'] ?? ''));
        $smtpSecure        = trim((string)($_POST['smtp_secure'] ?? 'ssl'));
        $sessionTimeout    = max(5, (int)($_POST['session_timeout_minutes'] ?? 30));

        set_system_setting('notification_email', $notificationEmail);
        set_system_setting('smtp_enabled', $smtpEnabled);
        set_system_setting('smtp_host', $smtpHost);
        set_system_setting('smtp_port', (string)$smtpPort);
        set_system_setting('smtp_user', $smtpUser);
        if ($smtpPass !== '') {
            set_system_setting('smtp_pass', $smtpPass);
        }
        set_system_setting('smtp_secure', $smtpSecure);
        set_system_setting('session_timeout_minutes', (string)$sessionTimeout);

        record_audit_log('update_settings', 'system_settings', null, 'System settings updated');
        $_SESSION['flash_success'] = 'System settings saved successfully.';
    }
    header('Location: /admin/settings.php');
    exit;
}

// Fetch all admin users
$users = $db->query('SELECT id, username, email, full_name, role, status, last_login_at, created_at FROM admin_users ORDER BY id ASC')->fetchAll();

// Fetch settings
$settings = [];
$settingsRows = $db->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll();
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch latest audit logs (10)
$auditLogs = $db->query('
    SELECT a.id, a.action, a.target_type, a.details, a.ip_address, a.created_at, u.username
    FROM audit_logs a
    LEFT JOIN admin_users u ON a.admin_user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10
')->fetchAll();

require_once __DIR__ . '/admin-header.php';
?>

<!-- Section 1: Administrator User Management -->
<div class="admin-card">
    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
        <div>
            <h3 style="margin-bottom: 4px;">Administrator &amp; Staff Accounts</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted); margin: 0;">Manage personnel with access to the Mowinton Admin System.</p>
        </div>
        <button type="button" class="admin-btn admin-btn-primary" onclick="openModal('createAdminModal')" style="width: auto; white-space: nowrap; padding: 10px 18px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(11, 94, 168, 0.25);">
            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Add Staff Account</span>
        </button>
    </div>

    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?php echo (int)$u['id']; ?></td>
                    <td>
                        <div><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></div>
                        <div style="font-size: 12px; color: var(--admin-text-muted);">@<?php echo htmlspecialchars($u['username']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <span class="status-pill <?php echo ($u['role'] === 'super_admin') ? 'status-new' : 'status-read'; ?>">
                            <?php echo htmlspecialchars(str_replace('_', ' ', $u['role'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-pill <?php echo ($u['status'] === 'active') ? 'status-resolved' : 'status-rejected'; ?>">
                            <?php echo htmlspecialchars($u['status']); ?>
                        </span>
                    </td>
                    <td style="font-size: 13px; color: var(--admin-text-muted);">
                        <?php echo $u['last_login_at'] ? htmlspecialchars(date('M j, Y h:i A', strtotime($u['last_login_at']))) : 'Never'; ?>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <?php if ($u['id'] !== $currentUser['id']): ?>
                                <button type="button" class="admin-btn admin-btn-sm admin-btn-secondary" 
                                        onclick="openResetPasswordModal(<?php echo (int)$u['id']; ?>, '<?php echo htmlspecialchars(addslashes($u['username'])); ?>', '<?php echo htmlspecialchars(addslashes($u['full_name'])); ?>')"
                                        title="Super Admin: Set new password for this user">
                                    Reset Password
                                </button>
                                <form method="POST" action="" style="display: inline;">
                                    <?php echo admin_csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_user_status">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo ($u['status'] === 'active') ? 'suspended' : 'active'; ?>">
                                    <button type="submit" class="admin-btn admin-btn-sm <?php echo ($u['status'] === 'active') ? 'admin-btn-danger' : 'admin-btn-success'; ?>"
                                            onclick="return confirm('Change status for <?php echo htmlspecialchars(addslashes($u['username'])); ?>?')">
                                        <?php echo ($u['status'] === 'active') ? 'Suspend' : 'Activate'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="admin-btn admin-btn-sm admin-btn-secondary" onclick="openModal('changeOwnPasswordModal')">
                                    Change Password
                                </button>
                                <span style="font-size: 12px; color: var(--admin-text-muted); font-style: italic;">(You)</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 2: General & Email Settings -->
<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h3>Notification &amp; Security Settings</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted);">Configure notification email destinations and security policies.</p>
        </div>
    </div>

    <form method="POST" action="" style="max-width: 720px;">
        <?php echo admin_csrf_field(); ?>
        <input type="hidden" name="action" value="save_settings">

        <div class="admin-form-group">
            <label for="notification_email">Notification Recipient Email</label>
            <input type="email" id="notification_email" name="notification_email" class="admin-form-input" 
                   value="<?php echo htmlspecialchars($settings['notification_email'] ?? 'info@mowinton.com'); ?>">
            <small style="color: var(--admin-text-muted); font-size: 12px;">Where new contact inquiries and applicant alerts will be directed.</small>
        </div>

        <div class="admin-form-group">
            <label for="session_timeout_minutes">Session Timeout (Minutes of Inactivity)</label>
            <input type="number" id="session_timeout_minutes" name="session_timeout_minutes" class="admin-form-input" min="5" max="1440"
                   value="<?php echo htmlspecialchars($settings['session_timeout_minutes'] ?? '30'); ?>">
            <small style="color: var(--admin-text-muted); font-size: 12px;">Administrators will be automatically logged out after this period of inactivity.</small>
        </div>

        <div style="border-top: 1px solid var(--admin-border); padding-top: 24px; margin-top: 28px; margin-bottom: 24px;">
            <h4 style="font-size: 16px; font-weight: 800; color: var(--admin-navy); margin-bottom: 6px;">SMTP Mail Delivery (Optional)</h4>
            <p style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 16px;">
                Configure when connecting to Hostinger Webmail or external SMTP service.
            </p>

            <div class="admin-form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; text-transform: none; font-size: 14px;">
                    <input type="checkbox" name="smtp_enabled" value="1" <?php echo (!empty($settings['smtp_enabled']) && $settings['smtp_enabled'] === '1') ? 'checked' : ''; ?>>
                    <span>Enable SMTP outbound notifications</span>
                </label>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <div class="admin-form-group">
                    <label for="smtp_host">SMTP Host</label>
                    <input type="text" id="smtp_host" name="smtp_host" class="admin-form-input" 
                           value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.hostinger.com'); ?>">
                </div>
                <div class="admin-form-group">
                    <label for="smtp_port">SMTP Port</label>
                    <input type="text" id="smtp_port" name="smtp_port" class="admin-form-input" 
                           value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '465'); ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="admin-form-group">
                    <label for="smtp_user">SMTP Username</label>
                    <input type="text" id="smtp_user" name="smtp_user" class="admin-form-input" 
                           value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>" placeholder="info@mowinton.com">
                </div>
                <div class="admin-form-group">
                    <label for="smtp_pass">SMTP Password</label>
                    <input type="password" id="smtp_pass" name="smtp_pass" class="admin-form-input" placeholder="Leave blank to keep current">
                </div>
            </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="width: auto;">
            Save All Settings
        </button>
    </form>
</div>

<!-- Section 3: Audit Trail -->
<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h3>Security &amp; Administrative Audit Logs</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted);">Recent administrative actions and traceability records.</p>
        </div>
    </div>

    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($auditLogs as $log): ?>
                <tr>
                    <td style="font-size: 13px; color: var(--admin-text-muted); white-space: nowrap;">
                        <?php echo htmlspecialchars(date('M j, Y h:i:s A', strtotime($log['created_at']))); ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></strong></td>
                    <td><span class="status-pill status-new"><?php echo htmlspecialchars($log['action']); ?></span></td>
                    <td style="font-size: 13px;"><?php echo htmlspecialchars($log['details'] ?? '—'); ?></td>
                    <td style="font-family: monospace; font-size: 12px; color: var(--admin-text-muted);">
                        <?php echo htmlspecialchars($log['ip_address']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Admin User -->
<div class="admin-modal-backdrop" id="createAdminModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3>Create Admin or Staff Account</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('createAdminModal')">✕</button>
        </div>

        <form method="POST" action="">
            <?php echo admin_csrf_field(); ?>
            <input type="hidden" name="action" value="create_admin_user">

            <div class="admin-form-group">
                <label for="new_username">Username</label>
                <input type="text" id="new_username" name="username" class="admin-form-input" required placeholder="e.g. jsmith">
            </div>

            <div class="admin-form-group">
                <label for="new_email">Email Address</label>
                <input type="email" id="new_email" name="email" class="admin-form-input" required placeholder="e.g. jsmith@mowinton.com">
            </div>

            <div class="admin-form-group">
                <label for="new_name">Full Name</label>
                <input type="text" id="new_name" name="full_name" class="admin-form-input" required placeholder="e.g. John Smith">
            </div>

            <div class="admin-form-group">
                <label for="new_role">Role</label>
                <select id="new_role" name="role" class="admin-form-select">
                    <option value="staff">Staff (Review Inquiries &amp; Resumes)</option>
                    <option value="super_admin">Super Administrator (Full System Control)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="new_password">Password (Minimum 8 Characters)</label>
                <div class="password-input-wrap">
                    <input type="password" id="new_password" name="password" class="admin-form-input" minlength="8" required placeholder="••••••••">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new_password', this)" aria-label="Show password" title="Show password">
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
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeModal('createAdminModal')">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary" style="width: auto;">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Super Admin Reset Staff Password -->
<div class="admin-modal-backdrop" id="resetUserPasswordModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <div>
                <h3 style="margin-bottom: 2px;">Reset User Password</h3>
                <span style="font-size: 13px; color: var(--admin-text-muted);">
                    Account: <strong id="reset_user_name_display" style="color: var(--admin-navy);"></strong>
                </span>
            </div>
            <button type="button" class="admin-modal-close" onclick="closeModal('resetUserPasswordModal')">✕</button>
        </div>

        <p style="font-size: 13px; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-bottom: 18px;">
            <strong>Super Admin Control:</strong> You can assign a new password for this user directly. Their existing password is not required.
        </p>

        <form method="POST" action="">
            <?php echo admin_csrf_field(); ?>
            <input type="hidden" name="action" value="reset_staff_password">
            <input type="hidden" id="reset_user_id" name="target_user_id" value="">

            <div class="admin-form-group">
                <label for="reset_new_password">New Password (Minimum 8 Characters)</label>
                <div class="password-input-wrap">
                    <input type="password" id="reset_new_password" name="new_password" class="admin-form-input" minlength="8" required placeholder="Enter new password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reset_new_password', this)" aria-label="Show password" title="Show password">
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
                <label for="reset_confirm_password">Confirm New Password</label>
                <div class="password-input-wrap">
                    <input type="password" id="reset_confirm_password" name="confirm_password" class="admin-form-input" minlength="8" required placeholder="Confirm new password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reset_confirm_password', this)" aria-label="Show password" title="Show password">
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
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeModal('resetUserPasswordModal')">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary" style="width: auto;">Set New Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetPasswordModal(userId, username, fullName) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('reset_user_name_display').textContent = '@' + username + (fullName ? ' (' + fullName + ')' : '');
    document.getElementById('reset_new_password').value = '';
    document.getElementById('reset_confirm_password').value = '';
    openModal('resetUserPasswordModal');
}
</script>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
