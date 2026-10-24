/**
 * ====================================================================
 * StudentHub Portal - Interactive DOM Engine,
 * Event Handling, and UI Interactivity
 * ====================================================================
 *
 * This script provides interactive functionality for:
 * 1. Theme Switcher (Dark/Light Mode) with localStorage persistence
 * 2. Mobile Navigation / Hamburger Menu toggle (Open, Close, Click Outside)
 * 3. Notification Banner dismissal
 * 4. Content / Announcement Modal popup (Open, Close, Outside Click, ESC key)
 * 5. Content Slider with Previous / Next navigation
 * 6. Collapsible FAQ with aria-expanded & icon toggle
 * 7. Page-specific interactions (Search filter, dynamic alerts, forms)
 *
 * All functions use modern ES6+ event listeners (addEventListener)
 * and defensive element checks (if (element)) to prevent errors on pages
 * where specific components are absent.
 */

// ====================================================================
// 1. LIGHT / DARK THEME 
// ====================================================================

/**
 * Updates the text/icon of all theme toggle buttons based on theme state.
 * @param {boolean} isDark - True if dark theme is currently active
 */
function updateThemeButtons(isDark) {
    const buttons = document.querySelectorAll("#themeButton, .theme-toggle-btn");
    buttons.forEach(function (btn) {
        btn.innerHTML = isDark ? "☀️ Light" : "🌙 Dark";
    });
}


function toggleTheme() {
    const isDark = document.body.classList.toggle("dark-theme");
    localStorage.setItem("theme", isDark ? "dark" : "light");
    updateThemeButtons(isDark);
}


(function applyInitialTheme() {
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
    } else if (savedTheme === "light") {
        document.body.classList.remove("dark-theme");
    }
})();


