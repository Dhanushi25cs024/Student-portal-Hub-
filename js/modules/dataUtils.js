// ==========================================
// dataUtils.js - Array Helper Functions
// Uses Array methods: filter, sort, slice
// ==========================================

// Filter items based on search keyword and selected category/department
function filterData(items, searchTerm, selectedFilter, currentTab) {
    let filtered = items;

    // 1. Filter by category or department
    if (selectedFilter && selectedFilter !== 'all') {
        filtered = filtered.filter(item => {
            if (currentTab === 'events' || currentTab === 'faqs') {
                return item.category === selectedFilter;
            } else if (currentTab === 'students') {
                return item.department === selectedFilter;
            }
            return true;
        });
    }

    // 2. Filter by search query
    if (searchTerm && searchTerm.trim() !== '') {
        const query = searchTerm.toLowerCase().trim();

        filtered = filtered.filter(item => {
            if (currentTab === 'events') {
                return (
                    item.title.toLowerCase().includes(query) ||
                    item.venue.toLowerCase().includes(query) ||
                    item.description.toLowerCase().includes(query)
                );
            } else if (currentTab === 'students') {
                return (
                    item.name.toLowerCase().includes(query) ||
                    item.enrollment.toLowerCase().includes(query) ||
                    item.department.toLowerCase().includes(query) ||
                    (item.skills && item.skills.some(skill => skill.toLowerCase().includes(query)))
                );
            } else if (currentTab === 'faqs') {
                return (
                    item.question.toLowerCase().includes(query) ||
                    item.answer.toLowerCase().includes(query)
                );
            }
            return true;
        });
    }

    return filtered;
}

// Sort items array based on chosen sorting option
function sortData(items, sortBy, currentTab) {
    // Clone array to avoid mutating original
    const result = [...items];

    if (sortBy === 'title-asc') {
        result.sort((a, b) => {
            const nameA = a.title || a.name || a.question || '';
            const nameB = b.title || b.name || b.question || '';
            return nameA.localeCompare(nameB);
        });
    } else if (sortBy === 'title-desc') {
        result.sort((a, b) => {
            const nameA = a.title || a.name || a.question || '';
            const nameB = b.title || b.name || b.question || '';
            return nameB.localeCompare(nameA);
        });
    } else if (sortBy === 'date-asc') {
        result.sort((a, b) => {
            const dateA = new Date(a.date || a.timestamp || 0);
            const dateB = new Date(b.date || b.timestamp || 0);
            return dateA - dateB;
        });
    } else if (sortBy === 'capacity-desc') {
        result.sort((a, b) => {
            const valA = a.capacity || a.cgpa || a.helpful || 0;
            const valB = b.capacity || b.cgpa || b.helpful || 0;
            return valB - valA;
        });
    }

    return result;
}

// Slice array for pagination
function paginateData(items, currentPage, perPage) {
    const startIndex = (currentPage - 1) * perPage;
    const endIndex = startIndex + perPage;
    return items.slice(startIndex, endIndex);
}

// Calculate total number of pages
function getTotalPages(totalItems, perPage) {
    return Math.ceil(totalItems / perPage) || 1;
}
