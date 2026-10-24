<?php
/**
 * ====================================================================
 * StudentHub Portal - AJAX Event Registration API
 * Allows students to register for events asynchronously without reload!
 * ====================================================================
 */

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST method required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$eventId     = (int)($input['event_id'] ?? 0);
$studentName = trim($input['student_name'] ?? '');
$studentMail = strtolower(trim($input['student_email'] ?? ''));
$notes       = trim($input['notes'] ?? 'Web portal registration');

if ($eventId <= 0 || empty($studentName) || empty($studentMail)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all registration fields']);
    exit;
}

if ($conn && $db_connected) {
    // Check if event exists and has seats
    $evStmt = $conn->prepare("SELECT id, title, max_seats, registered_seats, status FROM events WHERE id = ? LIMIT 1");
    $evStmt->bind_param("i", $eventId);
    $evStmt->execute();
    $evRes = $evStmt->get_result();

    if (!$evRes || $evRes->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    $event = $evRes->fetch_assoc();
    $evStmt->close();

    if ($event['status'] === 'completed' || $event['status'] === 'cancelled') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Registrations are closed for this event']);
        exit;
    }

    if ($event['registered_seats'] >= $event['max_seats']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Sorry, this event is already fully booked!']);
        exit;
    }

    // Find student ID if registered
    $stId = 1;
    $sFind = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
    $sFind->bind_param("s", $studentMail);
    $sFind->execute();
    $sRes = $sFind->get_result();
    if ($sRes && $sRes->num_rows > 0) {
        $stId = (int)$sRes->fetch_assoc()['id'];
    }
    $sFind->close();

    // Check duplicate event registration
    $dupChk = $conn->prepare("SELECT id FROM registrations WHERE event_id = ? AND student_email = ? LIMIT 1");
    $dupChk->bind_param("is", $eventId, $studentMail);
    $dupChk->execute();
    $dupRes = $dupChk->get_result();
    if ($dupRes && $dupRes->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'You are already registered for this event!']);
        exit;
    }
    $dupChk->close();

    // Insert registration
    $conn->begin_transaction();
    try {
        $regStmt = $conn->prepare("INSERT INTO registrations (event_id, student_id, student_name, student_email, registration_date, status, notes) VALUES (?, ?, ?, ?, NOW(), 'confirmed', ?)");
        $regStmt->bind_param("iisss", $eventId, $stId, $studentName, $studentMail, $notes);
        $regStmt->execute();
        $regId = $conn->insert_id;
        $regStmt->close();

        // Increment registered_seats count
        $incStmt = $conn->prepare("UPDATE events SET registered_seats = registered_seats + 1 WHERE id = ?");
        $incStmt->bind_param("i", $eventId);
        $incStmt->execute();
        $incStmt->close();

        $conn->commit();

        logAudit($conn, 'EVENT_REGISTER', 'registrations', $regId, "Student {$studentName} ({$studentMail}) registered for '{$event['title']}'");

        http_response_code(200);
        echo json_encode([
            'success'   => true,
            'message'   => "Registration confirmed! You have successfully booked a seat for '{$event['title']}'.",
            'reg_id'    => $regId,
            'new_count' => $event['registered_seats'] + 1,
            'max_seats' => $event['max_seats']
        ]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database transaction failed: ' . $e->getMessage()]);
        exit;
    }
} else {
    // Offline simulation
    http_response_code(200);
    echo json_encode([
        'success'   => true,
        'message'   => "Demo Registration confirmed (Offline Mode)! Welcome, {$studentName}."
    ]);
    exit;
}
