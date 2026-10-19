<?php
/**
 * Admin Logout Endpoint
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

admin_logout();

header('Location: /admin/login.php?logged_out=1');
exit;
