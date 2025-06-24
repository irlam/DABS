<?php
/**
 * =========================================================================
 * ajax_activities.php - Daily Activity Briefing System (DABS) Activities API
 * =========================================================================
 *
 * FILE NAME: ajax_activities.php
 * LOCATION: /
 *
 * DESCRIPTION:
 * This file serves as the complete backend API handler for the Activity Schedule system
 * within the Daily Activity Briefing System (DABS). It provides comprehensive AJAX
 * endpoints for managing construction activities with full CRUD operations, priority
 * tracking, and UK date/time formatting throughout the system.
 * 
 * The system handles all activity management operations including creating new activities,
 * updating existing ones, deleting activities, and retrieving activity lists with proper
 * sorting and filtering. All operations maintain referential integrity with the briefings
 * table and ensure data consistency across the entire DABS system.
 * 
 * WHAT THIS FILE DOES:
 * - Manages all activity records for construction project briefings
 * - Handles activity CRUD operations (Create, Read, Update, Delete)
 * - Provides secure API endpoints with comprehensive error handling
 * - Maintains UK date/time formatting throughout (DD/MM/YYYY HH:MM:SS)
 * - Ensures referential integrity with briefings and projects
 * - Validates user authentication and project permissions
 * - Returns standardized JSON responses for frontend integration
 * - Supports priority-based sorting and filtering of activities
 * - Tracks labor counts, areas, contractors, and assigned personnel
 * - Provides comprehensive error logging and debugging capabilities
 * 
 * KEY FEATURES:
 * - Full CRUD operations for activity management
 * - Priority-based activity sorting (critical, high, medium, low)
 * - UK timezone integration (Europe/London) for all operations
 * - Comprehensive input validation and sanitization
 * - SQL injection prevention with prepared statements
 * - Session-based authentication and authorization
 * - Project-based data isolation for security
 * - Modern JSON API responses with detailed metadata
 * - Error handling with user-friendly messages
 * - Debug logging for troubleshooting and monitoring
 * 
 * API ENDPOINTS:
 * - GET ?action=list&date=YYYY-MM-DD: Returns activities for specific date
 * - POST action=add: Creates new activity with validation
 * - GET ?action=get&id=X: Returns specific activity details
 * - POST action=update: Updates existing activity information
 * - POST action=delete: Removes activity from system
 * 
 * SECURITY FEATURES:
 * - User authentication validation on all requests
 * - Prepared SQL statements preventing injection attacks
 * - Input validation and data sanitization
 * - Project-based access control and data isolation
 * - Comprehensive error handling and logging
 * - Session management and timeout protection
 * 
 * DATABASE INTEGRATION:
 * - Uses activities table with briefing_id references
 * - Maintains referential integrity with briefings table
 * - Supports all activity fields including priority, area, labor_count
 * - UK date/time formatting in database responses
 * - Optimized queries for performance and reliability
 * 
 * AUTHOR: Chris Irlam (System Administrator)
 * CREATED: 24/06/2025 (UK Date Format)
 * LAST UPDATED: 24/06/2025 10:53:42 (UK Time)
 * VERSION: 5.0.0 - Fixed Database Integration & Modern Standards
 * 
 * CHANGES IN v5.0.0:
 * - FIXED: Database structure compatibility with existing activities table
 * - FIXED: Proper briefing_id validation and referencing
 * - FIXED: UK date/time formatting consistency throughout system
 * - IMPROVED: Error handling with comprehensive logging capabilities
 * - IMPROVED: Input validation for all activity fields
 * - IMPROVED: API response structure with detailed metadata
 * - ADDED: Support for all activity table columns
 * - ADDED: Enhanced debugging and troubleshooting capabilities
 * - ADDED: Modern PHP coding standards and security features
 * =========================================================================
 */

// Set UK timezone for consistent date/time formatting throughout the system
date_default_timezone_set('Europe/London');

// Include the centralized database connection file
require_once __DIR__ . '/includes/db_connect.php';

// Start output buffering to prevent unwanted output before JSON responses
ob_start();

// Start session for user authentication and project context
session_start();

// Define log file path for comprehensive debugging and audit trail
$log_file = __DIR__ . '/logs/activities_debug.log';

