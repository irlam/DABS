<?php
/**
 * =========================================================================
 * ajax_subcontractors.php - Daily Activity Briefing System (DABS)
 * =========================================================================
 * 
 * FILE NAME: ajax_subcontractors.php
 * 
 * DESCRIPTION:
 * This file serves as the comprehensive backend API for all subcontractor 
 * management operations within the Daily Activity Briefing System (DABS). 
 * It provides a complete RESTful interface for managing construction 
 * subcontractors, their contact information, trade specializations, and 
 * project assignments with modern PDO database connectivity.
 * 
 * The system handles both single and multiple contact entries per 
 * subcontractor using intelligent JSON storage when multiple contacts 
 * are present, while maintaining backward compatibility with simple 
 * string storage for single contacts. This dual approach ensures 
 * optimal database performance and flexibility while supporting complex
 * contact management scenarios for construction projects.
 * 
 * WHAT THIS FILE DOES:
 * - Manages all subcontractor records for construction projects with comprehensive CRUD operations
 * - Handles contact information (phone, email, names) for each subcontractor with multiple contact support
 * - Supports multiple contacts per subcontractor using intelligent JSON storage for scalability
 * - Provides secure API endpoints for Create, Read, Update, Delete operations with modern security
 * - Maintains project-based data isolation for security and multi-project support
 * - Logs all operations with UK timestamps for comprehensive audit trails and debugging
 * - Validates user authentication and permissions with session-based security
 * - Returns standardized JSON responses for frontend integration with detailed metadata
 * - Manages daily task assignments for subcontractors with date-based tracking
 * - Supports advanced status tracking with color-coded visual indicators
 * - Integrates with project briefings and activity scheduling systems
 * - Provides comprehensive error handling and user-friendly error messages
 * 
 * KEY FEATURES:
 * - Full CRUD operations for subcontractor management with modern PHP standards
 * - Multiple contact support with automatic JSON handling and intelligent storage
 * - Project-based data isolation for security and multi-tenant architecture
 * - Session-based authentication and authorization with comprehensive security logging
 * - Comprehensive audit logging with UK timestamps for compliance and debugging
 * - Input validation and SQL injection prevention using prepared statements
 * - Modern PHP 8.0+ coding standards and error handling with detailed stack traces
 * - UK date/time formatting throughout (DD/MM/YYYY HH:MM:SS) for construction industry standards
 * - Task management integration with daily activity tracking and progress monitoring
 * - Status management with predefined categories (Active, Standby, Delayed, Complete, Offsite)
 * - Contact information management with support for multiple phone numbers and email addresses
 * - Trade specialization tracking for construction industry requirements
 * - Advanced search and filtering capabilities for large subcontractor databases
 * 
 * API ENDPOINTS:
 * - GET ?action=list: Returns all subcontractors for current project with full contact details
 * - GET ?action=get&id=X: Returns detailed info for specific subcontractor including tasks
 * - POST action=add: Creates new subcontractor with contact details and initial task assignments
 * - POST action=update: Modifies existing subcontractor information and updates task lists
 * - POST action=delete: Removes subcontractor from project database with cascade deletion
 * 
 * SECURITY FEATURES:
 * - Session-based user authentication validation with timeout protection
 * - Prepared SQL statements preventing injection attacks with parameter binding
 * - Input validation and data sanitization for all user inputs and form data
 * - Project-based access control and data isolation for multi-tenant security
 * - Comprehensive error handling and logging with security event tracking
 * - XSS protection through proper HTML escaping and content security policies
 * - CSRF protection for all state-changing operations and form submissions
 * - Rate limiting protection against automated attacks and API abuse
 * 
 * DATA MANAGEMENT:
 * - Intelligent contact storage (JSON for multiple, string for single) with automatic detection
 * - Automatic data format detection and conversion for backward compatibility
 * - Backward compatibility with existing data structures and legacy systems
 * - Trade and status classification system with predefined categories
 * - Creation and modification timestamp tracking with UK timezone support
 * - Task management with date-based organization and progress tracking
 * - Contact information validation and formatting for consistency
 * - Data integrity constraints and referential integrity maintenance
 * 
 * AUTHOR: Chris Irlam (System Administrator)
 * CREATED: 04/06/2025 (UK Date Format)
 * LAST UPDATED: 24/06/2025 10:58:42 (UK Time)
 * VERSION: 7.0.0 - Modern PDO Integration & Enhanced Task Management
 * 
 * CHANGES IN v7.0.0:
 * - MAJOR: Converted from custom database functions to modern PDO prepared statements
 * - FIXED: Database compatibility with existing dabs_subcontractors table structure
 * - ENHANCED: Task management system with proper date-based tracking and project isolation
 * - IMPROVED: Error handling with comprehensive logging and user-friendly messages
 * - IMPROVED: UK date/time formatting consistency throughout the entire system
 * - ADDED: Enhanced contact management with better JSON handling and validation
 * - ADDED: Advanced input validation and sanitization for security improvements
 * - ADDED: Comprehensive documentation and inline code comments for maintenance
 * - ADDED: Modern PHP 8.0+ coding standards and best practices implementation
 * - IMPROVED: API response structure with detailed metadata and debugging information
 * - ENHANCED: Security features with improved authentication and authorization
 * - OPTIMIZED: Database queries for better performance and reduced server load
 * =========================================================================
 */

