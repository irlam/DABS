/**
 * Attendees Management System
 * Daily Activity Briefing System (DABS)
 * 
 * Manages meeting attendees with email storage for future reports
 */

(function() {
    'use strict';
    
    let attendeesData = [];
    
    /**
     * Load and display all attendees for today
     */
    function loadAttendees() {
        const container = document.getElementById('attendeesContainer');
        if (!container) return;
        
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        fetch('ajax_attendees.php?action=list')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.attendees) {
                    attendeesData = data.attendees;
                    displayAttendees(data.attendees);
                } else {
                    container.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No attendees yet. Add attendees to track who attended today\'s briefing.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading attendees:', error);
                container.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to load attendees.</div>';
            });
    }
    
    /**
     * Display attendees in a clean, compact list
     */
    function displayAttendees(attendees) {
        const container = document.getElementById('attendeesContainer');
        if (!attendees || attendees.length === 0) {
            container.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No attendees yet. Add attendees to track who attended today\'s briefing.</div>';
            return;
        }
        
        let html = '<div class="row g-2">';
        
        attendees.forEach(attendee => {
            html += `
                <div class="col-md-4 col-lg-2-4">
                    <div class="attendee-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="attendee-name">
                                    <i class="fas fa-user me-2"></i>${escapeHtml(attendee.attendee_name)}
                                </div>
                                ${attendee.subcontractor_name && attendee.subcontractor_name !== 'N/A' ? 
                                    `<div class="attendee-company">
                                        <i class="fas fa-building me-2"></i>${escapeHtml(attendee.subcontractor_name)}
                                    </div>` : ''}
                                <div class="attendee-meta">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>${attendee.briefing_date_uk}
                                    </small>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeAttendee(${attendee.id}, '${escapeHtml(attendee.attendee_name)}')" title="Remove attendee">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }
    
    /**
     * Get all attendee emails for pre-filling email reports
     */
    function getAttendeeEmails() {
        // This will be used by the email report functionality
        return attendeesData
            .filter(a => a.email && a.email.trim())
            .map(a => a.email.trim());
    }
    
    /**
     * Remove an attendee
     */
    window.removeAttendee = function(id, name) {
        if (!confirm(`Remove ${name} from attendees?`)) return;
        
        fetch('ajax_attendees.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Attendee removed successfully', 'success');
                loadAttendees();
            } else {
                showNotification(data.message || 'Failed to remove attendee', 'danger');
            }
        })
        .catch(error => {
            console.error('Error removing attendee:', error);
            showNotification('Error removing attendee', 'danger');
        });
    };
    
    /**
     * Escape HTML to prevent XSS
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
     * Show notification
     */
    function showNotification(message, type) {
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            alert(message);
        }
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Load attendees
        loadAttendees();
        
        // Add attendee button handler
        const addBtn = document.getElementById('addAttendeeBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('attendeeModal'));
                document.getElementById('attendeeForm').reset();
                modal.show();
            });
        }
        
        // Form submission handler
        const form = document.getElementById('attendeeForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                formData.append('action', 'add');
                formData.append('date', window.CURRENT_DATE || new Date().toISOString().split('T')[0]);
                
                fetch('ajax_attendees.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Attendee added successfully', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('attendeeModal')).hide();
                        form.reset();
                        loadAttendees();
                    } else {
                        showNotification(data.message || 'Failed to add attendee', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error adding attendee:', error);
                    showNotification('Error adding attendee', 'danger');
                });
            });
        }
    });
    
    // Export functions for use by other scripts
    window.getAttendeeEmails = getAttendeeEmails;
    window.reloadAttendees = loadAttendees;
})();
