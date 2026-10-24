<?php
/**
 * ====================================================================
 * StudentHub Portal - Interactive Directory Hub
 * Asynchronous Data Management with Fetch API
 * ====================================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Directory Hub';
$pageSubtitle = 'Interactive Student Directory with Live Search';
$activePage = 'ajax_hub';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">⚡ Interactive Directory Hub</h2>
            <p class="text-muted mb-0">Search, add, and manage student records with instant live updates and zero page reloads</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#ajaxAddModal">
                ➕ Quick Add Student
            </button>
            <button class="btn btn-outline-secondary" id="refreshDataBtn">
                🔄 Reload Feed
            </button>
        </div>
    </div>

    <!-- Toast Notification Container for Feedback -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <div id="ajaxToast" class="toast align-items-center text-white bg-primary border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">Action completed successfully.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card with Live Debounce -->
    <div class="card shadow mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">
                        Instant Search (Live Filtering)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">🔍</span>
                        <input type="text"
                               id="ajaxSearchInput"
                               class="form-control border-start-0"
                               placeholder="Type student name, email, or student ID...">
                        <span class="input-group-text bg-white d-none" id="searchSpinner">
                            <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                        </span>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Filter by Department</label>
                    <select id="ajaxCourseFilter" class="form-select">
                        <option value="all">All Departments</option>
                        <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Computer Engineering">Computer Engineering</option>
                        <option value="AI & Machine Learning">AI & Machine Learning</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Students Table -->
    <div class="card shadow">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                Live Students Feed
                <span class="badge bg-primary ms-2" id="studentCountBadge">Loading...</span>
            </h5>
            <small class="text-muted">Real-Time Data Feed</small>
        </div>

        <div class="card-body p-0">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-2 text-muted fw-semibold">Fetching records from server...</p>
            </div>

            <div class="table-responsive d-none" id="tableContainer">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name & Contact</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>CGPA</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Injected dynamically by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5 d-none">
                <p class="fs-4 text-muted">No student records match your query.</p>
            </div>
        </div>
    </div>

</main>

<!-- ADD STUDENT MODAL -->
<div class="modal fade" id="ajaxAddModal" tabindex="-1" aria-labelledby="ajaxAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="ajaxAddForm">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold text-dark" id="ajaxAddModalLabel">➕ Add Student Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addId" placeholder="e.g. 25CS090" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addName" placeholder="Full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="addEmail" placeholder="student@charusat.edu.in" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select class="form-select" id="addCourse" required>
                            <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="AI & Machine Learning">AI & Machine Learning</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Semester</label>
                            <input type="number" class="form-control" id="addSem" value="3" min="1" max="8">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">CGPA</label>
                            <input type="number" step="0.01" class="form-control" id="addCgpa" value="8.50" min="0" max="10">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="ajaxSaveBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="btnSpinner" role="status"></span>
                        Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tableBody       = document.getElementById("studentsTableBody");
    const tableContainer  = document.getElementById("tableContainer");
    const loadingInd      = document.getElementById("loadingIndicator");
    const emptyState      = document.getElementById("emptyState");
    const countBadge      = document.getElementById("studentCountBadge");
    const searchInput     = document.getElementById("ajaxSearchInput");
    const searchSpinner   = document.getElementById("searchSpinner");
    const courseFilter    = document.getElementById("ajaxCourseFilter");
    const refreshBtn      = document.getElementById("refreshDataBtn");
    const addForm         = document.getElementById("ajaxAddForm");
    const saveBtn         = document.getElementById("ajaxSaveBtn");
    const btnSpinner      = document.getElementById("btnSpinner");

    const toastEl         = document.getElementById("ajaxToast");
    const toastMsg        = document.getElementById("toastMessage");
    const toastInstance   = new bootstrap.Toast(toastEl, { delay: 4000 });

    function showToast(message, isSuccess = true) {
        toastEl.className = isSuccess
            ? "toast align-items-center text-white bg-success border-0 shadow"
            : "toast align-items-center text-white bg-danger border-0 shadow";
        toastMsg.textContent = message;
        toastInstance.show();
    }

    async function loadStudents(search = "", course = "all") {
        loadingInd.classList.remove("d-none");
        tableContainer.classList.add("d-none");
        emptyState.classList.add("d-none");

        try {
            const url = `api/students.php?search=${encodeURIComponent(search)}&course=${encodeURIComponent(course)}`;
            const response = await fetch(url);
            const result = await response.json();

            loadingInd.classList.add("d-none");

            if (result.success && Array.isArray(result.data)) {
                const list = result.data;
                countBadge.textContent = `${list.length} Students`;

                if (list.length === 0) {
                    emptyState.classList.remove("d-none");
                    return;
                }

                tableBody.innerHTML = list.map(st => `
                    <tr id="student-row-${st.id}">
                        <td>
                            <span class="badge bg-dark fw-bold">${st.student_id || st.enrollment || 'ID'}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">${st.full_name || st.name}</div>
                            <small class="text-muted">${st.email || ''}</small>
                        </td>
                        <td>${st.course || st.department || 'N/A'}</td>
                        <td>${st.semester || 3}</td>
                        <td>
                            <span class="badge ${parseFloat(st.cgpa) >= 9 ? 'bg-success' : 'bg-primary'}">
                                ${parseFloat(st.cgpa || 8.0).toFixed(2)}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${st.id}" data-name="${st.full_name || st.name}">
                                🗑️ Delete
                            </button>
                        </td>
                    </tr>
                `).join("");

                tableContainer.classList.remove("d-none");

                document.querySelectorAll(".delete-btn").forEach(btn => {
                    btn.addEventListener("click", function () {
                        deleteStudent(this.dataset.id, this.dataset.name);
                    });
                });
            } else {
                emptyState.classList.remove("d-none");
                countBadge.textContent = "0 Students";
            }
        } catch (err) {
            loadingInd.classList.add("d-none");
            emptyState.classList.remove("d-none");
            showToast("Failed to fetch students from API: " + err.message, false);
        }
    }

    async function deleteStudent(id, name) {
        if (!confirm(`Are you sure you want to delete ${name}?`)) return;

        try {
            const response = await fetch("api/students.php", {
                method: "DELETE",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: id })
            });
            const result = await response.json();

            if (result.success) {
                const row = document.getElementById(`student-row-${id}`);
                if (row) {
                    row.style.transition = "all 0.5s";
                    row.style.backgroundColor = "#ffcdd2";
                    setTimeout(() => row.remove(), 400);
                }
                showToast(`Student ${name} deleted successfully!`, true);
            } else {
                showToast(result.message || "Delete failed", false);
            }
        } catch (err) {
            showToast("Network error: " + err.message, false);
        }
    }

    let debounceTimer;
    searchInput.addEventListener("input", function () {
        searchSpinner.classList.remove("d-none");
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchSpinner.classList.add("d-none");
            loadStudents(searchInput.value.trim(), courseFilter.value);
        }, 300);
    });

    courseFilter.addEventListener("change", function () {
        loadStudents(searchInput.value.trim(), courseFilter.value);
    });

    refreshBtn.addEventListener("click", function () {
        loadStudents(searchInput.value.trim(), courseFilter.value);
        showToast("Student list refreshed.", true);
    });

    addForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        saveBtn.disabled = true;
        btnSpinner.classList.remove("d-none");

        const payload = {
            student_id: document.getElementById("addId").value.trim(),
            full_name:  document.getElementById("addName").value.trim(),
            email:      document.getElementById("addEmail").value.trim(),
            course:     document.getElementById("addCourse").value,
            semester:   parseInt(document.getElementById("addSem").value),
            cgpa:       parseFloat(document.getElementById("addCgpa").value),
            gender:     "Male"
        };

        try {
            const res = await fetch("api/students.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            saveBtn.disabled = false;
            btnSpinner.classList.add("d-none");

            if (data.success) {
                const modalEl = document.getElementById("ajaxAddModal");
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                addForm.reset();

                showToast(`Student ${payload.full_name} added successfully!`, true);
                loadStudents(searchInput.value.trim(), courseFilter.value);
            } else {
                showToast(data.message || "Failed to add student", false);
            }
        } catch (err) {
            saveBtn.disabled = false;
            btnSpinner.classList.add("d-none");
            showToast("Error communicating with API: " + err.message, false);
        }
    });

    loadStudents();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
