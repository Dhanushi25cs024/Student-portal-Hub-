<?php
/**
 * ====================================================================
 * StudentHub Portal - System Audit Log Viewer
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdmin('login.php');

$pageTitle = 'Security Audit Logs';
$pageSubtitle = 'Full System Activity & Modification Traceability';
$activePage = 'audit_logs';

$actionFilter = trim($_GET['action'] ?? 'all');
$searchQuery  = trim($_GET['search'] ?? '');

$logsList = [];

if ($conn && $db_connected) {
    $sql = "SELECT id, user_id, username, role, action, table_affected, record_id, details, ip_address, created_at FROM audit_logs WHERE 1=1";
    $params = [];
    $types = "";

    if ($actionFilter !== 'all' && !empty($actionFilter)) {
        $sql .= " AND action = ?";
        $params[] = $actionFilter;
        $types .= "s";
    }

    if (!empty($searchQuery)) {
        $sql .= " AND (username LIKE ? OR details LIKE ? OR ip_address LIKE ?)";
        $term = "%{$searchQuery}%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $types .= "sss";
    }

    $sql .= " ORDER BY id DESC LIMIT 100";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            $logsList = $res->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
} else {
    // Read from data/audit_logs.json fallback
    $jsonFile = __DIR__ . '/data/audit_logs.json';
    if (file_exists($jsonFile)) {
        $logsList = json_decode(file_get_contents($jsonFile), true) ?: [];
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">🛡️ System Security Audit Logs</h2>
            <p class="text-muted mb-0">Audit Trail tracking authentication, event edits, and database updates</p>
        </div>
        <div>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary fw-bold">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body p-3">
            <form method="GET" action="audit_logs.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Search by Username, Details or IP</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search logs...">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Filter by Action Type</label>
                    <select class="form-select" name="action">
                        <option value="all">All Actions</option>
                        <option value="USER_LOGIN" <?= ($actionFilter === 'USER_LOGIN') ? 'selected' : '' ?>>User Login</option>
                        <option value="USER_REGISTER" <?= ($actionFilter === 'USER_REGISTER') ? 'selected' : '' ?>>User Registration</option>
                        <option value="STUDENT_CREATE" <?= ($actionFilter === 'STUDENT_CREATE') ? 'selected' : '' ?>>Student Creation</option>
                        <option value="STUDENT_UPDATE" <?= ($actionFilter === 'STUDENT_UPDATE') ? 'selected' : '' ?>>Student Update</option>
                        <option value="EVENT_CREATE" <?= ($actionFilter === 'EVENT_CREATE') ? 'selected' : '' ?>>Event Creation</option>
                        <option value="EVENT_REGISTER" <?= ($actionFilter === 'EVENT_REGISTER') ? 'selected' : '' ?>>Event Registration</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-bold">Filter</button>
                    <a href="audit_logs.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Audit Events (<?= count($logsList) ?> Records)</h5>
            <span class="badge bg-dark">Immutable Log Table</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Timestamp</th>
                            <th>Actor / Role</th>
                            <th>Action</th>
                            <th>Affected Entity</th>
                            <th>Activity Summary</th>
                            <th>Origin IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logsList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No audit logs found matching criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logsList as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?? '#' ?></td>
                                    <td class="small text-muted font-monospace"><?= htmlspecialchars($log['created_at'] ?? ($log['timestamp'] ?? '')) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($log['username'] ?? 'System') ?></div>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($log['role'] ?? 'guest') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($log['action'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($log['table_affected'] ?? 'general') ?></code>
                                        <?php if (!empty($log['record_id'])): ?>
                                            <span class="badge bg-light text-dark border">#<?= $log['record_id'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                                    <td class="small font-monospace"><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
