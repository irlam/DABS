<?php
/**
 * =========================================================================
 * ajax_activities.php - Daily Activity Briefing System (DABS)
 * =========================================================================
 * 
 * FILE NAME: ajax_activities.php
 * 
 * DESCRIPTION:
 * This is the main backend API file for the Daily Activity Briefing System (DABS).
 * It serves as the comprehensive backend interface for managing construction site 
 * activities, resource allocation, and daily briefing operations. This file handles 
 * all AJAX requests from the frontend JavaScript to manage construction activities, 
 * labor tracking, subcontractor assignments, and generates detailed reports and 
 * statistics for project management.
 * 
 * WHAT THIS FILE DOES:
 * - Manages daily construction activity records for project briefings
 * - Handles Create, Read, Update, Delete (CRUD) operations for activities
 * - Tracks labor counts and resource allocation per activity
 * - Manages subcontractor assignments using JSON storage in activities table
 * - Generates comprehensive statistics and reports (daily, weekly, area-based)
 * - Provides copy functionality to duplicate activities from previous days
 * - Manages construction area organization and categorization
 * - Handles briefing creation and management for specific project dates
 * - Provides resource statistics for project planning and management
 * - Maintains audit trails with UK timestamp formatting for all operations
 * - Integrates with subcontractor management system for contractor assignments
 * - Supports multi-project data isolation for security and organization
 * 
 * CORE FUNCTIONALITY:
 * The system allows project managers to create daily briefings with activities 
 * assigned to different areas of a construction site. Each activity can have:
 * - Title and detailed description
 * - Priority level (low, medium, high, critical)
 * - Labor count requirements
 * - Assigned construction area
 * - Multiple subcontractor assignments (stored as JSON)
 * - Creation and modification timestamps in UK format
 * 
 * API ENDPOINTS SUPPORTED:
 * - list: Retrieve all activities for a specific date with statistics
 * - add: Create new activity with labor and contractor assignments
 * - update: Modify existing activity details and assignments
 * - delete: Remove activities from the system with audit logging
 * - get: Retrieve detailed information about specific activities
 * - get_subcontractors: List all available subcontractors for assignments
 * - add_subcontractor: Create new subcontractor records
 * - get_resource_stats: Generate comprehensive resource and labor statistics
 * - get_areas: List all construction areas with usage analytics
 * - copy_prev_day: Copy activities from previous days for efficiency
 * 
 * SECURITY FEATURES:
 * - Session-based user authentication and authorization
 * - Project-based data isolation preventing unauthorized access
 * - Prepared SQL statements preventing injection attacks
 * - Input validation and data sanitization for all parameters
 * - Comprehensive error handling with safe user messages
 * - Detailed audit logging for security monitoring
 * - User permission verification for all operations
 * 
 * DATA MANAGEMENT:
 * - Uses centralized database connection via includes/db_connect.php
 * - Stores contractor assignments as JSON in activities.contractors field
 * - Maintains referential integrity with briefings and subcontractors tables
 * - Supports both single and multiple contractor assignments per activity
 * - Automatic briefing creation when activities are added to new dates
 * - UK date/time formatting throughout (DD/MM/YYYY HH:MM:SS)
 * - Comprehensive data validation and type conversion
 * 
 * INTEGRATION POINTS:
 * - Integrates with dabs_subcontractors table for contractor information
 * - Links activities to briefings table for date-based organization
 * - Connects with activity_log table for comprehensive audit trails
 * - Uses centralized database helper functions for consistency
 * - Supports frontend JavaScript AJAX calls with JSON responses
 * 
 * AUTHOR: irlam (System Administrator)
 * CREATED: 16/06/2025 (UK Date Format)
 * LAST UPDATED: 16/06/2025 20:46:49 (UK Time)
 * VERSION: 6.0.0 - Centralized Database Integration & Modern Standards
 * 
 * CHANGE LOG v6.0.0:
 * - Integrated with centralized includes/db_connect.php for consistency
 * - Enhanced UK time formatting throughout entire system (DD/MM/YYYY HH:MM:SS)
 * - Modern PHP 8+ coding standards and best practices implementation
 * - Comprehensive error handling with detailed audit logging
 * - Enhanced security measures and input validation
 * - Improved JSON handling for contractor management
 * - Optimized database operations using centralized helper functions
 * - Enhanced documentation and inline code comments
 * - Consistent response formatting across all API endpoints
 * - Robust audit trail and debugging capabilities
 * - Better resource management and statistics generation
 * - Enhanced copy functionality for day-to-day operations
 * =========================================================================
 */

// Set timezone to Europe/London for consistent UK time formatting throughout the system
date_default_timezone_set('Europe/London');

// Include the centralized database connection file for consistent database operations
require_once __DIR__ . '/includes/db_connect.php';

// Start output buffering to prevent unwanted output before JSON responses
ob_start();

// Start session for user authentication and project context management
session_start();

// Set up comprehensive logging for debugging and audit trails
$log_file = __DIR__ . '/logs/activities_debug.log';

// Create logs directory if it doesn't exist with proper permissions
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Write comprehensive log entries with UK timestamp formatting
 * This function provides detailed debugging and audit trail for all operations
 * 
 * @param string $message - The primary message to log
 * @param mixed $data - Optional data to include (arrays/objects will be formatted)
 */
function write_log($message, $data = null) {
    global $log_file;
    
    // Format the log entry with UK time (DD/MM/YYYY HH:MM:SS)
    $date = date('d/m/Y H:i:s');
    $log_entry = "[$date] $message";
    
    // Add data if provided, with proper formatting for arrays and objects
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_entry .= ': ' . print_r($data, true);
        } else {
            $log_entry .= ': ' . $data;
        }
    }
    
    // Write to log file with thread-safe file locking
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Send standardized JSON response with UK timestamp and exit
 * This formats our response for JavaScript to understand with proper headers
 * 
 * @param array $data - The data to send as JSON response
 */
function send_json($data) {
    // Clean any output buffer to prevent JSON corruption
    if (ob_get_length()) ob_clean();
    
    // Add UK timestamp to all responses for better debugging and client reference
    $data['timestamp_uk'] = date('d/m/Y H:i:s');
    $data['server_time'] = date('c'); // ISO 8601 format for compatibility
    
    // Send proper JSON header with UTF-8 encoding for international character support
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    // Output the JSON-encoded data with pretty printing for debugging
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    // Log the response for debugging purposes with response size
    write_log('Sending JSON response', [
        'action' => $data['action'] ?? 'unknown', 
        'success' => $data['ok'] ?? false,
        'response_size' => strlen(json_encode($data))
    ]);
    
    exit;
}

// Check if user is logged in for security - all operations require authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    write_log('Authentication failed - user not logged in', [
        'session_id' => session_id(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ]);
    
    send_json([
        'ok' => false,
        'error' => 'Authentication required',
        'redirect' => 'login.php',
        'error_code' => 'AUTH_REQUIRED',
        'message' => 'Please log in to access this resource'
    ]);
}

// Get the current user and project information from session for context
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'unknown';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;

// Log user activity for comprehensive audit trail
write_log('User activity started', [
    'username' => $username,
    'user_id' => $user_id,
    'project_id' => $project_id,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
]);

// Get the requested action from GET or POST parameters
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
write_log('Action requested', [
    'action' => $action, 
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data_size' => isset($_POST) ? count($_POST) : 0,
    'get_data_size' => isset($_GET) ? count($_GET) : 0
]);

// Test database connection using the centralized connection system
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
        'error_code' => 'DB_CONNECTION_FAILED',
        'details' => 'Unable to connect to the database server',
        'message' => 'Please check database configuration and connectivity'
    ]);
}

/**
 * Convert date strings to database format with comprehensive validation
 * Changes DD/MM/YYYY to YYYY-MM-DD for database storage while handling multiple formats gracefully
 * 
 * @param string $date Date string in various formats
 * @return string Date in YYYY-MM-DD format suitable for database operations
 */
function validate_date($date) {
    // If already in YYYY-MM-DD format, validate and return as is
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return $date;
        }
    }
    
    // If in UK format DD/MM/YYYY, convert to YYYY-MM-DD for database storage
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        
        $converted_date = "{$year}-{$month}-{$day}";
        $timestamp = strtotime($converted_date);
        
        if ($timestamp !== false) {
            return $converted_date;
        }
    }

    // Invalid format detected, log warning and default to today's date for safety
    write_log('Invalid date format, using today instead', [
        'provided_date' => $date,
        'default_date' => date('Y-m-d'),
        'uk_format' => date('d/m/Y')
    ]);
    return date('Y-m-d');
}

