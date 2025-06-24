/**
 * main.js
 * 
 * Daily Activity Briefing System (DABS) - Core Application Script
 * 
 * This file handles the core functionality of the DABS dashboard including:
 * - Dashboard initialization and data loading
 * - Activity management (adding, editing, deleting activities)
 * - Edit mode toggling for content management
 * - UI interactions and event handling
 * - Email and print functionality
 * - Dynamic content updates via AJAX
 * 
 * The script formats all dates and times in UK format (DD/MM/YYYY HH:MM:SS)
 * using the Europe/London timezone as per company standards.
 * 
 * Current Date and Time (UK Format): 29/05/2025 14:47:55
 * Current User's Login: irlamkeep
 * 
 * @author irlamkeep
 * @version 1.1
 * @date 29/05/2025
 * @website dabs.defecttracker.uk
 */

// Use strict mode to prevent common coding errors
'use strict';

// Initialize application when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DABS Dashboard initializing...');
    initializeApplication();
});

/**
 * Display loading indicator in specified container
 * Shows a spinner with optional loading message
 * 
 * @param {string|HTMLElement} container - Container element or ID to show loader in
 * @param {string} message - Optional message to display with spinner
 */
function showLoadingIndicator(container, message = 'Loading...') {
    // If container is a string ID, get the actual element
    if (typeof container === 'string') {
        container = document.getElementById(container);
    }
    
    // Only proceed if we have a valid container
    if (container) {
        container.innerHTML = `
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">${message}</span>
            </div>
        `;
    }
}

/**
 * Hide loading indicator and optionally show new content
 * 
 * @param {string|HTMLElement} container - Container with the loading indicator
 * @param {string} content - Optional content to display after hiding loader
 */
function hideLoadingIndicator(container, content = null) {
    // If container is a string ID, get the actual element
    if (typeof container === 'string') {
        container = document.getElementById(container);
    }
    
    // Only proceed if we have a valid container
    if (container) {
        if (content !== null) {
            container.innerHTML = content;
        } else {
            // Just remove loading spinner elements if no content provided
            const spinner = container.querySelector('.spinner-border');
            if (spinner) {
                spinner.parentElement.remove();
            }
        }
    }
}

/**
 * Initialize the application
 * Sets up event listeners and loads initial data
 */
function initializeApplication() {
    // Set UK timezone for consistent date formatting
    try {
        // Check if moment.js is available (optional enhancement)
        if (typeof moment !== 'undefined') {
            moment.tz.setDefault('Europe/London');
        }
    } catch (e) {
        console.log('Moment.js not available, using native date functions');
    }
    
    // Set up event handlers for interactive elements
    setupEventHandlers();
    
    // Load initial briefing data from server
    loadBriefingData();
    
    // Initialize dashboard clock with UK time
    initializeClock();
    
    console.log('DABS Dashboard initialized successfully at ' + formatUKDateTime(new Date()));
}

/**
 * Set up event handlers for dashboard controls
 * Attaches listeners to buttons and interactive elements
 */
function setupEventHandlers() {
    // Edit mode toggle button
    const editModeBtn = document.getElementById('editModeBtn');
    if (editModeBtn) {
        editModeBtn.addEventListener('click', toggleEditMode);
    }
    
    // Email button
    const emailBtn = document.getElementById('emailBtn');
    if (emailBtn) {
        emailBtn.addEventListener('click', function() {
            const emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
            emailModal.show();
        });
    }
    
    // Print button
    const printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Add activity button
    const addActivityBtn = document.getElementById('addActivityBtn');
    if (addActivityBtn) {
        addActivityBtn.addEventListener('click', function() {
            const activityModal = new bootstrap.Modal(document.getElementById('activityEditModal'));
            // Reset form fields for new activity
            document.getElementById('activityEditForm').reset();
            activityModal.show();
        });
    }
    
    // Save activity button
    const saveActivityBtn = document.getElementById('saveActivityBtn');
    if (saveActivityBtn) {
        saveActivityBtn.addEventListener('click', saveActivity);
    }
    
    // Add subcontractor button
    const addSubcontractorBtn = document.getElementById('addSubcontractorBtn');
    if (addSubcontractorBtn) {
        addSubcontractorBtn.addEventListener('click', function() {
            alert('Add subcontractor feature will be available in the next update.');
        });
    }
}

/**
 * Initialize clock display with current UK time
 * Updates the date display element with current time
 */
function initializeClock() {
    // Get the date display element
    const dateDisplay = document.getElementById('currentDate');
    if (!dateDisplay) return;
    
    // Update time immediately
    updateClockDisplay(dateDisplay);
    
    // Then update every minute
    setInterval(function() {
        updateClockDisplay(dateDisplay);
    }, 60000); // Update every minute (60000ms)
}