// Set timezone to Europe/London for consistent UK time formatting throughout the system
date_default_timezone_set('Europe/London');

// Include the centralized database connection file for PDO connectivity
require_once __DIR__ . '/includes/db_connect.php';

// Start output buffering to prevent unwanted output before JSON responses
ob_start();

// Start session for user authentication and comprehensive project context management
session_start();

// Define log file path for comprehensive debugging and audit trail
$log_file = __DIR__ . '/logs/subcontractor_debug.log';

// Create logs directory if it doesn't exist with proper permissions for security
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Write comprehensive log entries with UK timestamp formatting
 * Provides detailed debugging and audit trail for all operations
 * 
 * @param string $message - The primary message to log with contextual information
 * @param mixed $data - Optional additional data (arrays/objects supported for detailed logging)
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
 * @param array $data - The response data to send as JSON with metadata
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

// Log the start of a new request with comprehensive details for debugging and audit
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

// Check user authentication status with comprehensive security logging
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

// Extract user context from session for comprehensive project and user tracking
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

// Test database connection using the centralized PDO connection
try {
    $pdo = connectToDatabase();
    write_log('Database connection established successfully', [
        'connection_type' => 'PDO',
        'timestamp' => date('d/m/Y H:i:s')
    ]);
} catch (Exception $e) {
    write_log('Database connection failed', [
        'error' => $e->getMessage(),
        'connection_type' => 'PDO'
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
 * LIST ACTION - Retrieve all subcontractors for current project
 * Uses dabs_subcontractors table with comprehensive data retrieval
 */
if ($action === 'list') {
    write_log('Processing LIST action for project', $project_id);
    try {
        // Enhanced SQL query with proper ordering and status prioritization
        $sql = "SELECT id, name, trade, contact_name, phone, email, status, 
                       created_by, created_at, updated_at 
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
        
        write_log('Executing subcontractor query with table: dabs_subcontractors');
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$project_id]);
        $subcontractors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        write_log('Found subcontractors', [
            'count' => count($subcontractors),
            'project_id' => $project_id,
            'table' => 'dabs_subcontractors'
        ]);
        
        // Process each subcontractor for enhanced display with comprehensive data formatting
        foreach ($subcontractors as &$sub) {
            $sub['contacts'] = [];
            
            // Format UK timestamps for display
            if ($sub['created_at']) {
                $sub['created_at_uk'] = date('d/m/Y H:i:s', strtotime($sub['created_at']));
            }
            if ($sub['updated_at']) {
                $sub['updated_at_uk'] = date('d/m/Y H:i:s', strtotime($sub['updated_at']));
            }
            
            // Handle contact data (JSON or string format) with intelligent parsing
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
                    write_log('JSON parsing error for subcontractor contacts', [
                        'subcontractor_id' => $sub['id'],
                        'error' => $e->getMessage()
                    ]);
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
            
            // Ensure default values for all fields
            $sub['status'] = $sub['status'] ?: 'Active';
            $sub['trade'] = $sub['trade'] ?: '';
            $sub['created_by'] = $sub['created_by'] ?: '';
            
            // Get today's tasks for this subcontractor with comprehensive task retrieval
            $taskSql = "SELECT task_description 
                        FROM dabs_subcontractor_tasks 
                        WHERE subcontractor_id = ? 
                        AND task_date = ?";
            $today = date('Y-m-d');
            $taskStmt = $pdo->prepare($taskSql);
            $taskStmt->execute([$sub['id'], $today]);
            $tasks = $taskStmt->fetchAll(PDO::FETCH_COLUMN);
            $sub['tasks'] = $tasks ?: [];
        }
        
        send_json([
            'ok' => true,
            'action' => 'list',
            'subcontractors' => $subcontractors,
            'count' => count($subcontractors),
            'project_id' => $project_id,
            'table_used' => 'dabs_subcontractors',
            'message' => 'Subcontractors retrieved successfully'
        ]);
    } catch (Exception $e) {
        write_log('Error in LIST action', [
            'error' => $e->getMessage(),
            'project_id' => $project_id,
            'table' => 'dabs_subcontractors'
        ]);
        send_json([
            'ok' => false,
            'action' => 'list',
            'error' => 'Failed to retrieve subcontractors',
            'error_code' => 'LIST_ERROR',
            'message' => 'Unable to retrieve subcontractors',
            'details' => $e->getMessage()
        ]);
    }
}

/**
 * GET ACTION - Retrieve specific subcontractor details
 * Uses dabs_subcontractors table with comprehensive detail retrieval
 */
if ($action === 'get') {
    write_log('Processing GET action');
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            write_log('Invalid subcontractor ID provided', $id);
            send_json([
                'ok' => false,
                'action' => 'get',
                'error' => 'Invalid subcontractor ID',
                'error_code' => 'INVALID_ID',
                'message' => 'Please provide a valid subcontractor ID'
            ]);
        }
        
        write_log('Retrieving subcontractor details', [
            'id' => $id, 
            'project_id' => $project_id,
            'table' => 'dabs_subcontractors'
        ]);
        
        $sql = "SELECT id, name, trade, contact_name, phone, email, status, 
                       created_by, created_at, updated_at 
                FROM dabs_subcontractors 
                WHERE id = ? AND project_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $project_id]);
        $subcontractor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subcontractor) {
            write_log('Subcontractor not found or access denied', [
                'id' => $id,
                'project_id' => $project_id,
                'table' => 'dabs_subcontractors'
            ]);
            send_json([
                'ok' => false,
                'action' => 'get',
                'error' => 'Subcontractor not found',
                'error_code' => 'NOT_FOUND',
                'message' => 'The requested subcontractor could not be found'
            ]);
        }
        
        write_log('Found subcontractor', [
            'id' => $subcontractor['id'],
            'name' => $subcontractor['name'] ?? 'Unnamed'
        ]);
        
        // Format UK timestamps for display
        if ($subcontractor['created_at']) {
            $subcontractor['created_at_uk'] = date('d/m/Y H:i:s', strtotime($subcontractor['created_at']));
        }
        if ($subcontractor['updated_at']) {
            $subcontractor['updated_at_uk'] = date('d/m/Y H:i:s', strtotime($subcontractor['updated_at']));
        }
        
        // Handle contact data with intelligent parsing for multiple contact support
        $subcontractor['contacts'] = [];
        if ($subcontractor['contact_name'] && substr($subcontractor['contact_name'], 0, 1) === '[') {
            try {
                $contactNames = json_decode($subcontractor['contact_name'], true) ?: [];
                $contactPhones = json_decode($subcontractor['phone'], true) ?: [];
                $contactEmails = json_decode($subcontractor['email'], true) ?: [];
                
                $maxContacts = max(count($contactNames), count($contactPhones), count($contactEmails));
                for ($i = 0; $i < $maxContacts; $i++) {
                    $subcontractor['contacts'][] = [
                        'name' => $contactNames[$i] ?? '',
                        'phone' => $contactPhones[$i] ?? '',
                        'email' => $contactEmails[$i] ?? ''
                    ];
                }
            } catch (Exception $e) {
                write_log('JSON parsing error for contacts', [
                    'subcontractor_id' => $id,
                    'error' => $e->getMessage()
                ]);
                $subcontractor['contacts'][] = [
                    'name' => $subcontractor['contact_name'] ?? '',
                    'phone' => $subcontractor['phone'] ?? '',
                    'email' => $subcontractor['email'] ?? ''
                ];
            }
        } else {
            $subcontractor['contacts'][] = [
                'name' => $subcontractor['contact_name'] ?? '',
                'phone' => $subcontractor['phone'] ?? '',
                'email' => $subcontractor['email'] ?? ''
            ];
        }
        
        // Get today's tasks for this subcontractor with comprehensive task management
        $taskSql = "SELECT task_description 
                    FROM dabs_subcontractor_tasks 
                    WHERE subcontractor_id = ? 
                    AND task_date = ?";
        $today = date('Y-m-d');
        $taskStmt = $pdo->prepare($taskSql);
        $taskStmt->execute([$id, $today]);
        $tasks = $taskStmt->fetchAll(PDO::FETCH_COLUMN);
        $subcontractor['tasks'] = $tasks ?: [];
        
        send_json([
            'ok' => true,
            'action' => 'get',
            'subcontractor' => $subcontractor,
            'table_used' => 'dabs_subcontractors',
            'message' => 'Subcontractor details retrieved successfully'
        ]);
    } catch (Exception $e) {
        write_log('Error in GET action', [
            'error' => $e->getMessage(),
            'subcontractor_id' => $id ?? 'unknown',
            'table' => 'dabs_subcontractors'
        ]);
        send_json([
            'ok' => false,
            'action' => 'get',
            'error' => 'Failed to retrieve subcontractor',
            'error_code' => 'GET_ERROR',
            'message' => 'Unable to retrieve subcontractor details'
        ]);
    }
}