/**
 * Get or create briefing ID for a specific date with comprehensive error handling
 * Associates activities with a specific day and project, creates new briefings automatically when needed
 * 
 * @param string $date Date in YYYY-MM-DD format
 * @return int Briefing ID for the specified date and project
 */
function get_briefing_id($date) {
    global $project_id, $user_id, $username;
    
    try {
        // Check if a briefing already exists for this date and project
        $sql = "SELECT id FROM briefings WHERE project_id = ? AND date = ?";
        $result = fetchOne($sql, [$project_id, $date]);
        
        if ($result) {
            // Briefing exists, return its ID
            write_log('Found existing briefing', [
                'id' => $result['id'], 
                'date' => $date,
                'uk_date' => date('d/m/Y', strtotime($date))
            ]);
            return $result['id'];
        }
        
        // No briefing found, create one with comprehensive details
        $briefingData = [
            'project_id' => $project_id,
            'date' => $date,
            'created_by' => $user_id,
            'last_updated' => date('Y-m-d H:i:s'),
            'status' => 'draft'
        ];
        
        $new_id = insertData('briefings', $briefingData);
        
        if ($new_id === false) {
            throw new Exception('Failed to create new briefing');
        }
        
        write_log('Created new briefing', [
            'id' => $new_id, 
            'date' => $date,
            'uk_date' => date('d/m/Y', strtotime($date)),
            'project_id' => $project_id,
            'created_by' => $user_id,
            'created_by_username' => $username
        ]);
        
        // Log the briefing creation in activity_log for comprehensive audit trail
        $activityLogData = [
            'user_id' => $user_id,
            'action' => 'create_briefing',
            'details' => "Created new briefing for " . date('d/m/Y', strtotime($date)),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        insertData('activity_log', $activityLogData);
        
        return $new_id;
        
    } catch (Exception $e) {
        write_log('Error getting/creating briefing', [
            'message' => $e->getMessage(),
            'date' => $date,
            'project_id' => $project_id
        ]);
        throw $e; // Re-throw to be handled by the caller
    }
}

/**
 * Get comprehensive subcontractor information for frontend display and contractor mapping
 * Builds efficient lookup tables for contractor ID to name mapping using actual database structure
 * 
 * @return array Array containing subcontractors data and lookup maps for efficient processing
 */
function get_subcontractors_with_mapping() {
    global $project_id;
    
    try {
        // Get all subcontractors with their complete information using centralized database function
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
        
        $subcontractors = fetchAll($sql, [$project_id]);
        
        if ($subcontractors === false) {
            throw new Exception('Failed to retrieve subcontractors');
        }
        
        // Build efficient lookup maps for frontend processing
        $contractor_id_to_name = [];
        $contractor_id_to_trade = [];
        $contractor_id_to_info = [];
        
        foreach ($subcontractors as $sub) {
            $id = (string)$sub['id']; // Convert to string for consistent lookup operations
            $contractor_id_to_name[$id] = $sub['name'] ?: 'Unnamed Contractor';
            $contractor_id_to_trade[$id] = $sub['trade'] ?: 'No Trade';
            $contractor_id_to_info[$id] = [
                'name' => $sub['name'],
                'trade' => $sub['trade'],
                'status' => $sub['status'],
                'contact_name' => $sub['contact_name'],
                'phone' => $sub['phone'],
                'email' => $sub['email']
            ];
        }
        
        write_log('Built subcontractor lookup maps', [
            'total_subcontractors' => count($subcontractors),
            'active_contractors' => count(array_filter($subcontractors, function($s) { 
                return $s['status'] === 'Active'; 
            })),
            'sample_mapping' => array_slice($contractor_id_to_name, 0, 3, true),
            'project_id' => $project_id
        ]);
        
        return [
            'subcontractors' => $subcontractors,
            'contractor_id_to_name' => $contractor_id_to_name,
            'contractor_id_to_trade' => $contractor_id_to_trade,
            'contractor_id_to_info' => $contractor_id_to_info
        ];
        
    } catch (Exception $e) {
        write_log('Error getting subcontractors', [
            'message' => $e->getMessage(),
            'project_id' => $project_id
        ]);
        
        // Return empty arrays on error to prevent system crashes
        return [
            'subcontractors' => [],
            'contractor_id_to_name' => [],
            'contractor_id_to_trade' => [],
            'contractor_id_to_info' => []
        ];
    }
}
/**
 * LIST action - Get activities for a specific date with enhanced contractor mapping
 * Shows all activities scheduled for a day using actual database structure
 * This action retrieves all activities for a given date, processes contractor information
 * from the JSON field, and returns comprehensive data including statistics
 */
if ($action === 'list') {
    // Get date from query parameter, default to today in UK timezone
    $date = isset($_GET['date']) ? validate_date($_GET['date']) : date('Y-m-d');
    write_log('Listing activities for date', [
        'project_id' => $project_id, 
        'date' => $date,
        'uk_date' => date('d/m/Y', strtotime($date)),
        'requested_by' => $username
    ]);
    
    // Get previous day's date for reference and navigation functionality
    $prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));

    // Get comprehensive subcontractor information for frontend mapping and display
    $subcontractor_data = get_subcontractors_with_mapping();
    $contractor_id_to_name = $subcontractor_data['contractor_id_to_name'];
    $contractor_id_to_trade = $subcontractor_data['contractor_id_to_trade'];

    // BEGIN: Workers per contractor per day for the past 7 days (enhanced reporting for management)
    $contractor_daily = [];
    for ($i = 6; $i >= 0; $i--) {
        $loop_date = date('Y-m-d', strtotime($date . " -$i days"));
        $uk_date = date('d/m/Y', strtotime($loop_date));

        // Get all labor counts grouped by assigned_to for this date using centralized database function
        $sql = "SELECT 
                    COALESCE(a.assigned_to, 'Unassigned') as contractor, 
                    SUM(a.labor_count) as workers
                FROM briefings b
                JOIN activities a ON b.id = a.briefing_id
                WHERE b.project_id = ? AND b.date = ?
                GROUP BY a.assigned_to";
        
        $workers_data = fetchAll($sql, [$project_id, $loop_date]);
        
        $workers = [];
        foreach ($workers_data as $row) {
            $contractor_name = $row['contractor'] ?: 'Unassigned';
            $workers[$contractor_name] = (int)$row['workers'];
        }
        
        $contractor_daily[] = [
            'date' => $uk_date, 
            'date_iso' => $loop_date,
            'workers' => $workers
        ];
    }
    // END: Workers per contractor per day calculation

    try {
        // First check if a briefing exists for this date and project
        $briefing_sql = "SELECT id FROM briefings WHERE project_id = ? AND date = ?";
        $result = fetchOne($briefing_sql, [$project_id, $date]);
        
        // Get previous day's briefing ID if exists for copy functionality
        $prev_result = fetchOne($briefing_sql, [$project_id, $prev_date]);
        $prev_briefing_exists = !empty($prev_result);
        $prev_briefing_id = $prev_result ? $prev_result['id'] : null;
        
        if ($result) {
            $briefing_id = $result['id'];
            
            // Get all activities for this briefing with proper structure using actual table columns
            $activities_sql = "SELECT a.id, a.briefing_id, a.date, a.time, a.title, a.description, 
                                     a.area, a.priority, a.labor_count, a.contractors, a.assigned_to,
                                     a.created_at, a.updated_at
                              FROM activities a
                              WHERE a.briefing_id = ?
                              ORDER BY a.area ASC, 
                                       CASE a.priority 
                                           WHEN 'critical' THEN 4 
                                           WHEN 'high' THEN 3 
                                           WHEN 'medium' THEN 2 
                                           WHEN 'low' THEN 1 
                                           ELSE 0 
                                       END DESC, 
                                       a.title ASC";
            
            $activities = fetchAll($activities_sql, [$briefing_id]);
            
            if ($activities === false) {
                throw new Exception('Failed to retrieve activities from database');
            }
            
            // Process activities and enhance with contractor information
            foreach ($activities as &$activity) {
                // Parse contractors from JSON field in activities table
                $contractor_ids = [];
                $contractor_names = [];
                $contractor_details = [];
                
                if (!empty($activity['contractors'])) {
                    // Try to decode JSON contractors field
                    $decoded_contractors = json_decode($activity['contractors'], true);
                    
                    if (is_array($decoded_contractors)) {
                        foreach ($decoded_contractors as $contractor_id) {
                            if (isset($contractor_id_to_name[$contractor_id])) {
                                $contractor_ids[] = $contractor_id;
                                $contractor_names[] = $contractor_id_to_name[$contractor_id];
                                $contractor_details[] = [
                                    'id' => $contractor_id,
                                    'name' => $contractor_id_to_name[$contractor_id],
                                    'trade' => $contractor_id_to_trade[$contractor_id] ?? 'Unknown Trade'
                                ];
                            }
                        }
                    }
                }
                
                // Add enhanced contractor information to activity
                $activity['contractor_ids'] = $contractor_ids;
                $activity['contractor_names'] = $contractor_names;
                $activity['contractor_details'] = $contractor_details;
                
                // Format timestamps for UK display (DD/MM/YYYY HH:MM:SS)
                if ($activity['created_at']) {
                    $activity['created_at_uk'] = date('d/m/Y H:i:s', strtotime($activity['created_at']));
                }
                if ($activity['updated_at']) {
                    $activity['updated_at_uk'] = date('d/m/Y H:i:s', strtotime($activity['updated_at']));
                }
                
                // Format activity date for UK display
                if ($activity['date'] && $activity['date'] !== '0000-00-00') {
                    $activity['date_uk'] = date('d/m/Y', strtotime($activity['date']));
                } else {
                    $activity['date_uk'] = date('d/m/Y', strtotime($date));
                }
                
                // Ensure labor_count is an integer for consistent data types
                $activity['labor_count'] = (int)$activity['labor_count'];
            }
            
            // Group activities by area for better organization and display
            $activities_by_area = [];
            foreach ($activities as $activity) {
                $area = $activity['area'] ?: 'Unspecified';
                if (!isset($activities_by_area[$area])) {
                    $activities_by_area[$area] = [];
                }
                $activities_by_area[$area][] = $activity;
            }
            
            // Get weekly resource stats for comprehensive reporting (Monday to Sunday)
            $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
            
            // Get daily stats with UK date formatting using actual table structure
            $weekly_stats_sql = "SELECT DATE_FORMAT(b.date, '%d/%m/%Y') as day_date, 
                                        b.date as date_iso,
                                        SUM(a.labor_count) as total_labor,
                                        COUNT(DISTINCT a.id) as activity_count
                                 FROM briefings b
                                 LEFT JOIN activities a ON b.id = a.briefing_id
                                 WHERE b.project_id = ?
                                 AND b.date BETWEEN ? AND ?
                                 GROUP BY b.date
                                 ORDER BY b.date ASC";
            
            $weekly_stats = fetchAll($weekly_stats_sql, [$project_id, $week_start, $week_end]);
            
            // Get contractor-specific stats for the week using JSON contractors field
            $contractor_stats_sql = "SELECT DATE_FORMAT(b.date, '%d/%m/%Y') as day_date,
                                            b.date as date_iso,
                                            a.area,
                                            a.contractors,
                                            a.labor_count,
                                            COUNT(a.id) as activity_count
                                     FROM briefings b
                                     JOIN activities a ON b.id = a.briefing_id
                                     WHERE b.project_id = ?
                                     AND b.date BETWEEN ? AND ?
                                     AND a.contractors IS NOT NULL
                                     AND a.contractors != ''
                                     AND a.contractors != 'null'
                                     GROUP BY b.date, a.area, a.contractors
                                     ORDER BY b.date ASC, a.area ASC";
            
            $contractor_stats_raw = fetchAll($contractor_stats_sql, [$project_id, $week_start, $week_end]);
            
            // Process contractor stats to expand JSON contractor fields
            $contractor_stats = [];
            foreach ($contractor_stats_raw as $stat) {
                $decoded_contractors = json_decode($stat['contractors'], true);
                if (is_array($decoded_contractors)) {
                    foreach ($decoded_contractors as $contractor_id) {
                        if (isset($contractor_id_to_name[$contractor_id])) {
                            $contractor_stats[] = [
                                'day_date' => $stat['day_date'],
                                'date_iso' => $stat['date_iso'],
                                'contractor_name' => $contractor_id_to_name[$contractor_id],
                                'area' => $stat['area'],
                                'labor_count' => $stat['labor_count'],
                                'activity_count' => $stat['activity_count']
                            ];
                        }
                    }
                }
            }
            
            // Get area stats for the week using actual table structure
            $area_stats_sql = "SELECT DATE_FORMAT(b.date, '%d/%m/%Y') as day_date,
                                      b.date as date_iso,
                                      a.area,
                                      SUM(a.labor_count) as total_labor,
                                      COUNT(a.id) as activity_count
                               FROM briefings b
                               JOIN activities a ON b.id = a.briefing_id
                               WHERE b.project_id = ?
                               AND b.date BETWEEN ? AND ?
                               AND a.area IS NOT NULL
                               AND a.area != ''
                               GROUP BY b.date, a.area
                               ORDER BY b.date ASC, a.area ASC";
            
            $area_stats = fetchAll($area_stats_sql, [$project_id, $week_start, $week_end]);
            
            // Calculate totals for the day for summary information
            $total_labor = array_sum(array_column($activities, 'labor_count'));
            $total_contractors = [];
            
            foreach ($activities as $activity) {
                foreach ($activity['contractor_names'] as $contractor) {
                    if (!in_array($contractor, $total_contractors)) {
                        $total_contractors[] = $contractor;
                    }
                }
            }
            
            // Log successful operation and return comprehensive results
            write_log('Found activities with enhanced contractor mapping', [
                'activities_count' => count($activities),
                'areas_count' => count($activities_by_area),
                'total_labor' => $total_labor,
                'unique_contractors' => count($total_contractors),
                'briefing_id' => $briefing_id
            ]);
            
            send_json([
                'ok' => true,
                'action' => 'list',
                'date' => date('d/m/Y', strtotime($date)), // UK date format for display
                'date_iso' => $date, // ISO format for internal use
                'briefing_id' => $briefing_id,
                'activities' => $activities,
                'activities_by_area' => $activities_by_area,
                'count' => count($activities),
                'total_labor' => $total_labor,
                'total_contractors' => count($total_contractors),
                'contractor_names' => $total_contractors,
                'weekly_stats' => $weekly_stats,
                'contractor_stats' => $contractor_stats,
                'area_stats' => $area_stats,
                'contractor_daily' => $contractor_daily,
                'prev_briefing_exists' => $prev_briefing_exists,
                'prev_briefing_id' => $prev_briefing_id,
                'prev_date' => date('d/m/Y', strtotime($prev_date)),
                // Include subcontractor mapping data for frontend processing
                'subcontractors' => $subcontractor_data['subcontractors'],
                'contractor_id_to_name' => $contractor_id_to_name,
                'contractor_id_to_trade' => $contractor_id_to_trade,
                'contractor_mapping_info' => [
                    'total_mapped' => count($contractor_id_to_name),
                    'mapping_complete' => true
                ],
                'message' => 'Activities retrieved successfully'
            ]);
        } else {
            // No briefing exists yet for this date - return empty structure
            write_log('No briefing exists for date', [
                'date' => $date,
                'uk_date' => date('d/m/Y', strtotime($date)),
                'project_id' => $project_id
            ]);
            
            send_json([
                'ok' => true,
                'action' => 'list',
                'date' => date('d/m/Y', strtotime($date)), // UK date format for display
                'date_iso' => $date,
                'activities' => [],
                'activities_by_area' => [],
                'count' => 0,
                'total_labor' => 0,
                'total_contractors' => 0,
                'contractor_names' => [],
                'weekly_stats' => [],
                'contractor_stats' => [],
                'area_stats' => [],
                'contractor_daily' => $contractor_daily,
                'prev_briefing_exists' => $prev_briefing_exists,
                'prev_briefing_id' => $prev_briefing_id,
                'prev_date' => date('d/m/Y', strtotime($prev_date)),
                // Include subcontractor data even when no activities exist
                'subcontractors' => $subcontractor_data['subcontractors'],
                'contractor_id_to_name' => $contractor_id_to_name,
                'contractor_id_to_trade' => $contractor_id_to_trade,
                'contractor_mapping_info' => [
                    'total_mapped' => count($contractor_id_to_name),
                    'mapping_complete' => true
                ],
                'message' => 'No activities found for this date'
            ]);
        }
    } catch (Exception $e) {
        // Log and return any database errors with comprehensive details
        write_log('Error when listing activities', [
            'message' => $e->getMessage(),
            'date' => $date,
            'project_id' => $project_id
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'list',
            'error' => 'Failed to retrieve activities',
            'error_code' => 'LIST_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to retrieve activities for the specified date'
        ]);
    }
}

