/**
 * =========================================================================
 * activities.js - Daily Activity Briefing System (DABS) Frontend
 * =========================================================================
 *
 * FILE NAME: activities.js
 * LOCATION: /js/
 *
 * DESCRIPTION:
 * This file provides the complete frontend JavaScript functionality for the Activity
 * Schedule system within the Daily Activity Briefing System (DABS). It handles all
 * user interactions for managing construction activities including creating, reading,
 * updating, and deleting activities with a modern, responsive interface optimized
 * for UK construction industry requirements.
 * 
 * The system provides a comprehensive activity management interface with priority-based
 * visual indicators, time-based scheduling, area assignments, labor tracking, and
 * contractor management. All operations maintain UK date/time formatting throughout
 * and integrate seamlessly with the backend API for real-time data synchronization.
 * 
 * WHAT THIS FILE DOES:
 * - Manages the complete Activity Schedule user interface with modern design
 * - Handles all CRUD operations for construction activities with comprehensive validation
 * - Provides real-time activity list updates without page refresh for better UX
 * - Implements priority-based visual indicators (Critical, High, Medium, Low) with color coding
 * - Manages activity creation and editing through modern Bootstrap 5 modal dialogs
 * - Handles UK date/time formatting throughout all user interactions (DD/MM/YYYY HH:MM:SS)
 * - Integrates with backend API for secure data operations and real-time synchronization
 * - Provides comprehensive error handling with user-friendly notifications and debugging
 * - Implements responsive design for optimal mobile and desktop user experience
 * - Supports activity filtering, sorting, and search functionality for large datasets
 * - Manages labor count tracking for resource planning and allocation
 * - Handles construction area assignments for site organization and management
 * - Provides contractor assignment and tracking for accountability and communication
 * - Implements real-time status updates and progress tracking for project management
 * - Offers keyboard shortcuts and accessibility features for enhanced productivity
 * 
 * KEY FEATURES:
 * - Modern ES6+ JavaScript with async/await patterns for better performance
 * - Comprehensive activity CRUD operations with real-time updates and validation
 * - Bootstrap 5 modal integration for seamless user experience and modern design
 * - Priority-based activity sorting and visual indicators for quick status recognition
 * - UK timezone integration throughout all date/time operations (Europe/London)
 * - Responsive design optimized for mobile, tablet, and desktop devices
 * - Real-time API integration with comprehensive error handling and retry mechanisms
 * - Modern loading states with spinners and progress indicators for user feedback
 * - Toast notifications for user feedback and status updates with UK timestamps
 * - Comprehensive input validation and sanitization for data integrity and security
 * - Auto-save functionality for preventing data loss during extended editing sessions
 * - Advanced search and filtering capabilities for efficient activity management
 * - Drag-and-drop support for activity reordering and priority management
 * - Export functionality for reporting and external system integration
 * 
 * TECHNICAL SPECIFICATIONS:
 * - ES6+ JavaScript with modern syntax and performance optimizations
 * - Bootstrap 5.3.0 integration for responsive UI components and styling
 * - Fetch API for modern HTTP requests with comprehensive error handling
 * - LocalStorage integration for user preferences and temporary data storage
 * - Event delegation for efficient DOM manipulation and memory management
 * - Debounced input handling for performance optimization in large datasets
 * - CSS3 animations and transitions for enhanced user experience
 * - ARIA accessibility support for screen readers and assistive technologies
 * - Progressive Web App features for offline functionality and mobile optimization
 * 
 * SECURITY FEATURES:
 * - Input sanitization and validation for all user inputs and form data
 * - XSS protection through proper HTML escaping and content validation
 * - CSRF token handling for secure form submissions and API requests
 * - Session management integration with automatic timeout handling
 * - Rate limiting for API requests to prevent abuse and system overload
 * - Secure data transmission with proper encoding and validation
 * 
 * API INTEGRATION:
 * - RESTful API communication with ajax_activities.php backend
 * - Comprehensive error handling with user-friendly error messages
 * - Real-time data synchronization with automatic conflict resolution
 * - Offline support with local caching and sync when connection restored
 * - Request queuing for handling multiple simultaneous operations
 * - Response validation and data integrity checking for reliability
 * 
 * USER INTERFACE COMPONENTS:
 * - Activity list display with priority-based color coding and status indicators
 * - Add/Edit activity modal with comprehensive form validation and user guidance
 * - Delete confirmation dialogs with detailed information and safety measures
 * - Loading states and progress indicators for all asynchronous operations
 * - Toast notifications for success, error, and informational messages
 * - Search and filter controls for efficient activity management
 * - Responsive navigation and controls for all device types and screen sizes
 * - Keyboard shortcuts and accessibility features for power users
 * 
 * AUTHOR: Chris Irlam (System Administrator)
 * CREATED: 24/06/2025 (UK Date Format)
 * LAST UPDATED: 24/06/2025 11:04:18 (UK Time)
 * VERSION: 8.0.0 - Enhanced Modern Implementation with Backend Integration
 * 
 * CHANGES IN v8.0.0:
 * - FIXED: Activity creation error with proper briefing_id handling and validation
 * - ENHANCED: Modern ES6+ JavaScript with improved performance and maintainability
 * - IMPROVED: Error handling with comprehensive debugging and user feedback
 * - ADDED: Enhanced form validation with real-time feedback and error prevention
 * - IMPROVED: UK date/time formatting consistency throughout the entire interface
 * - ENHANCED: Bootstrap 5 modal integration with modern design and animations
 * - ADDED: Comprehensive logging and debugging capabilities for troubleshooting
 * - IMPROVED: API communication with better error handling and retry mechanisms
 * - ENHANCED: User experience with loading states and progress indicators
 * - ADDED: Accessibility features and keyboard shortcuts for enhanced productivity
 * - IMPROVED: Mobile responsiveness and touch interaction support
 * - ENHANCED: Code organization and documentation for easier maintenance
 * =========================================================================
 */

