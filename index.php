<?php
/**
 * =========================================================================================
 * index.php - Daily Activity Briefing System (DABS) Main Dashboard
 * =========================================================================================
 * DESCRIPTION:
 * This is the modern main dashboard for the Daily Activity Briefing System (DABS),
 * for UK construction site management. Includes:
 * - Project overview, activity scheduling, briefing management, weather, safety, notes, and subcontractors
 * - UK date/time formatting everywhere (DD/MM/YYYY HH:MM:SS, Europe/London timezone)
 * - Modern, responsive Bootstrap 5.3 UI, accessible, mobile-friendly
 * - Tabs for work areas (Block 1, Block 2, etc) auto-populated from resource locations
 * - Activities filtered by work area tab, or "All Areas"/"Unassigned"
 * - Assigned Contractors field is a searchable, multi-select dropdown (from live SQL, with "Other" option)
 * - All CRUD is real-time, no refresh, everything auto-updates
 * - Print/email briefing, debug, error handling, and more
 * - Fully commented for non-coders, modern code, no backend changes needed
 * =========================================================================================
 * AUTHOR: Chris Irlam
 * LAST UPDATED: 27/06/2025 (UK Date Format)
 * =========================================================================================
 */

date_default_timezone_set('Europe/London');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
function logError($message, $context = []) {
    $timestamp = date('d/m/Y H:i:s');
    $logMessage = "[{$timestamp}] {$message}";
    if (!empty($context)) $logMessage .= ' | Context: ' . json_encode($context);
    error_log($logMessage);
}
try {
    require_once 'includes/db_connect.php';
    require_once 'includes/functions.php';
    require_once 'includes/auth.php';
} catch (Exception $e) {
    logError('Critical Error: Unable to load required system files', ['error' => $e->getMessage()]);
    die('<!DOCTYPE html>
    <html><head><title>DABS System Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light">
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>System Error</h4>
            <p>Unable to load required system files. Please check file permissions and paths.</p>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p><strong>Time:</strong> ' . date('d/m/Y H:i:s') . ' (UK)</p>
            <p><strong>User:</strong> ' . htmlspecialchars($_SESSION['user_name'] ?? 'Unknown') . '</p>
            <p><strong>Please contact the system administrator for assistance.</strong></p>
        </div>
    </div></body></html>');
}
if (!isUserLoggedIn()) {
    logError('Unauthorized access attempt', [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp' => date('d/m/Y H:i:s'),
        'requested_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ]);
    header('Location: login.php');
    exit;
}
$currentDate = date('d/m/Y');
$currentTime = date('H:i');
$currentDateTime = date('d/m/Y H:i:s');
$currentDateISO = date('Y-m-d');
$projectID = 1;
$projectInfo = null;
$briefingData = null;
$subcontractors = [];
$workAreas = [];
$systemErrors = [];
try {
    $pdo = connectToDatabase();
    if (isset($_SESSION['current_project']) && $_SESSION['current_project'] > 0) {
        $projectID = intval($_SESSION['current_project']);
    }
    // Get project info
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectID]);
    $projectInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$projectInfo) {
        logError('No project found, creating default project', ['project_id' => $projectID]);
        $stmt = $pdo->prepare("INSERT INTO projects (id, name, location, manager, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $projectID,
            'Default Construction Project',
            'Construction Site',
            $_SESSION['user_name'] ?? 'System Administrator',
            $currentDateISO,
            date('Y-m-d', strtotime('+1 year')),
            'active'
        ]);
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectID]);
        $projectInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$projectInfo) {
        $projectInfo = [
            'id' => $projectID,
            'name' => 'Default Project',
            'location' => 'Construction Site',
            'manager' => $_SESSION['user_name'] ?? 'System Administrator',
            'start_date' => $currentDateISO,
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'status' => 'active'
        ];
    }
    // Get today's briefing
    $stmt = $pdo->prepare("SELECT * FROM briefings WHERE project_id = ? AND date = ?");
    $stmt->execute([$projectID, $currentDateISO]);
    $briefingData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$briefingData) {
        logError('No briefing found for today, creating new briefing', [
            'project_id' => $projectID,
            'date' => $currentDateISO
        ]);
        $stmt = $pdo->prepare("INSERT INTO briefings (project_id, date, overview, safety_info, notes, created_by, last_updated, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $projectID,
            $currentDateISO,
            'Daily briefing for ' . $currentDate,
            '<ul><li>Follow all standard safety protocols</li><li>Wear appropriate PPE at all times</li><li>Report any safety concerns immediately</li></ul>',
            'Daily briefing notes for ' . $currentDate,
            $_SESSION['user_id'] ?? 1,
            date('Y-m-d H:i:s'),
            'draft'
        ]);
        $briefingId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM briefings WHERE id = ?");
        $stmt->execute([$briefingId]);
        $briefingData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$briefingData) {
        $briefingData = [
            'id' => 0,
            'project_id' => $projectID,
            'date' => $currentDateISO,
            'overview' => 'Daily briefing for ' . $currentDate,
            'safety_info' => '<ul><li>Follow all standard safety protocols</li><li>Wear appropriate PPE at all times</li><li>Report any safety concerns immediately</li></ul>',
            'notes' => 'Daily briefing notes for ' . $currentDate,
            'created_by' => $_SESSION['user_id'] ?? 1,
            'last_updated' => date('Y-m-d H:i:s'),
            'status' => 'draft'
        ];
    }
    // Load subcontractors for this project for contractor dropdown
    try {
        $stmt = $pdo->prepare("SELECT name FROM dabs_subcontractors WHERE project_id = ? AND status = 'Active' GROUP BY name ORDER BY name ASC");
        $stmt->execute([$projectID]);
        $subcontractors = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        logError('Error loading subcontractors', ['error' => $e->getMessage()]);
        $subcontractors = [];
        $systemErrors[] = 'Unable to load subcontractors. Some features may be limited.';
    }
    // Load unique work areas from resources.location for this project
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT location FROM resources WHERE location IS NOT NULL AND location <> '' AND name IS NOT NULL AND project_id = ?");
        $stmt->execute([$projectID]);
        $workAreas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $workAreas = array_unique(array_filter(array_map('trim', $workAreas)));
        sort($workAreas, SORT_NATURAL | SORT_FLAG_CASE);
    } catch (PDOException $e) {
        logError('Error loading work areas', ['error' => $e->getMessage()]);
        $workAreas = [];
        $systemErrors[] = 'Unable to load work areas. Area tabs disabled.';
    }
} catch (Exception $e) {
    logError('Critical database error in index.php', [
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'user' => $_SESSION['user_name'] ?? 'Unknown',
        'timestamp' => $currentDateTime
    ]);
    $projectInfo = [
        'id' => $projectID,
        'name' => 'System Error - Project Data Unavailable',
        'location' => 'Unknown',
        'manager' => $_SESSION['user_name'] ?? 'System Administrator',
        'start_date' => $currentDateISO,
        'end_date' => date('Y-m-d', strtotime('+1 year')),
        'status' => 'error'
    ];
    $briefingData = [
        'id' => 0,
        'project_id' => $projectID,
        'date' => $currentDateISO,
        'overview' => 'System error - unable to load briefing data',
        'safety_info' => '<div class="alert alert-warning">System error - unable to load safety information. Please contact system administrator.</div>',
        'notes' => 'System error - unable to load notes. Please contact system administrator.',
        'created_by' => $_SESSION['user_id'] ?? 1,
        'last_updated' => date('Y-m-d H:i:s'),
        'status' => 'error'
    ];
    $subcontractors = [];
    $workAreas = [];
    $systemErrors[] = 'Database connection error. Some features may not work properly.';
}
$_SESSION['current_project'] = $projectID;
// Export subcontractors and work areas for JS
$jsContractors = json_encode($subcontractors, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
$jsWorkAreas = json_encode($workAreas, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for responsive design and character encoding -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DABS - <?php echo htmlspecialchars($projectInfo['name']); ?> - <?php echo $currentDate; ?></title>
    <!-- Bootstrap 5.3.0, Font Awesome, and other CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/dark-theme.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/weather.css" rel="stylesheet">
    <link href="css/subcontractors.css" rel="stylesheet">
    <link href="css/resource-cards.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/cx3e21j3t5yv0ukx72zuh02xf9o75o3bgencxrbbzmad1p5c/tinymce/5/tinymce.min.js"></script>
</head>
<body>
<!-- Main Title Bar and Notification Area -->
<div class="main-title-bar">
    <h1><i class="fas fa-clipboard-list me-3"></i>Daily Activity Briefing System</h1>
</div>
<div id="notificationArea"></div>
<?php if (!empty($systemErrors)): ?>
<div class="container-fluid">
    <?php foreach ($systemErrors as $error): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>System Notice:</strong> <?php echo htmlspecialchars($error); ?>
        <small class="d-block mt-1">Time: <?php echo $currentDateTime; ?> (UK)</small>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<!-- Debug Console removed for cleaner interface -->
<!-- Main Dashboard Container -->
<div class="container-fluid dashboard">
    <!-- Header Section: Logo | Date/Project | Controls -->
    <header class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="logo-container">
                    <img src="images/logo.png" alt="Company Logo" class="logo" onerror="this.style.display='none'">
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="date-display" id="currentDate"><?php echo $currentDate; ?></div>
                <div class="project-name">
                    <i class="fas fa-project-diagram me-2"></i>
                    Project: <span id="projectName"><?php echo htmlspecialchars($projectInfo['name']); ?></span>
                </div>
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Last updated: <span id="lastUpdated"><?php echo $currentDateTime; ?></span> (UK)
                </small>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group me-2" role="group">
                    <button id="emailBtn" class="btn btn-success btn-modern" title="Email briefing report" onclick="emailReport()">
                        <i class="fas fa-envelope me-1"></i> Email
                    </button>
                    <button id="printBtn" class="btn btn-info btn-modern" title="Print briefing report" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-secondary dropdown-toggle btn-modern" type="button" id="userMenu"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-1"></i> System
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <li><h6 class="dropdown-header">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown User'); ?>
                        </h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="javascript:location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh Page
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- WORK AREA TABS (Block 1, Block 2, etc. auto-generated from SQL) -->
    <div class="row mb-3">
        <div class="col-12">
            <nav>
                <ul class="nav nav-tabs" id="workAreaTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-area="all" href="#">All Areas</a>
                    </li>
                    <?php foreach($workAreas as $area): ?>
                    <li class="nav-item">
                        <a class="nav-link" data-area="<?php echo htmlspecialchars($area); ?>" href="#"><?php echo htmlspecialchars($area); ?></a>
                    </li>
                    <?php endforeach; ?>
                    <li class="nav-item">
                        <a class="nav-link" data-area="unassigned" href="#">Unassigned</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Resource Statistics Container (populated by js/resource-tracker.js) -->
    <div id="resourceStats" class="fade-in scroll-fade"></div>

    <!-- Activity Schedule Panel (Tab/Panel include for main activities list and modal) -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- Activities Header and Controls -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <h2 class="h4 mb-0"><i class="fas fa-calendar-alt me-2"></i>Activity Schedule</h2>
                    <span class="badge bg-light text-dark fs-6 uk-date" id="todayDate"><?php echo $currentDate; ?></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Add Activity Button -->
                    <button id="addActivityBtn" class="btn add-btn-gradient" title="Add New Activity for Today">
                        <span class="add-btn-glow"></span>
                        <i class="fas fa-plus me-2"></i>Add Activity
                    </button>
                    <div class="date-input-wrap">
                        <label for="activityDate" class="form-label mb-1 text-white">
                            <i class="fa-solid fa-calendar-days me-1"></i>View Date:
                        </label>
                        <input type="date" id="activityDate" class="form-control" value="<?php echo $currentDateISO; ?>">
                    </div>
                </div>
            </div>
            <!-- Activities List Container (dynamically updated by activities.js) -->
            <div id="activitiesList"></div>
        </div>
    </div>

    <!-- WEATHER FORECAST PANEL -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card weather-card fade-in scroll-fade">
                <div class="card-header">
                    <h2><i class="fas fa-cloud-sun me-2"></i> Weather Forecast</h2>
                    <small class="opacity-75">Construction site weather planning and safety considerations</small>
                </div>
                <div class="card-body" id="weatherWidget">
                    <div class="d-flex justify-content-center py-4">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading weather...</span></div>
                        <span class="ms-2">Loading weather forecast...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SAFETY INFORMATION PANEL -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card fade-in scroll-fade">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-exclamation-triangle me-2"></i> Safety Information</h2>
                        <small class="opacity-75">Important safety updates, protocols, and site-specific information</small>
                    </div>
                    <button id="editSafetyBtn" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit Safety Info
                    </button>
                </div>
                <div class="card-body">
                    <div id="safetyContainer">
                        <div id="safetyContent" class="safety-editor readonly">
                            <?php echo $briefingData['safety_info']; ?>
                        </div>
                        <div id="safetyEditContainer" style="display:none;">
                            <textarea id="safetyEditor" class="form-control safety-editor" 
                                    style="min-height:200px;" 
                                    placeholder="Enter safety information, protocols, and site-specific requirements..."></textarea>
                            <div class="mt-3 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger me-2" id="cancelSafetyBtn">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-warning" id="saveSafetyBtn">
                                    <i class="fas fa-save me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="safetyLoading" class="text-center py-4" style="display:none;">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Saving safety info...</span>
                        </div>
                        <div class="mt-2">Updating safety information...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	    <!-- NOTES & UPDATES PANEL -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card fade-in scroll-fade">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-sticky-note me-2"></i> Notes & Updates</h2>
                        <small class="opacity-75">Daily briefing notes, important updates, and project communication</small>
                    </div>
                    <div>
                        <button id="editNotesBtn" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit Notes
                        </button>
                        <button id="historyNotesBtn" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-history me-1"></i> History
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="notesContainer">
                        <div id="notesContent" class="notes-editor readonly">
                            <?php echo htmlspecialchars($briefingData['notes']); ?>
                        </div>
                        <div id="notesEditContainer" style="display:none;">
                            <textarea id="notesEditor" class="form-control notes-editor" 
                                style="min-height:200px"
                                placeholder="Enter daily briefing notes, project updates, and important information..."></textarea>
                            <div class="mt-3 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger me-2" id="cancelNotesBtn">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-primary" id="saveNotesBtn">
                                    <i class="fas fa-save me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                        <div class="notes-meta" id="notesMeta">
                            Last updated: <?php echo date('d/m/Y H:i:s', strtotime($briefingData['last_updated'])); ?> (UK)
                            by <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'System'); ?>
                        </div>
                    </div>
                    <div id="notesLoading" class="text-center py-4" style="display:none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Processing notes...</span>
                        </div>
                        <div class="mt-2">Updating notes...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NOTES HISTORY MODAL -->
    <div class="modal fade" id="notesHistoryModal" tabindex="-1" aria-labelledby="notesHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesHistoryModalLabel">
                        <i class="fas fa-history me-2"></i>Notes History
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="notesHistoryContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading history...</span>
                            </div>
                            <div class="mt-2">Loading notes history...</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SUBCONTRACTORS MANAGEMENT PANEL -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card subcontractor-card fade-in scroll-fade">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-users me-2"></i> Subcontractor Information</h2>
                        <small class="opacity-75">Track subcontractor status, tasks, and comprehensive contact management</small>
                    </div>
                    <button id="addSubcontractorBtn" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Subcontractor
                    </button>
                </div>
                <div class="card-body">
                    <div id="subcontractorDebug" class="alert alert-warning mb-3" style="display:none;">
                        <h6 class="mb-1"><strong><i class="fas fa-exclamation-triangle me-1"></i>Debugging Information</strong></h6>
                        <div id="subcontractorDebugContent"></div>
                    </div>
                    <div id="subcontractorAccordion" class="accordion">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading subcontractors...</span>
                            </div>
                            <div class="mt-2">Loading subcontractor information...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1">© 2025 DABS - Daily Activity Briefing System</p>
                    <small class="text-muted">Version 6.0.0 | Developed and Maintained By Chris Irlam</small>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1">Last updated: <span id="footerLastUpdated"><?php echo $currentDateTime; ?></span> (UK)</p>
                    <small class="text-muted">by <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown User'); ?></small>
                </div>
            </div>
        </div>
    </footer>
</div>

<!-- MODALS: Activities, Subcontractors, etc -->
<!-- Subcontractor Modal -->
<div id="activitiesList"></div>
<div class="modal fade" id="subcontractorModal" tabindex="-1" aria-labelledby="subcontractorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subcontractorModalLabel">
                    <i class="fas fa-user-tie me-2"></i>Add/Edit Subcontractor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalDebugArea" class="alert alert-danger mb-3" style="display:none;"></div>
                <form id="subcontractorForm">
                    <input type="hidden" id="subcontractorId" value="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="subcontractorName" class="form-label">
                                <i class="fas fa-building me-1"></i>Subcontractor Name *
                            </label>
                            <input type="text" class="form-control" id="subcontractorName" 
                                placeholder="e.g., ABC Construction Ltd" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label for="subcontractorTrade" class="form-label">
                                <i class="fas fa-hard-hat me-1"></i>Trade/Specialty *
                            </label>
                            <input type="text" class="form-control" id="subcontractorTrade" 
                                placeholder="e.g., Electrical, Plumbing, Roofing" required maxlength="100">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="contactName" class="form-label">
                                <i class="fas fa-user me-1"></i>Contact Name *
                            </label>
                            <input type="text" class="form-control" id="contactName" 
                                placeholder="Primary contact person" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label for="subcontractorStatus" class="form-label">
                                <i class="fas fa-flag me-1"></i>Status
                            </label>
                            <select class="form-select" id="subcontractorStatus">
                                <option value="Active">Active - Currently working</option>
                                <option value="Standby">Standby - Ready to work</option>
                                <option value="Offsite">Offsite - Not on location</option>
                                <option value="Delayed">Delayed - Behind schedule</option>
                                <option value="Complete">Complete - Work finished</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="contactPhone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Phone Number *
                            </label>
                            <input type="tel" class="form-control" id="contactPhone" 
                                placeholder="e.g., 07123 456789" required maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label for="contactEmail" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="contactEmail" 
                                placeholder="contact@company.com" maxlength="100">
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">
                                <i class="fas fa-tasks me-1"></i>Today's Tasks
                            </label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addTaskBtn">
                                <i class="fas fa-plus me-1"></i> Add Task
                            </button>
                        </div>
                        <div id="tasksContainer"></div>
                        <div class="text-muted small mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Add specific tasks that this subcontractor will complete today.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="deleteSubcontractorBtn">
                    <i class="fas fa-trash me-1"></i>Delete
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveSubcontractorBtn">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Activity Management Modal -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">
                    <i class="fas fa-calendar-plus me-2"></i>
                    <span id="activityModalTitle">Add New Activity</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="activityForm" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="activityId" name="id" value="">
                    <input type="hidden" id="activityBriefingId" name="briefing_id" value="<?php echo isset($briefingData['id']) ? $briefingData['id'] : '0'; ?>">
                    <input type="hidden" id="activityDate" name="date" value="<?php echo $currentDateISO; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="activityTime" class="form-label">
                                <i class="fas fa-clock me-1"></i>Scheduled Time <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="time" class="form-control" id="activityTime" name="time" value="08:00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activityPriority" class="form-label">
                                <i class="fas fa-flag me-1"></i>Priority Level
                            </label>
                            <select class="form-select" id="activityPriority" name="priority">
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                                <option value="critical">Critical Priority</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="activityTitle" class="form-label">
                                <i class="fas fa-heading me-1"></i>Activity Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="activityTitle" name="title" required 
                                placeholder="Brief description of the activity" maxlength="255">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="activityDescription" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Detailed Description
                            </label>
                            <textarea class="form-control" id="activityDescription" name="description" rows="3"
                                placeholder="Detailed description of what needs to be done (optional)"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="activityArea" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Construction Area
                            </label>
                            <input type="text" class="form-control" id="activityArea" name="area" 
                                placeholder="e.g., Building A, Site Access, East Wing" maxlength="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activityLaborCount" class="form-label">
                                <i class="fas fa-users me-1"></i>Workers Required
                            </label>
                            <input type="number" min="0" max="999" class="form-control" id="activityLaborCount" 
                                name="labor_count" value="1" placeholder="Number of workers needed">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="activityContractors" class="form-label">
                                <i class="fas fa-hard-hat me-1"></i>Assigned Contractors
                            </label>
                            <!-- Modern Select2 multi-select dropdown (populated via JS) -->
                            <select class="form-select" id="activityContractors" name="contractors[]" multiple="multiple" style="width:100%;"></select>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Hold Ctrl or use checkboxes to select multiple. If not listed, select "Other".
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activityAssignedTo" class="form-label">
                                <i class="fas fa-user-check me-1"></i>Assigned To
                            </label>
                            <input type="text" class="form-control" id="activityAssignedTo" name="assigned_to" 
                                placeholder="Person responsible for this activity" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="deleteActivityBtn" class="btn btn-danger me-auto" style="display:none;">
                        <i class="fas fa-trash me-1"></i> Delete Activity
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" id="saveActivityBtn" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Activity
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
	<!-- SCRIPTS: Bootstrap, Libraries, Global Variables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>	
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<script>
window.CURRENT_BRIEFING_ID = <?php echo isset($briefingData['id']) ? $briefingData['id'] : '20'; ?>;
window.CURRENT_PROJECT_ID = <?php echo $projectID; ?>;
window.CURRENT_DATE = '<?php echo $currentDateISO; ?>';
window.CURRENT_DATE_UK = '<?php echo $currentDate; ?>';
window.username = '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown'); ?>';
window.userRole = 'System Administrator';
window.briefingData = {
    id: <?php echo isset($briefingData['id']) ? $briefingData['id'] : '0'; ?>,
    project_id: <?php echo $projectID; ?>,
    date: '<?php echo $currentDateISO; ?>',
    status: '<?php echo isset($briefingData['status']) ? $briefingData['status'] : 'draft'; ?>',
    last_updated: '<?php echo isset($briefingData['last_updated']) ? $briefingData['last_updated'] : date('Y-m-d H:i:s'); ?>'
};
window.dabsContractors = <?php echo $jsContractors ?: "[]"; ?>;
window.dabsWorkAreas = <?php echo $jsWorkAreas ?: "[]"; ?>;
</script>