/**
 * ADD action - Add a new activity with enhanced resource management
 * Creates a new activity using the actual activities table structure
 * Validates all inputs and stores contractor information in JSON format
 */
if ($action === 'add') {
    // Get parameters from POST with comprehensive validation and UK date handling
    $date = isset($_POST['date']) ? validate_date($_POST['date']) : date('Y-m-d');
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $area = isset($_POST['area']) ? trim($_POST['area']) : '';
    $priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'medium';
    $assigned_to = isset($_POST['assigned_to']) ? trim($_POST['assigned_to']) : '';
    $labor_count = isset($_POST['labor_count']) ? intval($_POST['labor_count']) : 0;
    $contractors = isset($_POST['contractors']) ? json_decode($_POST['contractors'], true) : [];
    
    // Validate required fields for activity creation
    if (empty($title)) {
        write_log('Missing title for activity', [
            'provided_data' => array_keys($_POST), // Log keys only for security
            'user' => $username
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'add',
            'error' => 'Activity title is required',
            'error_code' => 'MISSING_TITLE',
            'message' => 'Please provide a title for the activity'
        ]);
    }
    
    // Ensure priority is valid according to enum in database
    if (!in_array($priority, ['low', 'medium', 'high', 'critical'])) {
        $priority = 'medium';
    }
    
    write_log('Adding activity with enhanced resource tracking', [
        'date' => $date,
        'uk_date' => date('d/m/Y', strtotime($date)),
        'title' => $title,
        'area' => $area,
        'priority' => $priority,
        'labor_count' => $labor_count,
        'contractors_count' => count($contractors),
        'user' => $username
    ]);
    
    try {
        // Get or create briefing ID for this date
        $briefing_id = get_briefing_id($date);
        
        // Prepare contractors JSON for storage with validation
        $contractors_json = null;
        if (!empty($contractors) && is_array($contractors)) {
            // Filter out any invalid contractor IDs and ensure they're numeric
            $valid_contractors = [];
            foreach ($contractors as $contractor) {
                if (is_numeric($contractor)) {
                    // Verify contractor exists in dabs_subcontractors table
                    $contractor_check_sql = "SELECT id FROM dabs_subcontractors WHERE id = ? AND project_id = ?";
                    $contractor_exists = fetchOne($contractor_check_sql, [$contractor, $project_id]);
                    if ($contractor_exists) {
                        $valid_contractors[] = (int)$contractor;
                    }
                }
            }
            
            if (!empty($valid_contractors)) {
                $contractors_json = json_encode($valid_contractors);
            }
        }
        
        // Prepare data for insertion using the centralized helper function
        $activityData = [
            'briefing_id' => $briefing_id,
            'date' => $date,
            'time' => '00:00:00',
            'title' => $title,
            'description' => $description,
            'area' => $area,
            'priority' => $priority,
            'labor_count' => $labor_count,
            'contractors' => $contractors_json,
            'assigned_to' => $assigned_to,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert the new activity using centralized database function
        $activity_id = insertData('activities', $activityData);
        
        if ($activity_id === false) {
            throw new Exception('Failed to insert activity into database');
        }
        
        write_log('Activity added successfully', [
            'activity_id' => $activity_id, 
            'title' => $title,
            'area' => $area,
            'labor_count' => $labor_count,
            'contractors_count' => count($contractors),
            'briefing_id' => $briefing_id,
            'created_by' => $username
        ]);
        
        // Return the newly created activity with comprehensive data
        send_json([
            'ok' => true,
            'action' => 'add',
            'id' => $activity_id,
            'briefing_id' => $briefing_id,
            'title' => $title,
            'description' => $description,
            'area' => $area,
            'priority' => $priority,
            'assigned_to' => $assigned_to,
            'labor_count' => $labor_count,
            'contractors' => $contractors,
            'contractors_json' => $contractors_json,
            'date' => date('d/m/Y', strtotime($date)),
            'date_iso' => $date,
            'created_at' => date('d/m/Y H:i:s'),
            'created_by' => $username,
            'message' => 'Activity created successfully'
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when adding activity', [
            'message' => $e->getMessage(),
            'activity_data' => [
                'title' => $title,
                'area' => $area,
                'date' => $date
            ]
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'add',
            'error' => 'Failed to create activity',
            'error_code' => 'ADD_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to create activity. Please try again.'
        ]);
    }
}
/**
 * UPDATE action - Edit an existing activity with enhanced validation
 * Modifies the details of an existing activity using the actual table structure
 * Validates all inputs and updates contractor information in JSON format
 */
if ($action === 'update') {
    // Get parameters from POST with comprehensive validation and UK date handling
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $area = isset($_POST['area']) ? trim($_POST['area']) : '';
    $priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'medium';
    $assigned_to = isset($_POST['assigned_to']) ? trim($_POST['assigned_to']) : '';
    $labor_count = isset($_POST['labor_count']) ? intval($_POST['labor_count']) : 0;
    $contractors = isset($_POST['contractors']) ? json_decode($_POST['contractors'], true) : [];
    
    // Validate required fields for activity update
    if ($id <= 0 || empty($title)) {
        write_log('Missing required fields for update', [
            'id' => $id,
            'title' => $title,
            'provided_data' => array_keys($_POST), // Log keys only for security
            'user' => $username
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'update',
            'error' => 'Activity ID and title are required',
            'error_code' => 'MISSING_REQUIRED_FIELDS',
            'message' => 'Please provide both activity ID and title for update'
        ]);
    }
    
    // Ensure priority is valid according to enum in database
    if (!in_array($priority, ['low', 'medium', 'high', 'critical'])) {
        $priority = 'medium';
    }
    
    write_log('Updating activity with enhanced resource management', [
        'id' => $id,
        'title' => $title,
        'area' => $area,
        'priority' => $priority,
        'labor_count' => $labor_count,
        'contractors_count' => count($contractors),
        'user' => $username
    ]);
    
    try {
        // First verify the activity exists and get its briefing info for security validation
        $verify_sql = "SELECT a.briefing_id, b.date, b.project_id 
                      FROM activities a 
                      JOIN briefings b ON a.briefing_id = b.id 
                      WHERE a.id = ? AND b.project_id = ?";
        $activity = fetchOne($verify_sql, [$id, $project_id]);
        
        if (!$activity) {
            write_log('Activity not found for update', [
                'activity_id' => $id,
                'project_id' => $project_id
            ]);
            
            send_json([
                'ok' => false,
                'action' => 'update',
                'error' => 'Activity not found',
                'error_code' => 'NOT_FOUND',
                'message' => 'The activity could not be found or access is denied'
            ]);
        }
        
        // Prepare contractors JSON for storage with validation
        $contractors_json = null;
        if (!empty($contractors) && is_array($contractors)) {
            // Filter out any invalid contractor IDs and ensure they're numeric
            $valid_contractors = [];
            foreach ($contractors as $contractor) {
                if (is_numeric($contractor)) {
                    // Verify contractor exists in dabs_subcontractors table
                    $contractor_check_sql = "SELECT id FROM dabs_subcontractors WHERE id = ? AND project_id = ?";
                    $contractor_exists = fetchOne($contractor_check_sql, [$contractor, $project_id]);
                    if ($contractor_exists) {
                        $valid_contractors[] = (int)$contractor;
                    }
                }
            }
            
            if (!empty($valid_contractors)) {
                $contractors_json = json_encode($valid_contractors);
            }
        }
        
        // Prepare data for update using the centralized helper function
        $updateData = [
            'title' => $title,
            'description' => $description,
            'area' => $area,
            'priority' => $priority,
            'assigned_to' => $assigned_to,
            'labor_count' => $labor_count,
            'contractors' => $contractors_json,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update the activity with new information using centralized database function
        $rowsAffected = updateData('activities', $updateData, 'id = ?', [$id]);
        
        if ($rowsAffected === false) {
            throw new Exception('Failed to update activity in database');
        }
        
        write_log('Activity updated successfully', [
            'id' => $id, 
            'title' => $title,
            'area' => $area,
            'labor_count' => $labor_count,
            'contractors_count' => count($contractors),
            'updated_by' => $username,
            'update_time' => date('d/m/Y H:i:s'),
            'rows_affected' => $rowsAffected
        ]);
        
        // Return the updated activity with comprehensive data
        send_json([
            'ok' => true,
            'action' => 'update',
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'area' => $area,
            'priority' => $priority,
            'assigned_to' => $assigned_to,
            'labor_count' => $labor_count,
            'contractors' => $contractors,
            'contractors_json' => $contractors_json,
            'date' => date('d/m/Y', strtotime($activity['date'])),
            'updated_at' => date('d/m/Y H:i:s'),
            'updated_by' => $username,
            'message' => 'Activity updated successfully'
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when updating activity', [
            'message' => $e->getMessage(),
            'activity_id' => $id,
            'activity_data' => [
                'title' => $title,
                'area' => $area
            ]
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'update',
            'error' => 'Failed to update activity',
            'error_code' => 'UPDATE_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to update activity. Please try again.'
        ]);
    }
}

/**
 * DELETE action - Remove an activity with comprehensive cleanup
 * Deletes an activity from the database using proper structure validation
 * Includes safety checks to ensure only authorized deletions occur
 */
if ($action === 'delete') {
    // Get parameters from POST with validation
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Validate the activity ID for deletion
    if ($id <= 0) {
        write_log('Invalid activity ID for delete', [
            'id' => $id,
            'user' => $username
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'delete',
            'error' => 'Invalid activity ID',
            'error_code' => 'INVALID_ID',
            'message' => 'Please provide a valid activity ID for deletion'
        ]);
    }
    
    write_log('Attempting to delete activity', [
        'id' => $id,
        'user' => $username,
        'timestamp' => date('d/m/Y H:i:s')
    ]);
    
    try {
        // Get the activity details before deletion for logging and verification
        $activity_details_sql = "SELECT a.title, a.area, a.labor_count, b.date 
                                FROM activities a 
                                JOIN briefings b ON a.briefing_id = b.id 
                                WHERE a.id = ? AND b.project_id = ?";
        $activity = fetchOne($activity_details_sql, [$id, $project_id]);
        
        if (!$activity) {
            write_log('Activity not found for deletion', [
                'activity_id' => $id,
                'project_id' => $project_id
            ]);
            
            send_json([
                'ok' => false,
                'action' => 'delete',
                'error' => 'Activity not found',
                'error_code' => 'NOT_FOUND',
                'message' => 'The activity could not be found or access is denied'
            ]);
        }
        
        $title = $activity['title'];
        $area = $activity['area'];
        $labor_count = $activity['labor_count'];
        $date = $activity['date'];
        
        // Delete the activity from the database using centralized function
        $deleted = deleteData('activities', 'id = ?', [$id]);
        
        if ($deleted === false) {
            throw new Exception('Failed to delete activity from database');
        }
        
        write_log('Deleted activity successfully', [
            'activity_id' => $id,
            'title' => $title,
            'area' => $area,
            'labor_count' => $labor_count,
            'date' => $date,
            'rows_deleted' => $deleted,
            'deleted_by' => $username,
            'deletion_time' => date('d/m/Y H:i:s')
        ]);
        
        // Return comprehensive deletion result
        send_json([
            'ok' => true,
            'action' => 'delete',
            'deleted' => $deleted > 0,
            'id' => $id,
            'title' => $title,
            'area' => $area,
            'labor_count' => $labor_count,
            'date' => date('d/m/Y', strtotime($date)),
            'message' => $deleted > 0 ? 'Activity deleted successfully' : 'Activity not found',
            'deleted_at' => date('d/m/Y H:i:s'),
            'deleted_by' => $username
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when deleting activity', [
            'message' => $e->getMessage(),
            'activity_id' => $id
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'delete',
            'error' => 'Failed to delete activity',
            'error_code' => 'DELETE_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to delete activity. Please try again.'
        ]);
    }
}

/**
 * GET action - Get details for one activity with comprehensive information
 * Retrieves full information about a specific activity using actual table structure
 * Includes enhanced contractor information and formatted timestamps
 */
if ($action === 'get') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        write_log('Invalid activity ID for get request', [
            'id' => $id,
            'user' => $username
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'get',
            'error' => 'Invalid activity ID',
            'error_code' => 'INVALID_ID',
            'message' => 'Please provide a valid activity ID'
        ]);
    }
    
    write_log('Getting activity details', [
        'id' => $id,
        'user' => $username
    ]);
    
    try {
        // Get the activity with its date and project verification using actual structure
        $activity_sql = "SELECT a.id, a.briefing_id, a.date, a.time, a.title, a.description, 
                                a.area, a.priority, a.labor_count, a.contractors, a.assigned_to,
                                a.created_at, a.updated_at, b.date as briefing_date
                        FROM activities a 
                        JOIN briefings b ON a.briefing_id = b.id 
                        WHERE a.id = ? AND b.project_id = ?";
        $activity = fetchOne($activity_sql, [$id, $project_id]);
        
        if (!$activity) {
            write_log('Activity not found', [
                'activity_id' => $id,
                'project_id' => $project_id
            ]);
            
            send_json([
                'ok' => false,
                'action' => 'get',
                'error' => 'Activity not found or access denied',
                'error_code' => 'NOT_FOUND',
                'message' => 'The requested activity could not be found'
            ]);
        }
        
        // Parse contractors from JSON field using subcontractor mapping
        $contractor_ids = [];
        $contractor_names = [];
        
        // Get subcontractor mapping for proper ID resolution
        $subcontractor_data = get_subcontractors_with_mapping();
        $contractor_id_to_name = $subcontractor_data['contractor_id_to_name'];
        $contractor_id_to_trade = $subcontractor_data['contractor_id_to_trade'];
        
        if (!empty($activity['contractors'])) {
            $decoded_contractors = json_decode($activity['contractors'], true);
            if (is_array($decoded_contractors)) {
                foreach ($decoded_contractors as $contractor_id) {
                    if (isset($contractor_id_to_name[$contractor_id])) {
                        $contractor_ids[] = $contractor_id;
                        $contractor_names[] = $contractor_id_to_name[$contractor_id];
                    }
                }
            }
        }
        
        // Format dates for display in UK format (DD/MM/YYYY)
        $activity['date_uk'] = date('d/m/Y', strtotime($activity['date'])); // UK format
        $activity['date_iso'] = $activity['date']; // ISO format for internal use
        $activity['contractor_ids'] = $contractor_ids;
        $activity['contractor_names'] = $contractor_names;
        
        // Format timestamps in UK format (DD/MM/YYYY HH:MM:SS)
        if ($activity['created_at']) {
            $activity['created_at_uk'] = date('d/m/Y H:i:s', strtotime($activity['created_at']));
        }
        if ($activity['updated_at']) {
            $activity['updated_at_uk'] = date('d/m/Y H:i:s', strtotime($activity['updated_at']));
        }
        
        // Ensure labor_count is an integer for consistent data types
        $activity['labor_count'] = (int)$activity['labor_count'];
        
        write_log('Activity details retrieved successfully', [
            'id' => $id,
            'title' => $activity['title'],
            'area' => $activity['area'],
            'labor_count' => $activity['labor_count'],
            'contractors_count' => count($contractor_ids)
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'get',
            'activity' => $activity,
            // Include subcontractor mapping for frontend
            'contractor_id_to_name' => $contractor_id_to_name,
            'contractor_id_to_trade' => $contractor_id_to_trade,
            'message' => 'Activity details retrieved successfully'
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when getting activity', [
            'message' => $e->getMessage(),
            'activity_id' => $id
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'get',
            'error' => 'Failed to retrieve activity',
            'error_code' => 'GET_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to retrieve activity details'
        ]);
    }
}

