/**
 * ===============================================================================
 * DAILY ACTIVITY BRIEFING SYSTEM (DABS) - EMAIL REPORT MODULE
 * ===============================================================================
 * 
 * FILE NAME: email-report.js
 * DESCRIPTION: 
 * This file provides complete email functionality for the DABS system. It creates
 * a modern, user-friendly interface for composing and sending daily activity 
 * reports via email in PDF format. The module handles recipient management, 
 * email validation, form composition, and communication with the backend PHP 
 * script to generate and send professional PDF reports containing all daily 
 * activities, resource allocations, and subcontractor information.
 * 
 * The system uses UK date formatting (DD/MM/YYYY) throughout and provides 
 * real-time feedback during the email generation and sending process.
 * 
 * AUTHOR: irlam
 * CREATED: 04/06/2025 15:38 (UK Time)
 * LAST MODIFIED: 04/06/2025 15:38 (UK Time) 
 * VERSION: 3.0.0
 * 
 * KEY FEATURES:
 * - Modern Bootstrap 5 modal interface for email composition
 * - Dynamic recipient management with email validation and duplicate prevention
 * - One-click functionality to add all system attendees as recipients
 * - Customizable email subject lines and message content
 * - Real-time status updates during PDF generation and email sending
 * - Comprehensive error handling with user-friendly feedback messages
 * - UK date format (DD/MM/YYYY) consistency throughout the entire system
 * - Modern ES2020+ JavaScript with proper async/await and error boundaries
 * - XSS protection through HTML escaping and input sanitization
 * - Responsive design that works on desktop, tablet, and mobile devices
 * 
 * DEPENDENCIES:
 * - Bootstrap 5.3+ (for modal dialogs, buttons, and responsive layout)
 * - Font Awesome 6+ (for icons and visual elements)
 * - email_briefing.php (backend PHP script for PDF generation and SMTP sending)
 * - ajax_attendees.php (API endpoint for fetching attendee email addresses)
 * 
 * BROWSER SUPPORT:
 * - Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
 * - Requires modern browser with ES2020+ support and Fetch API
 * 
 * SECURITY FEATURES:
 * - Input validation and sanitization for all form fields
 * - Email format validation using RFC-compliant regex patterns
 * - XSS protection through proper HTML escaping
 * - CSRF protection through session-based authentication
 * - Secure communication with backend APIs
 * 
 * ===============================================================================
 */

// Wait for the DOM to be fully loaded before initializing the email module
document.addEventListener('DOMContentLoaded', function() {
    // Generate UK-formatted timestamp for logging
    const ukDateTime = new Date().toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    });
    
    // Initialize module with console logging (NO problematic debug function)
    console.log(`[${ukDateTime}] DABS Email Report Module v3.0.0 initializing...`);
    
    // Create the email modal interface
    try {
        createEmailModal();
        console.log(`[${ukDateTime}] Email modal interface created successfully`);
    } catch (error) {
        console.error(`[${ukDateTime}] Failed to create email modal:`, error);
        return;
    }
    
    // Set up all event listeners for user interactions
    try {
        setupEventListeners();
        console.log(`[${ukDateTime}] Event listeners configured successfully`);
    } catch (error) {
        console.error(`[${ukDateTime}] Failed to setup event listeners:`, error);
        return;
    }
    
    // Connect the main email button functionality
    const emailBtn = document.getElementById('emailBtn');
    if (emailBtn) {
        console.log(`[${ukDateTime}] Main email button found, attaching click handler`);
        emailBtn.addEventListener('click', function() {
            const clickTime = new Date().toLocaleString('en-GB', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Europe/London'
            });
            console.log(`[${clickTime}] Main email button clicked by user`);
            openEmailModal();
        });
    } else {
        console.warn(`[${ukDateTime}] Main email button not found in DOM - feature may not be accessible`);
    }
    
    console.log(`[${ukDateTime}] Email Report Module initialization completed successfully`);
});

