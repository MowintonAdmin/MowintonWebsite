<?php
/**
 * Mowinton Database Connection & Helper Functions
 * Uses PDO Prepared Statements for all operations.
 */

declare(strict_types=1);

// Load .env if present
if (!function_exists('load_env_file')) {
    function load_env_file(?string $path = null): void {
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\n\r\0\x0B\"'");
                if (!array_key_exists($k, $_ENV) && !array_key_exists($k, $_SERVER)) {
                    putenv("{$k}={$v}");
                    $_ENV[$k] = $v;
                    $_SERVER[$k] = $v;
                }
            }
        }
    }
}
load_env_file();

/**
 * Returns the PDO Database Connection.
 *
 * @throws PDOException
 */
function get_db_connection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host    = getenv('DB_HOST') ?: '127.0.0.1';
    $port    = getenv('DB_PORT') ?: '3306';
    $dbname  = getenv('DB_NAME') ?: 'mowinton_db';
    $user    = getenv('DB_USER') ?: 'root';
    $pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database Connection Error: ' . $e->getMessage());
        throw new PDOException('Database connection failed. Please ensure MySQL service is running and configured.');
    }
}

/**
 * Retrieves a system setting by key from `system_settings`.
 */
function get_system_setting(string $key, ?string $default = null): ?string {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? (string)$val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Updates or inserts a system setting.
 */
function set_system_setting(string $key, string $value): bool {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare('
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ');
        return $stmt->execute([$key, $value]);
    } catch (Exception $e) {
        error_log('Error setting system_setting: ' . $e->getMessage());
        return false;
    }
}

/**
 * Records an administrative audit trail.
 */
function record_audit_log(string $action, ?string $target_type = null, ?int $target_id = null, ?string $details = null): bool {
    try {
        $db = get_db_connection();
        $admin_id = $_SESSION['admin_user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $db->prepare('
            INSERT INTO audit_logs (admin_user_id, action, target_type, target_id, details, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ');
        return $stmt->execute([$admin_id, $action, $target_type, $target_id, $details, $ip]);
    } catch (Exception $e) {
        error_log('Error recording audit log: ' . $e->getMessage());
        return false;
    }
}
