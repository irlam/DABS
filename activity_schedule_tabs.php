<?php
/**
 * ==============================================================================
 * File: activity_schedule_tabs.php
 * ------------------------------------------------------------------------------
 * Modern DABS Activity Schedule Interface (UK Construction)
 * -------------------------------------------------------------------------------
 * - Displays all activities for the selected date, newest first.
 * - Add, edit, and delete activities using a modern modal form (Bootstrap 5).
 * - "Edit" now always updates the correct record (NO DUPLICATES bug fix June 2025).
 * - Uses only external CSS (css/styles.css) for appearance.
 * - Briefing Assignment and Primary Contractor are required.
 * - Time and Assigned Personnel are optional.
 * - UK date/time formats throughout (DD/MM/YYYY, 24-hour time).
 * - Works for non-coders: fully commented, ready to paste/replace.
 * ==============================================================================
 * Last updated: 27/06/2025 (UK date)
 * Author: Chris Irlam
 */

// Set timezone for all date/time handling (UK)
date_default_timezone_set('Europe/London');

// Session management
if (session_status() === PHP_SESSION_NONE) session_start();
$current_project = $_SESSION['current_project'] ?? 1;
$current_user = $_SESSION['user_name'] ?? 'irlam';

// Load contractors for dropdown
$contractors = [];
$connection_status = 'disconnected';
try {
    $db_host = '10.35.233.124';
    $db_name = 'k87747_dabs';
    $db_user = 'k87747_dabs';
    $db_pass = 'Subaru5554346';
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $connection_status = 'connected';
    $stmt = $pdo->prepare("SELECT id, name FROM dabs_subcontractors WHERE project_id = ? AND (status = 'Active' OR status IS NULL) ORDER BY name ASC");
    $stmt->execute([$current_project]);
    $contractors = $stmt->fetchAll();
} catch (Exception $e) {
    $contractors = [];
    $connection_status = 'failed';
}