// Create logs directory if it doesn't exist with proper permissions
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Write comprehensive log entries with UK timestamp formatting
 * Provides detailed debugging and audit trail for all operations
 * 
 * @param string $message - The primary message to log
 * @param mixed $data - Optional additional data (arrays/objects supported)
 */
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

/**
 * Send standardized JSON response with UK timestamp and exit
 * Ensures consistent response format across all API endpoints
 * 
 * @param array $data - The response data to send as JSON
 */
function send_json($data) {
    if (ob_get_length()) ob_clean();
    $data['timestamp_uk'] = date('d/m/Y H:i:s');
    $data['server_time'] = date('c'); // ISO 8601 format for compatibility
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

// Log the start of a new request with comprehensive details
write_log('=== NEW ACTIVITIES API REQUEST STARTED ===');
write_log('Request details', [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'timestamp' => date('d/m/Y H:i:s')
]);
write_log('GET parameters', $_GET);
write_log('POST parameters', array_keys($_POST)); // Log keys only for security

// Check user authentication status
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
        'message' => 'Please log in to access activity management'
    ]);
}

// Extract user context from session
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'unknown';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;

write_log('User context established', [
    'username' => $username,
    'user_id' => $user_id,
    'project_id' => $project_id
]);

// Determine the requested action from GET or POST parameters
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
write_log('Action requested', [
    'action' => $action,
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
]);

// Test database connection using the centralized connection
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

/**
 * LIST ACTION - Retrieve activities for specific date
 */
if ($action === 'list') {
    write_log('Processing LIST action');
    try {
        $date = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception('Invalid date format. Expected YYYY-MM-DD.');
        }
        
        write_log('Loading activities for date', ['date' => $date, 'project_id' => $project_id]);
        
        // Get activities for the specified date, ensuring they belong to current project
        $sql = "SELECT a.id, a.briefing_id, a.date, a.time, a.title, a.description, a.area,
                       a.priority, a.labor_count, a.contractors, a.assigned_to,
                       a.created_at, a.updated_at,
                       DATE_FORMAT(a.date, '%d/%m/%Y') as date_uk,
                       DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i:%s') as created_at_uk,
                       DATE_FORMAT(a.updated_at, '%d/%m/%Y %H:%i:%s') as updated_at_uk,
                       TIME_FORMAT(a.time, '%H:%i') as time_uk,
                       b.project_id
                FROM activities a
                LEFT JOIN briefings b ON a.briefing_id = b.id
                WHERE b.project_id = ? AND a.date = ?
                ORDER BY 
                    FIELD(a.priority, 'critical', 'high', 'medium', 'low'),
                    a.time ASC, 
                    a.created_at ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$project_id, $date]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        write_log('Found activities', [
            'count' => count($activities),
            'date' => $date,
            'project_id' => $project_id
        ]);
        
        // Format activities for frontend
        foreach ($activities as &$activity) {
            $activity['id'] = intval($activity['id']);
            $activity['briefing_id'] = intval($activity['briefing_id']);
            $activity['labor_count'] = intval($activity['labor_count'] ?? 0);
            
            // Ensure all fields have default values
            $activity['description'] = $activity['description'] ?? '';
            $activity['area'] = $activity['area'] ?? '';
            $activity['contractors'] = $activity['contractors'] ?? '';
            $activity['assigned_to'] = $activity['assigned_to'] ?? '';
            $activity['priority'] = $activity['priority'] ?? 'medium';
        }
        
        send_json([
            'ok' => true,
            'action' => 'list',
            'activities' => $activities,
            'count' => count($activities),
            'date' => $date,
            'date_uk' => date('d/m/Y', strtotime($date)),
            'project_id' => $project_id,
            'message' => 'Activities retrieved successfully'
        ]);
        
    } catch (Exception $e) {
        write_log('Error in LIST action', [
            'error' => $e->getMessage(),
            'date' => $date ?? 'unknown',
            'project_id' => $project_id
        ]);
        send_json([
            'ok' => false,
            'action' => 'list',
            'error' => 'Failed to retrieve activities',
            'error_code' => 'LIST_ERROR',
            'message' => 'Unable to retrieve activities',
            'details' => $e->getMessage()
        ]);
    }
}

