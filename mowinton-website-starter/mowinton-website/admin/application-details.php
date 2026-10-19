<?php
/**
 * Admin Individual Application Review & Details
 */

declare(strict_types=1);

$pageTitle = 'Application Details';
$currentAdminNav = 'applications';

require_once __DIR__ . '/../includes/auth.php';
$currentUser = require_admin_login();
$db = get_db_connection();

$appId = (int)($_GET['id'] ?? 0);
if ($appId <= 0) {
    header('Location: /admin/applications.php');
    exit;
}

// 1. Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security validation failed. Please try again.';
    } else {
        $allowedStatuses = ['new', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected'];
        $newStatus = (string)($_POST['new_status'] ?? '');
        if (in_array($newStatus, $allowedStatuses, true)) {
            $stmt = $db->prepare('UPDATE job_applications SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$newStatus, $appId]);
            record_audit_log('update_application_status', 'job_applications', $appId, "Status updated to {$newStatus}");
            $_SESSION['flash_success'] = "Application status successfully updated to " . strtoupper(str_replace('_', ' ', $newStatus)) . '.';
        }
    }
    header("Location: /admin/application-details.php?id={$appId}");
    exit;
}

// 2. Handle Add Internal Note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_note') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security validation failed. Please try again.';
    } else {
        $noteText = trim((string)($_POST['note_text'] ?? ''));
        if ($noteText !== '') {
            $stmt = $db->prepare('
                INSERT INTO application_notes (application_id, admin_user_id, note, created_at)
                VALUES (?, ?, ?, NOW())
            ');
            $stmt->execute([$appId, $currentUser['id'], $noteText]);
            record_audit_log('add_note', 'job_applications', $appId, 'Added an internal note');
            $_SESSION['flash_success'] = 'Internal note saved successfully.';
        }
    }
    header("Location: /admin/application-details.php?id={$appId}");
    exit;
}

require_once __DIR__ . '/admin-header.php';

// 3. Fetch Application Data
$stmt = $db->prepare('SELECT * FROM job_applications WHERE id = ?');
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    echo '<div class="admin-card" style="text-align: center; padding: 60px 20px;">';
    echo '<h3 style="color: var(--admin-danger); margin-bottom: 12px;">Application Not Found</h3>';
    echo '<p style="margin-bottom: 24px;">The requested job application record could not be found.</p>';
    echo '<a href="/admin/applications.php" class="admin-btn admin-btn-primary" style="width: auto;">Return to Applications</a>';
    echo '</div>';
    require_once __DIR__ . '/admin-footer.php';
    exit;
}

