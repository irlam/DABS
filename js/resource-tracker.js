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

// Main function to load and display resource stats
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

            // Use the same style for both headline stats and Labour by Contractor label
            const headlineTextClass = "fw-bold text-primary";

            // Use the same style for both badges
            const statBadgeStyle = "background: linear-gradient(90deg, #1abc9c 0%, #2980b9 100%); color: #fff; font-weight: bold;";

            container.innerHTML = `
                <div class="card mb-3 shadow-sm overview-card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-hard-hat me-2"></i>
                        Resource Allocation (as of ${stats.last_update})
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="${headlineTextClass}">
                                <i class="fas fa-users text-success me-2"></i>
                                Total Labour (Workers):
                            </span>
                            <span class="badge rounded-pill fs-6" style="${statBadgeStyle}">
                                ${ukNumber(stats.total_labour)}
                            </span>
                        </div>
                        <div class="mb-3">
                            <span class="${headlineTextClass}">
                                <i class="fas fa-briefcase text-info me-2"></i>
                                Active Subcontractors:
                            </span>
                            <span class="badge rounded-pill fs-6" style="${statBadgeStyle}">
                                ${ukNumber(stats.active_contractors)}
                            </span>
                        </div>
                        <div class="mb-1 mt-2">
                            <span class="${headlineTextClass}">
                                <i class="fas fa-user-tie me-2"></i>
                                Labour by Contractor
                            </span>
                        </div>
                        ${contractorList}
                        <div class="mb-1 mt-4">
                            <span class="fw-bold text-primary">
                                <i class="fas fa-layer-group me-2"></i>
                                Labour by Area
                            </span>
                        </div>
                        ${areaList}
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