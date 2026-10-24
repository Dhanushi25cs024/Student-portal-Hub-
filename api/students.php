<?php
/**
 * ====================================================================
 * StudentHub Portal - RESTful PHP JSON API for Students
 * ====================================================================
 * Supports:
 * - GET    : List & Search students with pagination & filters
 * - POST   : Asynchronously create a student record (HTTP 201)
 * - PUT    : Asynchronously update student details (HTTP 200)
 * - DELETE : Asynchronously delete a student record (HTTP 200)
 * Features Centralized JSON response & HTTP status codes (Advanced).
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

// Handle GET: Fetch and search students
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $course = trim($_GET['course'] ?? '');

    if ($conn && $db_connected) {
        $sql = "SELECT id, student_id, full_name, email, phone, course, semester, cgpa, gender, status, created_at FROM students WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($search)) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
            $term = "%{$search}%";
            $params = [$term, $term, $term];
            $types = "sss";
        }
        if (!empty($course) && $course !== 'all') {
            $sql .= " AND course = ?";
            $params[] = $course;
            $types .= "s";
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            $students = $res->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            sendJsonResponse(200, true, 'Students retrieved successfully', $students);
        } else {
            sendJsonResponse(500, false, 'Database query preparation failed');
        }
    } else {
        // Fallback from JSON file
        $jsonFile = __DIR__ . '/../data/students.json';
        $fallback = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        sendJsonResponse(200, true, 'Retrieved from fallback JSON store', $fallback);
    }
}

// Handle POST: Create student
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $studentId = strtoupper(trim($input['student_id'] ?? ''));
    $fullName  = trim($input['full_name'] ?? '');
    $email     = strtolower(trim($input['email'] ?? ''));
    $phone     = trim($input['phone'] ?? '');
    $course    = trim($input['course'] ?? 'Computer Science & Engineering');
    $semester  = (int)($input['semester'] ?? 3);
    $cgpa      = (float)($input['cgpa'] ?? 8.5);
    $gender    = trim($input['gender'] ?? 'Male');

    if (empty($studentId) || empty($fullName) || empty($email)) {
        sendJsonResponse(400, false, 'Required fields missing: student_id, full_name, email');
    }

    if ($conn && $db_connected) {
        // Check duplicate
        $chk = $conn->prepare("SELECT id FROM students WHERE student_id = ? OR email = ? LIMIT 1");
        $chk->bind_param("ss", $studentId, $email);
        $chk->execute();
        $res = $chk->get_result();
        if ($res && $res->num_rows > 0) {
            sendJsonResponse(409, false, 'Student ID or Email already exists in records');
        }
        $chk->close();

        $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, email, phone, course, semester, cgpa, gender, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
        if ($stmt) {
            $stmt->bind_param("sssssids", $studentId, $fullName, $email, $phone, $course, $semester, $cgpa, $gender);
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                logAudit($conn, 'API_STUDENT_CREATE', 'students', $newId, "API added student: {$fullName} ({$studentId})");
                sendJsonResponse(201, true, 'Student created successfully via API', [
                    'id'         => $newId,
                    'student_id' => $studentId,
                    'full_name'  => $fullName,
                    'email'      => $email,
                    'course'     => $course,
                    'semester'   => $semester,
                    'cgpa'       => $cgpa,
                    'status'     => 'active'
                ]);
            } else {
                sendJsonResponse(500, false, 'Failed to insert student: ' . $stmt->error);
            }
            $stmt->close();
        }
    } else {
        sendJsonResponse(503, false, 'Database service unavailable');
    }
}

// Handle PUT: Update student
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);

    if ($id <= 0) {
        sendJsonResponse(400, false, 'Valid student ID is required for update');
    }

    $fullName = trim($input['full_name'] ?? '');
    $email    = strtolower(trim($input['email'] ?? ''));
    $phone    = trim($input['phone'] ?? '');
    $course   = trim($input['course'] ?? '');
    $semester = (int)($input['semester'] ?? 3);
    $cgpa     = (float)($input['cgpa'] ?? 8.0);
    $status   = trim($input['status'] ?? 'active');

    if ($conn && $db_connected) {
        $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, course = ?, semester = ?, cgpa = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssidsi", $fullName, $email, $phone, $course, $semester, $cgpa, $status, $id);
            if ($stmt->execute()) {
                logAudit($conn, 'API_STUDENT_UPDATE', 'students', $id, "API updated student ID {$id}");
                sendJsonResponse(200, true, 'Student record updated successfully', ['id' => $id]);
            } else {
                sendJsonResponse(500, false, 'Update execution failed: ' . $stmt->error);
            }
            $stmt->close();
        }
    } else {
        sendJsonResponse(503, false, 'Database service unavailable');
    }
}

// Handle DELETE: Delete or soft delete student
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

    if ($id <= 0) {
        sendJsonResponse(400, false, 'Valid student ID is required for deletion');
    }

    if ($conn && $db_connected) {
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                logAudit($conn, 'API_STUDENT_DELETE', 'students', $id, "API deleted student ID {$id}");
                sendJsonResponse(200, true, "Student ID {$id} deleted successfully");
            } else {
                sendJsonResponse(500, false, 'Delete execution failed: ' . $stmt->error);
            }
            $stmt->close();
        }
    } else {
        sendJsonResponse(503, false, 'Database service unavailable');
    }
}

sendJsonResponse(405, false, 'HTTP method not allowed');