// 4. Fetch Notes
$notesStmt = $db->prepare('
    SELECT n.id, n.note, n.created_at, u.full_name, u.username, u.role
    FROM application_notes n
    JOIN admin_users u ON n.admin_user_id = u.id
    WHERE n.application_id = ?
    ORDER BY n.created_at DESC
');
$notesStmt->execute([$appId]);
$notes = $notesStmt->fetchAll();
?>

<!-- Back Bar -->
<div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
    <a href="/admin/applications.php" class="admin-btn admin-btn-sm admin-btn-secondary">
        ← Back to Applications
    </a>
    <div style="font-size: 13px; color: var(--admin-text-muted);">
        Application ID: <strong style="font-family: monospace; color: var(--admin-blue);"><?php echo htmlspecialchars($app['reference_id']); ?></strong>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Left Column: Applicant Profile & Credentials -->
    <div>
        <!-- Profile Card -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="admin-card-header" style="border-bottom: 1px solid var(--admin-border); padding-bottom: 18px;">
                <div>
                    <h3 style="font-size: 22px;"><?php echo htmlspecialchars($app['full_name']); ?></h3>
                    <p style="font-size: 14px; color: var(--admin-blue); font-weight: 700; margin-top: 4px;">
                        Applied for: <?php echo htmlspecialchars($app['position']); ?> 
                        (<?php echo ($app['application_type'] === 'internship') ? '🎓 Internship' : '💼 Full Time'; ?>)
                    </p>
                </div>
                <div>
                    <span class="status-pill status-<?php echo htmlspecialchars($app['status']); ?>" style="font-size: 13px; padding: 6px 16px;">
                        <?php echo htmlspecialchars(str_replace('_', ' ', $app['status'])); ?>
                    </span>
                </div>
            </div>

            <!-- Contact Meta Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 24px;">
                <div>
                    <strong style="display: block; font-size: 11px; text-transform: uppercase; color: var(--admin-text-muted); margin-bottom: 4px;">Email</strong>
                    <a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" style="color: var(--admin-blue); font-weight: 600; text-decoration: none;">
                        <?php echo htmlspecialchars($app['email']); ?>
                    </a>
                </div>
                <div>
                    <strong style="display: block; font-size: 11px; text-transform: uppercase; color: var(--admin-text-muted); margin-bottom: 4px;">Phone / Location</strong>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($app['phone']); ?></span>
                </div>
                <div>
                    <strong style="display: block; font-size: 11px; text-transform: uppercase; color: var(--admin-text-muted); margin-bottom: 4px;">Expected Salary</strong>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($app['expected_salary'] ?? 'Not specified'); ?></span>
                </div>
                <div>
                    <strong style="display: block; font-size: 11px; text-transform: uppercase; color: var(--admin-text-muted); margin-bottom: 4px;">Submitted On</strong>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($app['created_at']))); ?></span>
                </div>
            </div>

            <!-- Educational Background -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 14px; text-transform: uppercase; color: var(--admin-text-muted); margin-bottom: 8px;">Educational Background</h4>
                <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 12px; padding: 16px; font-size: 15px;">
                    <?php echo nl2br(htmlspecialchars($app['education'] ?? 'No educational background details entered.')); ?>
                </div>
            </div>

            <!-- Working Experience -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 14px; text-transform: uppercase; color: var(--admin-text-muted); margin-bottom: 8px;">Working Experience &amp; Background</h4>
                <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 12px; padding: 16px; font-size: 15px; line-height: 1.7;">
                    <?php echo nl2br(htmlspecialchars($app['experience'] ?? 'No working experience details entered.')); ?>
                </div>
            </div>

            <!-- Resume Download Section -->
            <div style="background: #eef6ff; border: 1.5px solid #bfdbfe; border-radius: 16px; padding: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="font-size: 32px;">📄</div>
                    <div>
                        <div style="font-weight: 700; color: var(--admin-navy);">
                            <?php echo htmlspecialchars($app['resume_original_name'] ?: 'Applicant Resume'); ?>
                        </div>
                        <div style="font-size: 12px; color: var(--admin-text-muted);">
                            Format: <?php echo strtoupper(pathinfo($app['resume_filename'] ?: '', PATHINFO_EXTENSION)); ?> • 
                            Size: <?php echo round($app['resume_filesize'] / 1024, 1); ?> KB • Protected File
                        </div>
                    </div>
                </div>

                <?php if (!empty($app['resume_filename'])): ?>
                    <a href="/admin/download-resume.php?id=<?php echo (int)$app['id']; ?>" class="admin-btn admin-btn-primary" style="width: auto;">
                        ⬇ Secure Download CV
                    </a>
                <?php else: ?>
                    <span style="color: var(--admin-text-muted); font-size: 13px;">No resume file attached.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Status Changer & Internal Notes -->
    <div>
        <!-- Status Box -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 16px;">Application Status</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 16px;">
                Updating status is for internal team tracking and will not trigger automated emails.
            </p>

            <form method="POST" action="">
                <?php echo admin_csrf_field(); ?>
                <input type="hidden" name="action" value="update_status">
                <div class="admin-form-group">
                    <label for="new_status">Change Status</label>
                    <select name="new_status" id="new_status" class="admin-form-select">
                        <option value="new" <?php echo ($app['status'] === 'new') ? 'selected' : ''; ?>>New</option>
                        <option value="under_review" <?php echo ($app['status'] === 'under_review') ? 'selected' : ''; ?>>Under Review</option>
                        <option value="shortlisted" <?php echo ($app['status'] === 'shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                        <option value="interview" <?php echo ($app['status'] === 'interview') ? 'selected' : ''; ?>>Interview</option>
                        <option value="accepted" <?php echo ($app['status'] === 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                        <option value="rejected" <?php echo ($app['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">
                    Update Status
                </button>
            </form>
        </div>

        <!-- Internal Notes Box -->
        <div class="admin-card">
            <h3 style="font-size: 16px; margin-bottom: 16px;">Internal Notes (<?php echo count($notes); ?>)</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 16px;">
                Confidential notes recorded by reviewers. Not visible to the candidate.
            </p>

            <!-- Add Note Form -->
            <form method="POST" action="" style="margin-bottom: 24px;">
                <?php echo admin_csrf_field(); ?>
                <input type="hidden" name="action" value="add_note">
                <div class="admin-form-group">
                    <textarea name="note_text" class="admin-form-textarea" rows="3" required placeholder="Add feedback, interview scheduling notes, or review comments..."></textarea>
                </div>
                <button type="submit" class="admin-btn admin-btn-sm admin-btn-secondary" style="width: 100%;">
                    + Save Internal Note
                </button>
            </form>

            <!-- Notes List -->
            <?php if (empty($notes)): ?>
                <div style="text-align: center; padding: 20px; color: var(--admin-text-muted); font-size: 13px;">
                    No notes recorded yet.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($notes as $n): ?>
                        <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 12px;">
                                <strong style="color: var(--admin-navy);"><?php echo htmlspecialchars($n['full_name']); ?></strong>
                                <span style="color: var(--admin-text-muted);"><?php echo htmlspecialchars(date('M j, Y h:i A', strtotime($n['created_at']))); ?></span>
                            </div>
                            <div style="font-size: 13px; line-height: 1.6; color: var(--admin-text-main);">
                                <?php echo nl2br(htmlspecialchars($n['note'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
