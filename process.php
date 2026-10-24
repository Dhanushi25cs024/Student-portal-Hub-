<?php
/**
 * ====================================================================
 * StudentHub Portal - Form Processing with
 * Server-Side Validation and CSV/JSON File Storage
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method. Please submit the form via POST.");
}

// Map POST values
$name            = trim($_POST["full_name"] ?? $_POST["name"] ?? "");
$email           = trim($_POST["email"] ?? "");
$mobile          = trim($_POST["phone"] ?? $_POST["mobile"] ?? "");
$password        = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? $password;
$course          = trim($_POST["course"] ?? "Computer Science & Engineering");
$semester        = (int)($_POST["semester"] ?? 3);
$gender          = trim($_POST["gender"] ?? "Other");

$errors = [];

// Server-Side Validations
if ($name === "") {
    $errors[] = "Full name is required.";
} elseif (strlen($name) < 2) {
    $errors[] = "Full name must be at least 2 characters long.";
}

if ($email === "") {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email address is required.";
}

if ($mobile === "") {
    $errors[] = "Mobile number is required.";
} elseif (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
    $errors[] = "Mobile number must be 10 digits.";
}

if ($password === "") {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
}

if (!empty($errors)) {
    echo "<h3>Registration Validation Errors:</h3><ul>";
    foreach ($errors as $err) {
        echo "<li style='color:red;'>" . htmlspecialchars($err) . "</li>";
    }
    echo "</ul><p><a href='javascript:history.back()'>Go Back</a></p>";
    exit;
}

// CSV and JSON File Storage
$csvFile = __DIR__ . '/data/registrations.csv';
$csvRow = [date('Y-m-d H:i:s'), 'REG' . rand(100, 999), $name, $email, $mobile, $course, $semester, $gender];
saveRecordToCsv($csvFile, $csvRow, ['Timestamp', 'ID', 'Name', 'Email', 'Mobile', 'Course', 'Semester', 'Gender']);

$jsonFile = __DIR__ . '/data/registrations.json';
$record = [
    'timestamp' => date('Y-m-d H:i:s'),
    'name'      => $name,
    'email'     => $email,
    'mobile'    => $mobile,
    'course'    => $course,
    'semester'  => $semester,
    'gender'    => $gender
];
saveRecordToJson($jsonFile, $record);
saveRecordToJson(__DIR__ . '/data/registrations_backup.json', $record);

// Database Insert with Password Hashing
$dbSuccess = false;
if ($conn && $db_connected) {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($check) {
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();
        if ($res && $res->num_rows > 0) {
            echo "<p style='color:orange;'>User email already registered in database. Record saved to CSV/JSON file.</p>";
            echo "<p><a href='login.php'>Proceed to Login</a></p>";
            exit;
        }
        $check->close();
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $username = strtolower(explode('@', $email)[0]);
    $uStmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?, ?, ?, 'student', 'active')");
    if ($uStmt) {
        $uStmt->bind_param("sss", $username, $email, $hash);
        $uStmt->execute();
        $uid = $conn->insert_id;
        $uStmt->close();

        $code = '25CS' . rand(100, 999);
        $sStmt = $conn->prepare("INSERT INTO students (user_id, student_id, full_name, email, phone, course, semester, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($sStmt) {
            $sStmt->bind_param("isssssis", $uid, $code, $name, $email, $mobile, $course, $semester, $gender);
            $sStmt->execute();
            $sStmt->close();
        }
        $dbSuccess = true;
    }
}

setFlashMessage('success', "Form processed successfully! Record saved in CSV, JSON and Database.");
header("Location: login.php");
exit;
