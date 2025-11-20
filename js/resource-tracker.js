/**
 * ============================================================================
 * js/resource-tracker.js
 * ============================================================================
 * DESCRIPTION:
 * Loads and displays live resource statistics for today for the DABS dashboard.
 * Shows:
 *  - Total Labour (Workers)
 *  - Active Subcontractors (now styled to match Labour by Contractor)
 *  - Labour by Contractor (list)
 *  - Labour by Area (list)
 * All numbers and dates in UK format. No plant/materials shown.
 *
 * AUTHOR: irlam
 * LAST UPDATED: 25/06/2025 (UK Time)
 * ============================================================================
 */

// Helper function: format number UK style
function ukNumber(num) {
    return Number(num).toLocaleString('en-GB');
}

// Helper function: generate a pretty list-group of name/value pairs
function listGroup(items, labelKey, valueKey, labelIcon) {
    if (!items || items.length === 0) {
        return `<div class="text-muted small ms-2">No data available.</div>`;
    }
    return `
        <ul class="list-group list-group-flush">
            ${items.map(row => `
                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                    <span>
                        <i class="fa-solid fa-${labelIcon} me-2 text-primary"></i>
                        ${row[labelKey] || '<em>Unknown</em>'}
                    </span>
                    <span class="badge bg-secondary rounded-pill">${ukNumber(row[valueKey])}</span>
                </li>
            `).join('')}
        </ul>
    `;
}

// Helper function: log for debugging
function logResource(msg, data = null) {
    const now = new Date().toLocaleString('en-GB', { hour12: false, timeZone: 'Europe/London' });
    if (data) {
        console.log(`[ResourceTracker ${now}] ${msg}`, data);
    } else {
        console.log(`[ResourceTracker ${now}] ${msg}`);
    }
}

// Main function to load and display resource stats as individual cards
function loadResourceStats() {
    const container = document.getElementById('resourceStats');
    logResource('Initializing resource statistics load...');
    if (!container) {
        logResource('ERROR: #resourceStats container not found in DOM!');
        return;
    }
    // Get project_id from global JS if available, fallback to 1
    const project_id = window.CURRENT_PROJECT_ID || 1;
    container.innerHTML = `
        <div class="text-center my-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading resource stats...</span>
            </div>
            <div>Loading resource statistics...</div>
        </div>
    `;
    logResource('Sending fetch request to ajax_resource_stats.php...');
    fetch('ajax_resource_stats.php?project_id=' + encodeURIComponent(project_id))
        .then(res => res.json())
        .then(data => {
            logResource('Server response received:', data);
            if (!data.success) throw new Error(data.message || 'Server error');
            const stats = data.resource_stats;

            // Build Labour by Contractor list
            const contractorList = listGroup(stats.labour_by_contractor, 'contractor', 'labour_count', 'user-tie');
            // Build Labour by Area list
            const areaList = listGroup(stats.labour_by_area, 'area', 'labour_count', 'layer-group');

            // Create horizontal layout for metric cards
            container.innerHTML = `
                <div class="row g-3 mb-4 resource-cards">
                    <!-- Total Labour Card -->
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="card resource-stat-card h-100 shadow-sm">
                            <div class="card-body text-center p-3">
                                <div class="stat-icon mb-2">
                                    <i class="fas fa-users text-success"></i>
                                </div>
                                <h6 class="card-title mb-2 small">Total Labour</h6>
                                <h3 class="stat-number text-success mb-0">${ukNumber(stats.total_labour)}</h3>
                                <small class="text-muted">Workers</small>
                            </div>
                        </div>
                    </div>

                    <!-- Active Subcontractors Card -->
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="card resource-stat-card h-100 shadow-sm">
                            <div class="card-body text-center p-3">
                                <div class="stat-icon mb-2">
                                    <i class="fas fa-briefcase text-info"></i>
                                </div>
                                <h6 class="card-title mb-2 small">Active Subcontractors</h6>
                                <h3 class="stat-number text-info mb-0">${ukNumber(stats.active_contractors)}</h3>
                                <small class="text-muted">Contractors</small>
                            </div>
                        </div>
                    </div>

                    <!-- Labour by Contractor Card -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card resource-stat-card h-100 shadow-sm">
                            <div class="card-body p-3">
                                <div class="stat-icon mb-2 text-center">
                                    <i class="fas fa-user-tie text-primary"></i>
                                </div>
                                <h6 class="card-title mb-2 text-center small">Labour by Contractor</h6>
                                <div class="contractor-list" style="max-height: 200px; overflow-y: auto;">
                                    ${contractorList}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Labour by Area Card -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card resource-stat-card h-100 shadow-sm">
                            <div class="card-body p-3">
                                <div class="stat-icon mb-2 text-center">
                                    <i class="fas fa-layer-group text-warning"></i>
                                </div>
                                <h6 class="card-title mb-2 text-center small">Labour by Area</h6>
                                <div class="area-list" style="max-height: 200px; overflow-y: auto;">
                                    ${areaList}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            logResource('Resource stats displayed successfully.');
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger">Error loading resource stats: ${error.message}</div>
            `;
            logResource('Error loading resource stats:', error);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    logResource('DOM loaded, starting resource stats initialization...');
    loadResourceStats();
});