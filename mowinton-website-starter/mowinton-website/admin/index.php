<?php
/**
 * Admin Dashboard Overview
 */

declare(strict_types=1);

$pageTitle = 'Dashboard Overview';
$currentAdminNav = 'dashboard';

require_once __DIR__ . '/admin-header.php';

try {
    $db = get_db_connection();

    // 1. Calculate Real Metrics
    $totalMessages = (int)$db->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    $newMessages = (int)$db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();

    $totalJobApps = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE application_type = 'job'")->fetchColumn();
    $totalInternApps = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE application_type = 'internship'")->fetchColumn();
    $totalAllApps = $totalJobApps + $totalInternApps;

    $awaitingReviewApps = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE status IN ('new', 'under_review')")->fetchColumn();
    $newApps = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE status = 'new'")->fetchColumn();

    // 2. Fetch Recent Applications (Latest 5)
    $stmtRecentApps = $db->query('
        SELECT id, reference_id, full_name, email, position, application_type, status, created_at
        FROM job_applications
        ORDER BY created_at DESC
        LIMIT 5
    ');
    $recentApps = $stmtRecentApps->fetchAll();

    // 3. Fetch Recent Contact Messages (Latest 5)
    $stmtRecentMsg = $db->query('
        SELECT id, name, email, subject, status, created_at
        FROM contact_messages
        ORDER BY created_at DESC
        LIMIT 5
    ');
    $recentMessages = $stmtRecentMsg->fetchAll();

} catch (Exception $e) {
    error_log('Dashboard query error: ' . $e->getMessage());
    $dbError = 'Unable to fetch dashboard statistics from MySQL database.';
}
?>

<?php if (isset($dbError)): ?>
    <div class="admin-alert admin-alert-danger">
        <span>⚠</span>
        <span><?php echo htmlspecialchars($dbError); ?></span>
    </div>
<?php endif; ?>

<!-- Summary Metrics Cards -->
<div class="admin-metrics-grid">
    <!-- Card 1: New Submissions -->
    <div class="admin-metric-card">
        <div class="admin-metric-icon amber">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div>
            <div class="admin-metric-val"><?php echo $newMessages + $newApps; ?></div>
            <div class="admin-metric-label">New Submissions (Unread)</div>
        </div>
    </div>

    <!-- Card 2: Total Contact Inquiries -->
    <div class="admin-metric-card">
        <div class="admin-metric-icon blue">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <div class="admin-metric-val"><?php echo $totalMessages; ?></div>
            <div class="admin-metric-label">Contact Messages</div>
        </div>
    </div>

    <!-- Card 3: Total Job Applications -->
    <div class="admin-metric-card">
        <div class="admin-metric-icon green">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <div class="admin-metric-val"><?php echo $totalJobApps; ?></div>
            <div class="admin-metric-label">Job Applications</div>
        </div>
    </div>

    <!-- Card 4: Total Internship Applications -->
    <div class="admin-metric-card">
        <div class="admin-metric-icon purple">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
            </svg>
        </div>
        <div>
            <div class="admin-metric-val"><?php echo $totalInternApps; ?></div>
            <div class="admin-metric-label">Internship Applications</div>
        </div>
    </div>

    <!-- Card 5: Awaiting Review -->
    <div class="admin-metric-card">
        <div class="admin-metric-icon amber">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="admin-metric-val"><?php echo $awaitingReviewApps; ?></div>
            <div class="admin-metric-label">Applications Awaiting Review</div>
        </div>
    </div>
</div>

<!-- Two-Column Recent Submissions Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 24px;">

    <!-- Recent Job Applications -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Recent Job &amp; Internship Applications</h3>
            <a href="/admin/applications.php" class="admin-btn admin-btn-sm admin-btn-secondary">View All →</a>
        </div>

        <?php if (empty($recentApps)): ?>
            <div style="text-align: center; padding: 40px 20px; color: var(--admin-text-muted);">
                <p>No job or internship applications received yet.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ref ID</th>
                            <th>Applicant</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentApps as $app): ?>
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 13px; color: var(--admin-blue);">
                                    <?php echo htmlspecialchars($app['reference_id']); ?>
                                </strong>
                            </td>
                            <td>
                                <div><strong><?php echo htmlspecialchars($app['full_name']); ?></strong></div>
                                <div style="font-size: 12px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($app['email']); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($app['position']); ?></div>
                                <span style="font-size: 11px; text-transform: uppercase; color: var(--admin-text-muted);">
                                    <?php echo htmlspecialchars($app['application_type']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-pill status-<?php echo htmlspecialchars($app['status']); ?>">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $app['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/application-details.php?id=<?php echo (int)$app['id']; ?>" class="admin-btn admin-btn-sm admin-btn-primary" style="width: auto;">
                                    Review
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Contact Messages -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Recent Contact Inquiries</h3>
            <a href="/admin/messages.php" class="admin-btn admin-btn-sm admin-btn-secondary">View All →</a>
        </div>

        <?php if (empty($recentMessages)): ?>
            <div style="text-align: center; padding: 40px 20px; color: var(--admin-text-muted);">
                <p>No contact inquiries received yet.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentMessages as $msg): ?>
                        <tr>
                            <td>
                                <div><strong><?php echo htmlspecialchars($msg['name']); ?></strong></div>
                                <div style="font-size: 12px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($msg['email']); ?></div>
                            </td>
                            <td>
                                <div style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($msg['subject'] ?? 'Website Inquiry'); ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-pill status-<?php echo htmlspecialchars($msg['status']); ?>">
                                    <?php echo htmlspecialchars($msg['status']); ?>
                                </span>
                            </td>
                            <td style="font-size: 13px; color: var(--admin-text-muted); white-space: nowrap;">
                                <?php echo htmlspecialchars(date('M j, Y', strtotime($msg['created_at']))); ?>
                            </td>
                            <td>
                                <a href="/admin/messages.php?view_id=<?php echo (int)$msg['id']; ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                                    Open
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
