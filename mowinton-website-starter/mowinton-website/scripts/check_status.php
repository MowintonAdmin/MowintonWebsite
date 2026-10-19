<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = get_db_connection();
    echo "1. MariaDB Connection: SUCCESSFUL (Database: mowinton_db)\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "2. Tables in Database (" . count($tables) . "):\n";
    foreach ($tables as $t) {
        echo "   - {$t}\n";
    }

    echo "3. Administrator Accounts:\n";
    $users = $pdo->query('SELECT id, username, email, full_name, role, status, created_at, last_login_at FROM admin_users')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        echo "   - ID {$u['id']}: {$u['username']} (Role: {$u['role']}, Status: {$u['status']}, Email: {$u['email']})\n";
    }

    echo "4. Database Record Counts:\n";
    echo "   - Contact Inquiries: " . $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn() . "\n";
    echo "   - Job / Internship Applications: " . $pdo->query('SELECT COUNT(*) FROM job_applications')->fetchColumn() . "\n";
    echo "   - Reviewer Notes: " . $pdo->query('SELECT COUNT(*) FROM application_notes')->fetchColumn() . "\n";
    echo "   - Audit Logs: " . $pdo->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn() . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
