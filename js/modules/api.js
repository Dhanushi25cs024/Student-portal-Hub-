// ==========================================
// api.js - Fetch API Module
// Loads data from JSON files asynchronously
// ==========================================

// Fetch events from data/events.json
async function fetchEventsData() {
    try {
        const response = await fetch('data/events.json');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.warn('Could not fetch events from server, using fallback data:', error);
        if (window.StudentHubFallbackData && window.StudentHubFallbackData.events) {
            return window.StudentHubFallbackData.events;
        }
        return [];
    }
}

// Fetch students from data/students.json
async function fetchStudentsData() {
    try {
        const response = await fetch('data/students.json');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.warn('Could not fetch students from server, using fallback data:', error);
        if (window.StudentHubFallbackData && window.StudentHubFallbackData.students) {
            return window.StudentHubFallbackData.students;
        }
        return [];
    }
}

// Fetch FAQs from data/faqs.json
async function fetchFaqsData() {
    try {
        const response = await fetch('data/faqs.json');
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.warn('Could not fetch FAQs from server, using fallback data:', error);
        if (window.StudentHubFallbackData && window.StudentHubFallbackData.faqs) {
            return window.StudentHubFallbackData.faqs;
        }
        return [];
    }
}