// Load briefings for dropdowns
$briefings_today = [];
$briefings_all = [];
$briefing_status = 'none';
try {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT id, overview, date FROM briefings WHERE project_id = ? AND date = ? ORDER BY id DESC");
    $stmt->execute([$current_project, $today]);
    $briefings_today = $stmt->fetchAll();
    $stmt = $pdo->prepare("SELECT id, overview, date FROM briefings WHERE project_id = ? ORDER BY date DESC, id DESC LIMIT 100");
    $stmt->execute([$current_project]);
    $briefings_all = $stmt->fetchAll();
    $briefing_status = count($briefings_all) > 0 ? 'available' : 'empty';
} catch (Exception $e) {
    $briefings_today = [];
    $briefings_all = [];
    $briefing_status = 'error';
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>DABS Activity Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php if ($connection_status !== 'connected'): ?>
    <div class="alert alert-danger text-center">DB Connection Issue</div>
<?php endif; ?>
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <h1 class="mb-0 h2"><i class="fas fa-calendar-alt me-3"></i>Activity Schedule</h1>
            <div class="d-flex flex-column">
                <span class="badge bg-light text-dark fs-6 uk-date" id="todayDate"></span>
                <small class="text-light opacity-75">
                    DVN - Rochdale Road | User: <?php echo htmlspecialchars($current_user); ?>
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Modern Add Activity Button (style in css/styles.css) -->
            <button id="addActivityBtn" class="btn add-btn-gradient" title="Add New Activity for Today">
                <span class="add-btn-glow"></span>
                <i class="fas fa-plus me-2"></i>Add Activity
            </button>
            <div class="date-input-wrap">
                <label for="activityDate" class="form-label mb-1 text-white">
                    <i class="fa-solid fa-calendar-days me-1"></i>View Date:
                </label>
                <input type="date" id="activityDate" class="form-control">
            </div>
        </div>
    </div>
    <!-- Activities List -->
    <div class="row">
        <div class="col-12">
            <div id="areaScheduleTabs" class="fade-in">
                <div class="loading-container">
                    <div class="spinner-border text-primary loading-spinner"></div>
                    <p class="loading-text">Loading today's activities...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal: Add/Edit Activity -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="activityForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">
                    <i class="fas fa-plus me-2" id="modalIcon"></i>
                    <span id="modalTitle">Add New Activity</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="activity-id">
                <!-- Briefing and Time -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-clipboard-list"></i>Briefing Assignment</label>
                            <select class="form-select" name="briefing_id" id="activity-briefing_id" required></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-clock"></i>Scheduled Time (Optional)</label>
                            <input type="time" class="form-control uk-time" name="time" id="activity-time">
                        </div>
                    </div>
                </div>
                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-tag"></i>Activity Title</label>
                    <input type="text" class="form-control" name="title" id="activity-title" required maxlength="200" placeholder="Describe the activity">
                </div>
                <!-- Contractor & Area -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user-tie"></i>Primary Contractor</label>
                            <select class="form-select" name="contractors" id="activity-contractors" required>
                                <option value="" disabled selected>Select contractor...</option>
                                <?php foreach ($contractors as $contractor): ?>
                                    <option value="<?php echo htmlspecialchars($contractor['name']); ?>">
                                        <?php echo htmlspecialchars($contractor['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="__other__">Other (Enter custom contractor)</option>
                            </select>
                            <div id="activity-contractors-other-wrap" class="mt-2" style="display:none;">
                                <input type="text" class="form-control" id="activity-contractors-other" placeholder="Other contractor">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-location-dot"></i>Work Area/Location</label>
                            <input type="text" class="form-control" name="area" id="activity-area" maxlength="100" placeholder="e.g., Block 1">
                        </div>
                    </div>
                </div>
                <!-- Labor & Priority -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-users"></i>Labor Count</label>
                            <input type="number" class="form-control" name="labor_count" id="activity-labor_count" min="0" max="999" value="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-exclamation-triangle"></i>Priority Level</label>
                            <select class="form-select" name="priority" id="activity-priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-align-left"></i>Detailed Description</label>
                    <textarea class="form-control" name="description" id="activity-description" rows="2" maxlength="1000"></textarea>
                </div>
                <!-- Assigned Personnel (optional) -->
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user-check"></i>Assigned Personnel (Optional)</label>
                    <input type="text" class="form-control" name="assigned_to" id="activity-assigned_to" maxlength="200">
                </div>
                <div id="editModeInfo" class="alert alert-info d-none" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="editModeText">You are editing an existing activity.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="deleteActivityBtn" class="btn btn-danger d-none">
                    <i class="fas fa-trash-alt me-2"></i>Delete
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save me-2"></i>
                    <span id="submitText">Add Activity</span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/**
 * ===== DABS Activity Schedule Modern JS (UK time) =====
 * Handles add, edit, delete with auto-refresh and modern UI.
 * 2025-06-27: Bug fix - Editing now always updates (never duplicates) by always sending correct id and date.
 */

// Utility: UK date formatting
function formatUKDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
}
function getTodayISO() {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    return now.toISOString().slice(0,10);
}
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
}

// State
let CURRENT_DATE = getTodayISO();
let CURRENT_ACTIVITIES = [];
let activityModal = null;
let isEditMode = false;
let editingActivityId = null;

// Briefings
const BRIEFINGS_TODAY = <?php echo json_encode($briefings_today); ?>;
const BRIEFINGS_ALL = <?php echo json_encode($briefings_all); ?>;

// Init
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('activityDate').value = CURRENT_DATE;
    document.getElementById('todayDate').textContent = formatUKDate(CURRENT_DATE);
    activityModal = new bootstrap.Modal(document.getElementById('activityModal'));
    loadActivitiesForDate(CURRENT_DATE);
    setupEventListeners();
});

// Setup listeners
function setupEventListeners() {
    document.getElementById('activityDate').addEventListener('change', function() {
        CURRENT_DATE = this.value;
        document.getElementById('todayDate').textContent = formatUKDate(CURRENT_DATE);
        loadActivitiesForDate(CURRENT_DATE);
    });
    document.getElementById('addActivityBtn').addEventListener('click', openModalInAddMode);
    document.getElementById('activityForm').addEventListener('submit', handleFormSubmission);
    document.getElementById('activity-contractors').addEventListener('change', handleContractorsDropdownChange);
    document.getElementById('deleteActivityBtn').addEventListener('click', function() {
        if (editingActivityId) handleDeleteActivity(editingActivityId);
    });
}

