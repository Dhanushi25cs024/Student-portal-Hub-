<?php
/**
 * ====================================================================
 * StudentHub Portal - Stored Registration Records (CSV / JSON)
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Stored Records';
$pageSubtitle = 'Server-Side Storage Records (CSV & JSON)';
$activePage = 'file_records';

$csvFile = __DIR__ . '/data/registrations.csv';
$jsonFile = __DIR__ . '/data/registrations.json';

// Read CSV
$csvRows = [];
if (file_exists($csvFile)) {
    if (($handle = fopen($csvFile, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $csvRows[] = $data;
        }
        fclose($handle);
    }
}

// Read JSON
$jsonRows = [];
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $jsonRows = $decoded;
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">📁 Stored Registration Records</h2>
            <p class="text-muted mb-0">Inspecting user registration records saved in CSV and JSON formats</p>
        </div>
        <div>
            <a href="register.php" class="btn btn-warning text-dark fw-bold">➕ New Registration</a>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-pills mb-4" id="recordsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="csv-tab" data-bs-toggle="pill" data-bs-target="#csv-pane" type="button" role="tab">
                📄 CSV File Data (<?= max(0, count($csvRows) - 1) ?> records)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="json-tab" data-bs-toggle="pill" data-bs-target="#json-pane" type="button" role="tab">
                📋 JSON File Data (<?= count($jsonRows) ?> records)
            </button>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content" id="recordsTabContent">
        <!-- CSV Pane -->
        <div class="tab-pane fade show active" id="csv-pane" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-success">
                        Target File: <code>data/registrations.csv</code>
                    </span>
                    <span class="badge bg-success">CSV Format</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($csvRows)): ?>
                        <div class="p-4 text-center text-muted">
                            <p>No records found in <code>data/registrations.csv</code> yet.</p>
                            <a href="register.php" class="btn btn-sm btn-primary">Submit a registration</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <?php foreach ($csvRows[0] as $head): ?>
                                            <th><?= htmlspecialchars($head) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 1; $i < count($csvRows); $i++): ?>
                                        <tr>
                                            <?php foreach ($csvRows[$i] as $col): ?>
                                                <td><?= htmlspecialchars($col) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- JSON Pane -->
        <div class="tab-pane fade" id="json-pane" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-info">
                        Target File: <code>data/registrations.json</code>
                    </span>
                    <span class="badge bg-info text-dark">JSON Format</span>
                </div>
                <div class="card-body">
                    <?php if (empty($jsonRows)): ?>
                        <div class="p-4 text-center text-muted">
                            <p>No JSON records found yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($jsonRows as $item): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100 bg-light">
                                        <div class="card-body">
                                            <h5 class="fw-bold text-primary mb-1">
                                                <?= htmlspecialchars($item['full_name'] ?? $item['name'] ?? 'Student') ?>
                                            </h5>
                                            <p class="text-muted small mb-2">ID: <?= htmlspecialchars($item['student_id'] ?? 'N/A') ?></p>
                                            <ul class="list-unstyled small mb-0">
                                                <li><strong>Email:</strong> <?= htmlspecialchars($item['email'] ?? '') ?></li>
                                                <li><strong>Phone:</strong> <?= htmlspecialchars($item['phone'] ?? $item['mobile'] ?? '') ?></li>
                                                <li><strong>Course:</strong> <?= htmlspecialchars($item['course'] ?? '') ?></li>
                                                <li><strong>Semester:</strong> <?= htmlspecialchars($item['semester'] ?? '') ?></li>
                                                <li><strong>Time:</strong> <?= htmlspecialchars($item['registered_at'] ?? $item['timestamp'] ?? '') ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
