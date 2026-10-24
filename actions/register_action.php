<?php
/**
 * ====================================================================
 * StudentHub Portal - Registration Action Handler
 * Secure Student Registration & Account Creation Processor
 * Validated Input, Prepared Statements, Password Hashing & File Sync
 * ====================================================================
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

// 1. Verify CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Security token expired or invalid. Please try submitting again.');
    header('Location: ../register.php');
    exit;
}

// 2. Extract and Sanitize Inputs
$name            = trim($_POST['full_name'] ?? '');
$username        = trim($_POST['username'] ?? '');
$email           = strtolower(trim($_POST['email'] ?? ''));
$mobile          = trim($_POST['phone'] ?? '');
$course          = trim($_POST['course'] ?? '');
$semester        = (int)($_POST['semester'] ?? 3);
$gender          = trim($_POST['gender'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$terms           = isset($_POST['terms']);

$errors = [];

// 3. Robust Server-Side Validations
// (a) Full Name validation (letters and spaces only, min 3 chars)
if (empty($name)) {
    $errors[] = "Full name is required.";
} elseif (!preg_match("/^[a-zA-Z\s]{3,60}$/", $name)) {
    $errors[] = "Full name must contain only letters and spaces (3-60 characters).";
}

// (b) Username validation (alphanumeric and underscore, 3-20 chars)
if (empty($username)) {
    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
} elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
    $errors[] = "Username must be 3-20 alphanumeric characters or underscores.";
}

// (c) Email validation
if (empty($email)) {
    $errors[] = "Email address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
    $errors[] = "Please provide a valid email address (e.g. user@domain.com).";
}

// (d) Mobile phone validation (10 digits starting with 6-9)
if (empty($mobile)) {
    $errors[] = "Mobile number is required.";
} elseif (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
    $errors[] = "Mobile number must be a valid 10-digit Indian phone number starting with 6, 7, 8, or 9.";
}

// (e) Course and Gender validation
$validCourses = [
    'Computer Science & Engineering',
    'Information Technology',
    'Computer Engineering',
    'AI & Machine Learning',
    'Electronics & Communication'
];
if (empty($course) || !in_array($course, $validCourses)) {
    $errors[] = "Please select a valid course/department.";
}

if (!in_array($gender, ['Male', 'Female', 'Other'])) {
    $errors[] = "Please select a valid gender option.";
}

// (f) Password validation (min 6 characters)
if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Password and confirm password do not match.";
}

// (g) Terms Acceptance
if (!$terms) {
    $errors[] = "You must accept the terms and conditions.";
}

// If validation failed, redirect back with error messages
if (!empty($errors)) {
    setFlashMessage('danger', implode('<br>', $errors));
    header('Location: ../register.php');
    exit;
}

// 4. Duplicate Email and Username Check (MySQL Prepared Statements)
$emailExists = false;
$userExists  = false;

if ($conn && $db_connected) {
    $dupStmt = $conn->prepare("SELECT id, email, username FROM users WHERE email = ? OR username = ? LIMIT 1");
    if ($dupStmt) {
        $dupStmt->bind_param("ss", $email, $username);
        $dupStmt->execute();
        $dupRes = $dupStmt->get_result();
        if ($dupRes && $dupRes->num_rows > 0) {
            $row = $dupRes->fetch_assoc();
            if (strtolower($row['email']) === $email) {
                $emailExists = true;
            }
            if (strtolower($row['username']) === strtolower($username)) {
                $userExists = true;
            }
        }
        $dupStmt->close();
    }
}

if ($emailExists) {
    setFlashMessage('danger', 'An account with this email address already exists. Please log in or use another email.');
    header('Location: ../register.php');
    exit;
}

if ($userExists) {
    setFlashMessage('danger', 'This username is already taken. Please choose another username.');
    header('Location: ../register.php');
    exit;
}

// 5. Password Hashing using password_hash() with BCRYPT
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Generate Student Code (e.g., 25CS + random 3-digit number)
$prefix = '25CS';
if (strpos($course, 'Information') !== false) {
    $prefix = '25IT';
} elseif (strpos($course, 'Machine') !== false) {
    $prefix = '25AIML';
} elseif (strpos($course, 'Computer Eng') !== false) {
    $prefix = '25CE';
}
$studentIdCode = $prefix . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);

// 6. Secure Database Insert via Prepared Statements
$dbInsertSuccess = false;
$newUserId = 0;

if ($conn && $db_connected) {
    $conn->begin_transaction();
    try {
        // Insert into users table
        $userStmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, status, created_at) VALUES (?, ?, ?, 'student', 'active', NOW())");
        $userStmt->bind_param("sss", $username, $email, $passwordHash);
        $userStmt->execute();
        $newUserId = $conn->insert_id;
        $userStmt->close();

        // Insert into students table
        $cgpa = round(rand(75, 98) / 10, 2);
        $stuStmt = $conn->prepare("INSERT INTO students (user_id, student_id, full_name, email, phone, course, semester, cgpa, gender, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
        $stuStmt->bind_param("isssssids", $newUserId, $studentIdCode, $name, $email, $mobile, $course, $semester, $cgpa, $gender);
        $stuStmt->execute();
        $newStudentId = $conn->insert_id;
        $stuStmt->close();

        $conn->commit();
        $dbInsertSuccess = true;

        // Audit Log Entry
        logAudit($conn, 'USER_REGISTER', 'users', $newUserId, "New student registered: {$name} ({$email}, ID: {$studentIdCode})", $newUserId, $username, 'student');
    } catch (Exception $e) {
        $conn->rollback();
        $dbInsertSuccess = false;
    }
}

// 7. Store record in CSV and JSON file storage
$csvFile = __DIR__ . '/../data/registrations.csv';
$csvRow = [
    date('Y-m-d H:i:s'),
    $studentIdCode,
    $name,
    $username,
    $email,
    $mobile,
    $course,
    $semester,
    $gender,
    'student',
    'active'
];
$csvHeaders = ['Registered At', 'Student ID', 'Full Name', 'Username', 'Email', 'Phone', 'Course', 'Semester', 'Gender', 'Role', 'Status'];
saveRecordToCsv($csvFile, $csvRow, $csvHeaders);

$jsonFile = __DIR__ . '/../data/registrations.json';
$jsonRecord = [
    'registered_at' => date('Y-m-d H:i:s'),
    'student_id'    => $studentIdCode,
    'full_name'     => $name,
    'username'      => $username,
    'email'         => $email,
    'phone'         => $mobile,
    'course'        => $course,
    'semester'      => $semester,
    'gender'        => $gender,
    'role'          => 'student',
    'db_persisted'  => $dbInsertSuccess
];
saveRecordToJson($jsonFile, $jsonRecord);

// Also sync with JSON backup storage
$phpJsonFile = __DIR__ . '/../data/registrations_backup.json';
saveRecordToJson($phpJsonFile, $jsonRecord);

// 8. Display Success Message and Redirect
setFlashMessage('success', "Registration successful! Welcome {$name}. Your Student ID is {$studentIdCode}. You can now log in.");
header('Location: ../login.php');
exit;
