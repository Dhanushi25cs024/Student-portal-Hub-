<?php
/**
 * ====================================================================
 * StudentHub Portal - Student Export Service
 * Export Student List to CSV File
 * ====================================================================
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin('../login.php');

$filename = "studenthub_students_export_" . date("Ymd_His") . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fputs($output, "\xEF\xBB\xBF");

// Column Headers
fputcsv($output, [
    'ID',
    'Student Code',
    'Full Name',
    'Email Address',
    'Mobile Phone',
    'Department / Course',
    'Semester',
    'CGPA',
    'Gender',
    'Status',
    'Enrollment Date'
]);

if ($conn && $db_connected) {
    $res = $conn->query("SELECT id, student_id, full_name, email, phone, course, semester, cgpa, gender, status, created_at FROM students ORDER BY student_id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['student_id'],
                $row['full_name'],
                $row['email'],
                $row['phone'],
                $row['course'],
                $row['semester'],
                $row['cgpa'],
                $row['gender'],
                $row['status'],
                $row['created_at']
            ]);
        }
    }
} else {
    // Fallback export from data/students.json
    $jsonFile = __DIR__ . '/../data/students.json';
    if (file_exists($jsonFile)) {
        $data = json_decode(file_get_contents($jsonFile), true);
        if (is_array($data)) {
            foreach ($data as $idx => $st) {
                fputcsv($output, [
                    $idx + 1,
                    $st['enrollment'] ?? ('25CS' . ($idx + 1)),
                    $st['name'] ?? '',
                    $st['email'] ?? '',
                    $st['phone'] ?? '9876543210',
                    $st['department'] ?? 'Computer Science',
                    $st['semester'] ?? 3,
                    $st['cgpa'] ?? 8.5,
                    'N/A',
                    'active',
                    date('Y-m-d')
                ]);
            }
        }
    }
}

fclose($output);
if ($conn && $db_connected) {
    logAudit($conn, 'STUDENTS_EXPORT_CSV', 'students', null, "Exported student roster to CSV file: {$filename}");
}
exit;