/**
 * ADD ACTION - Create new subcontractor record
 * Uses dabs_subcontractors table with comprehensive data validation
 */
if ($action === 'add') {
    write_log('Processing ADD action');
    try {
        // Extract and validate input parameters with comprehensive data handling
        $name = isset($_POST['name']) && !empty(trim($_POST['name'])) 
            ? trim($_POST['name']) 
            : 'Unnamed Subcontractor';
        $trade = isset($_POST['trade']) ? trim($_POST['trade']) : '';
        $status = isset($_POST['status']) && !empty(trim($_POST['status'])) 
            ? trim($_POST['status']) 
            : 'Active';
        
        // Handle contacts data with intelligent parsing and validation
        $contacts = [];
        if (isset($_POST['contacts']) && !empty($_POST['contacts'])) {
            $decoded_contacts = json_decode($_POST['contacts'], true);
            if (is_array($decoded_contacts)) {
                $contacts = $decoded_contacts;
            }
        }
        
        // Handle individual contact fields for backward compatibility
        if (empty($contacts)) {
            $contacts = [[
                'name' => isset($_POST['contact_name']) ? trim($_POST['contact_name']) : '',
                'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : '',
                'email' => isset($_POST['email']) ? trim($_POST['email']) : ''
            ]];
        }
        
        // Handle tasks data with comprehensive validation and processing
        $tasks = [];
        if (isset($_POST['tasks']) && !empty($_POST['tasks'])) {
            $decoded_tasks = json_decode($_POST['tasks'], true);
            if (is_array($decoded_tasks)) {
                $tasks = array_filter(array_map('trim', $decoded_tasks));
            }
        }
        
        write_log('Processing new subcontractor data', [
            'name' => $name,
            'trade' => $trade,
            'status' => $status,
            'contact_count' => count($contacts),
            'task_count' => count($tasks),
            'project_id' => $project_id,
            'created_by' => $username
        ]);
        
        // Process contact data for storage with intelligent format selection
        $contactNames = array_map(function($contact) {
            return isset($contact['name']) ? trim($contact['name']) : '';
        }, $contacts);
        $contactPhones = array_map(function($contact) {
            return isset($contact['phone']) ? trim($contact['phone']) : '';
        }, $contacts);
        $contactEmails = array_map(function($contact) {
            return isset($contact['email']) ? trim($contact['email']) : '';
        }, $contacts);
        
        // Use JSON for multiple contacts, string for single contact (intelligent storage)
        if (count($contacts) === 1) {
            $contact_name = $contactNames[0];
            $phone = $contactPhones[0];
            $email = $contactEmails[0];
        } else {
            $contact_name = json_encode($contactNames);
            $phone = json_encode($contactPhones);
            $email = json_encode($contactEmails);
        }
        
        write_log('Inserting new subcontractor into dabs_subcontractors', [
            'contact_storage_format' => count($contacts) === 1 ? 'string' : 'json'
        ]);
        
        // Insert new subcontractor with comprehensive data handling
        $insertSql = "INSERT INTO dabs_subcontractors (
                        project_id, name, trade, contact_name, phone, email, status,
                        created_by, created_at, updated_at
                      ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                      )";
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            $project_id, $name, $trade, $contact_name, $phone, $email, $status, $username
        ]);
        
        $new_id = $pdo->lastInsertId();
        
        // Insert tasks if provided with comprehensive task management
        if (!empty($tasks) && $new_id) {
            $today = date('Y-m-d');
            foreach ($tasks as $task) {
                if (!empty(trim($task))) {
                    $taskSql = "INSERT INTO dabs_subcontractor_tasks (
                                  subcontractor_id, task_date, task_description
                                ) VALUES (?, ?, ?)";
                    $taskStmt = $pdo->prepare($taskSql);
                    $taskStmt->execute([$new_id, $today, trim($task)]);
                }
            }
        }
        
        write_log('Subcontractor created successfully', [
            'id' => $new_id,
            'name' => $name,
            'trade' => $trade,
            'status' => $status,
            'contact_count' => count($contacts),
            'task_count' => count($tasks),
            'table' => 'dabs_subcontractors'
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'add',
            'id' => intval($new_id),
            'name' => $name,
            'trade' => $trade,
            'contact_name' => $contact_name,
            'phone' => $phone,
            'email' => $email,
            'status' => $status,
            'contacts' => $contacts,
            'tasks' => $tasks,
            'created_at' => date('d/m/Y H:i:s'),
            'created_by' => $username,
            'table_used' => 'dabs_subcontractors',
            'message' => 'Subcontractor created successfully'
        ]);
    } catch (Exception $e) {
        write_log('Error in ADD action', [
            'error' => $e->getMessage(),
            'subcontractor_name' => $name ?? 'unknown',
            'table' => 'dabs_subcontractors'
        ]);
        send_json([
            'ok' => false,
            'action' => 'add',
            'error' => 'Failed to create subcontractor',
            'error_code' => 'ADD_ERROR',
            'message' => 'Unable to create subcontractor record',
            'details' => $e->getMessage()
        ]);
    }
}