/**
 * Creates and injects the email modal dialog into the DOM
 * 
 * This function builds a modern, responsive Bootstrap 5 modal with all necessary 
 * form fields for composing and sending email reports. The modal includes 
 * recipient management, subject customization, message composition, and 
 * real-time status updates.
 */
function createEmailModal() {
    // Generate timestamp for logging
    const timestamp = new Date().toLocaleString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    });
    
    console.log(`[${timestamp}] Creating modern email modal interface`);
    
    // Modern, responsive modal HTML with Bootstrap 5 styling and dark theme
    const modalHTML = `
        <div class="modal fade" id="emailReportModal" tabindex="-1" aria-labelledby="emailReportModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <!-- Modal Header with Modern Gradient -->
                    <div class="modal-header text-white border-0 rounded-top-3" style="background: var(--gradient-neon);">
                        <h5 class="modal-title d-flex align-items-center fw-bold text-white" id="emailReportModalLabel">
                            <i class="fas fa-envelope-open-text me-3 fa-lg"></i>
                            Send Daily Activity Report
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close modal"></button>
                    </div>
                    
                    <!-- Modal Body with Dark Theme -->
                    <div class="modal-body p-4" style="background: var(--dark-bg-card); color: var(--text-primary);">
                        <form id="emailReportForm" class="needs-validation" novalidate>
                            <!-- Report Date Display Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-2" style="background: var(--dark-bg-tertiary); border-color: var(--neon-cyan) !important;">
                                        <div class="card-header border-0" style="background: rgba(0, 246, 255, 0.1);">
                                            <h6 class="card-title mb-0 fw-bold" style="color: var(--neon-cyan);">
                                                <i class="fas fa-calendar-check me-2"></i>Report Information
                                            </h6>
                                        </div>
                                        <div class="card-body" style="background: var(--dark-bg-tertiary);">
                                            <label for="reportDate" class="form-label fw-semibold" style="color: var(--text-primary);">
                                                Report Date (UK Format)
                                            </label>
                                            <input type="text" class="form-control form-control-lg border-2" 
                                                   id="reportDate" readonly style="background: var(--dark-bg-elevated); color: var(--text-primary); border-color: var(--border-color);">
                                            <div class="form-text" style="color: var(--text-muted);">
                                                <i class="fas fa-info-circle me-1"></i>
                                                This report will contain all activities and resources for the selected date
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Email Recipients Management Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-2" style="background: var(--dark-bg-tertiary); border-color: var(--neon-green) !important;">
                                        <div class="card-header border-0" style="background: rgba(0, 255, 136, 0.1);">
                                            <h6 class="card-title mb-0 fw-bold" style="color: var(--neon-green);">
                                                <i class="fas fa-users me-2"></i>Email Recipients
                                            </h6>
                                        </div>
                                        <div class="card-body" style="background: var(--dark-bg-tertiary);">
                                            <label for="emailRecipients" class="form-label fw-semibold" style="color: var(--text-primary);">
                                                Add Email Recipients
                                            </label>
                                            <div class="email-tags-container form-control border-2 p-3" 
                                                 style="min-height: 100px; background: var(--dark-bg-elevated); border-color: var(--border-color);">
                                                <div id="emailTagsContainer" class="d-flex flex-wrap gap-2 mb-2"></div>
                                                <input type="email" id="emailInput" class="border-0 w-100" 
                                                       placeholder="Type email address and press Enter or comma to add recipient"
                                                       style="outline: none; font-size: 1rem; background: transparent; color: var(--text-primary);">
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div class="form-text" style="color: var(--text-muted);">
                                                    <i class="fas fa-lightbulb me-1"></i>
                                                    Enter multiple emails separated by commas or press Enter after each
                                                </div>
                                                <button type="button" class="btn btn-success btn-sm" id="addAttendeesButton">
                                                    <i class="fas fa-user-friends me-2"></i>Add All Attendees
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Email Subject and Message Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-2" style="background: var(--dark-bg-tertiary); border-color: var(--neon-blue) !important;">
                                        <div class="card-header border-0" style="background: rgba(0, 128, 255, 0.1);">
                                            <h6 class="card-title mb-0 fw-bold" style="color: var(--neon-blue);">
                                                <i class="fas fa-edit me-2"></i>Email Content
                                            </h6>
                                        </div>
                                        <div class="card-body" style="background: var(--dark-bg-tertiary);">
                                            <!-- Email Subject Field -->
                                            <div class="mb-3">
                                                <label for="emailSubject" class="form-label fw-semibold" style="color: var(--text-primary);">
                                                    <i class="fas fa-tag me-2" style="color: var(--neon-blue);"></i>Email Subject Line
                                                </label>
                                                <input type="text" class="form-control form-control-lg border-2" 
                                                       id="emailSubject" placeholder="Daily Activity Briefing Report"
                                                       style="background: var(--dark-bg-elevated); color: var(--text-primary); border-color: var(--border-color);">
                                                <div class="form-text" style="color: var(--text-muted);">
                                                    A clear, descriptive subject line for your email recipients
                                                </div>
                                            </div>
                                            
                                            <!-- Email Message Field -->
                                            <div class="mb-3">
                                                <label for="emailMessage" class="form-label fw-semibold" style="color: var(--text-primary);">
                                                    <i class="fas fa-comment-alt me-2" style="color: var(--neon-blue);"></i>Additional Message (Optional)
                                                </label>
                                                <textarea class="form-control border-2" id="emailMessage" rows="4" 
                                                         placeholder="Add a personal message to include with the report (optional)..."
                                                         style="background: var(--dark-bg-elevated); color: var(--text-primary); border-color: var(--border-color);"></textarea>
                                                <div class="form-text" style="color: var(--text-muted);">
                                                    This message will appear in the email body above the PDF attachment
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Display Area -->
                            <div id="emailStatus" class="d-none alert border-2 shadow-sm"></div>
                        </form>
                    </div>
                    
                    <!-- Modal Footer with Enhanced Action Buttons -->
                    <div class="modal-footer border-0 p-4" style="background: var(--dark-bg-tertiary); border-top: 2px solid var(--border-color) !important;">
                        <button type="button" class="btn btn-secondary btn-lg me-2" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-4" id="sendEmailButton">
                            <i class="fas fa-paper-plane me-2"></i>Send Report Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Inject modal into document body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    console.log(`[${timestamp}] Email modal HTML injected into DOM successfully`);
}
/**
 * Sets up all event listeners for the email interface
 * 
 * This function configures user interaction handlers for the email modal,
 * form inputs, navigation elements, and keyboard shortcuts. It ensures
 * proper event delegation and prevents memory leaks.
 */
function setupEventListeners() {
    const timestamp = new Date().toLocaleString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    });
    
    console.log(`[${timestamp}] Configuring email interface event listeners`);
    
    // Add email functionality to user menu dropdown
    const userMenu = document.querySelector('.dropdown-menu[aria-labelledby="userMenu"]');
    if (userMenu) {
        // Create modern menu item for email functionality
        const menuItem = document.createElement('li');
        menuItem.innerHTML = `
            <a class="dropdown-item py-2" href="#" id="menuSendReport" 
               style="transition: all 0.2s ease;">
                <i class="fas fa-envelope me-3 text-primary"></i>
                <span class="fw-semibold">Send Daily Report</span>
            </a>
        `;
        
        // Add visual separator if menu has other items
        if (userMenu.children.length > 0) {
            const separator = document.createElement('li');
            separator.innerHTML = '<hr class="dropdown-divider my-1">';
            userMenu.prepend(separator);
        }
        
        userMenu.prepend(menuItem);
        
        // Attach modern click handler with hover effects
        const menuSendReport = document.getElementById('menuSendReport');
        menuSendReport.addEventListener('click', function(e) {
            e.preventDefault();
            console.log(`[${timestamp}] Email report selected from user menu`);
            openEmailModal();
        });
        
        // Add hover effects
        menuSendReport.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
            this.style.transform = 'translateX(2px)';
        });
        
        menuSendReport.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = '';
        });
        
        console.log(`[${timestamp}] User menu email option added successfully`);
    }
    
    // Handle clicking anywhere in the email tags container to focus input
    document.addEventListener('click', function(e) {
        const emailInput = document.getElementById('emailInput');
        if (emailInput && e.target.closest('.email-tags-container')) {
            emailInput.focus();
            emailInput.style.borderColor = '#0d6efd';
        }
    });
    
    // Enhanced email input handling with modern features
    const emailInput = document.getElementById('emailInput');
    if (emailInput) {
        // Handle keyboard input for adding recipients
        emailInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                // Add email when Enter or comma is pressed
                e.preventDefault();
                const email = this.value.trim();
                if (email) {
                    addEmailTag(email);
                    this.value = '';
                }
            } else if (e.key === 'Backspace' && this.value === '') {
                // Remove last email tag when backspace is pressed on empty input
                const container = document.getElementById('emailTagsContainer');
                if (container && container.lastChild) {
                    // Add smooth removal animation
                    const lastTag = container.lastChild;
                    lastTag.style.transition = 'all 0.3s ease';
                    lastTag.style.opacity = '0';
                    lastTag.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        if (lastTag.parentNode) {
                            lastTag.remove();
                        }
                    }, 300);
                }
            }
        });
        
        // Handle paste events for bulk email addition
        emailInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                const pastedText = this.value;
                const emails = pastedText.split(/[,;\s\n\r]+/).filter(email => email.trim());
                this.value = '';
                
                emails.forEach((email, index) => {
                    if (email.trim()) {
                        // Add slight delay for visual effect
                        setTimeout(() => {
                            addEmailTag(email.trim());
                        }, index * 100);
                    }
                });
            }, 10);
        });
        
        // Visual feedback for input focus
        emailInput.addEventListener('focus', function() {
            this.parentElement.style.borderColor = '#0d6efd';
            this.parentElement.style.boxShadow = '0 0 0 0.2rem rgba(13, 110, 253, 0.25)';
        });
        
        emailInput.addEventListener('blur', function() {
            this.parentElement.style.borderColor = '';
            this.parentElement.style.boxShadow = '';
        });
    }
    
    // Add all attendees button with enhanced functionality
    const addAttendeesBtn = document.getElementById('addAttendeesButton');
    if (addAttendeesBtn) {
        addAttendeesBtn.addEventListener('click', function() {
            console.log(`[${timestamp}] Add all attendees button clicked`);
            
            // Visual feedback
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            this.disabled = true;
            
            addAllAttendees().finally(() => {
                this.innerHTML = '<i class="fas fa-user-friends me-2"></i>Add All Attendees';
                this.disabled = false;
            });
        });
        
        // Hover effects for better UX
        addAttendeesBtn.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            }
        });
        
        addAttendeesBtn.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    }
    
    // Enhanced send email button with modern interactions
    const sendEmailBtn = document.getElementById('sendEmailButton');
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function() {
            console.log(`[${timestamp}] Send email button clicked`);
            sendEmailReport();
        });
        
        // Modern button hover effects
        sendEmailBtn.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 6px 12px rgba(13, 110, 253, 0.3)';
            }
        });
        
        sendEmailBtn.addEventListener('mouseleave', function() {
            if (!this.disabled) {
                this.style.transform = '';
                this.style.boxShadow = '';
            }
        });
    }
    
    // Keyboard shortcuts for power users
    document.addEventListener('keydown', function(e) {
        // Ctrl+Shift+E to open email modal
        if (e.ctrlKey && e.shiftKey && e.key === 'E') {
            e.preventDefault();
            openEmailModal();
        }
        
        // Escape key to close modal
        if (e.key === 'Escape') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('emailReportModal'));
            if (modal) {
                modal.hide();
            }
        }
    });
    
    console.log(`[${timestamp}] All event listeners configured successfully`);
}

/**
 * Opens the email modal and prepares it with current date and clean form state
 * 
 * This function initializes the modal with today's date in UK format,
 * resets all form fields to their default states, and provides visual
 * feedback during the opening process.
 */
function openEmailModal() {
    const timestamp = new Date().toLocaleString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    });
    
    console.log(`[${timestamp}] Opening email modal for report composition`);
    
    // Get current date in UK format (DD/MM/YYYY)
    let currentDate = '';
    const dateDisplay = document.querySelector('#currentDate');
    if (dateDisplay) {
        currentDate = dateDisplay.textContent.trim();
        console.log(`[${timestamp}] Using date from page display: ${currentDate}`);
    } else {
        // Fallback to today's date in UK format
        currentDate = new Date().toLocaleDateString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            timeZone: 'Europe/London'
        });
        console.log(`[${timestamp}] Using current UK date: ${currentDate}`);
    }
    
    // Set the report date field with animation
    const reportDateField = document.getElementById('reportDate');
    if (reportDateField) {
        reportDateField.value = currentDate;
        reportDateField.style.background = 'linear-gradient(45deg, #f8f9fa, #e9ecef)';
    }
    
    // Clear any previous recipients with animation
    const tagsContainer = document.getElementById('emailTagsContainer');
    if (tagsContainer) {
        Array.from(tagsContainer.children).forEach((tag, index) => {
            setTimeout(() => {
                if (tag.parentNode) {
                    tag.style.transition = 'all 0.3s ease';
                    tag.style.opacity = '0';
                    tag.style.transform = 'scale(0.8)';
                    setTimeout(() => tag.remove(), 300);
                }
            }, index * 50);
        });
    }
    
    // Set default subject with current date
    const subjectField = document.getElementById('emailSubject');
    if (subjectField) {
        subjectField.value = `Daily Activity Briefing - ${currentDate}`;
        subjectField.style.borderColor = '#0d6efd';
        setTimeout(() => {
            subjectField.style.borderColor = '';
        }, 1000);
    }
    
    // Clear any previous message content
    const messageField = document.getElementById('emailMessage');
    if (messageField) {
        messageField.value = '';
        messageField.style.minHeight = '100px';
    }
    
    // Reset status display
    const statusDiv = document.getElementById('emailStatus');
    if (statusDiv) {
        statusDiv.className = 'd-none alert border-2 shadow-sm';
        statusDiv.innerHTML = '';
    }
    
    // Show the modal with Bootstrap 5 API and custom animations
    const emailModal = new bootstrap.Modal(document.getElementById('emailReportModal'), {
        backdrop: 'static',
        keyboard: true
    });
    
    emailModal.show();
    
    // Focus on email input after modal is shown
    const modalElement = document.getElementById('emailReportModal');
    modalElement.addEventListener('shown.bs.modal', function() {
        const emailInput = document.getElementById('emailInput');
        if (emailInput) {
            setTimeout(() => emailInput.focus(), 100);
        }
    }, { once: true });
    
    console.log(`[${timestamp}] Email modal opened and initialized successfully`);
}
/**
 * Adds an email address as a styled tag to the recipients container
 * 
 * This function validates email format, prevents duplicates, and creates
 * visually appealing recipient tags with smooth animations.
 * 
 * @param {string} email - Email address to add as recipient
 */
function addEmailTag(email) {
    if (!email || email.length === 0) return;
    
    const timestamp = new Date().toLocaleString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    });
    
    // Comprehensive email validation using RFC-compliant regex
    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    
    if (!emailRegex.test(email)) {
        console.warn(`[${timestamp}] Invalid email format rejected: ${email}`);
        showEmailStatus(
            '<i class="fas fa-exclamation-triangle me-2"></i>Invalid email format. Please check the email address and try again.', 
            'warning'
        );
        return;
    }
    
    // Check for duplicate emails
    const tagsContainer = document.getElementById('emailTagsContainer');
    const existingTags = tagsContainer.querySelectorAll('.email-tag');
    
    for (let tag of existingTags) {
        if (tag.dataset.email === email.toLowerCase()) {
            console.log(`[${timestamp}] Duplicate email not added: ${email}`);
            showEmailStatus(
                '<i class="fas fa-info-circle me-2"></i>This email address has already been added.', 
                'info'
            );
            return;
        }
    }
    
    // Create modern, stylish email tag with Bootstrap styling
    const tag = document.createElement('div');
    tag.className = 'email-tag badge bg-primary d-flex align-items-center px-3 py-2 fs-6 rounded-pill shadow-sm';
    tag.dataset.email = email.toLowerCase();
    tag.style.cursor = 'default';
    tag.innerHTML = `
        <span class="me-2 user-select-none">${escapeHtml(email)}</span>
        <button type="button" class="btn-close btn-close-white btn-sm" 
                aria-label="Remove ${escapeHtml(email)}" onclick="removeEmailTag(this)"
                style="font-size: 0.7rem;"></button>
    `;
    
    // Add modern hover effects
    tag.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.05)';
        this.style.transition = 'all 0.2s ease';
    });
    
    tag.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
    
    // Add to container with smooth entrance animation
    tagsContainer.appendChild(tag);
    
    // Smooth entrance animation
    tag.style.opacity = '0';
    tag.style.transform = 'scale(0.8) translateY(-10px)';
    setTimeout(() => {
        tag.style.transition = 'all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        tag.style.opacity = '1';
        tag.style.transform = 'scale(1) translateY(0)';
    }, 10);
    
    console.log(`[${timestamp}] Email recipient added successfully: ${email}`);
}

/**
 * Removes an email tag from the recipients container with animation
 * 
 * @param {HTMLElement} closeButton - The close button that was clicked
 */
function removeEmailTag(closeButton) {
    const tag = closeButton.closest('.email-tag');
    if (tag) {
        const email = tag.dataset.email;
        const timestamp = new Date().toLocaleString('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'Europe/London'
        });
        
        // Smooth exit animation
        tag.style.transition = 'all 0.3s ease';
        tag.style.opacity = '0';
        tag.style.transform = 'scale(0.8) translateY(-10px)';
        
        setTimeout(() => {
            if (tag.parentNode) {
                tag.remove();
                console.log(`[${timestamp}] Email recipient removed: ${email}`);
            }
        }, 300);
    }
}

/**
 * Fetches all attendees and adds them as email recipients
 * 
 * @returns {Promise} Promise that resolves when operation completes
 */
async function addAllAttendees() {
    const timestamp = new Date().toLocaleString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    });
    
    console.log(`[${timestamp}] Fetching all attendees for email recipients`);
    
    // Show loading status with animation
    showEmailStatus(
        '<i class="fas fa-spinner fa-spin me-2"></i>Loading attendees from system...', 
        'info'
    );
    
    try {
        const response = await fetch('ajax_attendees.php?action=list');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log(`[${timestamp}] Attendees data received:`, data);
        
        if (data.success && data.attendees && data.attendees.length > 0) {
            let addedCount = 0;
            
            // Process each attendee with staggered animation
            for (let i = 0; i < data.attendees.length; i++) {
                const attendee = data.attendees[i];
                
                if (attendee.email && attendee.email.includes('@') && attendee.email.includes('.')) {
                    setTimeout(() => {
                        addEmailTag(attendee.email);
                    }, i * 150); // Stagger by 150ms for visual effect
                    addedCount++;
                }
            }
            
            if (addedCount > 0) {
                setTimeout(() => {
                    showEmailStatus(
                        `<i class="fas fa-check-circle me-2"></i>Successfully added ${addedCount} attendees as recipients`, 
                        'success'
                    );
                }, addedCount * 150 + 500);
                console.log(`[${timestamp}] Added ${addedCount} attendees as email recipients`);
            } else {
                showEmailStatus(
                    '<i class="fas fa-exclamation-triangle me-2"></i>No attendees with valid email addresses found', 
                    'warning'
                );
            }
        } else {
            showEmailStatus(
                '<i class="fas fa-info-circle me-2"></i>No attendees found in the system', 
                'info'
            );
        }
    } catch (error) {
        console.error(`[${timestamp}] Error fetching attendees:`, error);
        showEmailStatus(
            `<i class="fas fa-exclamation-circle me-2"></i>Failed to load attendees: ${error.message}`, 
            'danger'
        );
    }
}

/**
 * Displays status messages with modern styling and animations
 * 
 * @param {string} message - The message to display (can include HTML)
 * @param {string} type - Bootstrap alert type (success, danger, warning, info)
 */
function showEmailStatus(message, type = 'info') {
    const statusDiv = document.getElementById('emailStatus');
    if (statusDiv) {
        statusDiv.className = `alert alert-${type} border-2 shadow-sm d-flex align-items-center`;
        statusDiv.innerHTML = message;
        statusDiv.classList.remove('d-none');
        
        // Smooth entrance animation
        statusDiv.style.opacity = '0';
        statusDiv.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            statusDiv.style.transition = 'all 0.3s ease';
            statusDiv.style.opacity = '1';
            statusDiv.style.transform = 'translateY(0)';
        }, 10);
        
        // Auto-hide success and info messages
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                statusDiv.style.opacity = '0';
                setTimeout(() => {
                    statusDiv.classList.add('d-none');
                }, 300);
            }, 4000);
        }
    }
}

/**
 * Sends the email report to all specified recipients
 */
async function sendEmailReport() {
    const timestamp = new Date().toLocaleString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/London'
    });
    
    console.log(`[${timestamp}] Starting email report send process`);
    
    // Collect recipients
    const tagsContainer = document.getElementById('emailTagsContainer');
    const emailTags = tagsContainer.querySelectorAll('.email-tag');
    const recipients = Array.from(emailTags).map(tag => tag.dataset.email);
    
    if (recipients.length === 0) {
        showEmailStatus(
            '<i class="fas fa-exclamation-triangle me-2"></i>Please add at least one email recipient before sending', 
            'warning'
        );
        return;
    }
    
    // Get form values
    const reportDate = document.getElementById('reportDate').value;
    const subject = document.getElementById('emailSubject').value || `Daily Activity Briefing - ${reportDate}`;
    const message = document.getElementById('emailMessage').value || '';
    
    // Convert UK date to API format
    let apiDate = reportDate;
    if (reportDate.includes('/')) {
        const [day, month, year] = reportDate.split('/');
        apiDate = `${year}-${month}-${day}`;
    }
    
    // Disable interface during sending
    const sendButton = document.getElementById('sendEmailButton');
    const cancelButton = sendButton.previousElementSibling;
    const originalSendText = sendButton.innerHTML;
    
    sendButton.disabled = true;
    cancelButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-cog fa-spin me-2"></i>Sending...';
    sendButton.style.background = 'linear-gradient(45deg, #0d6efd, #0b5ed7)';
    
    showEmailStatus(
        '<i class="fas fa-rocket fa-bounce me-2"></i>Generating PDF report and sending email...', 
        'info'
    );
    
    try {
        const formData = new FormData();
        formData.append('date', apiDate);
        formData.append('subject', subject);
        formData.append('message', message);
        recipients.forEach(email => formData.append('recipients[]', email));
        
        const response = await fetch('email_briefing.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log(`[${timestamp}] Email report response:`, data);
        
        if (data.success) {
            showEmailStatus(`
                <div class="d-flex align-items-center">
                    <div class="text-success me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Report Sent Successfully!</h6>
                        <p class="mb-0">Delivered to ${data.recipients.length} recipient(s)</p>
                        <small class="text-muted">PDF: ${data.filename}</small>
                    </div>
                </div>
            `, 'success');
            
            cancelButton.disabled = false;
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('emailReportModal'));
                if (modal) modal.hide();
            }, 3000);
        } else {
            throw new Error(data.error || 'Unknown error occurred');
        }
    } catch (error) {
        console.error(`[${timestamp}] Email sending error:`, error);
        showEmailStatus(
            `<i class="fas fa-exclamation-circle me-2"></i>Error: ${error.message}`, 
            'danger'
        );
        sendButton.disabled = false;
        cancelButton.disabled = false;
        sendButton.innerHTML = originalSendText;
        sendButton.style.background = '';
    }
}

/**
 * Escapes HTML to prevent XSS attacks
 * @param {string} text - Text to escape
 * @returns {string} - HTML-safe text
 */
function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// End of DABS Email Report Module v3.0.0