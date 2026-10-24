<?php
/**
 * ====================================================================
 * StudentHub Portal - Admin Dashboard
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Route Guard: Admin only
requireAdmin('login.php');

$pageTitle = 'Admin Dashboard';
$pageSubtitle = 'System Overview, Analytics & Administrative Control';
$activePage = 'admin_dashboard';
$csrfToken = generateCsrfToken();

// Default statistics in case database is offline
$totalStudents = 21;
$totalEvents = 16;
$totalRegistrations = 6;
$activeUsers = 4;
$recentRegistrations = [];
$recentAuditLogs = [];
$usersList = [];

if ($conn && $db_connected) {
    // 1. Total Students
    $sRes = $conn->query("SELECT COUNT(*) AS total FROM students WHERE status != 'deleted'");
    if ($sRes) $totalStudents = (int)$sRes->fetch_assoc()['total'];

    // 2. Total Events
    $eRes = $conn->query("SELECT COUNT(*) AS total FROM events");
    if ($eRes) $totalEvents = (int)$eRes->fetch_assoc()['total'];

    // 3. Total Registrations
    $rRes = $conn->query("SELECT COUNT(*) AS total FROM registrations");
    if ($rRes) $totalRegistrations = (int)$rRes->fetch_assoc()['total'];

    // 4. Active Users
    $uRes = $conn->query("SELECT COUNT(*) AS total FROM users WHERE status = 'active'");
    if ($uRes) $activeUsers = (int)$uRes->fetch_assoc()['total'];

    // 5. Recent Registrations
    $regQuery = "
        SELECT r.id, r.student_name, r.student_email, r.registration_date, r.status, e.title AS event_title
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        ORDER BY r.id DESC
        LIMIT 6
    ";
    $regRes = $conn->query($regQuery);
    if ($regRes) {
        $recentRegistrations = $regRes->fetch_all(MYSQLI_ASSOC);
    }

    // 6. Recent Audit Logs
    $auditQuery = "SELECT id, username, role, action, table_affected, details, ip_address, created_at FROM audit_logs ORDER BY id DESC LIMIT 8";
    $auditRes = $conn->query($auditQuery);
    if ($auditRes) {
        $recentAuditLogs = $auditRes->fetch_all(MYSQLI_ASSOC);
    }

    // 7. Users for Status Toggle
    $usersRes = $conn->query("SELECT id, username, email, role, status, last_login FROM users ORDER BY id ASC LIMIT 10");
    if ($usersRes) {
        $usersList = $usersRes->fetch_all(MYSQLI_ASSOC);
    }
} else {
    // Simulated offline data
    $recentRegistrations = [
        ['id' => 1, 'student_name' => 'Dhanushi Patel', 'student_email' => 'student@studenthub.com', 'event_title' => 'CodeSprint Hackathon 2026', 'registration_date' => date('Y-m-d H:i'), 'status' => 'confirmed'],
        ['id' => 2, 'student_name' => 'Aarav Sharma', 'student_email' => 'aarav.sharma@charusat.edu.in', 'event_title' => 'GenAI Masterclass', 'registration_date' => date('Y-m-d H:i'), 'status' => 'confirmed'],
        ['id' => 3, 'student_name' => 'Priya Patel', 'student_email' => 'priya.patel@charusat.edu.in', 'event_title' => 'Spandan Cultural Festival', 'registration_date' => date('Y-m-d H:i'), 'status' => 'confirmed'],
    ];

    $recentAuditLogs = [
        ['id' => 1, 'username' => 'admin', 'role' => 'admin', 'action' => 'USER_LOGIN', 'table_affected' => 'users', 'details' => 'Admin signed in', 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s')],
        ['id' => 2, 'username' => 'admin', 'role' => 'admin', 'action' => 'STUDENT_CREATE', 'table_affected' => 'students', 'details' => 'Created student roster record', 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s')],
    ];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">

    <!-- Header Greeting -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">🛡️ Admin Executive Dashboard</h2>
            <p class="text-muted mb-0">System Overview, Real-Time Analytics & User Monitoring</p>
        </div>
        <div class="d-flex gap-2">
            <a href="manage_students.php" class="btn btn-warning fw-bold text-dark">
                👨‍🎓 Manage Students
            </a>
            <a href="manage_events.php" class="btn btn-primary fw-bold">
                📅 Manage Events
            </a>
            <a href="ajax_hub.php" class="btn btn-outline-dark fw-bold">
                ⚡ Directory Hub
            </a>
        </div>
    </div>

    <?php displayFlashMessage(); ?>
    <?php checkDbConnection(); ?>

    <!-- =====================================================
         KPI AGGREGATION CARDS
    ====================================================== -->
    <div class="row g-4 mb-5">
        <!-- Total Students -->
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow h-100 border-0" style="background: linear-gradient(135deg, #009688, #4CAF50); color: white;">
                <div class="card-body p-4 text-center">
                    <span class="fs-1">👨‍🎓</span>
                    <h5 class="fw-bold mt-2 text-white">Total Students</h5>
                    <h1 class="display-5 fw-bold my-1 text-white"><?= $totalStudents ?></h1>
                    <p class="small text-white-50 mb-0">Enrolled & Active</p>
                </div>
            </div>
        </div>

        <!-- Campus Events -->
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow h-100 border-0" style="background: linear-gradient(135deg, #1E3A5F, #3F51B5); color: white;">
                <div class="card-body p-4 text-center">
                    <span class="fs-1">📅</span>
                    <h5 class="fw-bold mt-2 text-white">Campus Events</h5>
                    <h1 class="display-5 fw-bold my-1 text-white"><?= $totalEvents ?></h1>
                    <p class="small text-white-50 mb-0">Workshops & Competitions</p>
                </div>
            </div>
        </div>

        <!-- Total Registrations -->
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow h-100 border-0" style="background: linear-gradient(135deg, #FF9800, #F57C00); color: white;">
                <div class="card-body p-4 text-center">
                    <span class="fs-1">📝</span>
                    <h5 class="fw-bold mt-2 text-white">Registrations</h5>
                    <h1 class="display-5 fw-bold my-1 text-white"><?= $totalRegistrations ?></h1>
                    <p class="small text-white-50 mb-0">Event Bookings</p>
                </div>
            </div>
        </div>

        <!-- Active User Accounts -->
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow h-100 border-0" style="background: linear-gradient(135deg, #673AB7, #9C27B0); color: white;">
                <div class="card-body p-4 text-center">
                    <span class="fs-1">👥</span>
                    <h5 class="fw-bold mt-2 text-white">Active Accounts</h5>
                    <h1 class="display-5 fw-bold my-1 text-white"><?= $activeUsers ?></h1>
                    <p class="small text-white-50 mb-0">System Users</p>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         ANALYTICS CHART & RECENT REGISTRATIONS
    ====================================================== -->
    <div class="row g-4 mb-5">
        <!-- Analytics Chart (HTML5 Canvas) -->
        <div class="col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">📊 Monthly Event Registrations</h5>
                    <span class="badge bg-primary">Annual Overview</span>
                </div>
                <div class="card-body p-4 text-center">
                    <canvas id="registrationChart" width="500" height="260" style="max-width: 100%; height: auto;"></canvas>
                    <div class="d-flex justify-content-center gap-4 mt-3 small text-muted">
                        <span><strong style="color: #009688;">■</strong> Technical</span>
                        <span><strong style="color: #FF9800;">■</strong> Cultural</span>
                        <span><strong style="color: #1E3A5F;">■</strong> Sports</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Registrations Feed -->
        <div class="col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">📋 Recent Event Registrations</h5>
                    <span class="badge bg-success">Live Status</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Event</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRegistrations as $reg): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($reg['student_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($reg['student_email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-primary"><?= htmlspecialchars($reg['event_title']) ?></span>
                                            <div class="small text-muted"><?= date('M d, H:i', strtotime($reg['registration_date'])) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Confirmed</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         SYSTEM AUDIT LOGS
    ====================================================== -->
    <div class="card shadow mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-dark">🛡️ Security & System Audit Trail</h5>
                <small class="text-muted">Tracks administrative actions, user logins, and database alterations</small>
            </div>
            <a href="audit_logs.php" class="btn btn-sm btn-outline-primary fw-bold">
                View Full Audit Logs (<?= count($recentAuditLogs) ?>) →
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Activity Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAuditLogs as $log): ?>
                            <tr>
                                <td class="small text-muted"><?= htmlspecialchars($log['created_at']) ?></td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($log['username'] ?? 'System') ?></span>
                                    <span class="badge bg-secondary ms-1"><?= htmlspecialchars($log['role'] ?? 'guest') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-dark"><?= htmlspecialchars($log['action']) ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($log['table_affected']) ?></code></td>
                                <td class="small text-truncate" style="max-width: 300px;">
                                    <?= htmlspecialchars($log['details']) ?>
                                </td>
                                <td class="small font-monospace"><?= htmlspecialchars($log['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =====================================================
         USER STATUS TOGGLE
    ====================================================== -->
    <?php if (!empty($usersList)): ?>
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark">⚙️ User Account Management & Status Control</h5>
                <small class="text-muted">Manage user access and toggle active/inactive account status</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usersList as $u): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="badge <?= ($u['role'] === 'admin') ? 'bg-danger' : 'bg-primary' ?>">
                                            <?= strtoupper($u['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= !empty($u['last_login']) ? htmlspecialchars($u['last_login']) : 'Never' ?>
                                    </td>
                                    <td class="text-center">
                                        <form action="actions/toggle_user_status.php" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <?php if ($u['status'] === 'active'): ?>
                                                <input type="hidden" name="status" value="inactive">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" <?= ($u['id'] == $_SESSION['user_id']) ? 'disabled title="Cannot deactivate yourself"' : '' ?>>
                                                    Deactivate
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    Activate
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- Canvas Registration Analytics Chart -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("registrationChart");
    if (!canvas) return;
    const ctx = canvas.getContext("2d");

    const months = ["Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const techData = [25, 45, 70, 95, 120, 160];
    const cultData = [10, 30, 50, 80, 110, 185];
    const sportData = [15, 20, 40, 60, 75, 95];

    const padding = 40;
    const width = canvas.width;
    const height = canvas.height;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    const maxVal = 200;

    ctx.strokeStyle = "#e0e0e0";
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = padding + (chartHeight / 4) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();

        ctx.fillStyle = "#888";
        ctx.font = "11px sans-serif";
        ctx.fillText(maxVal - (i * 50), 10, y + 4);
    }

    function drawLineSeries(data, color) {
        ctx.strokeStyle = color;
        ctx.lineWidth = 3;
        ctx.beginPath();

        const step = chartWidth / (months.length - 1);
        data.forEach((val, idx) => {
            const x = padding + idx * step;
            const y = padding + chartHeight - (val / maxVal) * chartHeight;
            if (idx === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.stroke();

        data.forEach((val, idx) => {
            const x = padding + idx * step;
            const y = padding + chartHeight - (val / maxVal) * chartHeight;
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, Math.PI * 2);
            ctx.fill();
        });
    }

    drawLineSeries(techData, "#009688");
    drawLineSeries(cultData, "#FF9800");
    drawLineSeries(sportData, "#1E3A5F");

    ctx.fillStyle = "#555";
    ctx.font = "12px sans-serif";
    ctx.textAlign = "center";
    const step = chartWidth / (months.length - 1);
    months.forEach((m, idx) => {
        const x = padding + idx * step;
        ctx.fillText(m, x, height - 15);
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
