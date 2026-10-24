<?php
/**
 * ====================================================================
 * StudentHub Portal - Student Management Actions
 * ====================================================================
 * Handles:
 * - Add New Student (Prepared INSERT)
 * - Edit Student (Prepared UPDATE)
 * - Soft Delete / Permanent Delete
 * - Restore Deleted Student
 * - Audit Logging
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Access Control: Admin only
requireAdmin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_students.php');
    exit;
}

// Verify CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Invalid security token. Please try again.');
    header('Location: ../manage_students.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $studentId = strtoupper(trim($_POST['student_id'] ?? ''));
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $phone     = trim($_POST['phone'] ?? '');
    $course    = trim($_POST['course'] ?? '');
    $semester  = (int)($_POST['semester'] ?? 3);
    $cgpa      = (float)($_POST['cgpa'] ?? 0.0);
    $gender    = trim($_POST['gender'] ?? 'Male');

    if (empty($studentId) || empty($fullName) || empty($email) || empty($phone)) {
        setFlashMessage('danger', 'Please fill in all required fields.');
        header('Location: ../manage_students.php');
        exit;
    }

    if ($conn && $db_connected) {
        // Check duplicate student_id or email
        $chk = $conn->prepare("SELECT id FROM students WHERE student_id = ? OR email = ? LIMIT 1");
        $chk->bind_param("ss", $studentId, $email);
        $chk->execute();
        $chkRes = $chk->get_result();
        if ($chkRes && $chkRes->num_rows > 0) {
            setFlashMessage('danger', 'A student with this Student ID or Email already exists.');
            header('Location: ../manage_students.php');
            exit;
        }
        $chk->close();

        // Create student record
        $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, email, phone, course, semester, cgpa, gender, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
        if ($stmt) {
            $stmt->bind_param("sssssids", $studentId, $fullName, $email, $phone, $course, $semester, $cgpa, $gender);
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                logAudit($conn, 'STUDENT_CREATE', 'students', $newId, "Added new student: {$fullName} ({$studentId})");
                setFlashMessage('success', "Student record for {$fullName} ({$studentId}) created successfully.");
            } else {
                setFlashMessage('danger', 'Database insert failed: ' . $stmt->error);
            }
            $stmt->close();
        }
    } else {
        setFlashMessage('warning', 'Database is currently offline. Cannot perform insert.');
    }
    header('Location: ../manage_students.php');
    exit;
}

if ($action === 'edit') {
    $id        = (int)($_POST['id'] ?? 0);
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $phone     = trim($_POST['phone'] ?? '');
    $course    = trim($_POST['course'] ?? '');
    $semester  = (int)($_POST['semester'] ?? 3);
    $cgpa      = (float)($_POST['cgpa'] ?? 0.0);
    $gender    = trim($_POST['gender'] ?? 'Male');
    $status    = trim($_POST['status'] ?? 'active');

    if ($id <= 0 || empty($fullName) || empty($email)) {
        setFlashMessage('danger', 'Invalid student ID or missing required data.');
        header('Location: ../manage_students.php');
        exit;
    }

    if ($conn && $db_connected) {
        $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, course = ?, semester = ?, cgpa = ?, gender = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssidssi", $fullName, $email, $phone, $course, $semester, $cgpa, $gender, $status, $id);
            if ($stmt->execute()) {
                logAudit($conn, 'STUDENT_UPDATE', 'students', $id, "Updated details for student ID {$id}: {$fullName}");
                setFlashMessage('success', "Student profile for {$fullName} updated successfully.");
            } else {
                setFlashMessage('danger', 'Update failed: ' . $stmt->error);
            }
            $stmt->close();
        }
    }
    header('Location: ../manage_students.php');
    exit;
}

if ($action === 'soft_delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $conn && $db_connected) {
        $stmt = $conn->prepare("UPDATE students SET status = 'deleted' WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            logAudit($conn, 'STUDENT_SOFT_DELETE', 'students', $id, "Soft-deleted student record ID: {$id}");
            setFlashMessage('info', "Student record #{$id} marked as deleted (Soft Delete).");
        }
    }
    header('Location: ../manage_students.php');
    exit;
}

if ($action === 'restore') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $conn && $db_connected) {
        $stmt = $conn->prepare("UPDATE students SET status = 'active' WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            logAudit($conn, 'STUDENT_RESTORE', 'students', $id, "Restored soft-deleted student ID: {$id}");
            setFlashMessage('success', "Student record #{$id} restored to active status.");
        }
    }
    header('Location: ../manage_students.php');
    exit;
}

if ($action === 'permanent_delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $conn && $db_connected) {
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            logAudit($conn, 'STUDENT_PERMANENT_DELETE', 'students', $id, "Permanently purged student ID: {$id}");
            setFlashMessage('warning', "Student record #{$id} permanently removed.");
        }
    }
    header('Location: ../manage_students.php');
    exit;
}

header('Location: ../manage_students.php');
exit;
