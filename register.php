<?php
/**
 * ====================================================================
 * StudentHub Portal - Student Registration
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect to dashboard/profile
if (isLoggedIn()) {
    header('Location: ' . (hasRole('admin') ? 'admin_dashboard.php' : 'profile.php'));
    exit;
}

$pageTitle = 'Student Registration';
$pageSubtitle = 'Create your StudentHub Account';
$activePage = 'register';
$csrfToken = generateCsrfToken();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            <?php displayFlashMessage(); ?>

            <div class="card shadow">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <span class="fs-1">🎓</span>
                        <h2 class="fw-bold mt-2">Student Registration</h2>
                        <p class="text-muted">Enter your details to create your secure student portal account</p>
                    </div>

                    <!-- Client-Side Error Box -->
                    <div id="frontendErrorBox" class="alert alert-danger d-none" role="alert" aria-live="polite"></div>

                    <form id="registrationForm" action="actions/register_action.php" method="POST" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <!-- Full Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="fullName" class="form-label fw-semibold">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="fullName"
                                       name="full_name"
                                       placeholder="e.g. Dhanushi Patel"
                                       required
                                       aria-describedby="nameHelp nameError">
                                <div id="nameError" class="invalid-feedback">
                                    Please enter your full name (letters and spaces only, min 3 chars).
                                </div>
                                <small id="nameHelp" class="form-text text-muted">Letters & spaces only</small>
                            </div>

                            <!-- Username -->
                            <div class="col-md-6">
                                <label for="regUsername" class="form-label fw-semibold">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="regUsername"
                                       name="username"
                                       placeholder="e.g. dhanushi25"
                                       required
                                       aria-describedby="usernameError">
                                <div id="usernameError" class="invalid-feedback">
                                    Username must be 3-20 alphanumeric characters or underscores.
                                </div>
                            </div>
                        </div>

                        <!-- Email & Mobile -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="regEmail" class="form-label fw-semibold">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="regEmail"
                                       name="email"
                                       placeholder="student@charusat.edu.in"
                                       required
                                       aria-describedby="emailError">
                                <div id="emailError" class="invalid-feedback">
                                    Please enter a valid email address (e.g. name@domain.com).
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="regPhone" class="form-label fw-semibold">
                                    Mobile Number <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">+91</span>
                                    <input type="tel"
                                           class="form-control"
                                           id="regPhone"
                                           name="phone"
                                           placeholder="9876543210"
                                           maxlength="10"
                                           required
                                           aria-describedby="phoneError">
                                    <div id="phoneError" class="invalid-feedback">
                                        Please enter a valid 10-digit mobile number starting with 6-9.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Course & Semester -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label for="regCourse" class="form-label fw-semibold">
                                    Degree Course / Department <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="regCourse" name="course" required>
                                    <option value="" selected disabled>Select your department...</option>
                                    <option value="Computer Science & Engineering">Computer Science & Engineering (CSPIT)</option>
                                    <option value="Information Technology">Information Technology (CSPIT)</option>
                                    <option value="Computer Engineering">Computer Engineering (CSPIT / DEPSTAR)</option>
                                    <option value="AI & Machine Learning">AI & Machine Learning (CSPIT)</option>
                                    <option value="Electronics & Communication">Electronics & Communication</option>
                                </select>
                                <div class="invalid-feedback">Please select your enrolled course.</div>
                            </div>

                            <div class="col-md-5">
                                <label for="regSemester" class="form-label fw-semibold">
                                    Current Semester <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="regSemester" name="semester" required>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                    <option value="3" selected>Semester 3 (Current)</option>
                                    <option value="4">Semester 4</option>
                                    <option value="5">Semester 5</option>
                                    <option value="6">Semester 6</option>
                                    <option value="7">Semester 7</option>
                                    <option value="8">Semester 8</option>
                                </select>
                            </div>
                        </div>

                        <!-- Gender Radio Buttons -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block">
                                Gender <span class="text-danger">*</span>
                            </label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required>
                                <label class="form-check-label" for="genderMale">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female">
                                <label class="form-check-label" for="genderFemale">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other">
                                <label class="form-check-label" for="genderOther">Other</label>
                            </div>
                        </div>

                        <!-- Password & Confirm Password -->
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label for="regPassword" class="form-label fw-semibold">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="regPassword"
                                       name="password"
                                       placeholder="Minimum 6 characters"
                                       required
                                       aria-describedby="passwordHelp passwordError">
                                <div id="passwordError" class="invalid-feedback">
                                    Password must be at least 6 characters with a number or symbol.
                                </div>

                                <!-- Password Strength Meter -->
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-bold" style="font-size: 0.75rem;">Password Strength:</small>
                                        <small id="strengthLabel" class="text-muted fw-bold" style="font-size: 0.75rem;">None</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div id="strengthBar"
                                             class="progress-bar bg-danger"
                                             role="progressbar"
                                             style="width: 0%; transition: width 0.3s ease;"
                                             aria-valuenow="0"
                                             aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="regConfirmPassword" class="form-label fw-semibold">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="regConfirmPassword"
                                       name="confirm_password"
                                       placeholder="Re-enter password"
                                       required
                                       aria-describedby="confirmError">
                                <div id="confirmError" class="invalid-feedback">
                                    Passwords do not match.
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Conditions Checkbox -->
                        <div class="form-check my-4">
                            <input class="form-check-input" type="checkbox" id="regTerms" name="terms" required>
                            <label class="form-check-label" for="regTerms">
                                I agree to the <a href="#" class="text-decoration-underline" data-bs-toggle="modal" data-bs-target="#termsModal">StudentHub Terms and Academic Integrity Policies</a>.
                            </label>
                            <div class="invalid-feedback">You must agree before submitting.</div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" id="submitRegBtn" class="btn btn-primary btn-lg fw-bold">
                                🚀 Complete Registration
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <span class="text-muted">Already registered on StudentHub?</span>
                            <a href="login.php" class="fw-bold ms-1" style="color: #009688;">
                                Log In here
                            </a>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Link to view saved file records -->
            <div class="text-center mt-4">
                <a href="view_file_records.php" class="btn btn-sm btn-outline-secondary">
                    📁 View Registration Records (CSV / JSON)
                </a>
            </div>

        </div>
    </div>
</main>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">StudentHub Terms of Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Welcome to StudentHub Portal. By registering an account, you agree to:</p>
                <ul>
                    <li>Provide truthful, verified academic identification credentials.</li>
                    <li>Respect cybersecurity policies: no brute-force attacks or script injections.</li>
                    <li>Keep your password confidential and notify administration of unauthorized access.</li>
                    <li>Abide by CHARUSAT academic integrity guidelines for event and course participation.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Form Validation & Password Strength JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form            = document.getElementById("registrationForm");
    const fullName        = document.getElementById("fullName");
    const username        = document.getElementById("regUsername");
    const email           = document.getElementById("regEmail");
    const phone           = document.getElementById("regPhone");
    const password        = document.getElementById("regPassword");
    const confirmPassword = document.getElementById("regConfirmPassword");
    const terms           = document.getElementById("regTerms");
    const strengthBar     = document.getElementById("strengthBar");
    const strengthLabel   = document.getElementById("strengthLabel");
    const errorBox        = document.getElementById("frontendErrorBox");

    const nameRegex  = /^[a-zA-Z\s]{3,60}$/;
    const userRegex  = /^[a-zA-Z0-9_]{3,20}$/;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const phoneRegex = /^[6-9]\d{9}$/;

    function validateField(input, isValid, errorElementId) {
        if (isValid) {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
            return true;
        } else {
            input.classList.remove("is-valid");
            input.classList.add("is-invalid");
            return false;
        }
    }

    fullName.addEventListener("input", function () {
        validateField(fullName, nameRegex.test(fullName.value.trim()), "nameError");
    });

    username.addEventListener("input", function () {
        validateField(username, userRegex.test(username.value.trim()), "usernameError");
    });

    email.addEventListener("input", function () {
        validateField(email, emailRegex.test(email.value.trim()), "emailError");
    });

    phone.addEventListener("input", function () {
        validateField(phone, phoneRegex.test(phone.value.trim()), "phoneError");
    });

    password.addEventListener("input", function () {
        const val = password.value;
        let score = 0;

        if (val.length >= 6) score += 25;
        if (val.length >= 10) score += 15;
        if (/[A-Z]/.test(val)) score += 20;
        if (/[0-9]/.test(val)) score += 20;
        if (/[^A-Za-z0-9]/.test(val)) score += 20;

        strengthBar.style.width = score + "%";

        if (score === 0) {
            strengthBar.className = "progress-bar bg-danger";
            strengthLabel.textContent = "None";
            strengthLabel.className = "text-muted fw-bold";
        } else if (score < 40) {
            strengthBar.className = "progress-bar bg-danger";
            strengthLabel.textContent = "Weak";
            strengthLabel.className = "text-danger fw-bold";
        } else if (score < 75) {
            strengthBar.className = "progress-bar bg-warning";
            strengthLabel.textContent = "Moderate";
            strengthLabel.className = "text-warning fw-bold";
        } else {
            strengthBar.className = "progress-bar bg-success";
            strengthLabel.textContent = "Strong";
            strengthLabel.className = "text-success fw-bold";
        }

        validateField(password, val.length >= 6, "passwordError");
        if (confirmPassword.value.length > 0) {
            validateField(confirmPassword, confirmPassword.value === val, "confirmError");
        }
    });

    confirmPassword.addEventListener("input", function () {
        validateField(confirmPassword, confirmPassword.value === password.value && confirmPassword.value !== "", "confirmError");
    });

    form.addEventListener("submit", function (e) {
        const isNameOk    = validateField(fullName, nameRegex.test(fullName.value.trim()));
        const isUserOk    = validateField(username, userRegex.test(username.value.trim()));
        const isEmailOk   = validateField(email, emailRegex.test(email.value.trim()));
        const isPhoneOk   = validateField(phone, phoneRegex.test(phone.value.trim()));
        const isPassOk    = validateField(password, password.value.length >= 6);
        const isMatchOk   = validateField(confirmPassword, confirmPassword.value === password.value && confirmPassword.value !== "");
        const isTermsOk   = terms.checked;

        if (!isTermsOk) {
            terms.classList.add("is-invalid");
        } else {
            terms.classList.remove("is-invalid");
        }

        if (!isNameOk || !isUserOk || !isEmailOk || !isPhoneOk || !isPassOk || !isMatchOk || !isTermsOk) {
            e.preventDefault();
            errorBox.classList.remove("d-none");
            errorBox.innerHTML = "<strong>Please correct the highlighted errors</strong> before proceeding with registration.";
            window.scrollTo({ top: errorBox.offsetTop - 100, behavior: "smooth" });
        } else {
            errorBox.classList.add("d-none");
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
