<?php
/**
 * StudentHub Portal - User Status Toggle Service
 * Toggle User Active / Inactive Status
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin_dashboard.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Security verification failed.');
    header('Location: ../admin_dashboard.php');
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$newStatus = trim($_POST['status'] ?? 'active');

if ($userId > 0 && in_array($newStatus, ['active', 'inactive'])) {
    if ($conn && $db_connected) {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $newStatus, $userId);
            $stmt->execute();
            $stmt->close();
            logAudit($conn, 'USER_STATUS_TOGGLE', 'users', $userId, "Toggled status of user #{$userId} to '{$newStatus}'");
            setFlashMessage('success', "User account #{$userId} status changed to '{$newStatus}'.");
        }
    }
}

header('Location: ../admin_dashboard.php');
exit;