<!-- All other scripts: Notes, Safety, Resource Tracker, Weather, Email, Contractor Breakdown, Subcontractors -->

<script src="js/weather.js"></script>
<script src="js/notes.js" defer></script>
<script src="js/safety.js" defer></script>
<script src="js/resource-tracker.js" defer></script>
<script src="js/email-report.js"></script>
<script src="js/contractor-daily-breakdown.js" defer></script>
<script src="js/subcontractors.js" defer></script>

<!-- ================= MODERN ACTIVITIES SCRIPT =================== -->
<script>
// Fallback for debug, debugError, and showNotification to prevent "not defined" errors
if (typeof debug !== "function") {
    function debug() {}
}
if (typeof debugError !== "function") {
    function debugError() {}
}
if (typeof showNotification !== "function") {
    function showNotification(msg, type) { alert((type ? type.toUpperCase() + ": " : "") + msg); }
}

/**
 * ===========================================================================
 * Modern DABS Activity Schedule Handler (UK Construction)
 * Fully fixes: duplicate rendering, delete, add/edit auto-refresh, edit bug!
 * Adds: Area tabs, Select2 contractors dropdown with "Other" option.
 * Last updated: 27/06/2025 (by Copilot for Chris Irlam)
 * ===========================================================================
 */

// Helper: Format date in UK format (DD/MM/YYYY)
function formatUKDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleDateString('en-GB');
}
// Escape HTML for security (prevents XSS!)
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// ================= WORK AREA TABS =================
// Store current area selection
let currentArea = 'all';

