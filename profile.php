<?php
/**
 * ====================================================================
 * StudentHub Portal - Student Profile & Dashboard
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin('login.php');

$currentUser = getCurrentUser();
$pageTitle = 'My Student Profile';
$pageSubtitle = 'Academic Information & Registered Events';
$activePage = 'profile';
$csrfToken = generateCsrfToken();

$studentData = [
    'student_id' => $currentUser['student_code'] ?: '25CS024',
    'full_name'  => $currentUser['full_name'],
    'email'      => $currentUser['email'],
    'phone'      => '9876543210',
    'course'     => 'Computer Science & Engineering',
    'semester'   => 3,
    'cgpa'       => 9.45,
    'status'     => 'active'
];

$myRegistrations = [];

if ($conn && $db_connected) {
    // Fetch student profile
    $stStmt = $conn->prepare("SELECT student_id, full_name, email, phone, course, semester, cgpa, status FROM students WHERE user_id = ? OR email = ? LIMIT 1");
    if ($stStmt) {
        $stStmt->bind_param("is", $currentUser['id'], $currentUser['email']);
        $stStmt->execute();
        $res = $stStmt->get_result();
        if ($res && $res->num_rows > 0) {
            $studentData = $res->fetch_assoc();
        }
        $stStmt->close();
    }

    // Fetch registered events
    $regStmt = $conn->prepare("
        SELECT r.id, r.registration_date, r.status, e.title, e.category, e.event_date, e.event_time, e.venue
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.student_email = ?
        ORDER BY r.id DESC
    ");
    if ($regStmt) {
        $regStmt->bind_param("s", $currentUser['email']);
        $regStmt->execute();
        $rRes = $regStmt->get_result();
        if ($rRes) {
            $myRegistrations = $rRes->fetch_all(MYSQLI_ASSOC);
        }
        $regStmt->close();
    }
}

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $newName  = trim($_POST['full_name'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        if (!empty($newName)) {
            if ($conn && $db_connected) {
                $upd = $conn->prepare("UPDATE students SET full_name = ?, phone = ? WHERE email = ?");
                $upd->bind_param("sss", $newName, $newPhone, $currentUser['email']);
                $upd->execute();
                $upd->close();

                $_SESSION['full_name'] = $newName;
                logAudit($conn, 'PROFILE_UPDATE', 'students', null, "User updated profile name to: {$newName}");
            }
            setFlashMessage('success', 'Profile information updated successfully!');
            header('Location: profile.php');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <?php displayFlashMessage(); ?>

            <!-- Profile Overview Card -->
            <div class="card shadow mb-4">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 90px; height: 90px; font-size: 40px; border: 3px solid #009688;">
                            👤
                        </div>
                        <h2 class="fw-bold mt-3 mb-1"><?= htmlspecialchars($studentData['full_name']) ?></h2>
                        <p class="text-muted mb-2">
                            <?= htmlspecialchars($studentData['course']) ?> | Semester <?= (int)$studentData['semester'] ?>
                        </p>
                        <span class="badge bg-success px-3 py-2 text-uppercase" style="letter-spacing: 1px;">
                            Active Student Account
                        </span>
                    </div>

                    <!-- Details Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-4">
                            <tbody>
                                <tr>
                                    <th class="bg-light" style="width: 35%;">Enrollment No.</th>
                                    <td class="fw-bold text-dark font-monospace"><?= htmlspecialchars($studentData['student_id']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Full Name</th>
                                    <td><?= htmlspecialchars($studentData['full_name']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Email Address</th>
                                    <td><?= htmlspecialchars($studentData['email']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Mobile Number</th>
                                    <td><?= htmlspecialchars($studentData['phone']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Department</th>
                                    <td><?= htmlspecialchars($studentData['course']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Current CGPA</th>
                                    <td>
                                        <span class="badge bg-warning text-dark fw-bold fs-6">
                                            <?= number_format((float)$studentData['cgpa'], 2) ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-2">
                        <button id="editProfileToggleBtn" class="btn btn-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#editProfileBox">
                            ✏️ Edit Profile Info
                        </button>
                        <a href="events.html" class="btn btn-warning text-dark fw-bold">
                            📅 Explore Campus Events
                        </a>
                    </div>

                    <!-- Collapsible Edit Box -->
                    <div class="collapse mt-4" id="editProfileBox">
                        <div class="card card-body bg-light border-0 shadow-sm">
                            <h5 class="fw-bold mb-3">Update Personal Details</h5>
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($studentData['full_name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($studentData['phone']) ?>" maxlength="10">
                                </div>
                                <button type="submit" class="btn btn-success fw-bold">Save Changes</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <!-- My Registered Campus Events Card -->
            <div class="card shadow">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        🎟️ My Event Registrations (<?= count($myRegistrations) ?>)
                    </h5>
                    <a href="events.html" class="btn btn-sm btn-outline-primary">Browse More Events</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($myRegistrations)): ?>
                        <div class="text-center py-4 text-muted">
                            <p class="mb-2">You haven't registered for any campus events yet.</p>
                            <a href="events.html" class="btn btn-sm btn-warning text-dark fw-bold">View Upcoming Events</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Event</th>
                                        <th>Category</th>
                                        <th>Date & Venue</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myRegistrations as $reg): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($reg['title']) ?></td>
                                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($reg['category']) ?></span></td>
                                            <td class="small">
                                                <div>📅 <?= date('M d, Y', strtotime($reg['event_date'])) ?></div>
                                                <div class="text-muted">📍 <?= htmlspecialchars($reg['venue']) ?></div>
                                            </td>
                                            <td><span class="badge bg-success">Confirmed</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
