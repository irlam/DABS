<?php
/**
 * =========================================================================
 * FILE: ajax_subcontractors.php
 * DESCRIPTION:
 *   Backend API for DABS Subcontractor Management. Handles all CRUD actions,
 *   project-based filtering, and returns JSON for the frontend. This debug version
 *   logs extra information and returns diagnostics with each response to help
 *   resolve empty dropdown issues.
 * AUTHOR: irlam
 * LAST UPDATED: 18/06/2025 12:15:00 (UK Time)
 * VERSION: 5.0.0-debug - Extra diagnostics for support
 * =========================================================================
 */

// UK timezone
date_default_timezone_set('Europe/London');
require_once __DIR__ . '/includes/db_connect.php';
ob_start();
session_start();

// Debug log file
$log_file = __DIR__ . '/logs/subcontractor_debug.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
function write_log($message, $data = null) {
    global $log_file;
    $timestamp = date('d/m/Y H:i:s');
    $log_entry = "[$timestamp] $message";
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_entry .= ': ' . print_r($data, true);
        } else {
            $log_entry .= ': ' . $data;
        }
    }
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}
function send_json($data) {
    if (ob_get_length()) ob_clean();
    $data['timestamp_uk'] = date('d/m/Y H:i:s');
    $data['server_time'] = date('c');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    write_log('Sending JSON response', [
        'action' => $data['action'] ?? 'unknown',
        'success' => $data['ok'] ?? ($data['error'] ? false : true),
        'response_size' => strlen(json_encode($data))
    ]);
    exit;
}

// ==== DEBUG LOG: Session and Environment ====
write_log('=== NEW SUBCONTRACTOR API REQUEST STARTED ===');
write_log('Request details', [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'timestamp' => date('d/m/Y H:i:s')
]);
write_log('GET parameters', $_GET);
write_log('POST parameters', array_keys($_POST)); // Log keys only for security
write_log('SESSION array', $_SESSION);

// ==== AUTH ====
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    write_log('Authentication failed - user not logged in', [
        'session_id' => session_id(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    send_json([
        'ok' => false,
        'error' => 'Authentication required',
        'redirect' => 'login.php',
        'error_code' => 'AUTH_REQUIRED',
        'message' => 'Please log in to access subcontractor management'
    ]);
}

// ==== PROJECT CONTEXT ====
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;
// DEBUG: Also allow overriding project_id via GET for testing
if (isset($_GET['debug_project_id'])) {
    $project_id = intval($_GET['debug_project_id']);
}
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'unknown';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;
write_log('User context established', [
    'username' => $username,
    'user_id' => $user_id,
    'project_id' => $project_id
]);

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
write_log('Action requested', [
    'action' => $action,
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
]);

// ==== DB CONNECT ====
try {
    $pdo = connectToDatabase();
    write_log('Database connection established successfully', [
        'connection_type' => 'centralized_include',
        'timestamp' => date('d/m/Y H:i:s')
    ]);
} catch (Exception $e) {
    write_log('Database connection failed', [
        'error' => $e->getMessage(),
        'connection_type' => 'centralized_include'
    ]);
    send_json([
        'ok' => false,
        'error' => 'Database connection failed',
        'error_code' => 'DB_CONNECTION_ERROR',
        'message' => 'Unable to connect to the database server',
        'details' => 'Please check database configuration and try again'
    ]);
}

// ========================================================================
// LIST ACTION: Return all subs for current project, with debug info
// ========================================================================
if ($action === 'list') {
    write_log('Processing LIST action for project', $project_id);
    $debug = [];
    try {
        $sql = "SELECT id, name, trade, contact_name, phone, email, status, created_by, created_at, updated_at 
                FROM dabs_subcontractors 
                WHERE project_id = ? 
                ORDER BY 
                    CASE status 
                        WHEN 'Active' THEN 1 
                        WHEN 'Standby' THEN 2 
                        WHEN 'Delayed' THEN 3 
                        WHEN 'Complete' THEN 4 
                        WHEN 'Offsite' THEN 5 
                        ELSE 6 
                    END, 
                    name ASC";
        $debug['sql'] = $sql;
        $debug['project_id'] = $project_id;
        $debug['session_project_id'] = $_SESSION['current_project'] ?? '(not set)';
        $debug['session'] = $_SESSION;
        $subcontractors = fetchAll($sql, [$project_id]);
        $debug['subcontractors_count'] = is_array($subcontractors) ? count($subcontractors) : 0;
        if ($subcontractors === false) {
            throw new Exception('Failed to retrieve subcontractors from database');
        }
        foreach ($subcontractors as &$sub) {
            $sub['contacts'] = [];
            if ($sub['created_at']) {
                $sub['created_at_uk'] = date('d/m/Y H:i:s', strtotime($sub['created_at']));
            }
            if ($sub['updated_at']) {
                $sub['updated_at_uk'] = date('d/m/Y H:i:s', strtotime($sub['updated_at']));
            }
            if ($sub['contact_name'] && substr($sub['contact_name'], 0, 1) === '[') {
                try {
                    $contactNames = json_decode($sub['contact_name'], true) ?: [];
                    $contactPhones = json_decode($sub['phone'], true) ?: [];
                    $contactEmails = json_decode($sub['email'], true) ?: [];
                    $maxContacts = max(count($contactNames), count($contactPhones), count($contactEmails));
                    for ($i = 0; $i < $maxContacts; $i++) {
                        $sub['contacts'][] = [
                            'name' => $contactNames[$i] ?? '',
                            'phone' => $contactPhones[$i] ?? '',
                            'email' => $contactEmails[$i] ?? ''
                        ];
                    }
                } catch (Exception $e) {
                    $sub['contacts'][] = [
                        'name' => $sub['contact_name'] ?? '',
                        'phone' => $sub['phone'] ?? '',
                        'email' => $sub['email'] ?? ''
                    ];
                }
            } else {
                $sub['contacts'][] = [
                    'name' => $sub['contact_name'] ?? '',
                    'phone' => $sub['phone'] ?? '',
                    'email' => $sub['email'] ?? ''
                ];
            }
            $sub['status'] = $sub['status'] ?: 'Active';
            $sub['trade'] = $sub['trade'] ?: '';
            $sub['created_by'] = $sub['created_by'] ?: '';
        }
        $debug['subcontractors_sample'] = array_slice($subcontractors, 0, 2);
        send_json([
            'ok' => true,
            'action' => 'list',
            'subcontractors' => $subcontractors,
            'count' => count($subcontractors),
            'project_id' => $project_id,
            'debug' => $debug,
            'message' => 'Subcontractors retrieved successfully'
        ]);
    } catch (Exception $e) {
        write_log('Error in LIST action', [
            'error' => $e->getMessage(),
            'project_id' => $project_id
        ]);
        $debug['error'] = $e->getMessage();
        send_json([
            'ok' => false,
            'action' => 'list',
            'error' => 'Failed to retrieve subcontractors',
            'error_code' => 'LIST_ERROR',
            'message' => 'Unable to retrieve subcontractors',
            'details' => $e->getMessage(),
            'debug' => $debug
        ]);
    }
}

// ====================
// ...Rest of the file is unchanged (get, add, update, delete, fallback)
// ====================
// Copy the rest of your production ajax_subcontractors.php here (from your previous version)
// ... [keep all the code for get, add, update, delete, and fallback actions unchanged]
?>