// Add click handlers for area tabs to filter activities by area
document.addEventListener('DOMContentLoaded', function() {
    const areaTabs = document.querySelectorAll('#workAreaTabs .nav-link');
    areaTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            areaTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentArea = this.getAttribute('data-area');
            loadActivitiesForDate(document.getElementById('activityDate').value);
        });
    });
});

// =============== ACTIVITIES DISPLAY/CRUD ================
function activityMatchesArea(activity) {
    if (currentArea === 'all') return true;
    if (currentArea === 'unassigned') return !activity.area || !activity.area.trim();
    return (activity.area && activity.area.trim() === currentArea);
}

// Load and display activities (ALWAYS clears list first, filters by area tab)
function loadActivitiesForDate(dateISO) {
    debug('[DABS] Loading activities for date', {date: dateISO, area: currentArea});
    const list = document.getElementById('activitiesList');
    if (list) list.innerHTML = '';
    fetch('ajax_activities.php?action=list&date=' + encodeURIComponent(dateISO))
        .then(r => r.json())
        .then(data => {
            debug('[DABS] API Response: list', data);
            if (!list) return;
            if (!data.ok || !Array.isArray(data.activities)) {
                list.innerHTML = '<div class="alert alert-danger">Failed to load activities for this date.</div>';
                return;
            }
            let filtered = data.activities.filter(activityMatchesArea);
            if (filtered.length === 0) {
                list.innerHTML = '<div class="alert alert-info">No activities scheduled for this area/date.</div>';
                return;
            }
            list.innerHTML = '';
            filtered.forEach(activity => {
                const priorityClass = 'priority-' + (activity.priority || 'medium');
                const time = activity.time ? activity.time.slice(0,5) : '';
                let contractorsDisplay = "";
                if (activity.contractors) {
                    let arr = activity.contractors.split(",");
                    contractorsDisplay = arr.map(escapeHtml).join(", ");
                }
                const html = `
                <div class="activity-item mb-3 ${priorityClass}" data-id="${activity.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${escapeHtml(activity.title)}</strong>
                            <span class="badge bg-light text-dark ms-2">${formatUKDate(activity.date)} ${escapeHtml(time)}</span>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm me-2" onclick="editActivity(${activity.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                    </div>
                    <div class="mt-2">${escapeHtml(activity.description || '')}</div>
                    <div class="small text-muted mt-1">
                        Area: ${escapeHtml(activity.area || 'N/A')}, 
                        Workers: ${escapeHtml(activity.labor_count || '1')}, 
                        Contractors: ${contractorsDisplay || 'N/A'}, 
                        Assigned to: ${escapeHtml(activity.assigned_to || 'N/A')}
                    </div>
                    <span class="badge bg-secondary">${activity.priority ? activity.priority.charAt(0).toUpperCase() + activity.priority.slice(1) : 'Medium'} Priority</span>
                </div>
                `;
                list.innerHTML += html;
            });
        })
        .catch(err => {
            debugError("[DABS] Failed to load activities", err);
            if (list) list.innerHTML = '<div class="alert alert-danger">Error loading activities.</div>';
        });
}

