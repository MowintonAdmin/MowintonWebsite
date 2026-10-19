<?php
/**
 * Admin Job & Internship Applications Management
 */

declare(strict_types=1);

$pageTitle = 'Job & Internship Applications';
$currentAdminNav = 'applications';

require_once __DIR__ . '/../includes/auth.php';
$currentUser = require_admin_login();
$db = get_db_connection();

// 1. Handle Delete Action (Super Admin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_application') {
    if (!admin_has_role('super_admin')) {
        $_SESSION['flash_error'] = 'Unauthorized: Only Super Administrators can delete applications.';
    } elseif (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security token invalid. Please try again.';
    } else {
        $appId = (int)($_POST['application_id'] ?? 0);
        if ($appId > 0) {
            // Fetch resume filename to remove file safely
            $stmt = $db->prepare('SELECT resume_filename, reference_id FROM job_applications WHERE id = ?');
            $stmt->execute([$appId]);
            $app = $stmt->fetch();
            if ($app) {
                if (!empty($app['resume_filename'])) {
                    $storageDir = get_resume_storage_path();
                    $filePath = rtrim($storageDir, '/\\') . DIRECTORY_SEPARATOR . basename($app['resume_filename']);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                $delStmt = $db->prepare('DELETE FROM job_applications WHERE id = ?');
                $delStmt->execute([$appId]);
                record_audit_log('delete_application', 'job_applications', $appId, "Application {$app['reference_id']} deleted");
                $_SESSION['flash_success'] = "Application {$app['reference_id']} was deleted successfully.";
            }
        }
    }
    header('Location: /admin/applications.php');
    exit;
}

require_once __DIR__ . '/admin-header.php';

