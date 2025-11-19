/**
 * =========================================================================
 * NAME: js/subcontractors.js - DABS Subcontractor Information Section
 * DESCRIPTION:
 *   This file manages the Subcontractor Information section for the DABS dashboard.
 *   It handles fetching, displaying, adding, editing, and deleting subcontractors using
 *   a modern, mobile-friendly Bootstrap accordion. All contact info, trade, status,
 *   summary stats, and CRUD operations are included. All popups and forms use Bootstrap 5 modals.
 *   Dates/times are UK format (DD/MM/YYYY HH:MM:SS) with Europe/London timezone.
 *
 * AUTHOR: irlamkeep (Subcontractor Management Specialist)
 * LAST UPDATED: 18/06/2025 13:15:00 (UK Time - Europe/London timezone)
 * VERSION: 4.0 - Debugged, fully modern, with robust initialization order and comments.
 * =========================================================================
 */

// --- INITIALIZE ON PAGE LOAD ---
// Ensures safe event binding and robust initialization for all DOM elements.
document.addEventListener('DOMContentLoaded', function() {
    console.log('Subcontractors Information v4.0 initialized at: ' +
        new Date().toLocaleString('en-GB', {
            timeZone: 'Europe/London',
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        })
    );
    // Only load if the container exists
    if (document.getElementById('subcontractorAccordion')) {
        loadSubcontractors();
    }

    // Add Subcontractor button
    const addBtn = document.getElementById('addSubcontractorBtn');
    if (addBtn) {
        addBtn.onclick = function(e) {
            e.preventDefault();
            openSubcontractorModal();
        }
    }

    // Modal reset handler
    const modal = document.getElementById('subcontractorModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            editingSubcontractorId = null;
            const form = document.getElementById('subcontractorForm');
            if (form) form.reset();
        });
    }

    // Modern submit event handling
    const form = document.getElementById('subcontractorForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveSubcontractor();
        });
    }

    // Defensive: Remove any accidental direct click event
    const saveBtn = document.getElementById('saveSubcontractorBtn');
    if (saveBtn) {
        saveBtn.onclick = null;
    }

    // Delete button (bind only once)
    const deleteBtn = document.getElementById('deleteSubcontractorBtn');
    if (deleteBtn) {
        deleteBtn.onclick = function(e) {
            e.preventDefault();
            if (editingSubcontractorId !== null) {
                confirmDeleteSubcontractor(editingSubcontractorId);
            }
        }
    }
});

// --- FETCH AND DISPLAY ALL SUBCONTRACTORS ---
function loadSubcontractors() {
    const container = document.getElementById('subcontractorAccordion');
    if (!container) return;

    // Show loading spinner
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading subcontractors...</span>
            </div>
            <h5 class="text-muted mb-2">Loading Subcontractor Information</h5>
            <p class="text-muted">Retrieving contractor details and current status...</p>
        </div>
    `;

    // Always return a Promise for compatibility with activities.js etc.
    return fetch('ajax_subcontractors.php?action=list')
        .then(response => response.json())
        .then(data => {
            if (data.ok && Array.isArray(data.subcontractors)) {
                displaySubcontractors(data.subcontractors);
                updateSummaryStats(data.subcontractors);
                return data; // For chaining if needed
            } else {
                throw new Error(data.error || 'No subcontractors found.');
            }
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger text-center mt-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> ${error.message}
                </div>
            `;
            return Promise.resolve({ok: false, error: error.message});
        });
}