/**
 * GET_SUBCONTRACTORS action - Get list of subcontractors for dropdown with enhanced information
 * Returns all subcontractors for the current project from dabs_subcontractors table
 * Includes comprehensive contact information and UK formatted timestamps
 */
if ($action === 'get_subcontractors') {
    write_log('Getting subcontractors for project', [
        'project_id' => $project_id,
        'user' => $username
    ]);
    
    try {
        // Get comprehensive subcontractor information using actual table structure
        $subcontractor_data = get_subcontractors_with_mapping();
        $subcontractors = $subcontractor_data['subcontractors'];
        
        // Process subcontractors to add UK formatted timestamps and ensure data quality
        foreach ($subcontractors as &$sub) {
            // Add formatted timestamps for UK display (DD/MM/YYYY HH:MM:SS)
            if ($sub['created_at']) {
                $sub['created_at_uk'] = date('d/m/Y H:i:s', strtotime($sub['created_at']));
            }
            if ($sub['updated_at']) {
                $sub['updated_at_uk'] = date('d/m/Y H:i:s', strtotime($sub['updated_at']));
            }
            
            // Ensure all fields have proper defaults to prevent frontend errors
            $sub['contact_name'] = $sub['contact_name'] ?: '';
            $sub['phone'] = $sub['phone'] ?: '';
            $sub['email'] = $sub['email'] ?: '';
            $sub['status'] = $sub['status'] ?: 'Active';
        }
        
        write_log('Found subcontractors', [
            'count' => count($subcontractors),
            'active_count' => count(array_filter($subcontractors, function($s) { 
                return $s['status'] === 'Active'; 
            }))
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'get_subcontractors',
            'subcontractors' => $subcontractors,
            'total_count' => count($subcontractors),
            'contractor_id_to_name' => $subcontractor_data['contractor_id_to_name'],
            'contractor_id_to_trade' => $subcontractor_data['contractor_id_to_trade'],
            'message' => 'Subcontractors retrieved successfully'
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when getting subcontractors', [
            'message' => $e->getMessage(),
            'project_id' => $project_id
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'get_subcontractors',
            'error' => 'Failed to retrieve subcontractors',
            'error_code' => 'GET_SUBCONTRACTORS_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to retrieve subcontractor information'
        ]);
    }
}

