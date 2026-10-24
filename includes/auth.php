<?php
/**
 * ====================================================================
 * StudentHub Portal - Authentication & RBAC Middleware
 * ====================================================================
 * Features:
 * 1. Secure Session Management & Session Fixation Protection
 * 2. Inactivity Session Timeout (15 minutes)
 * 3. Role-Based Access Control (RBAC: Admin vs Student)
 * 4. Secure Remember-Me Token via Persistent Cookie and MySQL lookup
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Inactivity timeout limit: 15 minutes = 900 seconds
define('SESSION_TIMEOUT_SECONDS', 900);

// Ensure session is started with secure flags
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session cookie params if not in CLI
    if (PHP_SAPI !== 'cli') {
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path'     => $cookieParams['path'],
            'domain'   => $cookieParams['domain'],
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    session_start();
}

/**
 * Handle Session Inactivity Timeout
 */
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
        // Log timeout
        if (isset($conn)) {
            logAudit($conn, 'SESSION_TIMEOUT', 'users', $_SESSION['user_id'], 'User session automatically expired due to 15m inactivity');
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
        setFlashMessage('warning', 'Your session has timed out due to 15 minutes of inactivity. Please log in again.');
        header('Location: login.php?error=timeout');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Handle Auto-Login via Remember-Me Cookie
 */
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['studenthub_remember']) && $conn && $db_connected) {
    $rawToken = $_COOKIE['studenthub_remember'];
    $tokenHash = hash('sha256', $rawToken);

    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.email, u.role, u.status, s.full_name, s.student_id
        FROM remember_tokens rt
        JOIN users u ON rt.user_id = u.id
        LEFT JOIN students s ON s.user_id = u.id
        WHERE rt.token_hash = ? AND rt.expires_at > NOW() AND u.status = 'active'
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            session_regenerate_id(true);
            $_SESSION['user_id']       = (int)$user['id'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['email']         = $user['email'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['full_name']     = $user['full_name'] ?? $user['username'];
            $_SESSION['student_code']  = $user['student_id'] ?? '';
            $_SESSION['last_activity'] = time();

            logAudit($conn, 'AUTO_LOGIN_REMEMBER_ME', 'users', $user['id'], 'User logged in via persistent remember-me token');
        }
        $stmt->close();
    }
}

/**
 * Helper: Check if user is authenticated.
 */
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

/**
 * Helper: Retrieve current authenticated user info.
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'           => $_SESSION['user_id'],
        'username'     => $_SESSION['username'] ?? '',
        'email'        => $_SESSION['email'] ?? '',
        'role'         => $_SESSION['role'] ?? 'student',
        'full_name'    => $_SESSION['full_name'] ?? ($_SESSION['username'] ?? 'User'),
        'student_code' => $_SESSION['student_code'] ?? ''
    ];
}

/**
 * Helper: Check if current user has a specific role.
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && strtolower($_SESSION['role']) === strtolower($role);
}

/**
 * Route Guard: Require login.
 */
function requireLogin($redirect = 'login.php') {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Please login to access this section.');
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Route Guard: Require Admin role (RBAC).
 */
function requireAdmin($redirect = 'login.php') {
    requireLogin($redirect);
    if (!hasRole('admin')) {
        setFlashMessage('danger', 'Access denied. Administrator privileges are required to view this area.');
        header('Location: profile.php');
        exit;
    }
}

/**
 * Route Guard: Require Student role.
 */
function requireStudent($redirect = 'login.php') {
    requireLogin($redirect);
    if (!hasRole('student') && !hasRole('admin')) {
        setFlashMessage('danger', 'Access denied. Student portal only.');
        header('Location: ' . $redirect);
        exit;
    }
}
