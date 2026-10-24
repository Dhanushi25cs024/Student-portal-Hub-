<?php
/**
 * StudentHub Portal - Reusable Dynamic Navbar
 */
require_once __DIR__ . '/auth.php';

if (!isset($activePage)) {
    $activePage = '';
}
$isUserLoggedIn = isLoggedIn();
$currentUser    = getCurrentUser();
$isAdmin        = hasRole('admin');
?>
<!-- ==================================================
     DYNAMIC NAVBAR (ROLE-BASED MENU)
================================================== -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">StudentHub</a>

        <!-- Hamburger Toggle -->
        <button class="navbar-toggler"
                type="button"
                id="menuToggle"
                aria-label="Open navigation menu"
                aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">

                <!-- Theme Switcher -->
                <li class="nav-item ms-2 me-2">
                    <button id="themeButton" class="btn btn-warning btn-sm fw-bold">
                        🌙 Dark Mode
                    </button>
                </li>

                <!-- Common: Home -->
                <li class="nav-item">
                    <a class="nav-link <?= ($activePage === 'home') ? 'active fw-bold' : '' ?>" href="index.php">
                        Home
                    </a>
                </li>

                <?php if ($isAdmin): ?>
                    <!-- ADMIN SPECIFIC MENU -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'admin_dashboard') ? 'active fw-bold' : '' ?>" href="admin_dashboard.php">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'students') ? 'active fw-bold' : '' ?>" href="manage_students.php">
                            Manage Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'events_admin') ? 'active fw-bold' : '' ?>" href="manage_events.php">
                            Manage Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'ajax_hub') ? 'active fw-bold' : '' ?>" href="ajax_hub.php">
                            Directory Hub
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'audit_logs') ? 'active fw-bold' : '' ?>" href="audit_logs.php">
                            Audit Logs
                        </a>
                    </li>
                <?php else: ?>
                    <!-- STUDENT / GUEST COMMON MENU -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'courses') ? 'active fw-bold' : '' ?>" href="cources.html">
                            Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'assignments') ? 'active fw-bold' : '' ?>" href="assignment.html">
                            Assignments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'attendance') ? 'active fw-bold' : '' ?>" href="attendence.html">
                            Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'events') ? 'active fw-bold' : '' ?>" href="events.html">
                            Events & Hub
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'notices') ? 'active fw-bold' : '' ?>" href="notice.html">
                            Notices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activePage === 'faculty') ? 'active fw-bold' : '' ?>" href="faculty.html">
                            Faculty
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link <?= ($activePage === 'contact') ? 'active fw-bold' : '' ?>" href="contact.html">
                        Contact
                    </a>
                </li>

                <!-- AUTHENTICATION STATE BUTTONS -->
                <?php if ($isUserLoggedIn): ?>
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle btn btn-sm btn-outline-light text-white px-3 py-1"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            👤 <?= htmlspecialchars($currentUser['full_name']) ?>
                            <span class="badge bg-warning text-dark ms-1 text-uppercase" style="font-size: 0.7rem;">
                                <?= htmlspecialchars($currentUser['role']) ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    📋 My Profile
                                </a>
                            </li>
                            <?php if ($isAdmin): ?>
                                <li>
                                    <a class="dropdown-item" href="admin_dashboard.php">
                                        ⚙️ Admin Console
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger fw-bold" href="logout.php">
                                    🚪 Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm px-3" href="register.php">
                            Register
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-warning btn-sm px-3 text-dark fw-bold" href="login.php">
                            Login
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>