// 2. Search & Filter Parameters
$search       = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$typeFilter   = trim((string)($_GET['type'] ?? 'all'));
$page         = max(1, (int)($_GET['p'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

$whereClauses = [];
$params       = [];

if ($search !== '') {
    $whereClauses[] = '(reference_id LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR position LIKE ?)';
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($statusFilter !== '' && in_array($statusFilter, ['new', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected'], true)) {
    $whereClauses[] = 'status = ?';
    $params[] = $statusFilter;
}

if ($typeFilter === 'job' || $typeFilter === 'internship') {
    $whereClauses[] = 'application_type = ?';
    $params[] = $typeFilter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Count Total
$countStmt = $db->prepare("SELECT COUNT(*) FROM job_applications {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Fetch Records
$dataStmt = $db->prepare("
    SELECT id, reference_id, full_name, email, phone, position, application_type,
           status, resume_filename, resume_original_name, created_at
    FROM job_applications
    {$whereSql}
    ORDER BY created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$applications = $dataStmt->fetchAll();

// Counts for Tabs
$countAll = (int)$db->query("SELECT COUNT(*) FROM job_applications")->fetchColumn();
$countJobs = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE application_type = 'job'")->fetchColumn();
$countInterns = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE application_type = 'internship'")->fetchColumn();
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h3>Candidate Applications</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted);">Manage and review job and internship applications.</p>
        </div>

        <div class="admin-toolbar">
            <!-- Search Form -->
            <form method="GET" action="" class="admin-search-box">
                <?php if ($statusFilter !== ''): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <?php endif; ?>
                <?php if ($typeFilter !== 'all'): ?>
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($typeFilter); ?>">
                <?php endif; ?>
                <span class="admin-search-icon">🔍</span>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name, position, ref ID...">
            </form>

            <!-- Status Filter -->
            <form method="GET" action="">
                <?php if ($search !== ''): ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                <?php if ($typeFilter !== 'all'): ?>
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($typeFilter); ?>">
                <?php endif; ?>
                <select name="status" class="admin-form-select" onchange="this.form.submit()" style="padding: 8px 14px; font-size: 13px; width: auto;">
                    <option value="">All Statuses</option>
                    <option value="new" <?php echo ($statusFilter === 'new') ? 'selected' : ''; ?>>New</option>
                    <option value="under_review" <?php echo ($statusFilter === 'under_review') ? 'selected' : ''; ?>>Under Review</option>
                    <option value="shortlisted" <?php echo ($statusFilter === 'shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                    <option value="interview" <?php echo ($statusFilter === 'interview') ? 'selected' : ''; ?>>Interview</option>
                    <option value="accepted" <?php echo ($statusFilter === 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                    <option value="rejected" <?php echo ($statusFilter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </form>

            <?php if ($search !== '' || $statusFilter !== '' || $typeFilter !== 'all'): ?>
                <a href="/admin/applications.php" class="admin-btn admin-btn-sm admin-btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Application Type Tabs -->
    <div class="admin-tabs">
        <a href="?type=all<?php echo ($statusFilter !== '') ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo ($search !== '') ? '&q=' . urlencode($search) : ''; ?>" 
           class="admin-tab <?php echo ($typeFilter === 'all') ? 'active' : ''; ?>">
            All Applications (<?php echo $countAll; ?>)
        </a>
        <a href="?type=job<?php echo ($statusFilter !== '') ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo ($search !== '') ? '&q=' . urlencode($search) : ''; ?>" 
           class="admin-tab <?php echo ($typeFilter === 'job') ? 'active' : ''; ?>">
            💼 Job Applications (<?php echo $countJobs; ?>)
        </a>
        <a href="?type=internship<?php echo ($statusFilter !== '') ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo ($search !== '') ? '&q=' . urlencode($search) : ''; ?>" 
           class="admin-tab <?php echo ($typeFilter === 'internship') ? 'active' : ''; ?>">
            🎓 Internship Program (<?php echo $countInterns; ?>)
        </a>
    </div>

    <?php if (empty($applications)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--admin-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📁</div>
            <h4 style="font-size: 18px; color: var(--admin-navy); margin-bottom: 6px;">No applications found</h4>
            <p>No candidate submissions match your current filter or search criteria.</p>
        </div>
    <?php else: ?>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ref ID</th>
                        <th>Applicant</th>
                        <th>Position / Type</th>
                        <th>Status</th>
                        <th>Resume</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td>
                            <strong style="font-family: monospace; font-size: 13px; color: var(--admin-blue);">
                                <?php echo htmlspecialchars($app['reference_id']); ?>
                            </strong>
                        </td>
                        <td>
                            <div><strong><?php echo htmlspecialchars($app['full_name']); ?></strong></div>
                            <div style="font-size: 12px; color: var(--admin-text-muted);">
                                <a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" style="color: var(--admin-blue); text-decoration: none;">
                                    <?php echo htmlspecialchars($app['email']); ?>
                                </a>
                            </div>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($app['phone']); ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 700;"><?php echo htmlspecialchars($app['position']); ?></div>
                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--admin-text-muted);">
                                <?php echo ($app['application_type'] === 'internship') ? '🎓 Internship' : '💼 Full Time'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-pill status-<?php echo htmlspecialchars($app['status']); ?>">
                                <?php echo htmlspecialchars(str_replace('_', ' ', $app['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($app['resume_filename'])): ?>
                                <a href="/admin/download-resume.php?id=<?php echo (int)$app['id']; ?>" 
                                   class="admin-btn admin-btn-sm admin-btn-secondary" title="Download CV">
                                    ⬇ Download CV
                                </a>
                            <?php else: ?>
                                <span style="font-size: 12px; color: var(--admin-text-muted);">No File</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 13px; color: var(--admin-text-muted); white-space: nowrap;">
                            <div><?php echo htmlspecialchars(date('M j, Y', strtotime($app['created_at']))); ?></div>
                            <div style="font-size: 11px;"><?php echo htmlspecialchars(date('h:i A', strtotime($app['created_at']))); ?></div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="/admin/application-details.php?id=<?php echo (int)$app['id']; ?>" class="admin-btn admin-btn-sm admin-btn-primary" style="width: auto;">
                                    Review Details →
                                </a>

                                <?php if (admin_has_role('super_admin')): ?>
                                <button type="button" class="admin-btn admin-btn-sm admin-btn-danger" 
                                        onclick="confirmDeleteApp(<?php echo (int)$app['id']; ?>, '<?php echo htmlspecialchars(addslashes($app['reference_id'])); ?>', '<?php echo htmlspecialchars(addslashes($app['full_name'])); ?>')">
                                    Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>
                Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $totalRecords; ?> total applications)
            </div>
            <div class="admin-pagination-links">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?p=<?php echo $i; ?><?php echo ($typeFilter !== 'all') ? '&type=' . urlencode($typeFilter) : ''; ?><?php echo ($statusFilter !== '') ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo ($search !== '') ? '&q=' . urlencode($search) : ''; ?>" 
                       class="admin-page-link <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal (Super Admin) -->
<div class="admin-modal-backdrop" id="deleteAppModal">
    <div class="admin-modal" style="max-width: 440px;">
        <div class="admin-modal-header">
            <h3 style="color: var(--admin-danger);">Delete Application Record</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('deleteAppModal')">✕</button>
        </div>
        <p style="color: var(--admin-text-main); margin-bottom: 24px;">
            Are you sure you want to delete application <strong id="deleteAppRef"></strong> from <strong id="deleteAppName"></strong>?
            The applicant's resume file will also be permanently deleted.
        </p>
        <form method="POST" action="" id="deleteAppForm" style="display: flex; justify-content: flex-end; gap: 12px;">
            <?php echo admin_csrf_field(); ?>
            <input type="hidden" name="action" value="delete_application">
            <input type="hidden" name="application_id" id="deleteAppId" value="0">
            <button type="button" class="admin-btn admin-btn-secondary" onclick="closeModal('deleteAppModal')">Cancel</button>
            <button type="submit" class="admin-btn admin-btn-danger">Yes, Delete Application</button>
        </form>
    </div>
</div>

<script>
function confirmDeleteApp(id, ref, name) {
    document.getElementById('deleteAppId').value = id;
    document.getElementById('deleteAppRef').innerText = ref;
    document.getElementById('deleteAppName').innerText = name;
    openModal('deleteAppModal');
}
</script>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
