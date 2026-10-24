<?php
/**
 * ====================================================================
 * StudentHub Portal - Database Configuration
 * ====================================================================
 * Provides MySQLi and PDO database connections with error handling,
 * UTF-8 charset configuration, and safe fallback reporting.
 */

// Database credentials for XAMPP default environment
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'studenthub';
$db_port = 3306;

$conn = null;
$pdo  = null;
$db_connected = false;
$db_error = '';

try {
    // 1. MySQLi Connection
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

    if ($conn->connect_error) {
        $db_error = $conn->connect_error;
        $db_connected = false;
    } else {
        $conn->set_charset('utf8mb4');
        $db_connected = true;
    }

    // 2. PDO Connection
    if ($db_connected) {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    }
} catch (Exception $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
}

/**
 * Helper function to ensure database connection is active.
 * If not connected, displays a user-friendly alert or returns false.
 */
function checkDbConnection($silent = false) {
    global $db_connected, $db_error;
    if (!$db_connected && !$silent) {
        echo '<div class="alert alert-warning text-center m-3 shadow-sm">';
        echo '<strong>⚠️ Database Offline Notice:</strong> MySQL server connection failed (' . htmlspecialchars($db_error) . '). ';
        echo 'Please ensure Apache & MySQL are running in your XAMPP Control Panel and database <code>studenthub.sql</code> is imported.';
        echo '</div>';
    }
    return $db_connected;
}
