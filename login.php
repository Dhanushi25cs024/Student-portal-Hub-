<?php
/**
 * ====================================================================
 * StudentHub Portal - Secure Login
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already authenticated
if (isLoggedIn()) {
    header('Location: ' . (hasRole('admin') ? 'admin_dashboard.php' : 'profile.php'));
    exit;
}

$pageTitle = 'Secure Student Login';
$pageSubtitle = 'Access Courses, Grades & Portal Hub';
$activePage = 'login';
$csrfToken = generateCsrfToken();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">

            <?php displayFlashMessage(); ?>

            <div class="card shadow">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <span class="fs-1">🔐</span>
                        <h2 class="fw-bold mt-2">Portal Login</h2>
                        <p class="text-muted small">Sign in with your email or username</p>
                    </div>

                    <form id="portalLoginForm" action="actions/login_action.php" method="POST">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <!-- Email / Username -->
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label fw-semibold">
                                Email Address or Username <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="loginEmail"
                                   name="email"
                                   placeholder="e.g. admin@studenthub.com"
                                   required
                                   autocomplete="username">
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="loginPassword" class="form-label fw-semibold mb-0">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <a href="#" onclick="alert('Demo password: admin123 or student123'); return false;" class="small text-muted text-decoration-none">
                                    Forgot Password?
                                </a>
                            </div>
                            <input type="password"
                                   class="form-control mt-1"
                                   id="loginPassword"
                                   name="password"
                                   placeholder="Enter your password"
                                   required
                                   autocomplete="current-password">
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check mb-4">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="rememberMe"
                                   name="remember_me">
                            <label class="form-check-label small" for="rememberMe">
                                Keep me signed in (Remember-Me for 30 days)
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                🔓 Sign In
                            </button>
                        </div>

                        <p class="text-center mt-3 mb-0 text-muted small">
                            Don't have an account yet?
                            <a href="register.php" class="fw-bold ms-1" style="color: #009688;">
                                Register as Student
                            </a>
                        </p>
                    </form>

                </div>
            </div>

            <!-- Demo Credentials Helper Box -->
            <div class="card border-0 bg-light shadow-sm mt-4">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-secondary mb-2">
                        💡 Quick Demo Logins:
                    </h6>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" id="demoAdminBtn">
                            🔑 Load Admin (admin@studenthub.com)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark" id="demoStudentBtn">
                            🎓 Load Student (student@studenthub.com)
                        </button>
                    </div>
                    <small class="text-muted d-block">
                        Password for both demo accounts: <code>admin123</code> / <code>student123</code>
                    </small>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const emailField = document.getElementById("loginEmail");
    const passField  = document.getElementById("loginPassword");
    const adminBtn   = document.getElementById("demoAdminBtn");
    const studentBtn = document.getElementById("demoStudentBtn");

    if (adminBtn) {
        adminBtn.addEventListener("click", function () {
            emailField.value = "admin@studenthub.com";
            passField.value  = "admin123";
        });
    }

    if (studentBtn) {
        studentBtn.addEventListener("click", function () {
            emailField.value = "student@studenthub.com";
            passField.value  = "student123";
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
