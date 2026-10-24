// ==========================================
// render.js - DOM Rendering Module
// Uses Array map() to dynamically build HTML
// ==========================================

// Display campus events cards
function renderEvents(eventsList, container) {
    if (!eventsList || eventsList.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">No events found matching your search.</p>
            </div>
        `;
        return;
    }

    // Using Array map() to create HTML for each event
    const htmlCards = eventsList.map(event => `
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm border-0 event-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary">${event.category}</span>
                        <span class="badge ${event.status === 'Open' ? 'bg-success' : 'bg-warning text-dark'}">${event.status}</span>
                    </div>
                    
                    <h5 class="card-title fw-bold text-dark mb-2">${event.title}</h5>
                    <p class="card-text text-muted small flex-grow-1">${event.description}</p>
                    
                    <div class="event-meta bg-light p-2 rounded small mt-2 mb-3">
                        <div>📅 <strong>Date:</strong> ${event.date}</div>
                        <div>🕒 <strong>Time:</strong> ${event.time}</div>
                        <div>📍 <strong>Venue:</strong> ${event.venue}</div>
                        <div>🎟️ <strong>Fee:</strong> <span class="badge bg-secondary">${event.fee}</span></div>
                    </div>

                    <button class="btn btn-outline-primary btn-sm w-100 mt-auto" onclick="openEventModal(${event.id})">
                        View Details & Register
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    container.innerHTML = htmlCards;
}

// Display students directory cards
function renderStudents(studentsList, container) {
    if (!studentsList || studentsList.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">No students found matching your search.</p>
            </div>
        `;
        return;
    }

    // Using Array map() to create student profile cards
    const htmlCards = studentsList.map(student => `
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm border-0 student-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary">${student.enrollment}</span>
                        <span class="badge bg-success">CGPA: ${student.cgpa}</span>
                    </div>

                    <h5 class="card-title fw-bold text-dark mb-1">${student.name}</h5>
                    <p class="text-muted small mb-2">${student.department} (${student.semester})</p>

                    <div class="small text-muted mb-3">
                        <div>📧 ${student.email}</div>
                        <div>📍 ${student.city}, ${student.state}</div>
                    </div>

                    <div class="mt-auto">
                        <div class="small fw-bold text-muted mb-1">Skills:</div>
                        <div>
                            ${student.skills.map(skill => `<span class="badge bg-light text-dark border me-1 mb-1">${skill}</span>`).join('')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    container.innerHTML = htmlCards;
}

// Display FAQs using Bootstrap Accordion
function renderFaqs(faqsList, container) {
    if (!faqsList || faqsList.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">No FAQs found matching your search.</p>
            </div>
        `;
        return;
    }

    // Using Array map() to create accordion items
    const accordionItems = faqsList.map((faq, index) => `
        <div class="accordion-item mb-2 border rounded">
            <h2 class="accordion-header" id="faqHeader${faq.id}">
                <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse${faq.id}" aria-expanded="${index === 0 ? 'true' : 'false'}">
                    <strong>${faq.question}</strong>
                    <span class="badge bg-info text-dark ms-2">${faq.category}</span>
                </button>
            </h2>
            <div id="faqCollapse${faq.id}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <p class="mb-2">${faq.answer}</p>
                    <small class="text-muted">Tags: ${faq.tags ? faq.tags.join(', ') : ''}</small>
                </div>
            </div>
        </div>
    `).join('');

    container.innerHTML = `
        <div class="col-12">
            <div class="accordion" id="faqAccordion">
                ${accordionItems}
            </div>
        </div>
    `;
}

// Render pagination links
function renderPagination(totalPages, currentPage, container) {
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let itemsHtml = '';

    // Previous Page Button
    itemsHtml += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button class="page-link" onclick="goToPage(${currentPage - 1})">Previous</button>
        </li>
    `;

    // Numeric Page Buttons
    for (let i = 1; i <= totalPages; i++) {
        itemsHtml += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <button class="page-link" onclick="goToPage(${i})">${i}</button>
            </li>
        `;
    }

    // Next Page Button
    itemsHtml += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <button class="page-link" onclick="goToPage(${currentPage + 1})">Next</button>
        </li>
    `;

    container.innerHTML = itemsHtml;
}
