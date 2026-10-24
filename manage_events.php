<?php
/**
 * ====================================================================
 * StudentHub Portal - Event Management
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdmin('login.php');

$pageTitle = 'Event Management';
$pageSubtitle = 'Create & Manage Campus Events with Poster Upload';
$activePage = 'events_admin';
$csrfToken = generateCsrfToken();

// Filtering
$categoryFilter = trim($_GET['category'] ?? 'all');
$statusFilter   = trim($_GET['status'] ?? 'all');
$searchQuery    = trim($_GET['search'] ?? '');

$eventsList = [];

if ($conn && $db_connected) {
    $sql = "SELECT id, title, category, event_date, event_time, venue, fee, max_seats, registered_seats, poster, status, description, created_at FROM events WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($searchQuery)) {
        $sql .= " AND (title LIKE ? OR venue LIKE ? OR description LIKE ?)";
        $term = "%{$searchQuery}%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $types .= "sss";
    }

    if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
        $sql .= " AND category = ?";
        $params[] = $categoryFilter;
        $types .= "s";
    }

    if ($statusFilter !== 'all' && !empty($statusFilter)) {
        $sql .= " AND status = ?";
        $params[] = $statusFilter;
        $types .= "s";
    }

    $sql .= " ORDER BY event_date ASC, id DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            $eventsList = $res->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
} else {
    // Offline fallback from data/events.json
    $jsonFile = __DIR__ . '/data/events.json';
    if (file_exists($jsonFile)) {
        $raw = json_decode(file_get_contents($jsonFile), true);
        if (is_array($raw)) {
            foreach ($raw as $i => $ev) {
                $eventsList[] = [
                    'id'               => $i + 1,
                    'title'            => $ev['title'] ?? 'Campus Event',
                    'category'         => $ev['category'] ?? 'Technical',
                    'event_date'       => $ev['date'] ?? date('Y-m-d', strtotime('+' . ($i + 5) . ' days')),
                    'event_time'       => '10:00:00',
                    'venue'            => $ev['venue'] ?? 'Campus Grounds',
                    'fee'              => isset($ev['fee']) ? (float)filter_var($ev['fee'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0.0,
                    'max_seats'        => $ev['capacity'] ?? 100,
                    'registered_seats' => $ev['registered'] ?? 25,
                    'poster'           => 'default_event.jpg',
                    'status'           => 'upcoming',
                    'description'      => $ev['description'] ?? 'Event description.',
                    'created_at'       => date('Y-m-d H:i:s')
                ];
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">📅 Event Management</h2>
            <p class="text-muted mb-0">Schedule Events, Upload Media & Manage Registrations</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addEventModal">
                ➕ Create New Event
            </button>
            <a href="events.html" class="btn btn-outline-primary fw-bold" target="_blank">
                🌐 Student View
            </a>
        </div>
    </div>

    <?php displayFlashMessage(); ?>
    <?php checkDbConnection(); ?>

    <!-- Filter & Search Toolbar -->
    <div class="card shadow mb-4">
        <div class="card-body p-3">
            <form method="GET" action="manage_events.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search Event Title or Venue</label>
                    <input type="text"
                           class="form-control"
                           name="search"
                           value="<?= htmlspecialchars($searchQuery) ?>"
                           placeholder="Type to search events...">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <select class="form-select" name="category">
                        <option value="all" <?= ($categoryFilter === 'all') ? 'selected' : '' ?>>All Categories</option>
                        <option value="Technical" <?= ($categoryFilter === 'Technical') ? 'selected' : '' ?>>Technical</option>
                        <option value="Workshop" <?= ($categoryFilter === 'Workshop') ? 'selected' : '' ?>>Workshop</option>
                        <option value="Cultural" <?= ($categoryFilter === 'Cultural') ? 'selected' : '' ?>>Cultural</option>
                        <option value="Sports" <?= ($categoryFilter === 'Sports') ? 'selected' : '' ?>>Sports</option>
                        <option value="Seminar" <?= ($categoryFilter === 'Seminar') ? 'selected' : '' ?>>Seminar</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select class="form-select" name="status">
                        <option value="all" <?= ($statusFilter === 'all') ? 'selected' : '' ?>>All</option>
                        <option value="upcoming" <?= ($statusFilter === 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
                        <option value="ongoing" <?= ($statusFilter === 'ongoing') ? 'selected' : '' ?>>Ongoing</option>
                        <option value="completed" <?= ($statusFilter === 'completed') ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-bold">Filter</button>
                    <a href="manage_events.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Grid / Table -->
    <div class="card shadow">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                Scheduled Events
                <span class="badge bg-primary ms-2"><?= count($eventsList) ?> Listed</span>
            </h5>
            <small class="text-muted">Storage: <code>uploads/posters/</code></small>
        </div>
        <div class="card-body p-0">
            <?php if (empty($eventsList)): ?>
                <div class="text-center py-5">
                    <p class="fs-4 text-muted">No events found matching current criteria.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 70px;">Poster</th>
                                <th>Event Details</th>
                                <th>Category</th>
                                <th>Date & Venue</th>
                                <th>Seats</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 130px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventsList as $ev): ?>
                                <?php
                                $posterPath = 'uploads/posters/' . $ev['poster'];
                                $hasCustomPoster = !empty($ev['poster']) && file_exists(__DIR__ . '/' . $posterPath);
                                $imgSrc = $hasCustomPoster ? $posterPath : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTo5SFHu6xPFG4GVAS_RSeQOdaARiaRjeaWNJAgi7gLgQ&s=10';
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars($imgSrc) ?>"
                                             alt="Poster"
                                             class="rounded shadow-sm"
                                             style="width: 55px; height: 55px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($ev['title']) ?></div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 320px;">
                                            <?= htmlspecialchars($ev['description']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($ev['category']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">📅 <?= date('M d, Y', strtotime($ev['event_date'])) ?></div>
                                        <div class="small text-muted">📍 <?= htmlspecialchars($ev['venue']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= (int)$ev['registered_seats'] ?> / <?= (int)$ev['max_seats'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($ev['status'] === 'upcoming'): ?>
                                            <span class="badge bg-success">Upcoming</span>
                                        <?php elseif ($ev['status'] === 'ongoing'): ?>
                                            <span class="badge bg-primary">Ongoing</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit Event Button -->
                                            <button type="button"
                                                    class="btn btn-outline-primary edit-event-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editEventModal"
                                                    data-id="<?= $ev['id'] ?>"
                                                    data-title="<?= htmlspecialchars($ev['title']) ?>"
                                                    data-category="<?= htmlspecialchars($ev['category']) ?>"
                                                    data-date="<?= htmlspecialchars($ev['event_date']) ?>"
                                                    data-time="<?= htmlspecialchars($ev['event_time']) ?>"
                                                    data-venue="<?= htmlspecialchars($ev['venue']) ?>"
                                                    data-fee="<?= $ev['fee'] ?>"
                                                    data-maxseats="<?= $ev['max_seats'] ?>"
                                                    data-status="<?= htmlspecialchars($ev['status']) ?>"
                                                    data-desc="<?= htmlspecialchars($ev['description']) ?>"
                                                    title="Edit Event">
                                                ✏️
                                            </button>

                                            <!-- Delete Event Button -->
                                            <form action="actions/event_actions.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete event: <?= addslashes($ev['title']) ?>?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Delete Event">
                                                    🗑️
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

<!-- ADD EVENT MODAL -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="actions/event_actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="add">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold text-dark" id="addEventModalLabel">➕ Create New Campus Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="e.g. AI & Robotics Showcase" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="Technical">Technical</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Cultural">Cultural</option>
                                <option value="Sports">Sports</option>
                                <option value="Seminar">Seminar</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Time</label>
                            <input type="time" class="form-control" name="event_time" value="10:00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Venue / Hall <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="venue" placeholder="e.g. CSPIT Auditorium" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Registration Fee (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="fee" value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Capacity (Seats)</label>
                            <input type="number" min="1" class="form-control" name="max_seats" value="100">
                        </div>

                        <!-- Poster Upload -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                Event Poster (Image Upload)
                                <span class="badge bg-secondary">Max 2MB | JPG, PNG, WEBP</span>
                            </label>
                            <input type="file"
                                   class="form-control"
                                   id="posterFileInput"
                                   name="poster_image"
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">
                                File is strictly validated on server for genuine image format and file size limit.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Event Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Provide overview, guidelines, and schedule..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Publish Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT EVENT MODAL -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="actions/event_actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_ev_id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="editEventModalLabel">✏️ Edit Event Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="edit_ev_title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" name="category" id="edit_ev_category">
                                <option value="Technical">Technical</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Cultural">Cultural</option>
                                <option value="Sports">Sports</option>
                                <option value="Seminar">Seminar</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Date</label>
                            <input type="date" class="form-control" name="event_date" id="edit_ev_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Event Time</label>
                            <input type="time" class="form-control" name="event_time" id="edit_ev_time">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Venue</label>
                            <input type="text" class="form-control" name="venue" id="edit_ev_venue" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fee (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="fee" id="edit_ev_fee">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Capacity</label>
                            <input type="number" min="1" class="form-control" name="max_seats" id="edit_ev_seats">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="status" id="edit_ev_status">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Replace Poster (Optional)</label>
                            <input type="file" class="form-control" name="poster_image" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" name="description" id="edit_ev_desc" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Update Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const editBtns = document.querySelectorAll(".edit-event-btn");
    editBtns.forEach(btn => {
        btn.addEventListener("click", function () {
            document.getElementById("edit_ev_id").value       = this.dataset.id;
            document.getElementById("edit_ev_title").value    = this.dataset.title;
            document.getElementById("edit_ev_category").value = this.dataset.category;
            document.getElementById("edit_ev_date").value     = this.dataset.date;
            document.getElementById("edit_ev_time").value     = this.dataset.time;
            document.getElementById("edit_ev_venue").value    = this.dataset.venue;
            document.getElementById("edit_ev_fee").value      = this.dataset.fee;
            document.getElementById("edit_ev_seats").value    = this.dataset.maxseats;
            document.getElementById("edit_ev_status").value   = this.dataset.status;
            document.getElementById("edit_ev_desc").value     = this.dataset.desc;
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
