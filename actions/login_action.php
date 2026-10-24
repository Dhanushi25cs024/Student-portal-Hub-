<?php
/**
 * ====================================================================
 * StudentHub Portal - Login Action Handler
 * ====================================================================
 * Features:
 * - Password Verification via password_verify()
 * - Session Regeneration against fixation attacks
 * - Role-Based Access Control & Redirection
 * - Inactivity Timestamp Initialization
 * - Persistent Remember-Me Token Generation
 * - Audit Trail Logging
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// 1. Verify CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Security verification failed. Please try logging in again.');
    header('Location: ../login.php');
    exit;
}

$loginId    = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']);

if (empty($loginId) || empty($password)) {
    setFlashMessage('danger', 'Please enter both your email/username and password.');
    header('Location: ../login.php');
    exit;
}

// Check database connection
if (!$conn || !$db_connected) {
    // If MySQL is not running in XAMPP, provide demo fallback authentication
    if (($loginId === 'admin@studenthub.com' || $loginId === 'admin') && $password === 'admin123') {
        session_regenerate_id(true);
        $_SESSION['user_id']       = 1;
        $_SESSION['username']      = 'admin';
        $_SESSION['email']         = 'admin@studenthub.com';
        $_SESSION['role']          = 'admin';
        $_SESSION['full_name']     = 'System Administrator';
        $_SESSION['student_code']  = 'ADMIN01';
        $_SESSION['last_activity'] = time();

        setFlashMessage('success', 'Logged in successfully as Administrator (Offline Demo Mode).');
        header('Location: ../admin_dashboard.php');
        exit;
    } elseif (($loginId === 'student@studenthub.com' || $loginId === 'student') && $password === 'student123') {
        session_regenerate_id(true);
        $_SESSION['user_id']       = 2;
        $_SESSION['username']      = 'dhanushi';
        $_SESSION['email']         = 'student@studenthub.com';
        $_SESSION['role']          = 'student';
        $_SESSION['full_name']     = 'Dhanushi Patel';
        $_SESSION['student_code']  = '25CS024';
        $_SESSION['last_activity'] = time();

        setFlashMessage('success', 'Logged in successfully as Student (Offline Demo Mode).');
        header('Location: ../profile.php');
        exit;
    } else {
        setFlashMessage('danger', 'Database offline. Please start MySQL in XAMPP or use demo credentials (admin@studenthub.com / admin123).');
        header('Location: ../login.php');
        exit;
    }
}

// 2. Fetch User Record via Prepared Statement
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.email, u.password_hash, u.role, u.status, s.full_name, s.student_id
    FROM users u
    LEFT JOIN students s ON s.user_id = u.id
    WHERE u.email = ? OR u.username = ?
    LIMIT 1
");

if (!$stmt) {
    setFlashMessage('danger', 'Database error. Please try again.');
    header('Location: ../login.php');
    exit;
}

$stmt->bind_param("ss", $loginId, $loginId);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    setFlashMessage('danger', 'Invalid email/username or password.');
    header('Location: ../login.php');
    exit;
}

$user = $res->fetch_assoc();
$stmt->close();

// 3. Verify Account Status
if ($user['status'] !== 'active') {
    setFlashMessage('danger', 'Your account has been deactivated. Please contact the portal administrator.');
    header('Location: ../login.php');
    exit;
}

// 4. Verify Password via password_verify()
if (!password_verify($password, $user['password_hash'])) {
    setFlashMessage('danger', 'Invalid email/username or password.');
    logAudit($conn, 'FAILED_LOGIN_ATTEMPT', 'users', $user['id'], "Failed login attempt for identifier: {$loginId}");
    header('Location: ../login.php');
    exit;
}

// 5. Secure Session Initiation
session_regenerate_id(true);
$_SESSION['user_id']       = (int)$user['id'];
$_SESSION['username']      = $user['username'];
$_SESSION['email']         = $user['email'];
$_SESSION['role']          = $user['role'];
$_SESSION['full_name']     = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
$_SESSION['student_code']  = $user['student_id'] ?? '';
$_SESSION['last_activity'] = time();

// Update last login timestamp in DB
$upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
if ($upd) {
    $upd->bind_param("i", $user['id']);
    $upd->execute();
    $upd->close();
}

// 6. Handle Remember-Me Persistent Cookie Token
if ($rememberMe) {
    $tokenRaw = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $tokenRaw);
    $expiryDays = 30;
    $expiresAt = date('Y-m-d H:i:s', time() + ($expiryDays * 86400));

    $remStmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
    if ($remStmt) {
        $remStmt->bind_param("iss", $user['id'], $tokenHash, $expiresAt);
        $remStmt->execute();
        $remStmt->close();

        setcookie('studenthub_remember', $tokenRaw, [
            'expires'  => time() + ($expiryDays * 86400),
            'path'     => '/',
            'httponly' => true,
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'samesite' => 'Lax'
        ]);
    }
}

// 7. Audit Log Entry
logAudit($conn, 'USER_LOGIN', 'users', $user['id'], "User logged in ({$user['role']}) from " . getClientIp());

// 8. Role-Based Redirection
setFlashMessage('success', "Welcome back, " . htmlspecialchars($_SESSION['full_name']) . "!");
if ($user['role'] === 'admin') {
    header('Location: ../admin_dashboard.php');
} else {
    header('Location: ../profile.php');
}
exit;