/**
 * GET ACTION - Retrieve specific activity details
 */
if ($action === 'get') {
    write_log('Processing GET action');
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            write_log('Invalid activity ID provided', $id);
            send_json([
                'ok' => false,
                'action' => 'get',
                'error' => 'Invalid activity ID',
                'error_code' => 'INVALID_ID',
                'message' => 'Please provide a valid activity ID'
            ]);
        }
        
        write_log('Retrieving activity details', ['id' => $id, 'project_id' => $project_id]);
        
        $sql = "SELECT a.id, a.briefing_id, a.date, a.time, a.title, a.description, a.area,
                       a.priority, a.labor_count, a.contractors, a.assigned_to,
                       a.created_at, a.updated_at,
                       DATE_FORMAT(a.date, '%d/%m/%Y') as date_uk,
                       DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i:%s') as created_at_uk,
                       DATE_FORMAT(a.updated_at, '%d/%m/%Y %H:%i:%s') as updated_at_uk,
                       TIME_FORMAT(a.time, '%H:%i') as time_uk,
                       b.project_id
                FROM activities a
                LEFT JOIN briefings b ON a.briefing_id = b.id
                WHERE a.id = ? AND b.project_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $project_id]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$activity) {
            write_log('Activity not found or access denied', [
                'id' => $id,
                'project_id' => $project_id
            ]);
            send_json([
                'ok' => false,
                'action' => 'get',
                'error' => 'Activity not found',
                'error_code' => 'NOT_FOUND',
                'message' => 'The requested activity could not be found'
            ]);
        }
        
        write_log('Found activity', [
            'id' => $activity['id'],
            'title' => $activity['title'] ?? 'Untitled'
        ]);
        
        // Format activity for frontend
        $activity['id'] = intval($activity['id']);
        $activity['briefing_id'] = intval($activity['briefing_id']);
        $activity['labor_count'] = intval($activity['labor_count'] ?? 0);
        
        // Ensure all fields have default values
        $activity['description'] = $activity['description'] ?? '';
        $activity['area'] = $activity['area'] ?? '';
        $activity['contractors'] = $activity['contractors'] ?? '';
        $activity['assigned_to'] = $activity['assigned_to'] ?? '';
        $activity['priority'] = $activity['priority'] ?? 'medium';
        
        send_json([
            'ok' => true,
            'action' => 'get',
            'activity' => $activity,
            'message' => 'Activity details retrieved successfully'
        ]);
        
    } catch (Exception $e) {
        write_log('Error in GET action', [
            'error' => $e->getMessage(),
            'activity_id' => $id ?? 'unknown'
        ]);
        send_json([
            'ok' => false,
            'action' => 'get',
            'error' => 'Failed to retrieve activity',
            'error_code' => 'GET_ERROR',
            'message' => 'Unable to retrieve activity details'
        ]);
    }
}

/**
 * ADD ACTION - Create new activity record
 */
