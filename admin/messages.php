<?php
/**
 * Admin Contact Messages Management
 */

declare(strict_types=1);

$pageTitle = 'Contact Messages';
$currentAdminNav = 'messages';

require_once __DIR__ . '/../includes/auth.php';
$currentUser = require_admin_login();
$db = get_db_connection();

// 1. Handle Status Update Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security token invalid. Please try again.';
    } else {
        $messageId = (int)($_POST['message_id'] ?? 0);
        $newStatus = (string)($_POST['new_status'] ?? '');
        if ($messageId > 0 && in_array($newStatus, ['new', 'read', 'resolved'], true)) {
            $stmt = $db->prepare('UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$newStatus, $messageId]);
            record_audit_log('update_status', 'contact_messages', $messageId, "Status changed to {$newStatus}");
            $_SESSION['flash_success'] = "Message #{$messageId} marked as " . strtoupper($newStatus) . '.';
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// 2. Handle Delete Action (Super Admin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    if (!admin_has_role('super_admin')) {
        $_SESSION['flash_error'] = 'Unauthorized: Only Super Administrators can delete messages.';
    } elseif (!admin_verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'Security token invalid. Please try again.';
    } else {
        $messageId = (int)($_POST['message_id'] ?? 0);
        if ($messageId > 0) {
            $stmt = $db->prepare('DELETE FROM contact_messages WHERE id = ?');
            $stmt->execute([$messageId]);
            record_audit_log('delete_message', 'contact_messages', $messageId, "Message deleted");
            $_SESSION['flash_success'] = "Message #{$messageId} was deleted successfully.";
        }
    }
    header('Location: /admin/messages.php');
    exit;
}

require_once __DIR__ . '/admin-header.php';

// 3. Search & Filter Parameters
$search = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($statusFilter !== '' && in_array($statusFilter, ['new', 'read', 'resolved'], true)) {
    $whereClauses[] = 'status = ?';
    $params[] = $statusFilter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Count Total matching records
$countStmt = $db->prepare("SELECT COUNT(*) FROM contact_messages {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Fetch Paginated Records
$dataStmt = $db->prepare("
    SELECT id, name, email, phone, subject, message, status, ip_address, created_at
    FROM contact_messages
    {$whereSql}
    ORDER BY created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$messages = $dataStmt->fetchAll();

// If view_id is passed, automatically fetch and mark as read if new
$viewMessage = null;
if (isset($_GET['view_id'])) {
    $viewId = (int)$_GET['view_id'];
    $viewStmt = $db->prepare('SELECT * FROM contact_messages WHERE id = ?');
    $viewStmt->execute([$viewId]);
    $viewMessage = $viewStmt->fetch();
    if ($viewMessage && $viewMessage['status'] === 'new') {
        $db->prepare("UPDATE contact_messages SET status = 'read', updated_at = NOW() WHERE id = ?")->execute([$viewId]);
        $viewMessage['status'] = 'read';
    }
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h3>All Contact Inquiries (<?php echo $totalRecords; ?>)</h3>
            <p style="font-size: 13px; color: var(--admin-text-muted);">Manage messages submitted from the public contact forms.</p>
        </div>

        <div class="admin-toolbar">
            <!-- Search Form -->
            <form method="GET" action="" class="admin-search-box">
                <?php if ($statusFilter !== ''): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <?php endif; ?>
                <span class="admin-search-icon">🔍</span>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name, email, message...">
            </form>

            <!-- Status Filter -->
            <form method="GET" action="">
                <?php if ($search !== ''): ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                <select name="status" class="admin-form-select" onchange="this.form.submit()" style="padding: 8px 14px; font-size: 13px; width: auto;">
                    <option value="">All Statuses</option>
                    <option value="new" <?php echo ($statusFilter === 'new') ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo ($statusFilter === 'read') ? 'selected' : ''; ?>>Read</option>
                    <option value="resolved" <?php echo ($statusFilter === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </form>

            <?php if ($search !== '' || $statusFilter !== ''): ?>
                <a href="/admin/messages.php" class="admin-btn admin-btn-sm admin-btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($messages)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--admin-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📭</div>
            <h4 style="font-size: 18px; color: var(--admin-navy); margin-bottom: 6px;">No messages found</h4>
            <p>No contact messages match your filter or search criteria.</p>
        </div>
    <?php else: ?>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Subject &amp; Message Snippet</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $m): ?>
                    <tr>
                        <td>#<?php echo (int)$m['id']; ?></td>
                        <td>
                            <div><strong><?php echo htmlspecialchars($m['name']); ?></strong></div>
                            <div style="font-size: 12px; color: var(--admin-text-muted);">
                                <a href="mailto:<?php echo htmlspecialchars($m['email']); ?>" style="color: var(--admin-blue); text-decoration: none;">
                                    <?php echo htmlspecialchars($m['email']); ?>
                                </a>
                            </div>
                            <?php if (!empty($m['phone'])): ?>
                                <div style="font-size: 11px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($m['phone']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 700; margin-bottom: 2px;">
                                <?php echo htmlspecialchars($m['subject'] ?? 'Website Inquiry'); ?>
                            </div>
                            <div style="color: var(--admin-text-muted); font-size: 13px; max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($m['message']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-pill status-<?php echo htmlspecialchars($m['status']); ?>">
                                <?php echo htmlspecialchars($m['status']); ?>
                            </span>
                        </td>
                        <td style="font-size: 13px; color: var(--admin-text-muted); white-space: nowrap;">
                            <div><?php echo htmlspecialchars(date('M j, Y', strtotime($m['created_at']))); ?></div>
                            <div style="font-size: 11px;"><?php echo htmlspecialchars(date('h:i A', strtotime($m['created_at']))); ?></div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="/admin/messages.php?view_id=<?php echo (int)$m['id']; ?><?php echo ($search !== '') ? '&q=' . urlencode($search) : ''; ?><?php echo ($statusFilter !== '') ? '&status=' . urlencode($statusFilter) : ''; ?>" 
                                   class="admin-btn admin-btn-sm admin-btn-secondary" style="width: auto;">
                                    View
                                </a>

                                <?php if (admin_has_role('super_admin')): ?>
                                <button type="button" class="admin-btn admin-btn-sm admin-btn-danger" 
                                        onclick="confirmDelete(<?php echo (int)$m['id']; ?>, '<?php echo htmlspecialchars(addslashes($m['name'])); ?>')">
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
                Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $totalRecords; ?> total messages)
            </div>
            <div class="admin-pagination-links">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?p=<?php echo $i; ?><?php echo ($search !== '') ? '&q=' . urlencode($search) : ''; ?><?php echo ($statusFilter !== '') ? '&status=' . urlencode($statusFilter) : ''; ?>" 
                       class="admin-page-link <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal: View Message Details -->
<?php if ($viewMessage): ?>
<div class="admin-modal-backdrop open" id="viewMessageModal">
    <div class="admin-modal" style="max-width: 650px;">
        <div class="admin-modal-header">
            <h3>Message Details #<?php echo (int)$viewMessage['id']; ?></h3>
            <a href="/admin/messages.php<?php echo ($search !== '') ? '?q=' . urlencode($search) : ''; ?>" class="admin-modal-close" style="text-decoration: none;">✕</a>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; font-size: 14px;">
            <div>
                <strong style="color: var(--admin-text-muted); font-size: 12px; text-transform: uppercase;">From:</strong>
                <div style="font-weight: 700; font-size: 16px;"><?php echo htmlspecialchars($viewMessage['name']); ?></div>
            </div>
            <div>
                <strong style="color: var(--admin-text-muted); font-size: 12px; text-transform: uppercase;">Email:</strong>
                <div>
                    <a href="mailto:<?php echo htmlspecialchars($viewMessage['email']); ?>" style="color: var(--admin-blue); font-weight: 600;">
                        <?php echo htmlspecialchars($viewMessage['email']); ?>
                    </a>
                </div>
            </div>
            <div>
                <strong style="color: var(--admin-text-muted); font-size: 12px; text-transform: uppercase;">Phone:</strong>
                <div><?php echo htmlspecialchars($viewMessage['phone'] ?? 'Not provided'); ?></div>
            </div>
            <div>
                <strong style="color: var(--admin-text-muted); font-size: 12px; text-transform: uppercase;">Date &amp; Time:</strong>
                <div><?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($viewMessage['created_at']))); ?></div>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <strong style="color: var(--admin-text-muted); font-size: 12px; text-transform: uppercase;">Subject:</strong>
            <div style="font-size: 15px; font-weight: 700; margin-top: 4px;">
                <?php echo htmlspecialchars($viewMessage['subject'] ?? 'Website Inquiry'); ?>
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <strong style="color: var(--admin-text-muted); font-size: 12px; text-transform: uppercase;">Message Content:</strong>
            <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px; margin-top: 6px; white-space: pre-wrap; font-size: 14px; line-height: 1.7;">
                <?php echo htmlspecialchars($viewMessage['message']); ?>
            </div>
        </div>

        <!-- Update Status Control -->
        <div style="background: #f1f5f9; padding: 18px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap: 12px;">
            <form method="POST" action="" style="display: flex; align-items: center; gap: 12px;">
                <?php echo admin_csrf_field(); ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="message_id" value="<?php echo (int)$viewMessage['id']; ?>">
                <label style="font-size: 13px; font-weight: 700;">Status:</label>
                <select name="new_status" class="admin-form-select" style="padding: 6px 12px; font-size: 13px; width: auto;">
                    <option value="new" <?php echo ($viewMessage['status'] === 'new') ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo ($viewMessage['status'] === 'read') ? 'selected' : ''; ?>>Read</option>
                    <option value="resolved" <?php echo ($viewMessage['status'] === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">Update Status</button>
            </form>

            <a href="mailto:<?php echo htmlspecialchars($viewMessage['email']); ?>?subject=Re: <?php echo urlencode($viewMessage['subject'] ?? 'Mowinton Inquiry'); ?>" 
               class="admin-btn admin-btn-sm admin-btn-success">
                Reply via Email ✉
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Confirmation Modal (Super Admin) -->
<div class="admin-modal-backdrop" id="deleteModal">
    <div class="admin-modal" style="max-width: 440px;">
        <div class="admin-modal-header">
            <h3 style="color: var(--admin-danger);">Delete Message Record</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('deleteModal')">✕</button>
        </div>
        <p style="color: var(--admin-text-main); margin-bottom: 24px;">
            Are you sure you want to permanently delete message from <strong id="deleteSenderName"></strong>? This action cannot be undone.
        </p>
        <form method="POST" action="" id="deleteForm" style="display: flex; justify-content: flex-end; gap: 12px;">
            <?php echo admin_csrf_field(); ?>
            <input type="hidden" name="action" value="delete_message">
            <input type="hidden" name="message_id" id="deleteMessageId" value="0">
            <button type="button" class="admin-btn admin-btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="submit" class="admin-btn admin-btn-danger">Yes, Delete Message</button>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteMessageId').value = id;
    document.getElementById('deleteSenderName').innerText = name;
    openModal('deleteModal');
}
</script>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