/**
 * Update clock display with current UK time
 * 
 * @param {HTMLElement} element - The element to update with current time
 */
function updateClockDisplay(element) {
    if (!element) return;
    
    // Get current date and format in UK style
    const now = new Date();
    element.textContent = formatUKDate(now);
}

/**
 * Format date in UK format (DD/MM/YYYY)
 * 
 * @param {Date} date - Date to format
 * @returns {string} Formatted date string
 */
function formatUKDate(date) {
    // Format as DD/MM/YYYY
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}/${month}/${year}`;
}

/**
 * Format date and time in full UK format (DD/MM/YYYY HH:MM:SS)
 * 
 * @param {Date} date - Date to format
 * @returns {string} Formatted date and time string
 */
function formatUKDateTime(date) {
    // Format as DD/MM/YYYY HH:MM:SS
    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    }).format(date);
}

/**
 * Load briefing data from server
 * Gets the latest briefing information via AJAX
 */
function loadBriefingData() {
    // Show loading indicator while fetching data
    showLoadingIndicator('weatherWidget', 'Updating weather data...');
    
    // For now, we'll just simulate data loading
    // In production, this would be an AJAX call to the server
    
    // Simulating network delay with timeout
    setTimeout(function() {
        hideLoadingIndicator('weatherWidget');
        console.log('Briefing data loaded at ' + formatUKDateTime(new Date()));
    }, 1500);
}

/**
 * Toggle edit mode on/off
 * Enables/disables editing of dashboard content
 */
function toggleEditMode() {
    const editModeBtn = document.getElementById('editModeBtn');
    const isEditMode = document.body.classList.toggle('edit-mode');
    
    if (isEditMode) {
        // Switching to edit mode
        if (editModeBtn) {
            editModeBtn.innerHTML = '<i class="fas fa-save"></i> Save';
            editModeBtn.classList.replace('btn-primary', 'btn-success');
        }
        
        // Make editable elements visibly editable
        const editableElements = document.querySelectorAll('.editable');
        editableElements.forEach(el => {
            el.classList.add('editing');
        });
        
        console.log('Edit mode enabled at ' + formatUKDateTime(new Date()));
    } else {
        // Switching back to view mode
        if (editModeBtn) {
            editModeBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
            editModeBtn.classList.replace('btn-success', 'btn-primary');
        }
        
        // Save changes and remove editing visual cues
        saveChanges();
        
        const editableElements = document.querySelectorAll('.editable');
        editableElements.forEach(el => {
            el.classList.remove('editing');
        });
        
        console.log('Edit mode disabled at ' + formatUKDateTime(new Date()));
    }
}

/**
 * Save changes made in edit mode
 * Collects and sends updated content to server
 */
function saveChanges() {
    // Show saving notification
    showNotification('Saving changes...', 'info');
    
    // In a real implementation, we would collect edited content
    // and send it to the server via AJAX
    
    // Simulate successful save
    setTimeout(function() {
        showNotification('All changes saved successfully!', 'success');
    }, 1000);
}

/**
 * Save activity from modal form
 * Collects form data and sends to server
 */
function saveActivity() {
    // Get form values
    const form = document.getElementById('activityEditForm');
    if (!form) return;
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Show saving notification
    showNotification('Saving activity...', 'info');
    
    // In real implementation, send data to server
    // For now, just simulate success
    setTimeout(function() {
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('activityEditModal'));
        if (modal) modal.hide();
        
        // Show success message
        showNotification('Activity saved successfully!', 'success');
        
        // Reload data to show changes
        loadBriefingData();
    }, 1000);
}

/**
 * Show notification message
 * Displays a temporary notification that fades out
 * 
 * @param {string} message - Message to display
 * @param {string} type - Bootstrap alert type (success, info, warning, danger)
 * @param {number} duration - How long to show in milliseconds
 */
function showNotification(message, type = 'success', duration = 3000) {
    // Create notification container if it doesn't exist
    let notificationContainer = document.getElementById('notificationContainer');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notificationContainer';
        notificationContainer.style.position = 'fixed';
        notificationContainer.style.top = '20px';
        notificationContainer.style.right = '20px';
        notificationContainer.style.zIndex = '9999';
        document.body.appendChild(notificationContainer);
    }
    
    // Create alert element
    const alertElement = document.createElement('div');
    alertElement.className = `alert alert-${type} alert-dismissible fade show`;
    alertElement.role = 'alert';
    alertElement.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to container
    notificationContainer.appendChild(alertElement);
    
    // Auto-remove after duration
    setTimeout(function() {
        alertElement.classList.remove('show');
        setTimeout(function() {
            alertElement.remove();
        }, 300); // Wait for fade animation
    }, duration);
}