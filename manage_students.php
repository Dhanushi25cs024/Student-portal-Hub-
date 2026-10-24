<?php
/**
 * ====================================================================
 * StudentHub Portal - Student Management
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Route Guard: Admin only
requireAdmin('login.php');

$pageTitle = 'Student Management';
$pageSubtitle = 'Manage Student Records, Enrollment & Academic Profiles';
$activePage = 'students';
$csrfToken = generateCsrfToken();

// Query Filtering
$searchQuery  = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');

$studentsList = [];

if ($conn && $db_connected) {
    $sql = "SELECT id, user_id, student_id, full_name, email, phone, course, semester, cgpa, gender, status, created_at FROM students WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($searchQuery)) {
        $sql .= " AND (full_name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
        $searchTerm = "%{$searchQuery}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    if (!empty($courseFilter) && $courseFilter !== 'all') {
        $sql .= " AND course = ?";
        $params[] = $courseFilter;
        $types .= "s";
    }

    if (!empty($statusFilter) && $statusFilter !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $statusFilter;
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
        if ($res) {
            $studentsList = $res->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
} else {
    // Fallback records from students.json for offline viewing
    $jsonFile = __DIR__ . '/data/students.json';
    if (file_exists($jsonFile)) {
        $raw = json_decode(file_get_contents($jsonFile), true);
        if (is_array($raw)) {
            foreach ($raw as $i => $s) {
                $studentsList[] = [
                    'id'         => $i + 1,
                    'student_id' => $s['enrollment'] ?? ('25CS' . ($i + 1)),
                    'full_name'  => $s['name'] ?? 'Student',
                    'email'      => $s['email'] ?? 'student@charusat.edu.in',
                    'phone'      => '9876543210',
                    'course'     => $s['department'] ?? 'Computer Science & Engineering',
                    'semester'   => $s['semester'] ?? 3,
                    'cgpa'       => $s['cgpa'] ?? 8.5,
                    'gender'     => 'Male',
                    'status'     => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">

    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">👨‍🎓 Student Management</h2>
            <p class="text-muted mb-0">Search, Filter, and Manage Student Profiles</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Add Student Button -->
            <button class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                ➕ Add New Student
            </button>
            <!-- Export CSV Button -->
            <a href="actions/export_students_csv.php" class="btn btn-success fw-bold text-white">
                📥 Export to CSV
            </a>
        </div>
    </div>

    <?php displayFlashMessage(); ?>
    <?php checkDbConnection(); ?>

    <!-- Search & Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-body p-3">
            <form method="GET" action="manage_students.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search by Name, Email or ID</label>
                    <input type="text"
                           class="form-control"
                           name="search"
                           value="<?= htmlspecialchars($searchQuery) ?>"
                           placeholder="Type to search...">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Filter by Department</label>
                    <select class="form-select" name="course">
                        <option value="all" <?= ($courseFilter === 'all' || empty($courseFilter)) ? 'selected' : '' ?>>All Departments</option>
                        <option value="Computer Science & Engineering" <?= ($courseFilter === 'Computer Science & Engineering') ? 'selected' : '' ?>>Computer Science & Engineering</option>
                        <option value="Information Technology" <?= ($courseFilter === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                        <option value="Computer Engineering" <?= ($courseFilter === 'Computer Engineering') ? 'selected' : '' ?>>Computer Engineering</option>
                        <option value="AI & Machine Learning" <?= ($courseFilter === 'AI & Machine Learning') ? 'selected' : '' ?>>AI & Machine Learning</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select class="form-select" name="status">
                        <option value="all" <?= ($statusFilter === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="deleted" <?= ($statusFilter === 'deleted') ? 'selected' : '' ?>>Soft-Deleted</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-bold">
                        🔍 Filter
                    </button>
                    <a href="manage_students.php" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table Card -->
    <div class="card shadow">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                Enrolled Students
                <span class="badge bg-primary ms-2"><?= count($studentsList) ?> Found</span>
            </h5>
            <small class="text-muted">Verified Database Records</small>
        </div>
        <div class="card-body p-0">
            <?php if (empty($studentsList)): ?>
                <div class="text-center py-5">
                    <p class="fs-4 text-muted">No students matching your criteria.</p>
                    <a href="manage_students.php" class="btn btn-sm btn-outline-primary">Clear Filters</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Student ID</th>
                                <th>Student Details</th>
                                <th>Department</th>
                                <th>Sem</th>
                                <th>CGPA</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 170px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentsList as $st): ?>
                                <tr class="<?= ($st['status'] === 'deleted') ? 'table-secondary text-muted' : '' ?>">
                                    <td>
                                        <span class="badge bg-dark fw-bold" style="font-size: 0.85rem;">
                                            <?= htmlspecialchars($st['student_id']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($st['full_name']) ?></div>
                                        <small class="text-muted">
                                            📧 <?= htmlspecialchars($st['email']) ?> | 📞 <?= htmlspecialchars($st['phone']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;">
                                            <?= htmlspecialchars($st['course']) ?>
                                        </span>
                                    </td>
                                    <td><?= (int)$st['semester'] ?></td>
                                    <td>
                                        <span class="badge <?= ($st['cgpa'] >= 9.0) ? 'bg-success' : (($st['cgpa'] >= 8.0) ? 'bg-info text-dark' : 'bg-warning text-dark') ?>">
                                            <?= number_format((float)$st['cgpa'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($st['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($st['status'] === 'inactive'): ?>
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Soft-Deleted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit Button -->
                                            <button type="button"
                                                    class="btn btn-outline-primary edit-student-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editStudentModal"
                                                    data-id="<?= $st['id'] ?>"
                                                    data-student-id="<?= htmlspecialchars($st['student_id']) ?>"
                                                    data-name="<?= htmlspecialchars($st['full_name']) ?>"
                                                    data-email="<?= htmlspecialchars($st['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($st['phone']) ?>"
                                                    data-course="<?= htmlspecialchars($st['course']) ?>"
                                                    data-semester="<?= $st['semester'] ?>"
                                                    data-cgpa="<?= $st['cgpa'] ?>"
                                                    data-gender="<?= htmlspecialchars($st['gender']) ?>"
                                                    data-status="<?= htmlspecialchars($st['status']) ?>"
                                                    title="Edit Record">
                                                ✏️
                                            </button>

                                            <?php if ($st['status'] === 'deleted'): ?>
                                                <!-- Restore Button -->
                                                <form action="actions/student_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Restore this student to active status?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-success" title="Restore Student">
                                                        ♻️
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <!-- Soft Delete Button -->
                                                <form action="actions/student_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Soft-delete this student record?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="action" value="soft_delete">
                                                    <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-warning" title="Soft Delete">
                                                        🗑️
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Permanent Purge Button -->
                                            <form action="actions/student_actions.php" method="POST" class="d-inline" onsubmit="return confirm('PERMANENTLY PURGE student record #<?= $st['id'] ?>? This cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="action" value="permanent_delete">
                                                <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Purge Record">
                                                    ❌
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- ADD STUDENT MODAL -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="actions/student_actions.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="add">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold text-dark" id="addStudentModalLabel">➕ Add New Student Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student ID / Enrollment <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="student_id" placeholder="e.g. 25CS099" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" placeholder="Student Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" placeholder="student@charusat.edu.in" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" placeholder="9876543210" maxlength="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department / Course <span class="text-danger">*</span></label>
                            <select class="form-select" name="course" required>
                                <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Computer Engineering">Computer Engineering</option>
                                <option value="AI & Machine Learning">AI & Machine Learning</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Semester</label>
                            <select class="form-select" name="semester">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">CGPA</label>
                            <input type="number" step="0.01" min="0" max="10" class="form-control" name="cgpa" value="8.50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT STUDENT MODAL -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="actions/student_actions.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="editStudentModalLabel">✏️ Edit Student Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student ID</label>
                            <input type="text" class="form-control" id="edit_student_id" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" id="edit_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" id="edit_phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department / Course <span class="text-danger">*</span></label>
                            <select class="form-select" name="course" id="edit_course" required>
                                <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Computer Engineering">Computer Engineering</option>
                                <option value="AI & Machine Learning">AI & Machine Learning</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Semester</label>
                            <input type="number" min="1" max="8" class="form-control" name="semester" id="edit_semester" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">CGPA</label>
                            <input type="number" step="0.01" min="0" max="10" class="form-control" name="cgpa" id="edit_cgpa" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gender</label>
                            <select class="form-select" name="gender" id="edit_gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Account Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="deleted">Soft-Deleted</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".edit-student-btn");
    editButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            document.getElementById("edit_id").value         = this.dataset.id;
            document.getElementById("edit_student_id").value = this.dataset.studentId;
            document.getElementById("edit_name").value       = this.dataset.name;
            document.getElementById("edit_email").value      = this.dataset.email;
            document.getElementById("edit_phone").value      = this.dataset.phone;
            document.getElementById("edit_course").value     = this.dataset.course;
            document.getElementById("edit_semester").value   = this.dataset.semester;
            document.getElementById("edit_cgpa").value       = this.dataset.cgpa;
            document.getElementById("edit_gender").value     = this.dataset.gender;
            document.getElementById("edit_status").value     = this.dataset.status;
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
