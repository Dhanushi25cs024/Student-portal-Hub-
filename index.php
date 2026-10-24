<?php
/**
 * ====================================================================
 * StudentHub Portal - Main Home Page
 * Unified Portal Interface
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$pageSubtitle = 'Welcome to your Centralized Student Information Portal';
$activePage = 'home';

$isUserLoggedIn = isLoggedIn();
$currentUser    = getCurrentUser();
$isAdmin        = hasRole('admin');

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container flex-grow-1 my-5">

    <?php displayFlashMessage(); ?>

    <!-- ==================================================
         NOTIFICATION BANNER
    ================================================== -->
    <div class="banner mb-4" id="notificationBanner">
        <span>📢 New Notice: Semester examination timetable and campus event registration schedules are now available.</span>
        <button id="closeBanner" aria-label="Close notification">×</button>
    </div>

    <!-- ==================================================
         CONTENT SLIDER
    ================================================== -->
    <section class="slider-section mb-5">
        <div class="slide active">
            <h2>Welcome to StudentHub Portal!</h2>
            <p>Your unified digital campus hub for academics, events, registrations, attendance, and administrative management.</p>
        </div>
        <div class="slide">
            <h2>Campus & Student Management</h2>
            <p>Seamlessly manage student rosters, publish campus events with poster uploads, and export data with ease.</p>
        </div>
        <div class="slide">
            <h2>Real-Time Directory & Instant Bookings</h2>
            <p>Interactive student directory with live search, event schedules, and instant booking confirmations.</p>
        </div>

        <div class="slider-buttons">
            <button id="previousSlide" class="btn btn-secondary">Previous</button>
            <button id="nextSlide" class="btn btn-primary">Next</button>
        </div>
    </section>

    <!-- ==================================================
         WELCOME STATUS CARD
    ================================================== -->
    <div class="card shadow mb-5 text-center p-4">
        <?php if ($isUserLoggedIn): ?>
            <h2 class="fw-bold">Welcome back, <?= htmlspecialchars($currentUser['full_name']) ?>!</h2>
            <p class="text-muted">
                Logged in as <span class="badge bg-warning text-dark text-uppercase"><?= htmlspecialchars($currentUser['role']) ?></span>
                | User: <strong><?= htmlspecialchars($currentUser['username']) ?></strong>
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <?php if ($isAdmin): ?>
                    <a href="admin_dashboard.php" class="btn btn-primary fw-bold">⚙️ Admin Dashboard</a>
                    <a href="manage_students.php" class="btn btn-warning fw-bold text-dark">👨‍🎓 Manage Students</a>
                    <a href="manage_events.php" class="btn btn-success fw-bold">📅 Manage Events</a>
                <?php else: ?>
                    <a href="profile.php" class="btn btn-primary fw-bold">👤 View My Profile</a>
                    <a href="events.html" class="btn btn-warning fw-bold text-dark">🎟️ Browse Campus Events</a>
                <?php endif; ?>
                <button data-modal-target class="btn btn-outline-secondary">
                    📢 Campus Announcement
                </button>
            </div>
        <?php else: ?>
            <h2 class="fw-bold">Welcome to StudentHub!</h2>
            <p class="text-muted">A centralized academic portal for courses, assignments, attendance, notices, and events.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="login.php" class="btn btn-primary fw-bold px-4">🔓 Login</a>
                <a href="register.php" class="btn btn-warning fw-bold text-dark px-4">🎓 Register</a>
                <button data-modal-target class="btn btn-outline-secondary">📢 Announcement</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- ==================================================
         ANNOUNCEMENT MODAL
    ================================================== -->
    <div id="modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-content">
            <button id="close" class="close" aria-label="Close modal">×</button>
            <h2 id="modalTitle">Portal Announcement</h2>
            <p>Welcome to the official StudentHub Portal. Manage your academic profile, browse campus events, check attendance, and access department resources seamlessly!</p>
            <button id="modalOkay" class="btn btn-primary">Got it!</button>
        </div>
    </div>

    <!-- ==================================================
         QUICK ACCESS MODULE CARDS
    ================================================== -->
    <h2 class="text-center mb-4 fw-bold">Explore Campus Modules</h2>

    <div class="row g-4 mb-5">
        <!-- Courses -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3">
                <h3 class="fs-1">📚</h3>
                <h4 class="fw-bold">Courses</h4>
                <p class="text-muted small">View registered subjects & study materials.</p>
                <a href="cources.html" class="btn btn-primary mt-auto">Open Courses</a>
            </div>
        </div>

        <!-- Assignments -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3">
                <h3 class="fs-1">📝</h3>
                <h4 class="fw-bold">Assignments</h4>
                <p class="text-muted small">Track pending tasks & lab assignments.</p>
                <a href="assignment.html" class="btn btn-primary mt-auto">Open Tasks</a>
            </div>
        </div>

        <!-- Attendance -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3">
                <h3 class="fs-1">📅</h3>
                <h4 class="fw-bold">Attendance</h4>
                <p class="text-muted small">Check subject-wise attendance percentages.</p>
                <a href="attendence.html" class="btn btn-primary mt-auto">Check Records</a>
            </div>
        </div>

        <!-- Notices -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3">
                <h3 class="fs-1">📢</h3>
                <h4 class="fw-bold">Notices</h4>
                <p class="text-muted small">University circulars & schedule alerts.</p>
                <a href="notice.html" class="btn btn-primary mt-auto">Read News</a>
            </div>
        </div>

        <!-- Events & Data Hub -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3 border-top border-4 border-warning">
                <h3 class="fs-1">🎪</h3>
                <h4 class="fw-bold">Events & Hub</h4>
                <p class="text-muted small">Campus events with search, sort & pagination.</p>
                <a href="events.html" class="btn btn-warning fw-bold text-dark mt-auto">Explore Hub</a>
            </div>
        </div>

        <!-- Student Management -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3 border-top border-4 border-primary">
                <h3 class="fs-1">👨‍🎓</h3>
                <h4 class="fw-bold">Students</h4>
                <p class="text-muted small">Student roster, profiles, and CSV export.</p>
                <a href="manage_students.php" class="btn btn-primary fw-bold mt-auto">Manage Students</a>
            </div>
        </div>

        <!-- Interactive Directory Hub -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3 border-top border-4 border-info">
                <h3 class="fs-1">⚡</h3>
                <h4 class="fw-bold">Directory Hub</h4>
                <p class="text-muted small">Fast directory with live search and instant updates.</p>
                <a href="ajax_hub.php" class="btn btn-info fw-bold text-dark mt-auto">Open Directory</a>
            </div>
        </div>

        <!-- Admin Console -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow text-center p-3 border-top border-4 border-success">
                <h3 class="fs-1">🛡️</h3>
                <h4 class="fw-bold">Admin Console</h4>
                <p class="text-muted small">Executive metrics, registration trends, and security logs.</p>
                <a href="admin_dashboard.php" class="btn btn-success fw-bold mt-auto">Admin Console</a>
            </div>
        </div>
    </div>

    <!-- ==================================================
         COLLAPSIBLE FAQ SECTION
    ================================================== -->
    <section class="faq-section">
        <h2 class="text-center mb-4 fw-bold">Frequently Asked Questions</h2>

        <div class="faq-item">
            <button class="faq-question" type="button" aria-expanded="false">
                What is StudentHub Portal?
                <span class="float-end">+</span>
            </button>
            <div class="faq-answer">
                StudentHub is an integrated academic portal built for Charotar University of Science and Technology (CHARUSAT) providing access to courses, student directories, campus events, attendance, and administrative monitoring.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button" aria-expanded="false">
                How does the portal ensure authentication security?
                <span class="float-end">+</span>
            </button>
            <div class="faq-answer">
                StudentHub implements industry-standard web defense: BCRYPT password hashing, parameterized database queries to eliminate SQL injection, anti-session fixation via regeneration, 15-minute inactivity timeouts, and persistent SHA-256 remember-me tokens.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button" aria-expanded="false">
                How do live search and instant event bookings work?
                <span class="float-end">+</span>
            </button>
            <div class="faq-answer">
                The portal uses modern asynchronous APIs to fetch and filter records without page reloads, providing instant search feedback with debouncing and instant seat confirmations.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button" aria-expanded="false">
                How do I toggle Dark Mode / Light Mode?
                <span class="float-end">+</span>
            </button>
            <div class="faq-answer">
                Click the theme button in the navbar. Your preference is automatically saved in your browser and persists across all pages.
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
