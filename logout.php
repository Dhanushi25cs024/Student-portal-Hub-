<?php
/**
 * ====================================================================
 * StudentHub Portal - Logout Handler
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Audit log
    if ($conn && $db_connected) {
        logAudit($conn, 'USER_LOGOUT', 'users', $userId, "User {$username} logged out");

        // Clear remember-me token if cookie was present
        if (!empty($_COOKIE['studenthub_remember'])) {
            $tokenHash = hash('sha256', $_COOKIE['studenthub_remember']);
            $delStmt = $conn->prepare("DELETE FROM remember_tokens WHERE token_hash = ?");
            if ($delStmt) {
                $delStmt->bind_param("s", $tokenHash);
                $delStmt->execute();
                $delStmt->close();
            }
        }
    }
}

// Expire remember-me cookie
if (isset($_COOKIE['studenthub_remember'])) {
    setcookie('studenthub_remember', '', time() - 3600, '/');
}

// Destroy session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

session_start();
setFlashMessage('info', 'You have been logged out securely. See you soon!');
header('Location: login.php');
exit;