// --- DISPLAY SUBCONTRACTORS IN BOOTSTRAP ACCORDION ---
function displaySubcontractors(subcontractors) {
    const container = document.getElementById('subcontractorAccordion');
    if (!container) return;

    if (!subcontractors.length) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-users-slash fa-4x text-muted mb-4"></i>
                <h4 class="text-muted mb-3">No Subcontractors Found</h4>
                <p class="text-muted mb-4 lead">
                    Start building your project team by adding subcontractors.
                </p>
                <button class="btn btn-success btn-lg" onclick="openSubcontractorModal()">
                    <i class="fas fa-plus-circle me-2"></i>Add Your First Subcontractor
                </button>
            </div>
        `;
        return;
    }

    let html = '<div class="accordion" id="subcontractorAccordionMain">';
    subcontractors.forEach((sub, idx) => {
        const lastUpdated = sub.updated_at
            ? new Date(sub.updated_at).toLocaleString('en-GB', {
                timeZone: 'Europe/London',
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            })
            : 'Not available';

        // Use best available contact info (contact_name/phone/email or first from contacts array)
        let contactName = sub.contact_name || '';
        let contactPhone = sub.phone || '';
        let contactEmail = sub.email || '';
        if (Array.isArray(sub.contacts) && sub.contacts.length && (!contactName && !contactPhone && !contactEmail)) {
            contactName = sub.contacts[0].name || '';
            contactPhone = sub.contacts[0].phone || '';
            contactEmail = sub.contacts[0].email || '';
        }

        html += `
            <div class="accordion-item mb-2 border-0 shadow-sm">
                <h2 class="accordion-header" id="heading${idx}">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse${idx}"
                        aria-expanded="false"
                        aria-controls="collapse${idx}">
                        <div class="d-flex flex-column flex-md-row w-100 align-items-center">
                            <div class="me-auto">
                                <span class="fw-bold fs-5 text-primary">${escapeHtml(sub.name || 'Unnamed')}</span>
                                <span class="ms-2 badge bg-info">${escapeHtml(sub.trade || 'No trade')}</span>
                            </div>
                            <div class="ms-md-3 text-end">
                                <span class="badge bg-success">${escapeHtml(sub.status || 'Active')}</span>
                                <div class="small text-muted mt-1">Updated: ${lastUpdated}</div>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse${idx}" class="accordion-collapse collapse"
                     aria-labelledby="heading${idx}" data-bs-parent="#subcontractorAccordionMain">
                    <div class="accordion-body bg-light">
                        <div class="row mb-2">
                            <div class="col-md-6 mb-2">
                                <strong>Contact Name:</strong>
                                <span class="ms-2">${escapeHtml(contactName)}</span>
                            </div>
                            <div class="col-md-3 mb-2">
                                <strong>Phone:</strong>
                                <span class="ms-2">${escapeHtml(contactPhone)}</span>
                            </div>
                            <div class="col-md-3 mb-2">
                                <strong>Email:</strong>
                                <span class="ms-2">${escapeHtml(contactEmail)}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 small text-muted">
                                Subcontractor ID: ${sub.id}
                                &nbsp;•&nbsp; Created by: ${escapeHtml(sub.created_by || 'N/A')}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-primary btn-sm me-2" onclick="openSubcontractorModal(${sub.id})">
                                    <i class="fas fa-edit me-1"></i>Edit Details
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="confirmDeleteSubcontractor(${sub.id})">
                                    <i class="fas fa-trash-alt me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

// --- UPDATE SUMMARY STATS ---
function updateSummaryStats(subcontractors) {
    let activeCount = 0, standbyCount = 0, totalCount = subcontractors.length;
    subcontractors.forEach(sub => {
        const status = (sub.status || '').toLowerCase();
        if (status === 'active') activeCount++;
        else if (status === 'standby') standbyCount++;
    });

    const activeElement = document.getElementById('activeSubcontractors');
    const standbyElement = document.getElementById('standbySubcontractors');
    const totalElement = document.getElementById('totalSubcontractors');

    if (activeElement) activeElement.textContent = activeCount;
    if (standbyElement) standbyElement.textContent = standbyCount;
    if (totalElement) totalElement.textContent = totalCount;
}

// --- UTILITY: ESCAPE HTML ---
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// --- MODAL MANAGEMENT AND CRUD ---
let editingSubcontractorId = null;

// Open the add/edit subcontractor modal
function openSubcontractorModal(id = null) {
    editingSubcontractorId = id;
    const modal = document.getElementById('subcontractorModal');
    if (!modal) {
        alert('Subcontractor modal not found in HTML.');
        return;
    }
    // Set up form fields
    const form = document.getElementById('subcontractorForm');
    if (form) form.reset();
    // Modal labels and button text
    document.getElementById('subcontractorModalLabel').textContent = id ? 'Edit Subcontractor' : 'Add Subcontractor';
    document.getElementById('saveSubcontractorBtn').textContent = id ? 'Save Changes' : 'Add Subcontractor';
    document.getElementById('deleteSubcontractorBtn').style.display = id ? '' : 'none';
    // If editing, load data and fill form
    if (id) {
        fetch(`ajax_subcontractors.php?action=get&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok && data.subcontractor) {
                    fillSubcontractorForm(data.subcontractor);
                } else {
                    alert('Could not load subcontractor data.');
                }
            });
    }
    // Show the modal using Bootstrap
    try {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        bsModal.show();
    } catch (e) {
        modal.style.display = 'block';
    }
}

// Fill the modal form with subcontractor data
function fillSubcontractorForm(sub) {
    document.getElementById('subcontractorName').value = sub.name || '';
    document.getElementById('subcontractorTrade').value = sub.trade || '';
    document.getElementById('subcontractorStatus').value = sub.status || 'Active';

    // Prefer "contact_name/phone/email", but if empty, use first contacts array element
    let contactName = sub.contact_name || '';
    let contactPhone = sub.phone || '';
    let contactEmail = sub.email || '';
    if (Array.isArray(sub.contacts) && sub.contacts.length && (!contactName && !contactPhone && !contactEmail)) {
        contactName = sub.contacts[0].name || '';
        contactPhone = sub.contacts[0].phone || '';
        contactEmail = sub.contacts[0].email || '';
    }
    document.getElementById('contactName').value = contactName;
    document.getElementById('contactPhone').value = contactPhone;
    document.getElementById('contactEmail').value = contactEmail;
}

// Save subcontractor (add or edit)
function saveSubcontractor() {
    // Get form data
    const name = document.getElementById('subcontractorName').value.trim();
    const trade = document.getElementById('subcontractorTrade').value.trim();
    const status = document.getElementById('subcontractorStatus').value.trim();
    const contactName = document.getElementById('contactName').value.trim();
    const contactPhone = document.getElementById('contactPhone').value.trim();
    const contactEmail = document.getElementById('contactEmail').value.trim();
    if (!name || !trade) {
        alert('Name and Trade are required.');
        return;
    }
    const payload = new URLSearchParams();
    payload.append('name', name);
    payload.append('trade', trade);
    payload.append('status', status);
    payload.append('contact_name', contactName);
    payload.append('phone', contactPhone);
    payload.append('email', contactEmail);
    if (editingSubcontractorId) {
        payload.append('action', 'update');
        payload.append('id', editingSubcontractorId);
    } else {
        payload.append('action', 'add');
    }

    // Disable the save button to prevent double submission
    const saveBtn = document.getElementById('saveSubcontractorBtn');
    if (saveBtn) saveBtn.disabled = true;

    fetch('ajax_subcontractors.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: payload
    })
    .then(r => r.json())
    .then(data => {
        if (saveBtn) saveBtn.disabled = false;
        if (data.ok) {
            // Close modal and refresh list
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('subcontractorModal'));
            modal.hide();
            loadSubcontractors();
        } else {
            alert('Failed to save subcontractor: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(() => {
        if (saveBtn) saveBtn.disabled = false;
        alert('Server error saving subcontractor.');
    });
}

// Confirm and delete a subcontractor
function confirmDeleteSubcontractor(id) {
    if (!confirm('Are you sure you want to delete this subcontractor? This cannot be undone.')) return;
    const payload = new URLSearchParams();
    payload.append('action', 'delete');
    payload.append('id', id);
    fetch('ajax_subcontractors.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: payload
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            loadSubcontractors();
        } else {
            alert('Failed to delete subcontractor: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(() => {
        alert('Server error deleting subcontractor.');
    });
}