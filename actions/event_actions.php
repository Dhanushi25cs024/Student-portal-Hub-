<?php
/**
 * ====================================================================
 * StudentHub Portal - Event Management Actions
 * ====================================================================
 * Features:
 * - Event Creation, Update, and Deletion
 * - File Upload Validation: MIME Type, Extension, Max 2MB Size
 * - Secure Image Renaming (poster_timestamp_random.ext)
 * - Directory Traversal Prevention
 * - Audit Trail Logging
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_events.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Security verification failed.');
    header('Location: ../manage_events.php');
    exit;
}

$action = $_POST['action'] ?? '';
$uploadDir = __DIR__ . '/../uploads/posters/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

/**
 * Handle File Upload Validation & Renaming
 */
function handlePosterUpload($fileInputName, &$errorMsg) {
    global $uploadDir;
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file uploaded
    }

    $file = $_FILES[$fileInputName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "File upload error code: " . $file['error'];
        return false;
    }

    // 1. Max File Size Check (2MB = 2097152 bytes)
    $maxBytes = 2 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        $errorMsg = "Event poster size exceeds the maximum limit of 2MB.";
        return false;
    }

    // 2. Validate Extension
    $origName = $file['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExts)) {
        $errorMsg = "Invalid file extension (.{$ext}). Allowed formats: JPG, JPEG, PNG, WEBP.";
        return false;
    }

    // 3. Validate MIME Type using mime_content_type or finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMimes)) {
        $errorMsg = "Invalid image content type ({$mime}). Only genuine images are permitted.";
        return false;
    }

    // 4. Secure File Renaming
    $newFileName = 'poster_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFileName;
    } else {
        $errorMsg = "Failed to move uploaded file to destination directory.";
        return false;
    }
}

if ($action === 'add') {
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? 'Technical');
    $eventDate   = trim($_POST['event_date'] ?? '');
    $eventTime   = trim($_POST['event_time'] ?? '10:00');
    $venue       = trim($_POST['venue'] ?? '');
    $fee         = (float)($_POST['fee'] ?? 0.0);
    $maxSeats    = (int)($_POST['max_seats'] ?? 100);
    $status      = trim($_POST['status'] ?? 'upcoming');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($eventDate) || empty($venue)) {
        setFlashMessage('danger', 'Please provide event title, date, and venue.');
        header('Location: ../manage_events.php');
        exit;
    }

    $uploadError = '';
    $posterFileName = handlePosterUpload('poster_image', $uploadError);
    if ($posterFileName === false) {
        setFlashMessage('danger', $uploadError);
        header('Location: ../manage_events.php');
        exit;
    }
    if ($posterFileName === null) {
        $posterFileName = 'default_event.jpg';
    }

    if ($conn && $db_connected) {
        $adminId = $_SESSION['user_id'] ?? 1;
        $stmt = $conn->prepare("INSERT INTO events (title, category, event_date, event_time, venue, fee, max_seats, poster, status, description, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("sssssdisssi", $title, $category, $eventDate, $eventTime, $venue, $fee, $maxSeats, $posterFileName, $status, $description, $adminId);
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                logAudit($conn, 'EVENT_CREATE', 'events', $newId, "Created event '{$title}' with poster '{$posterFileName}'");
                setFlashMessage('success', "Event '{$title}' created successfully.");
            } else {
                setFlashMessage('danger', 'Error adding event: ' . $stmt->error);
            }
            $stmt->close();
        }
    }
    header('Location: ../manage_events.php');
    exit;
}

if ($action === 'edit') {
    $id          = (int)($_POST['id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? 'Technical');
    $eventDate   = trim($_POST['event_date'] ?? '');
    $eventTime   = trim($_POST['event_time'] ?? '10:00');
    $venue       = trim($_POST['venue'] ?? '');
    $fee         = (float)($_POST['fee'] ?? 0.0);
    $maxSeats    = (int)($_POST['max_seats'] ?? 100);
    $status      = trim($_POST['status'] ?? 'upcoming');
    $description = trim($_POST['description'] ?? '');

    if ($id <= 0 || empty($title) || empty($eventDate)) {
        setFlashMessage('danger', 'Invalid event data.');
        header('Location: ../manage_events.php');
        exit;
    }

    $uploadError = '';
    $newPoster = handlePosterUpload('poster_image', $uploadError);
    if ($newPoster === false) {
        setFlashMessage('danger', $uploadError);
        header('Location: ../manage_events.php');
        exit;
    }

    if ($conn && $db_connected) {
        if ($newPoster !== null) {
            $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, event_date = ?, event_time = ?, venue = ?, fee = ?, max_seats = ?, poster = ?, status = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sssssdisssi", $title, $category, $eventDate, $eventTime, $venue, $fee, $maxSeats, $newPoster, $status, $description, $id);
        } else {
            $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, event_date = ?, event_time = ?, venue = ?, fee = ?, max_seats = ?, status = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sssssdissi", $title, $category, $eventDate, $eventTime, $venue, $fee, $maxSeats, $status, $description, $id);
        }

        if ($stmt) {
            if ($stmt->execute()) {
                logAudit($conn, 'EVENT_UPDATE', 'events', $id, "Updated event ID {$id}: '{$title}'");
                setFlashMessage('success', "Event '{$title}' updated successfully.");
            } else {
                setFlashMessage('danger', 'Update failed: ' . $stmt->error);
            }
            $stmt->close();
        }
    }
    header('Location: ../manage_events.php');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $conn && $db_connected) {
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            logAudit($conn, 'EVENT_DELETE', 'events', $id, "Deleted event ID {$id}");
            setFlashMessage('info', "Event #{$id} deleted successfully.");
        }
    }
    header('Location: ../manage_events.php');
    exit;
}

header('Location: ../manage_events.php');
exit;