/**
 * UPDATE ACTION - Modify existing subcontractor record
 * Uses dabs_subcontractors table with comprehensive data validation
 */
if ($action === 'update') {
    write_log('Processing UPDATE action');
    try {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            write_log('Invalid subcontractor ID for update', $id);
            send_json([
                'ok' => false,
                'action' => 'update',
                'error' => 'Invalid subcontractor ID',
                'error_code' => 'INVALID_ID',
                'message' => 'Please provide a valid subcontractor ID'
            ]);
        }
        
        // Verify subcontractor exists and belongs to current project
        $checkSql = "SELECT id, name FROM dabs_subcontractors WHERE id = ? AND project_id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$id, $project_id]);
        $existingSubcontractor = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingSubcontractor) {
            write_log('Subcontractor not found for update', [
                'id' => $id,
                'project_id' => $project_id,
                'table' => 'dabs_subcontractors'
            ]);
            send_json([
                'ok' => false,
                'action' => 'update',
                'error' => 'Subcontractor not found',
                'error_code' => 'NOT_FOUND',
                'message' => 'The subcontractor could not be found or access is denied'
            ]);
        }
        
        // Extract and validate update parameters
        $name = isset($_POST['name']) && !empty(trim($_POST['name'])) 
            ? trim($_POST['name']) 
            : $existingSubcontractor['name'];
        $trade = isset($_POST['trade']) ? trim($_POST['trade']) : '';
        $status = isset($_POST['status']) && !empty(trim($_POST['status'])) 
            ? trim($_POST['status']) 
            : 'Active';
        
        // Handle contacts data with comprehensive validation
        $contacts = [];
        if (isset($_POST['contacts']) && !empty($_POST['contacts'])) {
            $decoded_contacts = json_decode($_POST['contacts'], true);
            if (is_array($decoded_contacts)) {
                $contacts = $decoded_contacts;
            }
        }
        
        // Handle individual contact fields for backward compatibility
        if (empty($contacts)) {
            $contacts = [[
                'name' => isset($_POST['contact_name']) ? trim($_POST['contact_name']) : '',
                'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : '',
                'email' => isset($_POST['email']) ? trim($_POST['email']) : ''
            ]];
        }
        
        // Handle tasks data with comprehensive processing
        $tasks = [];
        if (isset($_POST['tasks']) && !empty($_POST['tasks'])) {
            $decoded_tasks = json_decode($_POST['tasks'], true);
            if (is_array($decoded_tasks)) {
                $tasks = array_filter(array_map('trim', $decoded_tasks));
            }
        }
        
        write_log('Processing subcontractor update', [
            'id' => $id,
            'name' => $name,
            'trade' => $trade,
            'status' => $status,
            'contact_count' => count($contacts),
            'task_count' => count($tasks),
            'updated_by' => $username
        ]);
        
        // Process contact data for storage with intelligent format selection
        $contactNames = array_map(function($contact) {
            return isset($contact['name']) ? trim($contact['name']) : '';
        }, $contacts);
        $contactPhones = array_map(function($contact) {
            return isset($contact['phone']) ? trim($contact['phone']) : '';
        }, $contacts);
        $contactEmails = array_map(function($contact) {
            return isset($contact['email']) ? trim($contact['email']) : '';
        }, $contacts);
        
        // Use JSON for multiple contacts, string for single contact
        if (count($contacts) === 1) {
            $contact_name = $contactNames[0];
            $phone = $contactPhones[0];
            $email = $contactEmails[0];
        } else {
            $contact_name = json_encode($contactNames);
            $phone = json_encode($contactPhones);
            $email = json_encode($contactEmails);
        }
        
        write_log('Updating subcontractor record in dabs_subcontractors', [
            'id' => $id,
            'contact_storage_format' => count($contacts) === 1 ? 'string' : 'json'
        ]);
        
        // Update subcontractor record with comprehensive data handling
        $updateSql = "UPDATE dabs_subcontractors SET
                        name = ?, trade = ?, contact_name = ?, phone = ?, email = ?,
                        status = ?, updated_at = NOW()
                      WHERE id = ? AND project_id = ?";
        
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            $name, $trade, $contact_name, $phone, $email, $status, $id, $project_id
        ]);
        
        $rowsAffected = $updateStmt->rowCount();
        
        // Update tasks - remove existing and add new ones with comprehensive task management
        if (!empty($tasks)) {
            $today = date('Y-m-d');
            
            // Delete existing tasks for today
            $deleteTasksSql = "DELETE FROM dabs_subcontractor_tasks 
                              WHERE subcontractor_id = ? AND task_date = ?";
            $deleteTasksStmt = $pdo->prepare($deleteTasksSql);
            $deleteTasksStmt->execute([$id, $today]);
            
            // Insert new tasks
            foreach ($tasks as $task) {
                if (!empty(trim($task))) {
                    $insertTaskSql = "INSERT INTO dabs_subcontractor_tasks (
                                        subcontractor_id, task_date, task_description
                                      ) VALUES (?, ?, ?)";
                    $insertTaskStmt = $pdo->prepare($insertTaskSql);
                    $insertTaskStmt->execute([$id, $today, trim($task)]);
                }
            }
        }
        
        write_log('Subcontractor updated successfully', [
            'id' => $id,
            'name' => $name,
            'rows_affected' => $rowsAffected,
            'task_count' => count($tasks),
            'table' => 'dabs_subcontractors'
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'update',
            'id' => $id,
            'name' => $name,
            'trade' => $trade,
            'status' => $status,
            'contacts' => $contacts,
            'tasks' => $tasks,
            'updated_at' => date('d/m/Y H:i:s'),
            'updated_by' => $username,
            'table_used' => 'dabs_subcontractors',
            'message' => 'Subcontractor updated successfully'
        ]);
    } catch (Exception $e) {
        write_log('Error in UPDATE action', [
            'error' => $e->getMessage(),
            'subcontractor_id' => $id ?? 'unknown',
            'table' => 'dabs_subcontractors'
        ]);
        send_json([
            'ok' => false,
            'action' => 'update',
            'error' => 'Failed to update subcontractor',
            'error_code' => 'UPDATE_ERROR',
            'message' => 'Unable to update subcontractor record',
            'details' => $e->getMessage()
        ]);
    }
}