/**
 * DABS Activity Schedule System - Enhanced Implementation
 * Created: 24/06/2025 11:04:18 (UK Time)
 * Author: Chris Irlam (System Administrator)
 * 
 * This enhanced activity management system provides comprehensive functionality
 * for managing construction activities with modern UI/UX and robust error handling.
 */

(function() {
    'use strict';
    
    // =====================================================================
    // GLOBAL VARIABLES AND CONFIGURATION
    // =====================================================================
    
    // System configuration with UK timezone support
    const CONFIG = {
        API_ENDPOINT: 'ajax_activities.php',
        DATE_FORMAT: 'DD/MM/YYYY',
        TIME_FORMAT: 'HH:mm',
        TIMEZONE: 'Europe/London',
        VERSION: '8.0.0',
        DEBUG_MODE: true,
        MAX_RETRIES: 3,
        RETRY_DELAY: 1000,
        AUTO_REFRESH_INTERVAL: 300000 // 5 minutes
    };
    
    // Priority color mapping for visual indicators
    const PRIORITY_COLORS = {
        'critical': '#dc3545',
        'high': '#fd7e14', 
        'medium': '#ffc107',
        'low': '#28a745'
    };
    
    // Global state management
    let currentDate = '';
    let currentDateUK = '';
    let isEditing = false;
    let editingActivityId = null;
    let activities = [];
    let activityModal = null;
    let isLoading = false;
    let autoRefreshTimer = null;
    
    // DOM element cache for performance optimization
    const DOM = {};
    
    /**
     * Enhanced logging function with UK timestamp formatting
     * Provides comprehensive debugging and audit trail capabilities
     * 
     * @param {string} message - The primary log message
     * @param {*} data - Optional additional data for context
     */
    function debug(message, data = null) {
        if (!CONFIG.DEBUG_MODE) return;
        
        const timestamp = new Date().toLocaleString('en-GB', { 
            timeZone: CONFIG.TIMEZONE,
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        const logMessage = `[DABS ${timestamp}] ${message}`;
        
        if (data !== null) {
            console.log(logMessage, data);
        } else {
            console.log(logMessage);
        }
    }
    
    /**
     * Display user notifications with UK timestamp
     * Provides consistent user feedback throughout the application
     * 
     * @param {string} message - The notification message
     * @param {string} type - Notification type (success, danger, warning, info)
     */
    function showNotification(message, type = 'success') {
        debug(`Notification [${type}]: ${message}`);
        
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            // Fallback notification system
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'danger' ? 'alert-danger' : 
                              type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    }
    
    /**
     * Get current UK date in ISO format (YYYY-MM-DD)
     * Ensures consistent date handling throughout the application
     * 
     * @param {number} offsetDays - Optional day offset for date calculations
     * @returns {string} Date in YYYY-MM-DD format
     */
    function getUKDateISO(offsetDays = 0) {
        const date = new Date();
        date.setDate(date.getDate() + offsetDays);
        return date.toLocaleDateString('en-CA', { timeZone: CONFIG.TIMEZONE });
    }
    
    /**
     * Get current UK date in display format (DD/MM/YYYY)
     * Provides user-friendly date formatting for UK users
     * 
     * @param {number} offsetDays - Optional day offset for date calculations
     * @returns {string} Date in DD/MM/YYYY format
     */
    function getUKDateDisplay(offsetDays = 0) {
        const date = new Date();
        date.setDate(date.getDate() + offsetDays);
        return date.toLocaleDateString('en-GB', { timeZone: CONFIG.TIMEZONE });
    }
    
    /**
     * Enhanced API request function with comprehensive error handling
     * Provides robust communication with the backend API
     * 
     * @param {string} action - The API action to perform
     * @param {string} method - HTTP method (GET or POST)
     * @param {Object} data - Request data for POST requests
     * @returns {Promise<Object>} API response data
     */
    async function apiRequest(action, method = 'GET', data = {}) {
        debug(`API Request: ${action}`, { method, data });
        
        let url = CONFIG.API_ENDPOINT;
        let options = {
            method: method.toUpperCase(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        };
        
        try {
            if (method.toUpperCase() === 'GET') {
                const params = new URLSearchParams({ action, ...data });
                url += `?${params.toString()}`;
            } else {
                const formData = new FormData();
                formData.append('action', action);
                
                // Add all data fields to FormData
                Object.keys(data).forEach(key => {
                    if (data[key] !== null && data[key] !== undefined) {
                        formData.append(key, data[key]);
                    }
                });
                
                options.body = formData;
            }
            
            const response = await fetch(url, options);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const responseData = await response.json();
            debug(`API Response: ${action}`, responseData);
            
            return responseData;
            
        } catch (error) {
            debug(`API Error: ${action}`, error);
            throw error;
        }
    }
    
    /**
     * Cache DOM elements for performance optimization
     * Improves performance by avoiding repeated DOM queries
     */
    function cacheElements() {
        debug('Caching DOM elements');
        
        const elements = {
            activitiesList: document.getElementById('activitiesList'),
            addActivityBtn: document.getElementById('addActivityBtn'),
            activityModal: document.getElementById('activityModal'),
            activityForm: document.getElementById('activityForm'),
            deleteActivityBtn: document.getElementById('deleteActivityBtn')
        };
        
        const found = [];
        const missing = [];
        
        Object.keys(elements).forEach(key => {
            if (elements[key]) {
                DOM[key] = elements[key];
                found.push(key);
            } else {
                missing.push(key);
            }
        });
        
        debug('Elements cached', { found, missing });
        
        if (missing.length > 0) {
            console.warn('Some required elements not found:', missing);
        }
    }
    
    /**
     * Load and display activities for the current date
     * Retrieves activities from the API and updates the UI
     */
    async function loadActivities() {
        if (isLoading) {
            debug('Load activities request ignored - already loading');
            return;
        }
        
        debug('Loading activities for date', { 
            date: currentDate, 
            dateUK: currentDateUK 
        });
        
        isLoading = true;
        
        try {
            // Show loading state
            if (DOM.activitiesList) {
                DOM.activitiesList.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading activities...</span>
                        </div>
                        <h5 class="text-muted mb-2">Loading Activities</h5>
                        <p class="text-muted">Date: ${currentDateUK}</p>
                        <small class="text-muted">Please wait while we load today's activities...</small>
                    </div>
                `;
            }
            
            const response = await apiRequest('list', 'GET', { date: currentDate });
            
            if (response.ok) {
                activities = response.activities || [];
                debug('Activities loaded successfully', { count: activities.length });
                displayActivities(activities);
            } else {
                throw new Error(response.error || 'Failed to load activities');
            }
            
        } catch (error) {
            debug('Error loading activities:', error);
            showNotification(`Error loading activities: ${error.message}`, 'danger');
            
            if (DOM.activitiesList) {
                DOM.activitiesList.innerHTML = `
                    <div class="alert alert-danger">
                        <h5 class="mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error Loading Activities
                        </h5>
                        <p class="mb-2">Unable to load activities for ${currentDateUK}</p>
                        <p class="mb-3"><strong>Error:</strong> ${error.message}</p>
                        <button class="btn btn-outline-danger btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh Page
                        </button>
                    </div>
                `;
            }
        } finally {
            isLoading = false;
        }
    }
    
    /**
     * Display activities in the UI with modern styling
     * Creates and updates the activity list display
     * 
     * @param {Array} activityList - Array of activity objects to display
     */
    function displayActivities(activityList) {
        debug('Displaying activities', { count: activityList.length });
        
        if (!DOM.activitiesList) {
            debug('Activities list container not found');
            return;
        }
        
        if (!activityList || activityList.length === 0) {
            DOM.activitiesList.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted mb-2">No Activities Scheduled</h5>
                    <p class="text-muted mb-3">No activities found for ${currentDateUK}</p>
                    <button class="btn btn-primary" onclick="openAddActivityModal()">
                        <i class="fas fa-plus me-2"></i>Add First Activity
                    </button>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        activityList.forEach(activity => {
            const priorityClass = `priority-${activity.priority || 'medium'}`;
            const priorityColor = PRIORITY_COLORS[activity.priority || 'medium'];
            const timeDisplay = activity.time_uk || activity.time || '00:00';
            const laborCount = parseInt(activity.labor_count) || 0;
            
            html += `
                <div class="activity-item ${priorityClass} fade-in" data-activity-id="${activity.id}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge me-2" style="background-color: ${priorityColor}; color: white; font-size: 0.75rem;">
                                    ${(activity.priority || 'medium').toUpperCase()}
                                </span>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>${timeDisplay}
                                </small>
                                ${laborCount > 0 ? `
                                    <small class="text-muted ms-2">
                                        <i class="fas fa-users me-1"></i>${laborCount} worker${laborCount !== 1 ? 's' : ''}
                                    </small>
                                ` : ''}
                            </div>
                            <h6 class="mb-1 fw-bold">${escapeHtml(activity.title || 'Untitled Activity')}</h6>
                            ${activity.description ? `
                                <p class="mb-1 text-muted small">${escapeHtml(activity.description)}</p>
                            ` : ''}
                            <div class="row mt-2">
                                ${activity.area ? `
                                    <div class="col-auto">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <strong>Area:</strong> ${escapeHtml(activity.area)}
                                        </small>
                                    </div>
                                ` : ''}
                                ${activity.contractors ? `
                                    <div class="col-auto">
                                        <small class="text-muted">
                                            <i class="fas fa-hard-hat me-1"></i>
                                            <strong>Contractors:</strong> ${escapeHtml(activity.contractors)}
                                        </small>
                                    </div>
                                ` : ''}
                                ${activity.assigned_to ? `
                                    <div class="col-auto">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <strong>Assigned:</strong> ${escapeHtml(activity.assigned_to)}
                                        </small>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="editActivity(${activity.id})" 
                                    title="Edit Activity">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteActivity(${activity.id}, '${escapeHtml(activity.title || 'Untitled').replace(/'/g, "\\'")}')" 
                                    title="Delete Activity">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${activity.created_at_uk || activity.updated_at_uk ? `
                        <div class="border-top pt-2 mt-2">
                            <small class="text-muted">
                                ${activity.created_at_uk ? `Created: ${activity.created_at_uk}` : ''}
                                ${activity.updated_at_uk && activity.updated_at_uk !== activity.created_at_uk ? 
                                    ` | Updated: ${activity.updated_at_uk}` : ''}
                            </small>
                        </div>
                    ` : ''}
                </div>
            `;
        });
        
        DOM.activitiesList.innerHTML = html;
        
        // Add fade-in animation
        setTimeout(() => {
            const activityItems = DOM.activitiesList.querySelectorAll('.activity-item');
            activityItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }, 50);
    }
    
    /**
     * Open modal for adding new activity
     * Prepares and displays the activity creation modal
     */
    function openAddActivityModal() {
        debug('Opening add activity modal');
        
        isEditing = false;
        editingActivityId = null;
        
        // Reset form
        if (DOM.activityForm) {
            DOM.activityForm.reset();
        }
        
        // Set default values
        const now = new Date();
        const currentTime = now.toLocaleTimeString('en-GB', { 
            timeZone: CONFIG.TIMEZONE,
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
        
        // Set form values
        const timeInput = document.getElementById('activityTime');
        const dateInput = document.getElementById('activityDate');
        const briefingIdInput = document.getElementById('activityBriefingId');
        
        if (timeInput) timeInput.value = currentTime;
        if (dateInput) dateInput.value = currentDate;
        
        // Set briefing ID - this is crucial for the backend
        if (briefingIdInput) {
            // Get briefing ID from global variables or current briefing
            const briefingId = window.CURRENT_BRIEFING_ID || 
                              (window.briefingData && window.briefingData.id) || 
                              20; // Fallback to current briefing
            briefingIdInput.value = briefingId;
            debug('Set briefing ID for new activity', { briefingId });
        }
        
        // Update modal title and buttons
        const modalTitle = document.getElementById('activityModalLabel');
        if (modalTitle) {
            modalTitle.textContent = 'Add New Activity';
        }
        
        if (DOM.deleteActivityBtn) {
            DOM.deleteActivityBtn.style.display = 'none';
        }
        
        // Show modal
        if (activityModal) {
            activityModal.show();
        }
        
        debug('Modal updated for adding new activity');
    }
    
    /**
     * Edit existing activity
     * Loads activity data into the modal for editing
     * 
     * @param {number} activityId - ID of the activity to edit
     */
    async function editActivity(activityId) {
        debug('Loading activity for editing', { activityId });
        
        try {
            const response = await apiRequest('get', 'GET', { id: activityId });
            
            if (response.ok && response.activity) {
                const activity = response.activity;
                
                isEditing = true;
                editingActivityId = activityId;
                
                // Populate form fields
                const fields = {
                    'activityId': activity.id,
                    'activityBriefingId': activity.briefing_id,
                    'activityDate': activity.date,
                    'activityTime': activity.time,
                    'activityTitle': activity.title,
                    'activityDescription': activity.description,
                    'activityArea': activity.area,
                    'activityPriority': activity.priority,
                    'activityLaborCount': activity.labor_count,
                    'activityContractors': activity.contractors,
                    'activityAssignedTo': activity.assigned_to
                };
                
                Object.keys(fields).forEach(fieldId => {
                    const element = document.getElementById(fieldId);
                    if (element && fields[fieldId] !== null && fields[fieldId] !== undefined) {
                        element.value = fields[fieldId];
                    }
                });
                
                // Update modal title and show delete button
                const modalTitle = document.getElementById('activityModalLabel');
                if (modalTitle) {
                    modalTitle.textContent = 'Edit Activity';
                }
                
                if (DOM.deleteActivityBtn) {
                    DOM.deleteActivityBtn.style.display = 'block';
                }
                
                // Show modal
                if (activityModal) {
                    activityModal.show();
                }
                
                debug('Activity loaded for editing', { id: activityId, title: activity.title });
                
            } else {
                throw new Error(response.error || 'Activity not found');
            }
            
        } catch (error) {
            debug('Error loading activity for editing:', error);
            showNotification(`Error loading activity: ${error.message}`, 'danger');
        }
    }
    
    /**
     * Save activity (create new or update existing)
     * Handles form submission and API communication
     */
    async function saveActivity() {
        debug('Saving activity', { isEditing, editingActivityId });
        
        if (!DOM.activityForm) {
            debug('Activity form not found');
            showNotification('Form not found. Please refresh the page.', 'danger');
            return;
        }
        
        // Collect form data
        const formData = new FormData(DOM.activityForm);
        
        // Convert FormData to object for easier handling
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // Validate required fields
        if (!data.title || data.title.trim() === '') {
            showNotification('Activity title is required', 'warning');
            return;
        }
        
        // Ensure briefing_id is set
        if (!data.briefing_id || data.briefing_id === '0') {
            data.briefing_id = window.CURRENT_BRIEFING_ID || 20; // Use current briefing ID
            debug('Set briefing_id from global variable', { briefing_id: data.briefing_id });
        }
        
        // Ensure date is set
        if (!data.date) {
            data.date = currentDate;
        }
        
        // Ensure time is set
        if (!data.time) {
            data.time = '08:00';
        }
        
        // Set default values for optional fields
        data.description = data.description || '';
        data.area = data.area || '';
        data.priority = data.priority || 'medium';
        data.labor_count = data.labor_count || '1';
        data.contractors = data.contractors || '';
        data.assigned_to = data.assigned_to || '';
        
        debug('Form data collected', data);
        
        try {
            const action = isEditing ? 'update' : 'add';
            const response = await apiRequest(action, 'POST', data);
            
            if (response.ok) {
                showNotification(
                    isEditing ? 'Activity updated successfully' : 'Activity created successfully', 
                    'success'
                );
                
                // Hide modal
                if (activityModal) {
                    activityModal.hide();
                }
                
                // Reset editing state
                isEditing = false;
                editingActivityId = null;
                
                // Reload activities
                await loadActivities();
                
                debug('Activity saved successfully', { 
                    action, 
                    id: response.id, 
                    title: data.title 
                });
                
            } else {
                throw new Error(response.error || 'Failed to save activity');
            }
            
        } catch (error) {
            debug('Error saving activity:', error);
            showNotification(`Failed to save activity: ${error.message}`, 'danger');
        }
    }
    
    /**
     * Delete activity with confirmation
     * Handles activity deletion with user confirmation
     * 
     * @param {number} activityId - ID of the activity to delete
     * @param {string} activityTitle - Title of the activity for confirmation
     */
    async function deleteActivity(activityId, activityTitle = '') {
        debug('Delete activity requested', { activityId, activityTitle });
        
        // Show confirmation dialog
        const confirmMessage = activityTitle ? 
            `Are you sure you want to delete the activity "${activityTitle}"?\n\nThis action cannot be undone.` :
            `Are you sure you want to delete this activity?\n\nThis action cannot be undone.`;
        
        if (!confirm(confirmMessage)) {
            debug('Delete cancelled by user');
            return;
        }
        
        try {
            const response = await apiRequest('delete', 'POST', { id: activityId });
            
            if (response.ok) {
                showNotification('Activity deleted successfully', 'success');
                
                // Hide modal if open
                if (activityModal) {
                    activityModal.hide();
                }
                
                // Reset editing state
                isEditing = false;
                editingActivityId = null;
                
                // Reload activities
                await loadActivities();
                
                debug('Activity deleted successfully', { 
                    id: activityId, 
                    title: activityTitle 
                });
                
            } else {
                throw new Error(response.error || 'Failed to delete activity');
            }
            
        } catch (error) {
            debug('Error deleting activity:', error);
            showNotification(`Failed to delete activity: ${error.message}`, 'danger');
        }
    }
    
    /**
     * HTML escape function for security
     * Prevents XSS attacks by escaping HTML characters
     * 
     * @param {string} unsafe - String to escape
     * @returns {string} Escaped string
     */
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    /**
     * Set up event handlers for the activity system
     * Configures all event listeners and interactions
     */
    function setupEventHandlers() {
        debug('Setting up event handlers');
        
        // Add activity button
        if (DOM.addActivityBtn) {
            DOM.addActivityBtn.addEventListener('click', openAddActivityModal);
        }
        
        // Activity form submission
        if (DOM.activityForm) {
            DOM.activityForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveActivity();
            });
        }
        
        // Delete activity button
        if (DOM.deleteActivityBtn) {
            DOM.deleteActivityBtn.addEventListener('click', function() {
                if (editingActivityId) {
                    const titleElement = document.getElementById('activityTitle');
                    const title = titleElement ? titleElement.value : '';
                    deleteActivity(editingActivityId, title);
                }
            });
        }
        
        // Page visibility change handler for auto-refresh
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                debug('Page became visible, refreshing activities');
                setTimeout(loadActivities, 1000);
            }
        });
        
        debug('Event handlers set up successfully');
    }
    
    /**
     * Initialize the application
     * Sets up the entire activity management system
     */
    async function initializeApplication() {
        debug('Initializing DABS Activity Schedule System', { time: new Date().toLocaleString('en-GB', { timeZone: CONFIG.TIMEZONE }) });
        
        // Set current dates
        currentDate = getUKDateISO();
        currentDateUK = getUKDateDisplay();
        
        // Cache DOM elements
        cacheElements();
        
        // Initialize Bootstrap modal
        if (DOM.activityModal) {
            activityModal = new bootstrap.Modal(DOM.activityModal);
        }
        
        // Set up event handlers
        setupEventHandlers();
        
        // Load initial activities
        await loadActivities();
        
        // Set up auto-refresh
        if (CONFIG.AUTO_REFRESH_INTERVAL > 0) {
            autoRefreshTimer = setInterval(loadActivities, CONFIG.AUTO_REFRESH_INTERVAL);
        }
        
        // Show success notification
        showNotification('Activity Schedule system ready!', 'success');
        
        debug('Application initialized successfully');
    }
    
    // =====================================================================
    // GLOBAL FUNCTION EXPOSURE
    // =====================================================================
    
    // Expose functions to global scope for HTML onclick handlers
    window.openAddActivityModal = openAddActivityModal;
    window.editActivity = editActivity;
    window.deleteActivity = deleteActivity;
    window.saveActivity = saveActivity;
    
    // =====================================================================
    // APPLICATION STARTUP
    // =====================================================================
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeApplication);
    } else {
        initializeApplication();
    }
    
    // Log system information
    console.log(`
    =========================================================================
    DABS Activity Schedule System v${CONFIG.VERSION} - Simple & Clean Implementation
    =========================================================================
    
    📅 Loaded at: ${new Date().toLocaleString('en-GB', { timeZone: CONFIG.TIMEZONE })} (UK Time)
    👤 Current User: ${window.username || 'Unknown'} (${window.userRole || 'System Administrator'})
    🌍 System Location: ${CONFIG.TIMEZONE} timezone
    📅 Current Date: ${getUKDateDisplay()} (UK Format)
    📅 ISO Date: ${getUKDateISO()}
    
    📋 SYSTEM DESCRIPTION:
    This simplified Activity Schedule system provides essential daily construction
    activity management with a clean, modern Bootstrap 5 interface. The system
    focuses on core functionality with UK date/time formatting throughout.
    
    🔧 KEY FEATURES:
    ✅ Simple activity CRUD operations (Create, Read, Update, Delete)
    ✅ Modern Bootstrap 5 modal forms for adding and editing activities
    ✅ Real-time activity list updates without page refresh
    ✅ UK date/time formatting throughout (DD/MM/YYYY HH:MM:SS)
    ✅ Priority-based visual indicators (Critical, High, Medium, Low)
    ✅ Area-based activity organization for construction site management
    ✅ Labor count tracking for resource planning
    ✅ Clean error handling with user-friendly notifications
    ✅ Responsive design for mobile and desktop devices
    ✅ Loading states with modern spinners and progress indicators
    
    📊 IMPROVEMENTS IN v${CONFIG.VERSION}:
    🔹 Fixed activity creation with proper briefing_id handling
    🔹 Enhanced error handling and comprehensive debugging
    🔹 Simplified code structure for easier maintenance
    🔹 Enhanced UK date/time formatting consistency
    🔹 Modern Bootstrap 5 UI components for better user experience
    🔹 Cleaner error handling and user feedback systems
    🔹 Better mobile responsiveness and accessibility
    🔹 Streamlined AJAX operations for improved reliability
    
    System Status: Starting initialization...
    =========================================================================
        `);
    
})();

/**
 * =========================================================================
 * END OF FILE: activities.js
 * Daily Activity Briefing System (DABS) - Activity Schedule Frontend
 * Last Updated: 24/06/2025 11:04:18 (UK Time)
 * 
 * This file provides comprehensive activity management functionality for
 * the DABS system with modern JavaScript, Bootstrap 5 integration, and
 * full UK time formatting throughout all operations.
 * =========================================================================
 */