if ($action === 'add') {
    write_log('Processing ADD action');
    try {
        $briefing_id = intval($_POST['briefing_id'] ?? 0);
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $time = trim($_POST['time'] ?? '08:00');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $priority = strtolower(trim($_POST['priority'] ?? 'medium'));
        $labor_count = intval($_POST['labor_count'] ?? 1);
        $contractors = trim($_POST['contractors'] ?? '');
        $assigned_to = trim($_POST['assigned_to'] ?? '');
        
        // Validate required fields
        if (empty($title)) {
            throw new Exception('Activity title is required');
        }
        
        if ($briefing_id <= 0) {
            throw new Exception('Valid briefing ID is required');
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception('Invalid date format. Expected YYYY-MM-DD.');
        }
        
        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            $time = '08:00'; // Default fallback
        }
        
        // Validate priority
        $valid_priorities = ['low', 'medium', 'high', 'critical'];
        if (!in_array($priority, $valid_priorities)) {
            $priority = 'medium';
        }
        
        // Verify briefing exists and belongs to current project
        $briefing_check = "SELECT id, project_id FROM briefings WHERE id = ? AND project_id = ?";
        $briefing_stmt = $pdo->prepare($briefing_check);
        $briefing_stmt->execute([$briefing_id, $project_id]);
        
        if (!$briefing_stmt->fetch()) {
            throw new Exception('Invalid briefing ID or access denied');
        }
        
        write_log('Creating new activity', [
            'title' => $title,
            'date' => $date,
            'time' => $time,
            'briefing_id' => $briefing_id,
            'priority' => $priority,
            'labor_count' => $labor_count,
            'created_by' => $username
        ]);
        
        // Insert new activity
        $insert_sql = "INSERT INTO activities (
                        briefing_id, date, time, title, description, area, priority,
                        labor_count, contractors, assigned_to, created_at, updated_at
                       ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                       )";
        
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([
            $briefing_id, $date, $time, $title, $description, $area, $priority,
            $labor_count, $contractors, $assigned_to
        ]);
        
        $new_id = $pdo->lastInsertId();
        
        write_log('Activity created successfully', [
            'id' => $new_id,
            'title' => $title,
            'briefing_id' => $briefing_id
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'add',
            'id' => intval($new_id),
            'title' => $title,
            'date' => $date,
            'date_uk' => date('d/m/Y', strtotime($date)),
            'time' => $time,
            'priority' => $priority,
            'labor_count' => $labor_count,
            'created_at' => date('d/m/Y H:i:s'),
            'created_by' => $username,
            'message' => 'Activity created successfully'
        ]);
        
    } catch (Exception $e) {
        write_log('Error in ADD action', [
            'error' => $e->getMessage(),
            'activity_title' => $title ?? 'unknown'
        ]);
        send_json([
            'ok' => false,
            'action' => 'add',
            'error' => 'Failed to create activity',
            'error_code' => 'ADD_ERROR',
            'message' => 'Unable to create activity record',
            'details' => $e->getMessage()
        ]);
    }
}

/**
 * UPDATE ACTION - Modify existing activity record
 */
if ($action === 'update') {
    write_log('Processing UPDATE action');
    try {
        $id = intval($_POST['id'] ?? 0);
        $briefing_id = intval($_POST['briefing_id'] ?? 0);
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $time = trim($_POST['time'] ?? '08:00');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $priority = strtolower(trim($_POST['priority'] ?? 'medium'));
        $labor_count = intval($_POST['labor_count'] ?? 1);
        $contractors = trim($_POST['contractors'] ?? '');
        $assigned_to = trim($_POST['assigned_to'] ?? '');
        
        // Validate required fields
        if ($id <= 0) {
            throw new Exception('Valid activity ID is required');
        }
        
        if (empty($title)) {
            throw new Exception('Activity title is required');
        }
        
        if ($briefing_id <= 0) {
            throw new Exception('Valid briefing ID is required');
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception('Invalid date format. Expected YYYY-MM-DD.');
        }
        
        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            $time = '08:00'; // Default fallback
        }
        
        // Validate priority
        $valid_priorities = ['low', 'medium', 'high', 'critical'];
        if (!in_array($priority, $valid_priorities)) {
            $priority = 'medium';
        }
        
        // Verify activity exists and belongs to current project
        $activity_check = "SELECT a.id, a.title FROM activities a 
                          LEFT JOIN briefings b ON a.briefing_id = b.id 
                          WHERE a.id = ? AND b.project_id = ?";
        $activity_stmt = $pdo->prepare($activity_check);
        $activity_stmt->execute([$id, $project_id]);
        $existing_activity = $activity_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing_activity) {
            throw new Exception('Activity not found or access denied');
        }
        
        // Verify briefing exists and belongs to current project
        $briefing_check = "SELECT id, project_id FROM briefings WHERE id = ? AND project_id = ?";
        $briefing_stmt = $pdo->prepare($briefing_check);
        $briefing_stmt->execute([$briefing_id, $project_id]);
        
        if (!$briefing_stmt->fetch()) {
            throw new Exception('Invalid briefing ID or access denied');
        }
        
        write_log('Updating activity', [
            'id' => $id,
            'old_title' => $existing_activity['title'],
            'new_title' => $title,
            'date' => $date,
            'priority' => $priority,
            'updated_by' => $username
        ]);
        
        // Update activity
        $update_sql = "UPDATE activities SET
                        briefing_id = ?, date = ?, time = ?, title = ?, description = ?, 
                        area = ?, priority = ?, labor_count = ?, contractors = ?, 
                        assigned_to = ?, updated_at = NOW()
                       WHERE id = ?";
        
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            $briefing_id, $date, $time, $title, $description, $area, $priority,
            $labor_count, $contractors, $assigned_to, $id
        ]);
        
        write_log('Activity updated successfully', [
            'id' => $id,
            'title' => $title,
            'rows_affected' => $update_stmt->rowCount()
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'update',
            'id' => $id,
            'title' => $title,
            'date' => $date,
            'date_uk' => date('d/m/Y', strtotime($date)),
            'time' => $time,
            'priority' => $priority,
            'labor_count' => $labor_count,
            'updated_at' => date('d/m/Y H:i:s'),
            'updated_by' => $username,
            'message' => 'Activity updated successfully'
        ]);
        
    } catch (Exception $e) {
        write_log('Error in UPDATE action', [
            'error' => $e->getMessage(),
            'activity_id' => $id ?? 'unknown'
        ]);
        send_json([
            'ok' => false,
            'action' => 'update',
            'error' => 'Failed to update activity',
            'error_code' => 'UPDATE_ERROR',
            'message' => 'Unable to update activity record',
            'details' => $e->getMessage()
        ]);
    }
}