// ========== CONTRACTORS DROPDOWN (SELECT2) ==========

function setupContractorsDropdown(selected) {
    // selected: array of contractor names to pre-select (e.g. from DB, comma separated)
    let options = (window.dabsContractors || []).map(function(c) {
        return {id: c, text: c};
    });
    // Always add "Other" at the end if not already in list
    if (!options.some(opt => opt.text === "Other")) {
        options.push({id: "Other", text: "Other"});
    }
    $('#activityContractors').empty();
    options.forEach(opt => {
        const optionElem = new Option(opt.text, opt.id, false, false);
        $('#activityContractors').append(optionElem);
    });
    $('#activityContractors').select2({
        placeholder: "Select contractor(s)...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#activityModal')
    });
    if (selected && selected.length > 0) {
        $('#activityContractors').val(selected).trigger('change');
    } else {
        $('#activityContractors').val(null).trigger('change');
    }
}

// ========== ACTIVITY MODAL: ADD/EDIT/DELETE ==========
function openActivityModal(mode, activity = {}) {
    document.getElementById('activityId').value = activity.id || '';
    document.getElementById('activityTitle').value = activity.title || '';
    document.getElementById('activityTime').value = activity.time || '08:00';
    document.getElementById('activityPriority').value = activity.priority || 'medium';
    document.getElementById('activityDescription').value = activity.description || '';
    document.getElementById('activityArea').value = activity.area || '';
    document.getElementById('activityLaborCount').value = activity.labor_count || '1';
    document.getElementById('activityAssignedTo').value = activity.assigned_to || '';
    document.getElementById('deleteActivityBtn').style.display = mode === 'edit' ? 'block' : 'none';
    document.getElementById('activityModalTitle').textContent = mode === 'edit' ? 'Edit Activity' : 'Add New Activity';
    // Contractors: parse (could be CSV string or array), always use array for select2
    let selContractors = [];
    if (activity.contractors) {
        if (Array.isArray(activity.contractors)) selContractors = activity.contractors;
        else selContractors = activity.contractors.split(',').map(s => s.trim()).filter(Boolean);
    }
    setupContractorsDropdown(selContractors);
    new bootstrap.Modal(document.getElementById('activityModal')).show();
}
// Edit button handler
window.editActivity = function(id) {
    fetch('ajax_activities.php?action=get&id=' + encodeURIComponent(id))
        .then(r => r.json())
        .then(data => {
            if (data.ok && data.activity) {
                openActivityModal('edit', data.activity);
            } else {
                showNotification('Failed to load activity.', 'danger');
            }
        });
};
// Add button handler
document.getElementById('addActivityBtn').onclick = function() {
    openActivityModal('add');
};
// Save handler (always sends action=add or action=update)
document.getElementById('activityForm').onsubmit = function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const isEdit = !!formData.get('id');
    // Contractors: collect from select2 and join as comma-separated
    const contractors = $('#activityContractors').val() || [];
    formData.delete('contractors[]');
    formData.set('contractors', contractors.join(','));
    // Always set the correct action for backend (add or update)
    formData.set('action', isEdit ? 'update' : 'add');
    fetch('ajax_activities.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showNotification('Activity ' + (isEdit ? 'updated' : 'added') + ' successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('activityModal')).hide();
            loadActivitiesForDate(document.getElementById('activityDate').value);
        } else {
            showNotification(data.error || 'Failed to save activity.', 'danger');
        }
    })
    .catch(err => {
        showNotification('Error saving activity.', 'danger');
        debugError("Save activity failed", err);
    });
};
// Delete handler
document.getElementById('deleteActivityBtn').onclick = function() {
    const id = document.getElementById('activityId').value;
    if (!id) return;
    if (!confirm('Are you sure you want to delete this activity?')) return;
    fetch('ajax_activities.php', {
        method: 'POST',
        body: new URLSearchParams({action: 'delete', id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showNotification('Activity deleted.', 'success');
            bootstrap.Modal.getInstance(document.getElementById('activityModal')).hide();
            loadActivitiesForDate(document.getElementById('activityDate').value);
        } else {
            showNotification(data.error || 'Failed to delete activity.', 'danger');
        }
    })
    .catch(err => {
        showNotification('Error deleting activity.', 'danger');
        debugError("Delete activity failed", err);
    });
};
// Reload activities when date changes
document.getElementById('activityDate').onchange = function() {
    loadActivitiesForDate(this.value);
};
// Initial load (also on DOM ready)
document.addEventListener('DOMContentLoaded', function() {
    loadActivitiesForDate(document.getElementById('activityDate').value);
    
    // Scroll animation for cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.scroll-fade').forEach(el => {
        observer.observe(el);
    });
});
</script>
<!-- END Modernised DABS index.php (19/11/2025) -->
</body>
</html>