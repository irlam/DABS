<?php
/**
 * =========================================================================
 * admin_panel.php - Daily Activity Briefing System (DABS) Admin Panel
 * =========================================================================
 * 
 * DESCRIPTION:
 * Admin-only page for managing users, viewing logs, and configuring email settings.
 * Only accessible by users with 'admin' role.
 * 
 * FEATURES:
 * - User management (create, edit, delete users)
 * - Log viewer (read logs from logs folder)
 * - Email settings configuration and testing
 * 
 * AUTHOR: System
 * CREATED: 20/11/2025 (UK Date Format)
 * =========================================================================
 */

date_default_timezone_set('Europe/London');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check if user has admin role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('<!DOCTYPE html>
    <html><head><title>Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light">
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
            <p>You do not have permission to access this page. Only administrators can access the admin panel.</p>
            <a href="index.php" class="btn btn-primary">Return to Dashboard</a>
        </div>
    </div></body></html>');
}

$currentDate = date('d/m/Y');
$currentDateTime = date('d/m/Y H:i:s');

try {
    $pdo = connectToDatabase();
} catch (Exception $e) {
    die('Database connection error: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DABS - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="css/styles.css" rel="stylesheet">
    <style>
        .admin-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .admin-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            color: white;
        }
        .admin-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .log-viewer {
            background: #1e1e1e;
            color: #d4d4d4;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 15px;
            border-radius: 5px;
            max-height: 500px;
            overflow-y: auto;
        }
        .log-line {
            padding: 2px 0;
            border-bottom: 1px solid #2d2d2d;
        }
        .nav-tabs .nav-link.active {
            background-color: #667eea;
            color: white;
            border-color: #667eea;
        }
        .test-result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="admin-panel">
    <div class="container-fluid">
        <!-- Header -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-user-shield me-2"></i>Admin Panel</h1>
                    <p class="mb-0">System Administration & Configuration</p>
                </div>
                <div class="text-end">
                    <div class="mb-2">
                        <strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </div>
                    <div class="mb-2">
                        <strong>Time:</strong> <?php echo $currentDateTime; ?> (UK)
                    </div>
                    <a href="index.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" 
                        type="button" role="tab">
                    <i class="fas fa-users me-2"></i>User Management
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" 
                        type="button" role="tab">
                    <i class="fas fa-file-alt me-2"></i>Log Viewer
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" 
                        type="button" role="tab">
                    <i class="fas fa-envelope me-2"></i>Email Settings
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="adminTabContent">
            <!-- User Management Tab -->
            <div class="tab-pane fade show active" id="users" role="tabpanel">
                <div class="card admin-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>User Management</h5>
                        <button class="btn btn-light btn-sm" id="addUserBtn">
                            <i class="fas fa-plus me-1"></i>Add New User
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="usersTable">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading users...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Viewer Tab -->
            <div class="tab-pane fade" id="logs" role="tabpanel">
                <div class="card admin-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>System Logs</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="logFileSelect" class="form-label">Select Log File:</label>
                                <select class="form-select" id="logFileSelect">
                                    <option value="">-- Select a log file --</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button class="btn btn-primary me-2" id="loadLogBtn">
                                    <i class="fas fa-sync me-1"></i>Load Log
                                </button>
                                <button class="btn btn-secondary" id="refreshLogsBtn">
                                    <i class="fas fa-redo me-1"></i>Refresh List
                                </button>
                            </div>
                        </div>
                        <div id="logContent">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Select a log file from the dropdown and click "Load Log" to view its contents.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings Tab -->
            <div class="tab-pane fade" id="email" role="tabpanel">
                <div class="card admin-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Current Configuration:</strong> The system uses PHP's built-in mail() function.
                            For better reliability, consider configuring SMTP settings.
                        </div>
                        
                        <form id="emailConfigForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emailFrom" class="form-label">From Email Address</label>
                                    <input type="email" class="form-control" id="emailFrom" 
                                           value="no-reply@defecttracker.uk" required>
                                    <small class="form-text text-muted">Email address that appears as sender</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="emailFromName" class="form-label">From Name</label>
                                    <input type="text" class="form-control" id="emailFromName" 
                                           value="DABS" required>
                                    <small class="form-text text-muted">Name that appears as sender</small>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="mb-3">Test Email Functionality</h6>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="testEmailAddress" class="form-label">Test Email Address</label>
                                    <input type="email" class="form-control" id="testEmailAddress" 
                                           placeholder="Enter email to test" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane me-1"></i>Send Test Email
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div id="emailTestResult"></div>
                        
                        <hr class="my-4">
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Troubleshooting Email Issues</h6>
                            <ul class="mb-0">
                                <li>Check that your server has mail() function enabled</li>
                                <li>Verify SPF/DKIM records are configured for your domain</li>
                                <li>Check spam folders - emails may be filtered</li>
                                <li>Review email logs in the Log Viewer tab (email_log.txt)</li>
                                <li>Consider using SMTP instead of mail() for better deliverability</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId" name="userId">
                    <div class="mb-3">
                        <label for="userName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="userName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="userUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="userUsername" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="userPassword" name="password">
                        <small class="form-text text-muted">Leave blank to keep existing password (when editing)</small>
                    </div>
                    <div class="mb-3">
                        <label for="userRole" class="form-label">Role</label>
                        <select class="form-select" id="userRole" name="role" required>
                            <option value="user">User</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">Save User</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin-panel.js"></script>
</body>
</html>
