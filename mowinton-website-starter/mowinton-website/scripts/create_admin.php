<?php
/**
 * CLI Administrator Account Creation Script
 * Securely creates or updates an Administrator account.
 *
 * Usage:
 *   php scripts/create_admin.php --username=admin --email=admin@mowinton.com --role=super_admin --name="Super Admin"
 * Or interactive mode:
 *   php scripts/create_admin.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

echo "===============================================\n";
echo " Mowinton Website — Admin Account Provisioning\n";
echo "===============================================\n\n";

// Parse CLI options
$longopts = [
    'username::',
    'email::',
    'password::',
    'role::',
    'name::',
];
$options = getopt('', $longopts);

function prompt_input(string $prompt, bool $isPassword = false): string {
    echo $prompt;
    if ($isPassword && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Hide password input on Windows using powershell read-host if possible
        $cmd = 'powershell -Command "$p = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"';
        $res = trim((string)shell_exec($cmd));
        if ($res !== '') {
            return $res;
        }
    }
    $val = trim((string)fgets(STDIN));
    return $val;
}

$username = $options['username'] ?? '';
$email    = $options['email'] ?? '';
$password = $options['password'] ?? '';
$role     = $options['role'] ?? '';
$name     = $options['name'] ?? '';

if ($username === '') {
    $username = prompt_input('Enter Admin Username: ');
}
if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,30}$/', $username)) {
    fwrite(STDERR, "[ERROR] Username must be 3-30 alphanumeric characters, underscores, or dashes.\n");
    exit(1);
}

if ($email === '') {
    $email = prompt_input('Enter Admin Email: ');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "[ERROR] Invalid email address format.\n");
    exit(1);
}

if ($name === '') {
    $name = prompt_input('Enter Full Name: ');
}
if (trim($name) === '') {
    $name = ucfirst($username);
}

if ($role === '') {
    $roleInput = prompt_input('Select Role (1 = super_admin, 2 = staff) [1]: ');
    $role = ($roleInput === '2' || strtolower($roleInput) === 'staff') ? 'staff' : 'super_admin';
}
if (!in_array($role, ['super_admin', 'staff'], true)) {
    fwrite(STDERR, "[ERROR] Invalid role. Must be 'super_admin' or 'staff'.\n");
    exit(1);
}

if ($password === '') {
    $password = prompt_input('Enter Password (min 8 chars): ', true);
}
if (strlen($password) < 8) {
    fwrite(STDERR, "[ERROR] Password must be at least 8 characters long.\n");
    exit(1);
}

try {
    $db = get_db_connection();

    // Check if user exists
    $stmt = $db->prepare('SELECT id, username FROM admin_users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $email]);
    $existing = $stmt->fetch();

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($existing) {
        $update = $db->prepare('
            UPDATE admin_users
            SET password_hash = ?, full_name = ?, role = ?, status = "active", updated_at = NOW()
            WHERE id = ?
        ');
        $update->execute([$hash, $name, $role, $existing['id']]);
        echo "\n[SUCCESS] Administrator '{$username}' (ID: {$existing['id']}) updated successfully.\n";
        echo "Role: {$role}\nEmail: {$email}\n";
    } else {
        $insert = $db->prepare('
            INSERT INTO admin_users (username, email, password_hash, full_name, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, "active", NOW())
        ');
        $insert->execute([$username, $email, $hash, $name, $role]);
        $newId = (int)$db->lastInsertId();
        echo "\n[SUCCESS] Administrator '{$username}' (ID: {$newId}) created successfully.\n";
        echo "Role: {$role}\nEmail: {$email}\n";
    }

    echo "===============================================\n";
    exit(0);

} catch (Exception $e) {
    fwrite(STDERR, "\n[ERROR] Failed to save administrator: " . $e->getMessage() . "\n");
    exit(1);
}