/**
 * ADD_SUBCONTRACTOR action - Add a new subcontractor with enhanced validation
 * Adds a new subcontractor to the dabs_subcontractors table with proper validation
 * Includes duplicate checking and comprehensive error handling
 */
if ($action === 'add_subcontractor') {
    // Get parameters from POST with comprehensive validation
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $trade = isset($_POST['trade']) ? trim($_POST['trade']) : '';
    $contact_name = isset($_POST['contact_name']) ? trim($_POST['contact_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Active';
    
    // Validate required fields for subcontractor creation
    if (empty($name) || empty($trade)) {
        write_log('Missing required fields for subcontractor', [
            'provided_data' => array_keys($_POST), // Log keys only for security
            'user' => $username
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'add_subcontractor',
            'error' => 'Subcontractor name and trade are required',
            'error_code' => 'MISSING_REQUIRED_FIELDS',
            'message' => 'Please provide both name and trade for the subcontractor'
        ]);
    }
    
    write_log('Adding subcontractor', [
        'name' => $name,
        'trade' => $trade,
        'status' => $status,
        'project_id' => $project_id,
        'user' => $username
    ]);
    
    try {
        // Check if subcontractor already exists in this project
        $duplicate_check_sql = "SELECT id FROM dabs_subcontractors WHERE project_id = ? AND name = ?";
        $existing = fetchOne($duplicate_check_sql, [$project_id, $name]);
        
        if ($existing) {
            write_log('Duplicate subcontractor name detected', [
                'name' => $name,
                'existing_id' => $existing['id']
            ]);
            
            send_json([
                'ok' => false,
                'action' => 'add_subcontractor',
                'error' => 'A subcontractor with this name already exists in this project',
                'error_code' => 'DUPLICATE_NAME',
                'message' => 'Please choose a different name for the subcontractor'
            ]);
        }
        
        // Validate status against common values
        $valid_statuses = ['Active', 'Standby', 'Delayed', 'Complete', 'Offsite'];
        if (!in_array($status, $valid_statuses)) {
            $status = 'Active';
        }
        
        // Prepare data for insertion using the centralized helper function
        $subcontractorData = [
            'project_id' => $project_id,
            'name' => $name,
            'trade' => $trade,
            'contact_name' => $contact_name,
            'phone' => $phone,
            'email' => $email,
            'status' => $status,
            'created_by' => $username,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert the new subcontractor using centralized database function
        $id = insertData('dabs_subcontractors', $subcontractorData);
        
        if ($id === false) {
            throw new Exception('Failed to insert subcontractor into database');
        }
        
        write_log('Subcontractor added successfully', [
            'id' => $id, 
            'name' => $name,
            'trade' => $trade,
            'status' => $status,
            'created_by' => $username,
            'creation_time' => date('d/m/Y H:i:s')
        ]);
        
        // Return the newly created subcontractor with comprehensive data
        send_json([
            'ok' => true,
            'action' => 'add_subcontractor',
            'id' => $id,
            'name' => $name,
            'trade' => $trade,
            'contact_name' => $contact_name,
            'phone' => $phone,
            'email' => $email,
            'status' => $status,
            'created_at' => date('d/m/Y H:i:s'),
            'created_by' => $username,
            'message' => 'Subcontractor added successfully'
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when adding subcontractor', [
            'message' => $e->getMessage(),
            'subcontractor_data' => [
                'name' => $name,
                'trade' => $trade,
                'project_id' => $project_id
            ]
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'add_subcontractor',
            'error' => 'Failed to create subcontractor',
            'error_code' => 'ADD_SUBCONTRACTOR_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to add subcontractor. Please try again.'
        ]);
    }
}
/**
 * GET_RESOURCE_STATS action - Get comprehensive resource statistics for project management
 * Returns detailed labor and contractor statistics for a specified date range using actual table structure
 * Provides detailed analytics for project management, resource planning, and performance monitoring
 */
if ($action === 'get_resource_stats') {
    // Get date range parameters with UK date validation, default to current week
    $start_date = isset($_GET['start_date']) ? validate_date($_GET['start_date']) : date('Y-m-d', strtotime('monday this week'));
    $end_date = isset($_GET['end_date']) ? validate_date($_GET['end_date']) : date('Y-m-d', strtotime('sunday this week'));
    
    write_log('Getting comprehensive resource stats', [
        'project_id' => $project_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'uk_start_date' => date('d/m/Y', strtotime($start_date)),
        'uk_end_date' => date('d/m/Y', strtotime($end_date)),
        'requested_by' => $username
    ]);
    
    try {
        // Get daily stats with UK date formatting using actual table structure
        $daily_stats_sql = "SELECT DATE_FORMAT(b.date, '%d/%m/%Y') as day_date, 
                                   b.date as date_iso,
                                   SUM(a.labor_count) as labor_count,
                                   COUNT(DISTINCT a.id) as activity_count
                            FROM briefings b
                            LEFT JOIN activities a ON b.id = a.briefing_id
                            WHERE b.project_id = ?
                            AND b.date BETWEEN ? AND ?
                            GROUP BY b.date
                            ORDER BY b.date ASC";
        
        $daily_stats = fetchAll($daily_stats_sql, [$project_id, $start_date, $end_date]);
        
        // Get contractor-specific stats using JSON contractors field for detailed analysis
        $contractor_stats_sql = "SELECT 
                                    DATE_FORMAT(b.date, '%d/%m/%Y') as day_date,
                                    b.date as date_iso,
                                    a.area,
                                    a.contractors,
                                    a.labor_count,
                                    a.title
                                FROM briefings b
                                JOIN activities a ON b.id = a.briefing_id
                                WHERE b.project_id = ?
                                AND b.date BETWEEN ? AND ?
                                AND a.contractors IS NOT NULL
                                AND a.contractors != ''
                                AND a.contractors != 'null'
                                ORDER BY b.date ASC, a.area ASC";
        
        $contractor_stats_raw = fetchAll($contractor_stats_sql, [$project_id, $start_date, $end_date]);
        
        // Process contractor stats to expand JSON contractor fields for detailed reporting
        $contractor_stats = [];
        $subcontractor_data = get_subcontractors_with_mapping();
        $contractor_id_to_name = $subcontractor_data['contractor_id_to_name'];
        
        foreach ($contractor_stats_raw as $stat) {
            $decoded_contractors = json_decode($stat['contractors'], true);
            if (is_array($decoded_contractors)) {
                foreach ($decoded_contractors as $contractor_id) {
                    if (isset($contractor_id_to_name[$contractor_id])) {
                        $contractor_stats[] = [
                            'day_date' => $stat['day_date'],
                            'date_iso' => $stat['date_iso'],
                            'contractor_name' => $contractor_id_to_name[$contractor_id],
                            'area' => $stat['area'],
                            'labor_count' => (int)$stat['labor_count'],
                            'activity_title' => $stat['title']
                        ];
                    }
                }
            }
        }
        
        // Get area-specific stats using actual table structure for project area analysis
        $area_stats_sql = "SELECT 
                              DATE_FORMAT(b.date, '%d/%m/%Y') as day_date,
                              b.date as date_iso,
                              a.area,
                              SUM(a.labor_count) as labor_count,
                              COUNT(a.id) as activity_count
                          FROM briefings b
                          JOIN activities a ON b.id = a.briefing_id
                          WHERE b.project_id = ?
                          AND b.date BETWEEN ? AND ?
                          AND a.area IS NOT NULL AND a.area != ''
                          GROUP BY b.date, a.area
                          ORDER BY b.date ASC, a.area ASC";
        
        $area_stats = fetchAll($area_stats_sql, [$project_id, $start_date, $end_date]);
        
        // Get total stats for the period using actual table structure for summary information
        $total_stats_sql = "SELECT 
                               SUM(a.labor_count) as total_labor,
                               COUNT(DISTINCT a.id) as total_activities,
                               COUNT(DISTINCT a.area) as total_areas,
                               COUNT(DISTINCT b.date) as total_days
                           FROM briefings b
                           LEFT JOIN activities a ON b.id = a.briefing_id
                           WHERE b.project_id = ?
                           AND b.date BETWEEN ? AND ?";
        
        $total_stats = fetchOne($total_stats_sql, [$project_id, $start_date, $end_date]);
        
        // Get unique contractor list with additional information for management reporting
        $contractors_sql = "SELECT DISTINCT s.name, s.trade, s.status,
                                   COUNT(a.id) as total_assignments,
                                   COUNT(DISTINCT b.date) as days_worked,
                                   GROUP_CONCAT(DISTINCT a.area SEPARATOR ', ') as areas_worked
                           FROM briefings b
                           JOIN activities a ON b.id = a.briefing_id
                           JOIN dabs_subcontractors s ON JSON_CONTAINS(a.contractors, CAST(s.id AS JSON))
                           WHERE b.project_id = ?
                           AND b.date BETWEEN ? AND ?
                           AND a.contractors IS NOT NULL
                           GROUP BY s.id, s.name, s.trade, s.status
                           ORDER BY s.name ASC";
        
        $contractors = fetchAll($contractors_sql, [$project_id, $start_date, $end_date]);
        
        // Get unique areas list with statistics using actual table structure for area analysis
        $areas_sql = "SELECT DISTINCT a.area,
                             SUM(a.labor_count) as total_labor,
                             COUNT(DISTINCT a.id) as total_activities,
                             COUNT(DISTINCT b.date) as days_active
                     FROM briefings b
                     JOIN activities a ON b.id = a.briefing_id
                     WHERE b.project_id = ?
                     AND b.date BETWEEN ? AND ?
                     AND a.area IS NOT NULL AND a.area != ''
                     GROUP BY a.area
                     ORDER BY a.area ASC";
        
        $areas = fetchAll($areas_sql, [$project_id, $start_date, $end_date]);
        
        write_log('Resource stats retrieved successfully', [
            'daily_stats_count' => count($daily_stats),
            'contractor_stats_count' => count($contractor_stats),
            'area_stats_count' => count($area_stats),
            'total_labor' => $total_stats['total_labor'],
            'total_activities' => $total_stats['total_activities']
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'get_resource_stats',
            'start_date' => date('d/m/Y', strtotime($start_date)),
            'end_date' => date('d/m/Y', strtotime($end_date)),
            'start_date_iso' => $start_date,
            'end_date_iso' => $end_date,
            'daily_stats' => $daily_stats,
            'contractor_stats' => $contractor_stats,
            'area_stats' => $area_stats,
            'total_labor' => (int)$total_stats['total_labor'],
            'total_activities' => (int)$total_stats['total_activities'],
            'total_areas' => (int)$total_stats['total_areas'],
            'total_days' => (int)$total_stats['total_days'],
            'contractor_list' => $contractors,
            'area_list' => $areas,
            'period_summary' => [
                'labor_per_day' => $total_stats['total_days'] > 0 ? round($total_stats['total_labor'] / $total_stats['total_days'], 2) : 0,
                'activities_per_day' => $total_stats['total_days'] > 0 ? round($total_stats['total_activities'] / $total_stats['total_days'], 2) : 0
            ],
            'message' => 'Resource statistics retrieved successfully'
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when getting resource stats', [
            'message' => $e->getMessage(),
            'date_range' => "$start_date to $end_date"
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'get_resource_stats',
            'error' => 'Failed to retrieve resource statistics',
            'error_code' => 'RESOURCE_STATS_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to retrieve resource statistics'
        ]);
    }
}

/**
 * GET_AREAS action - Get list of areas for dropdown with comprehensive usage statistics
 * Returns all areas used in the system using actual activities table with detailed analytics
 * Provides comprehensive usage analytics for each construction area for management planning
 */
if ($action === 'get_areas') {
    write_log('Getting areas for project with usage statistics', [
        'project_id' => $project_id,
        'requested_by' => $username
    ]);
    
    try {
        // Get unique areas from activities with usage statistics using actual table structure
        $areas_sql = "SELECT DISTINCT a.area,
                             COUNT(a.id) as activity_count,
                             SUM(a.labor_count) as total_labor,
                             COUNT(DISTINCT DATE_FORMAT(b.date, '%Y-%m')) as months_used,
                             MAX(b.date) as last_used_date,
                             MIN(b.date) as first_used_date
                     FROM activities a
                     JOIN briefings b ON a.briefing_id = b.id
                     WHERE b.project_id = ?
                     AND a.area IS NOT NULL AND a.area != ''
                     GROUP BY a.area
                     ORDER BY activity_count DESC, a.area ASC";
        
        $areas_with_stats = fetchAll($areas_sql, [$project_id]);
        
        if ($areas_with_stats === false) {
            throw new Exception('Failed to retrieve areas from database');
        }
        
        // Format dates for display and ensure proper data types for frontend compatibility
        foreach ($areas_with_stats as &$area) {
            if ($area['last_used_date']) {
                $area['last_used_date_uk'] = date('d/m/Y', strtotime($area['last_used_date']));
            }
            if ($area['first_used_date']) {
                $area['first_used_date_uk'] = date('d/m/Y', strtotime($area['first_used_date']));
            }
            
            // Ensure numeric fields are properly typed for JavaScript consumption
            $area['activity_count'] = (int)$area['activity_count'];
            $area['total_labor'] = (int)$area['total_labor'];
            $area['months_used'] = (int)$area['months_used'];
        }
        
        // Extract simple area names for backward compatibility with existing frontend code
        $areas = array_column($areas_with_stats, 'area');
        
        write_log('Found areas with statistics', [
            'count' => count($areas),
            'most_used' => !empty($areas_with_stats) ? $areas_with_stats[0]['area'] : 'None'
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'get_areas',
            'areas' => $areas,
            'areas_with_stats' => $areas_with_stats,
            'total_count' => count($areas),
            'usage_summary' => [
                'most_active_area' => !empty($areas_with_stats) ? $areas_with_stats[0]['area'] : null,
                'total_activities' => array_sum(array_column($areas_with_stats, 'activity_count')),
                'total_labor' => array_sum(array_column($areas_with_stats, 'total_labor'))
            ],
            'message' => 'Areas retrieved successfully'
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when getting areas', [
            'message' => $e->getMessage(),
            'project_id' => $project_id
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'get_areas',
            'error' => 'Failed to retrieve areas',
            'error_code' => 'GET_AREAS_ERROR',
            'details' => $e->getMessage(),
            'areas' => [], // Return empty array as fallback for frontend stability
            'message' => 'Unable to retrieve area information'
        ]);
    }
}

/**
 * COPY_PREV_DAY action - Copy activities from previous day with comprehensive validation
 * Copies all activities from the previous working day using actual table structure
 * Includes comprehensive validation and error handling for safe operations and data integrity
 */
if ($action === 'copy_prev_day') {
    // Get date parameters with UK date validation for copy operation
    $target_date = isset($_POST['target_date']) ? validate_date($_POST['target_date']) : date('Y-m-d');
    $source_date = isset($_POST['source_date']) ? validate_date($_POST['source_date']) : date('Y-m-d', strtotime('-1 day', strtotime($target_date)));
    
    write_log('Copying activities from previous day', [
        'source_date' => $source_date,
        'target_date' => $target_date,
        'source_date_uk' => date('d/m/Y', strtotime($source_date)),
        'target_date_uk' => date('d/m/Y', strtotime($target_date)),
        'user' => $username,
        'timestamp' => date('d/m/Y H:i:s')
    ]);
    
    try {
        // Check if source briefing exists for the copy operation
        $source_briefing_sql = "SELECT id FROM briefings WHERE project_id = ? AND date = ?";
        $source_briefing = fetchOne($source_briefing_sql, [$project_id, $source_date]);
        
        if (!$source_briefing) {
            write_log('No source briefing found for copy operation', [
                'source_date' => $source_date,
                'project_id' => $project_id
            ]);
            
            send_json([
                'ok' => false,
                'action' => 'copy_prev_day',
                'error' => 'No activities found for the source date',
                'error_code' => 'NO_SOURCE_ACTIVITIES',
                'source_date' => date('d/m/Y', strtotime($source_date)),
                'message' => 'Cannot copy from a date with no activities'
            ]);
        }
        
        $source_briefing_id = $source_briefing['id'];
        
        // Get or create target briefing for the copy destination
        $target_briefing_id = get_briefing_id($target_date);
        
        // Check if target date already has activities to prevent accidental overwriting
        $existing_activities_sql = "SELECT COUNT(*) as count FROM activities WHERE briefing_id = ?";
        $existing_activities = fetchOne($existing_activities_sql, [$target_briefing_id]);
        
        if ($existing_activities['count'] > 0) {
            write_log('Target date already has activities', [
                'target_date' => $target_date,
                'existing_count' => $existing_activities['count']
            ]);
            
            send_json([
                'ok' => false,
                'action' => 'copy_prev_day',
                'error' => 'Target date already has activities. Please delete them first if you want to copy.',
                'error_code' => 'TARGET_HAS_ACTIVITIES',
                'target_date' => date('d/m/Y', strtotime($target_date)),
                'existing_count' => $existing_activities['count'],
                'message' => 'Cannot overwrite existing activities'
            ]);
        }
        
        // Get all activities from source date using actual table structure for copying
        $source_activities_sql = "SELECT date, time, title, description, area, priority, 
                                         labor_count, contractors, assigned_to
                                 FROM activities 
                                 WHERE briefing_id = ? 
                                 ORDER BY area ASC, 
                                          CASE priority 
                                              WHEN 'critical' THEN 4 
                                              WHEN 'high' THEN 3 
                                              WHEN 'medium' THEN 2 
                                              WHEN 'low' THEN 1 
                                              ELSE 0 
                                          END DESC, 
                                          title ASC";
        
        $source_activities = fetchAll($source_activities_sql, [$source_briefing_id]);
        
        if (empty($source_activities)) {
            write_log('No activities found to copy', [
                'source_date' => $source_date,
                'source_briefing_id' => $source_briefing_id
            ]);
            
            send_json([
                'ok' => false,
                'action' => 'copy_prev_day',
                'error' => 'No activities found to copy from source date',
                'error_code' => 'NO_ACTIVITIES_TO_COPY',
                'source_date' => date('d/m/Y', strtotime($source_date)),
                'message' => 'Source date contains no activities to copy'
            ]);
        }
        
        // Copy activities using actual table structure with centralized database functions
        $copied_activities = 0;
        
        foreach ($source_activities as $activity) {
            // Prepare data for insertion with updated date and timestamps
            $activityData = [
                'briefing_id' => $target_briefing_id,
                'date' => $target_date, // Use target date instead of source date
                'time' => $activity['time'],
                'title' => $activity['title'],
                'description' => $activity['description'],
                'area' => $activity['area'],
                'priority' => $activity['priority'],
                'labor_count' => $activity['labor_count'],
                'contractors' => $activity['contractors'],
                'assigned_to' => $activity['assigned_to'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $inserted_id = insertData('activities', $activityData);
            
            if ($inserted_id !== false) {
                $copied_activities++;
            }
        }
        
        // Log the copy operation in activity_log for comprehensive audit trail
        $activityLogData = [
            'user_id' => $user_id,
            'action' => 'copy_activities',
            'details' => "Copied {$copied_activities} activities from " . date('d/m/Y', strtotime($source_date)) . " to " . date('d/m/Y', strtotime($target_date)),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        insertData('activity_log', $activityLogData);
        
        write_log('Activities copied successfully', [
            'source_date' => $source_date,
            'target_date' => $target_date,
            'activities_copied' => $copied_activities,
            'copied_by' => $username,
            'copy_time' => date('d/m/Y H:i:s')
        ]);
        
        send_json([
            'ok' => true,
            'action' => 'copy_prev_day',
            'source_date' => date('d/m/Y', strtotime($source_date)),
            'target_date' => date('d/m/Y', strtotime($target_date)),
            'activities_copied' => $copied_activities,
            'target_briefing_id' => $target_briefing_id,
            'message' => "Successfully copied {$copied_activities} activities",
            'copied_at' => date('d/m/Y H:i:s'),
            'copied_by' => $username
        ]);
        
    } catch (Exception $e) {
        // Log and return comprehensive error information
        write_log('Error when copying activities', [
            'message' => $e->getMessage(),
            'source_date' => $source_date,
            'target_date' => $target_date
        ]);
        
        send_json([
            'ok' => false,
            'action' => 'copy_prev_day',
            'error' => 'Failed to copy activities',
            'error_code' => 'COPY_ERROR',
            'details' => $e->getMessage(),
            'message' => 'Unable to copy activities. Please try again.'
        ]);
    }
}

// Handle unrecognized actions with comprehensive error reporting
if (!in_array($action, ['list', 'add', 'update', 'delete', 'get', 'get_subcontractors', 'add_subcontractor', 'get_resource_stats', 'get_areas', 'copy_prev_day'])) {
    write_log('Unknown action requested', [
        'action' => $action,
        'user' => $username,
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'timestamp' => date('d/m/Y H:i:s'),
        'valid_actions' => [
            'list', 'add', 'update', 'delete', 'get', 
            'get_subcontractors', 'add_subcontractor',
            'get_resource_stats', 'get_areas', 'copy_prev_day'
        ]
    ]);

    send_json([
        'ok' => false,
        'action' => $action ?: 'none',
        'error' => 'Unknown or missing action',
        'error_code' => 'UNKNOWN_ACTION', 
        'received_action' => $action,
        'valid_actions' => [
            'list', 'add', 'update', 'delete', 'get', 
            'get_subcontractors', 'add_subcontractor',
            'get_resource_stats', 'get_areas', 'copy_prev_day'
        ],
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'help' => 'Please specify a valid action parameter',
        'message' => 'Invalid or missing action parameter',
        'processed_by' => $username,
        'processing_time' => date('d/m/Y H:i:s')
    ]);
}

/**
 * =========================================================================
 * END OF FILE: ajax_activities.php
 * =========================================================================
 * 
 * SUMMARY OF MAJOR IMPROVEMENTS IN VERSION 6.0.0:
 * 
 * 1. 🔗 CENTRALIZED DATABASE CONNECTION
 *    - Integrated with includes/db_connect.php for consistency across application
 *    - Uses centralized helper functions: fetchAll(), fetchOne(), insertData(), updateData(), deleteData()
 *    - Eliminates code duplication and ensures consistent error handling
 * 
 * 2. 🇬🇧 COMPREHENSIVE UK TIME FORMATTING
 *    - All timestamps display in DD/MM/YYYY HH:MM:SS format throughout
 *    - Consistent timezone handling with Europe/London timezone setting
 *    - Proper date conversion between UK format and database format
 * 
 * 3. 🛡️ ENHANCED SECURITY & VALIDATION
 *    - Comprehensive input validation and sanitization for all parameters
 *    - Project-based data isolation preventing unauthorized access
 *    - Prepared statements and SQL injection prevention through helper functions
 *    - Session-based authentication with detailed logging
 * 
 * 4. 📊 ADVANCED RESOURCE MANAGEMENT
 *    - Enhanced contractor assignment using JSON storage in activities table
 *    - Comprehensive statistics and reporting for project management
 *    - Weekly and daily resource allocation tracking
 *    - Area-based activity organization and analytics
 * 
 * 5. 🔍 COMPREHENSIVE AUDIT LOGGING
 *    - Detailed operation logging with UK timestamps for debugging
 *    - User activity tracking for security and compliance
 *    - Error logging with context for troubleshooting
 *    - Operation success/failure tracking with metrics
 * 
 * 6. 💻 MODERN PHP STANDARDS
 *    - PHP 8+ compatible code with type safety and error handling
 *    - Clean, well-documented code structure with inline comments
 *    - Consistent response formatting across all API endpoints
 *    - Proper exception handling and error recovery
 * 
 * 7. 🔄 ENHANCED FUNCTIONALITY
 *    - Copy activities from previous days for operational efficiency
 *    - Multiple contractor assignments per activity with JSON storage
 *    - Comprehensive briefing management with automatic creation
 *    - Resource statistics and analytics for management reporting
 * 
 * FILE DESCRIPTION:
 * This file serves as the main backend API for the Daily Activity Briefing 
 * System (DABS). It handles all AJAX requests from the frontend to manage 
 * construction site activities, labor tracking, subcontractor assignments, 
 * and generates comprehensive reports for project management. The system 
 * allows project managers to create daily briefings with activities assigned 
 * to different construction areas, track labor requirements, manage contractor 
 * assignments, and generate detailed statistics for planning and compliance.
 * 
 * The file integrates with the centralized database connection system and 
 * maintains all data in UK time format for consistency with UK-based 
 * construction operations.
 * 
 * Last Updated: 16/06/2025 21:57:06 (UK Time)
 * Updated By: irlam (System Administrator)
 * Version: 6.0.0 - Centralized Database Integration & Modern Standards
 * File Name: ajax_activities.php
 * =========================================================================
 */
?>