// Load activities (newest first)
function loadActivitiesForDate(dateStr) {
    showLoadingSpinner("Loading activities...");
    fetch(`ajax_activities.php?action=list&date=${encodeURIComponent(dateStr)}`)
        .then(r => r.json())
        .then(data => {
            if(!data.ok) throw new Error(data.error || "Load error");
            CURRENT_ACTIVITIES = (data.activities || []).sort((a,b) => b.id - a.id);
            renderActivitiesList(CURRENT_ACTIVITIES, dateStr);
        })
        .catch(e => showErrorMessage(e.message));
}
function showLoadingSpinner(msg) {
    document.getElementById('areaScheduleTabs').innerHTML =
        `<div class="loading-container"><div class="spinner-border text-primary"></div><p>${escapeHtml(msg)}</p></div>`;
}
function showErrorMessage(msg) {
    document.getElementById('areaScheduleTabs').innerHTML =
        `<div class="alert alert-danger text-center">${escapeHtml(msg)}</div>`;
}

// Render activity list, newest first
function renderActivitiesList(activities, dateStr) {
    if (!activities.length) {
        document.getElementById('areaScheduleTabs').innerHTML =
            `<div class="alert alert-info text-center">No activities for ${formatUKDate(dateStr)}.<br>
            <button class="btn add-btn-gradient mt-3" onclick="openModalInAddMode()">
                <i class="fas fa-plus me-2"></i>Add First Activity
            </button></div>`;
        return;
    }
    let html = `<div class="list-group">`;
    activities.forEach(activity => {
        html += `
        <div class="list-group-item activity-item">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div><span class="activity-time">${activity.time ? activity.time.slice(0,5) : ''}</span>
                        <span class="activity-title">${escapeHtml(activity.title)}</span>
                        <span class="ms-2 activity-contractor"><i class="fas fa-user-tie text-secondary me-1"></i>${escapeHtml(activity.contractors)}</span>
                        <span class="ms-2 activity-labor"><i class="fas fa-users text-info me-1"></i>${activity.labor_count||1}</span>
                        <span class="badge priority-${activity.priority||'medium'} ms-2">${(activity.priority||'medium').toUpperCase()}</span>
                    </div>
                    ${activity.description ? `<div class="activity-description mt-2">${escapeHtml(activity.description)}</div>` : ''}
                </div>
                <div class="btn-group ms-3">
                    <button class="btn btn-outline-primary btn-sm edit-btn" title="Edit" onclick="openModalInEditMode(${activity.id})">
                        <i class="fa fa-pen"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });
    html += `</div>`;
    document.getElementById('areaScheduleTabs').innerHTML = html;
}

// Modal logic
function openModalInAddMode() {
    isEditMode = false; editingActivityId = null;
    document.getElementById('modalIcon').className = 'fas fa-plus me-2';
    document.getElementById('modalTitle').textContent = 'Add New Activity';
    document.getElementById('submitText').textContent = 'Add Activity';
    document.getElementById('submitBtn').className = 'btn btn-primary';
    document.getElementById('deleteActivityBtn').classList.add('d-none');
    resetForm();
    populateBriefingsDropdown(BRIEFINGS_TODAY, true);
    activityModal.show();
}
function openModalInEditMode(id) {
    isEditMode = true; editingActivityId = id;
    const activity = CURRENT_ACTIVITIES.find(a => a.id == id);
    if (!activity) return alert('Activity not found.');
    document.getElementById('modalIcon').className = 'fas fa-edit me-2';
    document.getElementById('modalTitle').textContent = 'Edit Activity';
    document.getElementById('submitText').textContent = 'Save Changes';
    document.getElementById('submitBtn').className = 'btn btn-primary';
    document.getElementById('deleteActivityBtn').classList.remove('d-none');
    resetForm();
    document.getElementById('activity-id').value = activity.id;
    document.getElementById('activity-briefing_id').value = activity.briefing_id || '';
    document.getElementById('activity-time').value = activity.time || '';
    document.getElementById('activity-title').value = activity.title || '';
    document.getElementById('activity-area').value = activity.area || '';
    document.getElementById('activity-labor_count').value = activity.labor_count || 1;
    document.getElementById('activity-priority').value = activity.priority || 'medium';
    document.getElementById('activity-description').value = activity.description || '';
    document.getElementById('activity-assigned_to').value = activity.assigned_to || '';
    handleContractorsPopulation(activity.contractors || '');
    populateBriefingsDropdown(BRIEFINGS_ALL, false, activity.briefing_id);
    activityModal.show();
}
function resetForm() {
    document.getElementById('activityForm').reset();
    document.getElementById('activity-contractors-other-wrap').style.display = 'none';
    document.getElementById('activity-contractors-other').required = false;
}
function populateBriefingsDropdown(briefings, isAdd, selectedId) {
    const select = document.getElementById('activity-briefing_id');
    select.innerHTML = `<option value="" disabled selected>Select briefing...</option>`;
    (briefings||[]).forEach(briefing => {
        const opt = document.createElement('option');
        opt.value = briefing.id;
        opt.textContent = (briefing.overview || `Briefing ${briefing.id}`) + ' (' + formatUKDate(briefing.date) + ')';
        if (selectedId && selectedId == briefing.id) opt.selected = true;
        select.appendChild(opt);
    });
}
function handleContractorsDropdownChange() {
    const select = document.getElementById('activity-contractors');
    const otherWrap = document.getElementById('activity-contractors-other-wrap');
    const otherInput = document.getElementById('activity-contractors-other');
    if (select.value === '__other__') {
        otherWrap.style.display = 'block'; otherInput.required = true;
    } else {
        otherWrap.style.display = 'none'; otherInput.required = false; otherInput.value = '';
    }
}
function handleContractorsPopulation(contractorName) {
    const contractorsSelect = document.getElementById('activity-contractors');
    const otherWrap = document.getElementById('activity-contractors-other-wrap');
    const otherInput = document.getElementById('activity-contractors-other');
    let found = false;
    for (let i = 0; i < contractorsSelect.options.length; i++) {
        if (contractorsSelect.options[i].value === contractorName) {
            contractorsSelect.selectedIndex = i;
            found = true; otherWrap.style.display = 'none'; otherInput.value = ''; otherInput.required = false;
            break;
        }
    }
    if (!found && contractorName) {
        contractorsSelect.value = '__other__';
        otherInput.value = contractorName;
        otherWrap.style.display = 'block';
        otherInput.required = true;
    }
}

// Form submit (add/edit) - Always send correct id and date for update
function handleFormSubmission(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

    // Always force the correct id in the form for edit mode
    if (isEditMode && editingActivityId) {
        document.getElementById('activity-id').value = editingActivityId;
    }
    // Debug: Show what will be sent
    //console.log("Submit mode:", isEditMode, "Editing ID:", editingActivityId, "Form ID:", document.getElementById('activity-id').value);

    try {
        const formData = new FormData(document.getElementById('activityForm'));
        formData.append('action', isEditMode ? 'update' : 'add');

        // Always set date, for both add and update
        if (isEditMode) {
            const activity = CURRENT_ACTIVITIES.find(a => a.id == editingActivityId);
            if (activity && activity.date) formData.set('date', activity.date);
        } else {
            formData.set('date', getTodayISO());
        }

        // contractors field
        const contractorsSelect = document.getElementById('activity-contractors');
        const otherInput = document.getElementById('activity-contractors-other');
        if (contractorsSelect.value === '__other__') {
            if (!otherInput.value.trim()) throw new Error('Please enter contractor name.');
            formData.set('contractors', otherInput.value.trim());
        } else {
            formData.set('contractors', contractorsSelect.value);
        }

        fetch('ajax_activities.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) throw new Error(data.error || "Failed to save activity");
                activityModal.hide();
                loadActivitiesForDate(CURRENT_DATE);
            })
            .catch(e => showFormError(e.message))
            .finally(() => { submitBtn.disabled = false; submitBtn.innerHTML = `<i class="fas fa-save me-2"></i>${isEditMode ? 'Save Changes':'Add Activity'}`; });
    } catch (error) {
        showFormError(error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="fas fa-save me-2"></i>${isEditMode ? 'Save Changes':'Add Activity'}`;
    }
}
function showFormError(msg) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${escapeHtml(msg)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    const modalBody = document.querySelector('.modal-body');
    modalBody.insertBefore(alertDiv, modalBody.firstChild);
    setTimeout(() => { if (alertDiv.parentNode) alertDiv.remove(); }, 4000);
}

// Delete
function handleDeleteActivity(activityId) {
    if (!confirm('Are you sure you want to delete this activity?')) return;
    fetch('ajax_activities.php', { method:'POST', body: new URLSearchParams({ action: 'delete', id: activityId }) })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) throw new Error(data.error || "Delete failed");
            activityModal.hide();
            loadActivitiesForDate(CURRENT_DATE);
        })
        .catch(e => alert('Delete failed: ' + e.message));
}
</script>
</body>
</html>