// ====================================================================
// DOM CONTENT LOADED - ATTACH ALL EVENT LISTENERS
// ====================================================================
document.addEventListener("DOMContentLoaded", function () {

    // --- 1. Initialize Theme Buttons ---
    const savedTheme = localStorage.getItem("theme");
    const isDarkActive = savedTheme === "dark";
    updateThemeButtons(isDarkActive);

    const themeButtons = document.querySelectorAll("#themeButton, .theme-toggle-btn");
    themeButtons.forEach(function (button) {
        button.addEventListener("click", toggleTheme);
    });


    // ================================================================
    // 2. HAMBURGER MENU (MOBILE NAVIGATION)
    // ================================================================
    const menuTogglers = document.querySelectorAll("#menuToggle, .navbar-toggler");
    const navbarNav = document.getElementById("navbarNav");

    menuTogglers.forEach(function (toggler) {
        toggler.addEventListener("click", function (event) {
            event.stopPropagation();
            if (navbarNav) {
                navbarNav.classList.toggle("show");
                const isExpanded = navbarNav.classList.contains("show");
                toggler.setAttribute("aria-expanded", isExpanded ? "true" : "false");
            }
        });
    });

    // Close mobile menu when clicking outside navbar
    document.addEventListener("click", function (event) {
        if (navbarNav && navbarNav.classList.contains("show")) {
            const isClickInside = event.target.closest(".navbar");
            if (!isClickInside) {
                navbarNav.classList.remove("show");
                menuTogglers.forEach(function (toggler) {
                    toggler.setAttribute("aria-expanded", "false");
                });
            }
        }
    });


    // ================================================================
    // 3. NOTIFICATION BANNER DISMISSAL
    // ================================================================
    const banner = document.getElementById("notificationBanner");
    const closeBanner = document.getElementById("closeBanner");

    if (banner && closeBanner) {
        closeBanner.addEventListener("click", function () {
            banner.style.display = "none";
        });
    }


    // ================================================================
    // 4. CONTENT SLIDER 
    // ================================================================
    const slides = document.querySelectorAll(".slide");
    const prevSlideBtn = document.getElementById("previousSlide");
    const nextSlideBtn = document.getElementById("nextSlide");

    if (slides.length > 0) {
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(function (slide) {
                slide.classList.remove("active");
            });
            slides[index].classList.add("active");
        }

        if (nextSlideBtn) {
            nextSlideBtn.addEventListener("click", function () {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            });
        }

        if (prevSlideBtn) {
            prevSlideBtn.addEventListener("click", function () {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            });
        }
    }


    // ================================================================
    // 5. MODAL POPUP (OPEN, CLOSE, OUTSIDE CLICK, ESC KEY)
    // ================================================================
    const modal = document.getElementById("modal");
    const openModalButtons = document.querySelectorAll("[data-modal-target]");
    const closeModalButton = document.getElementById("close");
    const modalOkayButton = document.getElementById("modalOkay");

    function openModal() {
        if (modal) {
            modal.style.display = "block";
            document.body.style.overflow = "hidden";
        }
    }

    function closeModal() {
        if (modal) {
            modal.style.display = "none";
            document.body.style.overflow = "";
        }
    }

    openModalButtons.forEach(function (button) {
        button.addEventListener("click", openModal);
    });

    if (closeModalButton) {
        closeModalButton.addEventListener("click", closeModal);
    }

    if (modalOkayButton) {
        modalOkayButton.addEventListener("click", closeModal);
    }

    // Contact Page Modal
    const contactModal = document.getElementById("contactModal");
    const contactNotifBtn = document.getElementById("contactNotificationButton");
    const contactDismissBtns = document.querySelectorAll("#contactModal [data-bs-dismiss='modal']");

    function openContactModal() {
        if (contactModal) {
            if (window.bootstrap && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(contactModal);
                modalInstance.show();
            } else {
                contactModal.style.display = "block";
                contactModal.classList.add("show");
            }
        }
    }

    function closeContactModal() {
        if (contactModal) {
            if (window.bootstrap && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(contactModal);
                if (modalInstance) modalInstance.hide();
            } else {
                contactModal.style.display = "none";
                contactModal.classList.remove("show");
            }
        }
    }

    if (contactNotifBtn) {
        contactNotifBtn.addEventListener("click", openContactModal);
    }

    contactDismissBtns.forEach(function (btn) {
        btn.addEventListener("click", closeContactModal);
    });

    // Close on outside click
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
        if (event.target === contactModal) {
            closeContactModal();
        }
    });

    // Close on Escape key press
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            if (modal && modal.style.display === "block") {
                closeModal();
            }
            if (contactModal && (contactModal.style.display === "block" || contactModal.classList.contains("show"))) {
                closeContactModal();
            }
        }
    });


    // ================================================================
    // 6. COLLAPSIBLE FAQ ACCORDION
    // ================================================================
    const faqQuestions = document.querySelectorAll(".faq-question");

    faqQuestions.forEach(function (question) {
        question.addEventListener("click", function () {
            const answer = this.nextElementSibling;
            if (!answer) return;

            const icon = this.querySelector(".float-end");
            const isCurrentlyHidden =
                answer.style.display === "none" ||
                window.getComputedStyle(answer).display === "none";

            if (isCurrentlyHidden) {
                answer.style.display = "block";
                this.setAttribute("aria-expanded", "true");
                if (icon) icon.textContent = "−";
            } else {
                answer.style.display = "none";
                this.setAttribute("aria-expanded", "false");
                if (icon) icon.textContent = "+";
            }
        });
    });


    // ================================================================
    // 7. COMPONENT INTERACTIONS (PAGES 1-10)
    // ================================================================

    // (A) Contact Form
    const contactForm = document.getElementById("contactForm");
    const contactResponse = document.getElementById("contactResponse");
    if (contactForm) {
        contactForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const nameInput = document.getElementById("contactName");
            const name = nameInput ? nameInput.value : "Student";
            if (contactResponse) {
                contactResponse.style.display = "block";
                contactResponse.textContent = `Thank you, ${name}! Your message has been sent successfully.`;
            }
            contactForm.reset();
        });
    }

    // (B) Login Form
    const loginForm = document.getElementById("loginForm");
    const loginMessage = document.getElementById("loginMessage");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const emailInput = document.getElementById("email");
            const email = emailInput ? emailInput.value : "";
            if (loginMessage) {
                loginMessage.className = "alert alert-success mt-3 text-center";
                loginMessage.style.display = "block";
                loginMessage.textContent = `Login successful! Welcome back, ${email}.`;
            }
        });
    }

    // (C) Assignment Buttons
    const assignmentButtons = document.querySelectorAll(".assignment-btn");
    const assignmentMessage = document.getElementById("assignmentMessage");
    if (assignmentButtons.length > 0 && assignmentMessage) {
        assignmentButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                const title = this.getAttribute("data-assignment") || "Assignment";
                assignmentMessage.style.display = "block";
                assignmentMessage.textContent = `Viewing details for: "${title}"`;
            });
        });
    }

    // (D) Courses Buttons
    const courseButtons = document.querySelectorAll(".course-btn");
    const courseMessage = document.getElementById("courseMessage");
    if (courseButtons.length > 0 && courseMessage) {
        courseButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                const courseName = this.getAttribute("data-course") || "Course";
                courseMessage.style.display = "block";
                courseMessage.textContent = `Showing course syllabus and study materials for: ${courseName}`;
            });
        });
    }

    // (E) Faculty Buttons & Search Filter
    const facultyButtons = document.querySelectorAll(".faculty-btn");
    const facultyMessage = document.getElementById("facultyMessage");
    if (facultyButtons.length > 0 && facultyMessage) {
        facultyButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                const name = this.getAttribute("data-faculty") || "Faculty";
                facultyMessage.style.display = "block";
                facultyMessage.textContent = `Faculty profile selected: ${name}`;
            });
        });
    }

    const facultySearch = document.getElementById("facultySearch");
    const facultyCards = document.querySelectorAll(".faculty-card");
    if (facultySearch && facultyCards.length > 0) {
        facultySearch.addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            facultyCards.forEach(function (card) {
                const cardColumn = card.closest(".col-md-6, .col-lg-3") || card;
                const nameHeading = card.querySelector("h4");
                const facultyName = nameHeading ? nameHeading.textContent.toLowerCase() : "";
                if (facultyName.includes(query)) {
                    cardColumn.style.display = "";
                } else {
                    cardColumn.style.display = "none";
                }
            });
        });
    }

    // (F) Notices Read More & Toggle Notices Visibility
    const noticeButtons = document.querySelectorAll(".notice-btn");
    const noticeMessage = document.getElementById("noticeMessage");
    if (noticeButtons.length > 0 && noticeMessage) {
        noticeButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                const noticeName = this.getAttribute("data-notice") || "Notice";
                noticeMessage.style.display = "block";
                noticeMessage.textContent = `Notice opened: ${noticeName}`;
            });
        });
    }

    const toggleNoticesBtn = document.getElementById("toggleNotices");
    const noticeCards = document.querySelectorAll(".notice-card");
    if (toggleNoticesBtn && noticeCards.length > 0) {
        let isNoticesVisible = true;
        toggleNoticesBtn.addEventListener("click", function () {
            isNoticesVisible = !isNoticesVisible;
            noticeCards.forEach(function (card) {
                card.style.display = isNoticesVisible ? "block" : "none";
            });
            toggleNoticesBtn.textContent = isNoticesVisible ? "Hide Notices" : "Show Notices";
            toggleNoticesBtn.classList.toggle("btn-success", isNoticesVisible);
            toggleNoticesBtn.classList.toggle("btn-secondary", !isNoticesVisible);
        });
    }

    // (G) Attendance Information Toggle
    const attendanceBtn = document.getElementById("attendanceInfoButton");
    const attendanceBox = document.getElementById("attendanceInfo");
    if (attendanceBtn && attendanceBox) {
        attendanceBtn.addEventListener("click", function () {
            const isHidden = window.getComputedStyle(attendanceBox).display === "none";
            attendanceBox.style.display = isHidden ? "block" : "none";
            attendanceBtn.textContent = isHidden ? "Hide Attendance Information" : "Show Attendance Information";
        });
    }

    // (H) Admin Actions & Add Notice Form
    const addNoticeBtn = document.getElementById("addNoticeButton");
    const noticeFormBox = document.getElementById("noticeFormBox");
    const adminMessage = document.getElementById("adminMessage");
    const manageStudentsBtn = document.getElementById("manageStudentsButton");
    const manageCoursesBtn = document.getElementById("manageCoursesButton");
    const adminNoticeForm = document.getElementById("adminNoticeForm");

    if (addNoticeBtn && noticeFormBox) {
        addNoticeBtn.addEventListener("click", function () {
            const isHidden = window.getComputedStyle(noticeFormBox).display === "none";
            noticeFormBox.style.display = isHidden ? "block" : "none";
        });
    }

    if (manageStudentsBtn && adminMessage) {
        manageStudentsBtn.addEventListener("click", function () {
            adminMessage.style.display = "block";
            adminMessage.innerHTML = 'Student management panel loaded: <a href="manage_students.php" class="alert-link fw-bold text-decoration-underline">Manage Students &rarr;</a>';
        });
    }

    if (manageCoursesBtn && adminMessage) {
        manageCoursesBtn.addEventListener("click", function () {
            adminMessage.style.display = "block";
            adminMessage.innerHTML = 'Event management panel loaded: <a href="manage_events.php" class="alert-link fw-bold text-decoration-underline">Manage Events &rarr;</a>';
        });
    }

    if (adminNoticeForm) {
        adminNoticeForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const titleInput = document.getElementById("noticeTitle");
            const title = titleInput ? titleInput.value : "Notice";
            if (adminMessage) {
                adminMessage.style.display = "block";
                adminMessage.textContent = `Notice "${title}" added successfully!`;
            }
            adminNoticeForm.reset();
            if (noticeFormBox) noticeFormBox.style.display = "none";
        });
    }

    // (I) Profile Edit Toggle & Form
    const editProfileBtn = document.getElementById("editProfileButton");
    const profileEditBox = document.getElementById("profileEditBox");
    const showProfileBtn = document.getElementById("showProfileButton");
    const profileMessage = document.getElementById("profileMessage");
    const profileForm = document.getElementById("profileForm");

    if (editProfileBtn && profileEditBox) {
        editProfileBtn.addEventListener("click", function () {
            const isHidden = window.getComputedStyle(profileEditBox).display === "none";
            profileEditBox.style.display = isHidden ? "block" : "none";
        });
    }

    if (showProfileBtn && profileMessage) {
        showProfileBtn.addEventListener("click", function () {
            profileMessage.style.display = "block";
            profileMessage.textContent = "Student profile is verified and active for Academic Year 2026.";
        });
    }

    if (profileForm) {
        profileForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const editNameInput = document.getElementById("editName");
            const newName = editNameInput ? editNameInput.value : "";
            if (profileMessage) {
                profileMessage.style.display = "block";
                profileMessage.textContent = newName
                    ? `Profile updated for ${newName}!`
                    : "Profile changes saved successfully!";
            }
            if (profileEditBox) profileEditBox.style.display = "none";
        });
    }

});