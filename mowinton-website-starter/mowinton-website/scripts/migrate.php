<?php
/**
 * Database Migration Script
 * Initializes or updates the Mowinton MySQL Database Schema.
 *
 * Usage: php scripts/migrate.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

echo "===============================================\n";
echo " Mowinton Website — Database Migration\n";
echo "===============================================\n\n";

$host    = getenv('DB_HOST') ?: '127.0.0.1';
$port    = getenv('DB_PORT') ?: '3306';
$dbname  = getenv('DB_NAME') ?: 'mowinton_db';
$user    = getenv('DB_USER') ?: 'root';
$pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

try {
    echo "1. Connecting to MySQL server at {$host}:{$port}...\n";
    $serverPdo = new PDO("mysql:host={$host};port={$port};charset={$charset}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "   -> Connected successfully to " . $serverPdo->query('SELECT VERSION()')->fetchColumn() . "\n";

    echo "2. Ensuring database `{$dbname}` exists...\n";
    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   -> Database `{$dbname}` verified.\n";

    echo "3. Reading schema file (database/schema.sql)...\n";
    $schemaFile = __DIR__ . '/../database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found at: {$schemaFile}");
    }
    $sql = file_get_contents($schemaFile);

    echo "4. Executing schema definitions...\n";
    $db = get_db_connection();
    $db->exec($sql);
    echo "   -> Schema executed successfully.\n";

    echo "5. Verifying database tables in `{$dbname}`:\n";
    $tables = $db->query("SHOW TABLES FROM `{$dbname}`")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "   - Table `{$t}` (current rows: {$count})\n";
    }

    echo "\n>>> MIGRATION COMPLETED SUCCESSFULLY! <<<\n";
    exit(0);

} catch (PDOException $e) {
    fwrite(STDERR, "\n[ERROR] PDO Exception: " . $e->getMessage() . "\n");
    exit(1);
} catch (Exception $e) {
    fwrite(STDERR, "\n[ERROR] Exception: " . $e->getMessage() . "\n");
    exit(1);
}
