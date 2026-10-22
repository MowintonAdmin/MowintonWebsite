<?php
/**
 * Shared Admin Layout Header
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// Enforce authentication
$currentUser = require_admin_login();

// Active nav highlight
$currentAdminNav = $currentAdminNav ?? 'dashboard';

// Real-time unread/new counts from MySQL
try {
    $db = get_db_connection();
    $unreadMessages = (int)$db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
    $newApplications = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE status = 'new'")->fetchColumn();
} catch (Exception $e) {
    $unreadMessages = 0;
    $newApplications = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin Dashboard'); ?> — Mowinton Consulting</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <a href="/admin/" class="admin-sidebar-logo">
                <img src="/img/4fb4414e-52aaaaaaad-3-300x113.webp" alt="Mowinton" onerror="this.onerror=null; this.src='/img/mowinton-consulting-AGBbZjJ9kpuxp8Gb.jpg';">
                <span>Mowinton</span>
            </a>
            <button class="admin-menu-toggle" id="sidebarCloseBtn" aria-label="Close Sidebar" style="color: white; display: none;">
                ✕
            </button>
        </div>

        <ul class="admin-nav-menu">
            <li class="admin-nav-item">
                <a href="/admin/" class="admin-nav-link <?php echo ($currentAdminNav === 'dashboard') ? 'active' : ''; ?>">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="admin-nav-item">
                <a href="/admin/messages.php" class="admin-nav-link <?php echo ($currentAdminNav === 'messages') ? 'active' : ''; ?>">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Contact Messages</span>
                    <?php if ($unreadMessages > 0): ?>
                        <span class="admin-badge-count" style="background: #ef4444;"><?php echo $unreadMessages; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="admin-nav-item">
                <a href="/admin/applications.php" class="admin-nav-link <?php echo ($currentAdminNav === 'applications') ? 'active' : ''; ?>">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Job Applications</span>
                    <?php if ($newApplications > 0): ?>
                        <span class="admin-badge-count" style="background: #3b82f6;"><?php echo $newApplications; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <?php if ($currentUser['role'] === 'super_admin'): ?>
            <li class="admin-nav-item">
                <a href="/admin/settings.php" class="admin-nav-link <?php echo ($currentAdminNav === 'settings') ? 'active' : ''; ?>">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>System Settings</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="admin-nav-item" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px;">
                <a href="/" target="_blank" class="admin-nav-link" rel="noopener noreferrer">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>Public Website ↗</span>
                </a>
            </li>
        </ul>

        <div class="admin-sidebar-footer">
            <div class="admin-user-info">
                <div class="admin-user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></div>
                <div class="admin-user-role"><?php echo htmlspecialchars(str_replace('_', ' ', $currentUser['role'])); ?></div>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <button type="button" class="admin-logout-btn" onclick="openModal('changeOwnPasswordModal')" title="Change Password" style="background: none; border: none; cursor: pointer; color: inherit; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </button>
                <a href="/admin/logout.php" class="admin-logout-btn" title="Sign Out">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div style="display: flex; align-items: center; gap: 14px;">
                <button class="admin-menu-toggle" id="sidebarToggleBtn" aria-label="Toggle Navigation">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="admin-topbar-title">
                    <h2><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h2>
                </div>
            </div>

            <div class="admin-topbar-actions">
                <span style="font-size: 13px; color: var(--admin-text-muted);">
                    Logged in as <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                </span>
                <button type="button" class="admin-btn admin-btn-sm admin-btn-secondary" onclick="openModal('changeOwnPasswordModal')" style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span>Change Password</span>
                </button>
                <a href="/admin/logout.php" class="admin-btn admin-btn-sm admin-btn-secondary">
                    Logout
                </a>
            </div>
        </header>

        <main class="admin-content-area">
            <!-- Flash Message Banner -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="admin-alert admin-alert-success">
                    <span>✓</span>
                    <span><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="admin-alert admin-alert-danger">
                    <span>⚠</span>
                    <span><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
                </div>
            <?php endif; ?>
