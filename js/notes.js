/**
 * js/notes.js - Daily Activity Briefing System (DABS)
 * 
 * Purpose: Handles all rich text note editing for the Notes & Updates panel
 * Author: irlamkeep
 * Date: 02/06/2025
 * Version: 1.1
 * 
 * This file provides:
 * - Loading project notes from the database using AJAX
 * - Rich text editing with TinyMCE editor 
 * - Saving notes back to the database
 * - Viewing notes history with pagination
 * - Easy switching between view and edit modes
 * 
 * All dates/times use UK format (DD/MM/YYYY) with Europe/London timezone
 * Works with dabs_notes database table
 */

// Initialize when the document is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Debug helper function - logs messages to console if debug is enabled
    function logDebug(message, data = null) {
        // Only log if global debug is enabled
        if (window.debugEnabled) {
            if (data) {
                console.log(`[Notes] ${message}`, data);
            } else {
                console.log(`[Notes] ${message}`);
            }
        }
    }

    // Store all important DOM elements we'll need to work with
    const elements = {
        // Main containers
        notesContainer: document.getElementById('notesContainer'),
        notesContent: document.getElementById('notesContent'),
        notesEditContainer: document.getElementById('notesEditContainer'),
        notesMeta: document.getElementById('notesMeta'),
        notesLoading: document.getElementById('notesLoading'),
        
        // Buttons
        editBtn: document.getElementById('editNotesBtn'),
        saveBtn: document.getElementById('saveNotesBtn'),
        cancelBtn: document.getElementById('cancelNotesBtn'),
        historyBtn: document.getElementById('historyNotesBtn'),
        
        // Editor
        notesEditor: document.getElementById('notesEditor'),
        
        // History modal
        historyModal: document.getElementById('notesHistoryModal'),
        historyContainer: document.getElementById('notesHistoryContainer')
    };
    
    // Track notes state to manage user interaction
    let notesState = {
        content: '',           // Current notes content
        lastUpdated: '',       // Last update time (UK format)
        updatedBy: '',         // Who last updated the notes
        isEditing: false,      // Whether we're currently in edit mode
        hasChanges: false      // Whether there are unsaved changes
    };
    
    // Initialize TinyMCE rich text editor
    initTinyMCE();
    
    // Load initial notes on page load
    loadNotes();
    
    // Set up event handlers for buttons
    setupEventHandlers();
    
    /**
     * Initialize TinyMCE rich text editor 
     * Uses your premium TinyMCE API key for advanced features
     */
    function initTinyMCE() {
        logDebug('Initializing TinyMCE editor');
        
        // Check if TinyMCE is available
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                // Target the textarea 
                selector: '#notesEditor',
                
                // Editor appearance
                height: 300,
                menubar: false,
                
                // Your TinyMCE premium API key
                api_key: 'cx3e21j3t5yv0ukx72zuh02xf9o75o3bgencxrbbzmad1p5c',
                
                // Enable useful features
                plugins: [
                    'advlist autolink lists link image charmap',
                    'searchreplace visualblocks code',
                    'insertdatetime media table paste help'
                ],
                
                // Create a clean toolbar with common formatting options
                toolbar: 'undo redo | formatselect | ' +
                        'bold italic backcolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist | ' +
                        'removeformat | help',
                        
                // Make content look like your site
                content_style: 'body { font-family:Roboto,Arial,sans-serif; font-size:14px }',
                
                // Disable TinyMCE branding
                branding: false,
                
                // Allow editor to be resized
                resize: true,
                
                // Track changes to detect unsaved work
                setup: function(editor) {
                    editor.on('change', function() {
                        notesState.hasChanges = true;
                    });
                }
            });
        } else {
            // Fallback for if TinyMCE isn't loaded properly
            logDebug('WARNING: TinyMCE not available, using standard textarea');
            elements.notesEditor.addEventListener('input', function() {
                notesState.hasChanges = true;
            });
        }
    }
    
    /**
     * Set up event handlers for all notes interface buttons
     */
    function setupEventHandlers() {
        logDebug('Setting up event handlers');
        
        // Edit button - switches to edit mode
        if (elements.editBtn) {
            elements.editBtn.addEventListener('click', function() {
                startEditing();
            });
        }
        
        // Save button - saves changes to database
        if (elements.saveBtn) {
            elements.saveBtn.addEventListener('click', function() {
                saveNotes();
            });
        }
        
        // Cancel button - discards changes
        if (elements.cancelBtn) {
            elements.cancelBtn.addEventListener('click', function() {
                cancelEditing();
            });
        }
        
        // History button - shows notes history
        if (elements.historyBtn) {
            elements.historyBtn.addEventListener('click', function() {
                loadNotesHistory();
            });
        }
        
        // Warn user if they try to leave with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (notesState.isEditing && notesState.hasChanges) {
                const message = 'You have unsaved notes changes. Are you sure you want to leave?';
                e.returnValue = message;
                return message;
            }
        });
    }
    
    /**
     * Load notes from the server via AJAX
     * Gets today's notes for the current project
     */
    function loadNotes() {
        logDebug('Loading notes from server');
        
        // Show loading spinner
        if (elements.notesLoading) {
            elements.notesLoading.style.display = 'block';
        }
        
        // Hide content while loading
        if (elements.notesContent) {
            elements.notesContent.style.display = 'none';
        }
        
        // Make AJAX request to get notes
        fetch('ajax_notes.php?action=get')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                logDebug('Notes loaded successfully', data);
                
                // Store the notes data in our state
                notesState.content = data.notes || '';
                notesState.lastUpdated = data.updated_at || '';
                notesState.updatedBy = data.updated_by || '';
                notesState.hasChanges = false;
                
                // Update the display with the loaded content
                updateNotesDisplay();
            })
            .catch(error => {
                logDebug('Error loading notes', error);
                
                // Show error message to user
                if (elements.notesContent) {
                    elements.notesContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading notes: ${error.message}
                        </div>
                    `;
                    elements.notesContent.style.display = 'block';
                }
                
                // Also show in notification area if available
                if (typeof showNotification === 'function') {
                    showNotification('Error loading notes: ' + error.message, 'danger');
                }
            })
            .finally(() => {
                // Hide loading spinner
                if (elements.notesLoading) {
                    elements.notesLoading.style.display = 'none';
                }
            });
    }
    
    /**
     * Update the notes display with current state
     * Shows the content in read-only form
     */
    function updateNotesDisplay() {
        logDebug('Updating notes display');
        
        // Update the content area
        if (elements.notesContent) {
            elements.notesContent.innerHTML = notesState.content || '<em class="text-muted">No notes for today.</em>';
            elements.notesContent.style.display = 'block';
        }
        
        // Update metadata (last updated info)
        if (elements.notesMeta) {
            if (notesState.lastUpdated && notesState.updatedBy) {
                elements.notesMeta.textContent = `Last updated: ${notesState.lastUpdated} by ${notesState.updatedBy}`;
                elements.notesMeta.style.display = 'block';
            } else {
                elements.notesMeta.style.display = 'none';
            }
        }
        
        // Make sure edit container is hidden
        if (elements.notesEditContainer) {
            elements.notesEditContainer.style.display = 'none';
        }
        
        // Reset editing state
        notesState.isEditing = false;
    }
    
    /**
     * Switch to edit mode
     * Shows TinyMCE editor with current content
     */
    function startEditing() {
        logDebug('Starting edit mode');
        
        // Don't do anything if already editing
        if (notesState.isEditing) return;
        
        // Hide content display
        if (elements.notesContent) {
            elements.notesContent.style.display = 'none';
        }
        
        // Show editor container
        if (elements.notesEditContainer) {
            elements.notesEditContainer.style.display = 'block';
        }
        
        // Set editor content - either use TinyMCE or fallback to regular textarea
        if (typeof tinymce !== 'undefined' && tinymce.get('notesEditor')) {
            tinymce.get('notesEditor').setContent(notesState.content);
        } else {
            elements.notesEditor.value = notesState.content;
        }
        
        // Update state
        notesState.isEditing = true;
        notesState.hasChanges = false;
        
        // Focus the editor (with small delay to ensure TinyMCE is ready)
        setTimeout(() => {
            if (typeof tinymce !== 'undefined' && tinymce.get('notesEditor')) {
                tinymce.get('notesEditor').focus();
            } else if (elements.notesEditor) {
                elements.notesEditor.focus();
            }
        }, 100);
    }
    
    /**
     * Cancel editing and discard changes
     * Returns to view-only mode without saving
     */
    function cancelEditing() {
        logDebug('Canceling edit mode');
        
        // If there are unsaved changes, ask for confirmation
        if (notesState.hasChanges) {
            if (!confirm('You have unsaved changes. Are you sure you want to discard them?')) {
                return;
            }
        }
        
        // Clear editor
        if (typeof tinymce !== 'undefined' && tinymce.get('notesEditor')) {
            tinymce.get('notesEditor').setContent('');
        } else if (elements.notesEditor) {
            elements.notesEditor.value = '';
        }
        
        // Hide editor container
        if (elements.notesEditContainer) {
            elements.notesEditContainer.style.display = 'none';
        }
        
        // Show content display
        if (elements.notesContent) {
            elements.notesContent.style.display = 'block';
        }
        
        // Update state
        notesState.isEditing = false;
        notesState.hasChanges = false;
    }
    
    /**
     * Save notes to the server via AJAX
     * Updates the dabs_notes table with current content
     */
    function saveNotes() {
        logDebug('Saving notes to server');
        
        // Get notes content from editor
        let notesContent = '';
        if (typeof tinymce !== 'undefined' && tinymce.get('notesEditor')) {
            notesContent = tinymce.get('notesEditor').getContent();
        } else if (elements.notesEditor) {
            notesContent = elements.notesEditor.value;
        }
        
        // Show loading indicator
        if (elements.notesLoading) {
            elements.notesLoading.style.display = 'block';
        }
        
        // Create form data for the request
        const formData = new FormData();
        formData.append('action', 'save');
        formData.append('notes', notesContent);
        
        // Send AJAX request to save notes
        fetch('ajax_notes.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.ok) {
                logDebug('Notes saved successfully', data);
                
                // Update state with new data
                notesState.content = notesContent;
                notesState.lastUpdated = data.updated_at || '';
                notesState.updatedBy = data.updated_by || '';
                notesState.hasChanges = false;
                
                // Exit edit mode and show updated content
                updateNotesDisplay();
                
                // Show success notification
                if (typeof showNotification === 'function') {
                    showNotification('Notes saved successfully', 'success');
                }
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        })
        .catch(error => {
            logDebug('Error saving notes', error);
            
            // Show error message
            if (typeof showNotification === 'function') {
                showNotification('Error saving notes: ' + error.message, 'danger');
            } else {
                alert('Error saving notes: ' + error.message);
            }
        })
        .finally(() => {
            // Hide loading indicator
            if (elements.notesLoading) {
                elements.notesLoading.style.display = 'none';
            }
        });
    }
    
    /**
     * Load and display notes history in modal
     * Shows all previous notes for this project
     */
    function loadNotesHistory() {
        logDebug('Loading notes history');
        
        // Show the modal
        const historyModal = new bootstrap.Modal(elements.historyModal);
        historyModal.show();
        
        // Show loading state in the modal
        elements.historyContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading history...</span>
                </div>
                <div class="mt-2">Loading notes history...</div>
            </div>
        `;
        
        // Fetch history from server
        fetch('ajax_notes.php?action=history')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                logDebug('History loaded', data);
                
                if (data.ok && data.history && data.history.length > 0) {
                    // Create HTML for history entries
                    let html = '';
                    
                    data.history.forEach(entry => {
                        html += `
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong>${entry.note_date}</strong> 
                                    <small class="text-muted">Updated: ${entry.updated_at} by ${entry.updated_by || 'Unknown'}</small>
                                </div>
                                <div class="card-body">
                                    <div class="notes-preview">${entry.notes_preview}</div>
                                    <button class="btn btn-sm btn-outline-primary mt-2 view-note-btn" 
                                            data-date="${entry.note_date}" 
                                            data-bs-toggle="tooltip" 
                                            title="View full notes for this date">
                                        <i class="fas fa-eye"></i> View Full Notes
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    
                    // Add pagination if needed
                    if (data.pages > 1) {
                        html += `<nav aria-label="Notes history pagination">
                            <ul class="pagination justify-content-center">`;
                        
                        // Previous page button
                        html += `
                            <li class="page-item ${data.page <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${data.page - 1}">Previous</a>
                            </li>
                        `;
                        
                        // Page numbers
                        for (let i = 1; i <= data.pages; i++) {
                            html += `
                                <li class="page-item ${i === data.page ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `;
                        }
                        
                        // Next page button
                        html += `
                            <li class="page-item ${data.page >= data.pages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${data.page + 1}">Next</a>
                            </li>
                        `;
                        
                        html += `</ul></nav>`;
                    }
                    
                    // Update modal content
                    elements.historyContainer.innerHTML = html;
                    
                    // Add event listeners for pagination
                    document.querySelectorAll('.pagination .page-link').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const page = parseInt(this.getAttribute('data-page'), 10);
                            if (page && !isNaN(page)) {
                                loadHistoryPage(page);
                            }
                        });
                    });
                    
                    // Add event listeners for view buttons
                    document.querySelectorAll('.view-note-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const date = this.getAttribute('data-date');
                            viewNoteByDate(date);
                        });
                    });
                    
                } else {
                    // No history available
                    elements.historyContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No notes history found.
                        </div>
                    `;
                }
            })
            .catch(error => {
                logDebug('Error loading history', error);
                
                elements.historyContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading notes history: ${error.message}
                    </div>
                `;
            });
    }
    
    /**
     * Load a specific page of history
     * @param {number} page - The page number to load
     */
    function loadHistoryPage(page) {
        logDebug('Loading history page', page);
        
        // Show loading state
        elements.historyContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading history...</span>
                </div>
                <div class="mt-2">Loading page ${page}...</div>
            </div>
        `;
        
        // Calculate offset based on page number (default 10 per page)
        const limit = 10;
        const offset = (page - 1) * limit;
        
        // Fetch the specific page
        fetch(`ajax_notes.php?action=history&limit=${limit}&offset=${offset}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                logDebug('History page loaded', data);
                
                // Use the main history loading function to process and display
                loadNotesHistory();
            })
            .catch(error => {
                logDebug('Error loading history page', error);
                
                elements.historyContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading notes history: ${error.message}
                    </div>
                `;
            });
    }
    
    /**
     * View notes for a specific date
     * @param {string} date - The date to view (DD/MM/YYYY)
     */
    function viewNoteByDate(date) {
        logDebug('Viewing note by date', date);
        
        // Convert date format if needed (from UK DD/MM/YYYY to YYYY-MM-DD)
        let apiDate = date;
        if (date.includes('/')) {
            const parts = date.split('/');
            if (parts.length === 3) {
                apiDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
        }
        
        // Close the history modal
        bootstrap.Modal.getInstance(elements.historyModal).hide();
        
        // Show loading indicator
        if (elements.notesLoading) {
            elements.notesLoading.style.display = 'block';
        }
        
        // Fetch notes for this date
        fetch(`ajax_notes.php?action=get_date&date=${apiDate}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                logDebug('Historical note loaded', data);
                
                // Update content
                if (elements.notesContent) {
                    elements.notesContent.innerHTML = data.notes || '<em class="text-muted">No notes for this date.</em>';
                    elements.notesContent.style.display = 'block';
                }
                
                // Update metadata
                if (elements.notesMeta) {
                    let metaText = `Viewing notes for ${data.date}`;
                    if (data.updated_at && data.updated_by) {
                        metaText += ` (Last updated: ${data.updated_at} by ${data.updated_by})`;
                    }
                    elements.notesMeta.textContent = metaText;
                    elements.notesMeta.style.display = 'block';
                }
                
                // Create a temporary back button
                const backBtn = document.createElement('button');
                backBtn.className = 'btn btn-sm btn-outline-secondary mt-2';
                backBtn.innerHTML = '<i class="fas fa-arrow-left"></i> Back to Today\'s Notes';
                backBtn.addEventListener('click', function() {
                    loadNotes();
                    this.remove();
                });
                
                // Add the back button
                elements.notesContent.insertAdjacentElement('afterend', backBtn);
                
                // Show notification
                if (typeof showNotification === 'function') {
                    showNotification(`Viewing notes from ${data.date}`, 'info');
                }
            })
            .catch(error => {
                logDebug('Error loading historical note', error);
                
                // Show error message
                if (elements.notesContent) {
                    elements.notesContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading notes for ${date}: ${error.message}
                        </div>
                    `;
                    elements.notesContent.style.display = 'block';
                }
            })
            .finally(() => {
                // Hide loading indicator
                if (elements.notesLoading) {
                    elements.notesLoading.style.display = 'none';
                }
            });
    }
    
    // End of module
    logDebug('Notes module initialized');
});