<?php
/**
 * ====================================================================
 * StudentHub Portal - RESTful PHP JSON API for Events
 * ====================================================================
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

function sendJsonResponse($status, $success, $message, $data = null) {
    http_response_code($status);
    echo json_encode([
        'status'    => $status,
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $category = trim($_GET['category'] ?? '');
    $search   = trim($_GET['search'] ?? '');

    if ($conn && $db_connected) {
        $sql = "SELECT id, title, category, event_date, event_time, venue, fee, max_seats, registered_seats, poster, status, description FROM events WHERE 1=1";
        $params = [];
        $types  = "";

        if (!empty($category) && $category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
            $types .= "s";
        }
        if (!empty($search)) {
            $sql .= " AND (title LIKE ? OR venue LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $types .= "ss";
        }
        $sql .= " ORDER BY event_date ASC";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            $events = $res->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            sendJsonResponse(200, true, 'Events retrieved', $events);
        }
    } else {
        $jsonFile = __DIR__ . '/../data/events.json';
        $fallback = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        sendJsonResponse(200, true, 'Events from fallback file', $fallback);
    }
}

sendJsonResponse(405, false, 'Method not allowed');