/**
 * DELETE ACTION - Remove activity record
 */
if ($action === 'delete') {
    write_log('Processing DELETE action');
    try {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            throw new Exception('Valid activity ID is required');
        }
        
        // Verify activity exists and belongs to current project
        $activity_check = "SELECT a.id, a.title FROM activities a 
                          LEFT JOIN briefings b ON a.briefing_id = b.id 
                          WHERE a.id = ? AND b.project_id = ?";
        $activity_stmt = $pdo->prepare($activity_check);
        $activity_stmt->execute([$id, $project_id]);
        $activity = $activity_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$activity) {
            throw new Exception('Activity not found or access denied');
        }
        
        write_log('Deleting activity', [
            'id' => $id,
            'title' => $activity['title'],
            'deleted_by' => $username
        ]);
        
        // Delete the activity
        $delete_sql = "DELETE FROM activities WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$id]);
        
        write_log('Activity deleted successfully', [
            'id' => $id,
            'title' => $activity['title'],
            'rows_affected' => $delete_stmt->rowCount()
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'delete',
            'id' => $id,
            'title' => $activity['title'],
            'deleted_at' => date('d/m/Y H:i:s'),
            'deleted_by' => $username,
            'message' => 'Activity deleted successfully'
        ]);
        
    } catch (Exception $e) {
        write_log('Error in DELETE action', [
            'error' => $e->getMessage(),
            'activity_id' => $id ?? 'unknown'
        ]);
        send_json([
            'ok' => false,
            'action' => 'delete',
            'error' => 'Failed to delete activity',
            'error_code' => 'DELETE_ERROR',
            'message' => 'Unable to delete activity record',
            'details' => $e->getMessage()
        ]);
    }
}

// Handle unrecognized actions
if (!in_array($action, ['list', 'get', 'add', 'update', 'delete'])) {
    write_log('Unknown or missing action requested', [
        'action' => $action,
        'valid_actions' => ['list', 'get', 'add', 'update', 'delete'],
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
    ]);
    send_json([
        'ok' => false,
        'error' => 'Unknown or missing action',
        'error_code' => 'INVALID_ACTION',
        'received_action' => $action,
        'valid_actions' => ['list', 'get', 'add', 'update', 'delete'],
        'message' => 'Please specify a valid action parameter',
        'help' => 'Available actions: list, get, add, update, delete'
    ]);
}

/**
 * =========================================================================
 * END OF FILE: ajax_activities.php
 * Daily Activity Briefing System (DABS) - Activities Management API
 * Last Updated: 24/06/2025 10:53:42 (UK Time)
 * =========================================================================
 */
?>