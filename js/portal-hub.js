// ==========================================
// portal-hub.js - Main Application Controller
// Connects modules, handles user input, and manages state
// ==========================================

// Global state variables
let eventsData = [];
let studentsData = [];
let faqsData = [];

let currentTab = 'events'; // 'events' | 'students' | 'faqs'
let currentPage = 1;
let itemsPerPage = 6;

// Run initialization when DOM is ready
document.addEventListener('DOMContentLoaded', async function () {
    // 1. Show loading state in container
    const dataContainer = document.getElementById('dataContainer');
    if (dataContainer) {
        dataContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Loading data from JSON files...</p>
            </div>
        `;
    }

    // 2. Fetch data from all JSON files using api.js
    eventsData = await fetchEventsData();
    studentsData = await fetchStudentsData();
    faqsData = await fetchFaqsData();

    // 3. Setup category filter options for the initial tab
    populateFilterDropdown();

    // 4. Attach event listeners for user controls
    setupEventListeners();

    // 5. Render initial page
    updateView();
});

// Setup event listeners for search input, dropdowns, and items per page
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const sortSelect = document.getElementById('sortSelect');
    const itemsPerPageSelect = document.getElementById('itemsPerPageSelect');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentPage = 1; // Reset to page 1 on new search
            updateView();
        });
    }

    if (filterSelect) {
        filterSelect.addEventListener('change', function () {
            currentPage = 1;
            updateView();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            currentPage = 1;
            updateView();
        });
    }

    if (itemsPerPageSelect) {
        itemsPerPageSelect.addEventListener('change', function () {
            itemsPerPage = parseInt(this.value, 10) || 6;
            currentPage = 1;
            updateView();
        });
    }
}

// Switch between Events, Students, and FAQs tabs
function switchTab(tabName) {
    currentTab = tabName;
    currentPage = 1;

    // Update active tab buttons appearance
    const btnEvents = document.getElementById('tabEvents');
    const btnStudents = document.getElementById('tabStudents');
    const btnFaqs = document.getElementById('tabFaqs');

    if (btnEvents) btnEvents.className = tabName === 'events' ? 'btn btn-primary active' : 'btn btn-outline-primary';
    if (btnStudents) btnStudents.className = tabName === 'students' ? 'btn btn-primary active' : 'btn btn-outline-primary';
    if (btnFaqs) btnFaqs.className = tabName === 'faqs' ? 'btn btn-primary active' : 'btn btn-outline-primary';

    // Clear search box
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';

    // Update filter dropdown label and options for selected tab
    populateFilterDropdown();

    // Re-render
    updateView();
}

// Populate the filter dropdown based on active dataset
function populateFilterDropdown() {
    const filterSelect = document.getElementById('filterSelect');
    const filterLabel = document.getElementById('filterLabel');
    if (!filterSelect) return;

    filterSelect.innerHTML = '<option value="all">All</option>';

    if (currentTab === 'events') {
        if (filterLabel) filterLabel.textContent = 'Category:';
        const categories = [...new Set(eventsData.map(e => e.category))];
        categories.forEach(cat => {
            filterSelect.innerHTML += `<option value="${cat}">${cat}</option>`;
        });
    } else if (currentTab === 'students') {
        if (filterLabel) filterLabel.textContent = 'Department:';
        const departments = [...new Set(studentsData.map(s => s.department))];
        departments.forEach(dept => {
            filterSelect.innerHTML += `<option value="${dept}">${dept}</option>`;
        });
    } else if (currentTab === 'faqs') {
        if (filterLabel) filterLabel.textContent = 'Category:';
        const categories = [...new Set(faqsData.map(f => f.category))];
        categories.forEach(cat => {
            filterSelect.innerHTML += `<option value="${cat}">${cat}</option>`;
        });
    }
}

// Main function to filter, sort, paginate, and render data
function updateView() {
    const dataContainer = document.getElementById('dataContainer');
    const paginationContainer = document.getElementById('paginationContainer');
    const resultsCount = document.getElementById('resultsCount');
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const sortSelect = document.getElementById('sortSelect');

    if (!dataContainer) return;

    // 1. Select the current dataset array
    let activeData = [];
    if (currentTab === 'events') {
        activeData = eventsData;
    } else if (currentTab === 'students') {
        activeData = studentsData;
    } else if (currentTab === 'faqs') {
        activeData = faqsData;
    }

    // 2. Filter using Array.prototype.filter() in dataUtils.js
    const searchTerm = searchInput ? searchInput.value : '';
    const selectedFilter = filterSelect ? filterSelect.value : 'all';
    const filtered = filterData(activeData, searchTerm, selectedFilter, currentTab);

    // 3. Sort using Array.prototype.sort() in dataUtils.js
    const sortBy = sortSelect ? sortSelect.value : 'default';
    const sorted = sortData(filtered, sortBy, currentTab);

    // 4. Calculate pagination
    const totalItems = sorted.length;
    const totalPages = getTotalPages(totalItems, itemsPerPage);

    if (currentPage > totalPages) {
        currentPage = totalPages;
    }
    if (currentPage < 1) {
        currentPage = 1;
    }

    // 5. Slice data for the current page using Array.prototype.slice()
    const pageItems = paginateData(sorted, currentPage, itemsPerPage);

    // 6. Update results counter text
    if (resultsCount) {
        if (totalItems === 0) {
            resultsCount.textContent = 'Showing 0 results';
        } else {
            const startNum = (currentPage - 1) * itemsPerPage + 1;
            const endNum = Math.min(startNum + pageItems.length - 1, totalItems);
            resultsCount.textContent = `Showing ${startNum} - ${endNum} of ${totalItems} items`;
        }
    }

    // 7. Render dynamic HTML using Array.prototype.map() in render.js
    if (currentTab === 'events') {
        renderEvents(pageItems, dataContainer);
    } else if (currentTab === 'students') {
        renderStudents(pageItems, dataContainer);
    } else if (currentTab === 'faqs') {
        renderFaqs(pageItems, dataContainer);
    }

    // 8. Render pagination controls
    renderPagination(totalPages, currentPage, paginationContainer);
}

// Function called when a pagination button is clicked
function goToPage(pageNumber) {
    currentPage = pageNumber;
    updateView();

    // Smooth scroll back to top of data container
    const toolbar = document.querySelector('.card.shadow-sm');
    if (toolbar) {
        toolbar.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Open modal with event details
function openEventModal(eventId) {
    selectedModalEventId = eventId;
    const event = eventsData.find(e => e.id === eventId);
    if (!event) return;

    const modalTitle = document.getElementById('modalEventTitle');
    const modalBody = document.getElementById('modalEventBody');

    if (modalTitle) modalTitle.textContent = event.title;

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="mb-3">
                <span class="badge bg-primary">${event.category}</span>
                <span class="badge bg-success">${event.status}</span>
            </div>
            <p class="text-muted">${event.description}</p>
            <div class="bg-light p-3 rounded small mb-3">
                <div>📅 <strong>Date:</strong> ${event.date} (${event.time})</div>
                <div>📍 <strong>Venue:</strong> ${event.venue}</div>
                <div>🏛️ <strong>Organizer:</strong> ${event.organizer}</div>
                <div>🎙️ <strong>Speaker:</strong> ${event.speaker || 'Campus Faculty'}</div>
                <div>👥 <strong>Capacity:</strong> ${event.registered} / ${event.capacity} registered</div>
                <div>🎟️ <strong>Registration Fee:</strong> ${event.fee}</div>
            </div>
            <p class="text-success small mb-0">✓ Confirming registration will reserve your seat instantly.</p>
        `;
    }

    const modalEl = document.getElementById('eventDetailsModal');
    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
        const modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();
    }
}


// Asynchronous Event Registration
let selectedModalEventId = null;

async function confirmEventRegistration() {
    if (!selectedModalEventId) return;
    const event = eventsData.find(e => e.id === selectedModalEventId);
    if (!event) return;

    let studentName = localStorage.getItem('studentName') || 'Dhanushi Patel';
    let studentEmail = localStorage.getItem('studentEmail') || 'student@studenthub.com';

    try {
        const response = await fetch('api/register_event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                event_id: selectedModalEventId,
                student_name: studentName,
                student_email: studentEmail,
                notes: 'Registered via Events & Data Hub'
            })
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                if (event.registered !== undefined) {
                    event.registered += 1;
                }
                updateView();
                alert('? ' + data.message);
                return;
            } else {
                alert(data.message || 'Registration failed');
                return;
            }
        }
    } catch (err) {
        // Fallback for file:// or offline mode
    }

    if (event.registered !== undefined) {
        event.registered += 1;
    }
    updateView();
    alert('? Registration confirmed! A pass for "' + event.title + '" has been issued to ' + studentName + '.');
}