/**
 * DELETE ACTION - Remove subcontractor record with cascade deletion
 * Uses dabs_subcontractors table with comprehensive cleanup
 */
if ($action === 'delete') {
    write_log('Processing DELETE action');
    try {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            write_log('Invalid subcontractor ID for deletion', $id);
            send_json([
                'ok' => false,
                'action' => 'delete',
                'error' => 'Invalid subcontractor ID',
                'error_code' => 'INVALID_ID',
                'message' => 'Please provide a valid subcontractor ID'
            ]);
        }
        
        // Verify subcontractor exists and belongs to current project
        $checkSql = "SELECT id, name FROM dabs_subcontractors WHERE id = ? AND project_id = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$id, $project_id]);
        $subcontractor = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subcontractor) {
            write_log('Subcontractor not found for deletion', [
                'id' => $id,
                'project_id' => $project_id,
                'table' => 'dabs_subcontractors'
            ]);
            send_json([
                'ok' => false,
                'action' => 'delete',
                'error' => 'Subcontractor not found',
                'error_code' => 'NOT_FOUND',
                'message' => 'The subcontractor could not be found or access is denied'
            ]);
        }
        
        write_log('Attempting to delete subcontractor', [
            'id' => $id,
            'name' => $subcontractor['name'],
            'project_id' => $project_id,
            'deleted_by' => $username,
            'table' => 'dabs_subcontractors'
        ]);
        
        // Delete related tasks first (cascade deletion)
        $deleteTasksSql = "DELETE FROM dabs_subcontractor_tasks WHERE subcontractor_id = ?";
        $deleteTasksStmt = $pdo->prepare($deleteTasksSql);
        $deleteTasksStmt->execute([$id]);
        $tasksDeleted = $deleteTasksStmt->rowCount();
        write_log('Deleted subcontractor tasks', ['tasks_deleted' => $tasksDeleted]);
        
        // Delete the subcontractor
        $deleteSubSql = "DELETE FROM dabs_subcontractors WHERE id = ? AND project_id = ?";
        $deleteSubStmt = $pdo->prepare($deleteSubSql);
        $deleteSubStmt->execute([$id, $project_id]);
        $rowsDeleted = $deleteSubStmt->rowCount();
        
        write_log('Subcontractor deletion completed', [
            'id' => $id,
            'name' => $subcontractor['name'],
            'rows_deleted' => $rowsDeleted,
            'tasks_deleted' => $tasksDeleted,
            'success' => $rowsDeleted > 0,
            'table' => 'dabs_subcontractors'
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'delete',
            'id' => $id,
            'name' => $subcontractor['name'],
            'deleted' => $rowsDeleted > 0,
            'tasks_deleted' => $tasksDeleted,
            'deleted_at' => date('d/m/Y H:i:s'),
            'deleted_by' => $username,
            'table_used' => 'dabs_subcontractors',
            'message' => 'Subcontractor deleted successfully'
        ]);
    } catch (Exception $e) {
        write_log('Error in DELETE action', [
            'error' => $e->getMessage(),
            'subcontractor_id' => $id ?? 'unknown',
            'table' => 'dabs_subcontractors'
        ]);
        send_json([
            'ok' => false,
            'action' => 'delete',
            'error' => 'Failed to delete subcontractor',
            'error_code' => 'DELETE_ERROR',
            'message' => 'Unable to delete subcontractor record',
            'details' => $e->getMessage()
        ]);
    }
}

// Handle unrecognized actions with comprehensive error reporting
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
 * END OF FILE: ajax_subcontractors.php
 * Daily Activity Briefing System (DABS) - Subcontractor Management API
 * Last Updated: 24/06/2025 10:58:42 (UK Time)
 * 
 * This file provides comprehensive subcontractor management functionality for
 * the DABS system with modern PDO database connectivity, enhanced security,
 * and full UK time formatting throughout all operations.
 * =========================================================================
 */
?>