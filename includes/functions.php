<?php
/**
 * ====================================================================
 * StudentHub Portal - Core Helper Functions
 * Core Portal Utility Library
 * ====================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize user input to prevent XSS and stripping unwanted characters.
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token and store in session.
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token against session.
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set a Flash Message to be displayed on next page load.
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Retrieve and clear the Flash Message.
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Render Flash Message HTML if present.
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = 'alert-' . htmlspecialchars($flash['type']);
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show my-3 text-center shadow-sm" role="alert">';
        echo htmlspecialchars($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

/**
 * Get Client IP Address.
 */
function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Log System Activity into MySQL audit_logs and local audit_logs.json.
 */
function logAudit($conn, $action, $table, $recordId, $details, $userId = null, $username = null, $role = null) {
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    if ($username === null && isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    } else if ($username === null) {
        $username = 'Guest/System';
    }
    if ($role === null && isset($_SESSION['role'])) {
        $role = $_SESSION['role'];
    } else if ($role === null) {
        $role = 'guest';
    }

    $ip = getClientIp();

    // 1. MySQL insert if connected
    if ($conn instanceof mysqli && $conn->ping()) {
        $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, username, role, action, table_affected, record_id, details, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("isssisss", $userId, $username, $role, $action, $table, $recordId, $details, $ip);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 2. Local JSON log backup
    $logFile = __DIR__ . '/../data/audit_logs.json';
    $entry = [
        'timestamp'      => date('Y-m-d H:i:s'),
        'user_id'        => $userId,
        'username'       => $username,
        'role'           => $role,
        'action'         => $action,
        'table_affected' => $table,
        'record_id'      => $recordId,
        'details'        => $details,
        'ip_address'     => $ip
    ];
    
    $existing = [];
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $existing = $decoded;
        }
    }
    array_unshift($existing, $entry);
    if (count($existing) > 100) {
        $existing = array_slice($existing, 0, 100);
    }
    @file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT));
}

/**
 * Save registration / contact to CSV file.
 */
function saveRecordToCsv($filePath, array $row, array $headers = []) {
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $isNew = !file_exists($filePath) || filesize($filePath) === 0;
    $fp = @fopen($filePath, 'a');
    if ($fp) {
        if ($isNew && !empty($headers)) {
            fputcsv($fp, $headers);
        }
        fputcsv($fp, $row);
        fclose($fp);
        return true;
    }
    return false;
}

/**
 * Save registration / contact to JSON file.
 */
function saveRecordToJson($filePath, array $record) {
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $records = [];
    if (file_exists($filePath)) {
        $content = @file_get_contents($filePath);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $records = $decoded;
        }
    }
    $records[] = $record;
    return @file_put_contents($filePath, json_encode($records, JSON_PRETTY_PRINT)) !== false;
}
