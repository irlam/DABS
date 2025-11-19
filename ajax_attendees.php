<?php
/**
 * =========================================================================
 * ATTENDEES MANAGEMENT AJAX HANDLER
 * Daily Activity Briefing System (DABS) - Version 4.1
 * =========================================================================
 * 
 * FILE NAME: ajax_attendees.php
 * DESCRIPTION: This file provides a complete AJAX API for managing construction
 *              site attendees within the DABS system. It handles viewing all
 *              attendees stored in the database, adding new attendees with
 *              optional subcontractor associations, and removing attendees
 *              as needed. The system displays all attendees for the current
 *              project regardless of their original briefing date, providing
 *              a comprehensive view of all personnel who have attended
 *              briefings on the project.
 * 
 * CREATED BY: irlam
 * CREATED DATE: 24/06/2025 20:36:54 (UK Time)
 * LAST MODIFIED: 24/06/2025 20:36:54 (UK Time)
 * VERSION: 4.1
 * 
 * MAIN FUNCTIONALITY:
 * ==================
 * 1. LIST ATTENDEES: Displays all attendees from the 'dabs_attendees' table
 *    for the current project with full details including names, subcontractors,
 *    original briefing dates, and who added them.
 * 
 * 2. ADD ATTENDEES: Creates new attendee records with validation to prevent
 *    duplicates and ensures data integrity with proper audit trails.
 * 
 * 3. DELETE ATTENDEES: Removes attendee records by ID with proper authorization
 *    checks to ensure users can only delete attendees from their project.
 * 
 * TECHNICAL FEATURES:
 * ==================
 * - Works with 'dabs_attendees' MySQL table as specified
 * - All times displayed in UK format (DD/MM/YYYY HH:MM:SS)
 * - Timezone set to Europe/London for accurate UK time handling
 * - Modern PHP 8.x compatible code with comprehensive error handling
 * - Session-based authentication with project isolation
 * - JSON API responses with proper HTTP status codes
 * - SQL injection prevention using prepared statements
 * - Complete audit trail for all operations
 * - Automatic database schema upgrades for backward compatibility
 * 
 * DATABASE TABLE: dabs_attendees
 * COLUMNS: id, project_id, briefing_date, attendee_name, subcontractor_name,
 *          added_by, added_at
 * 
 * API ENDPOINTS:
 * =============
 * GET  ?action=list                    : Show all attendees in database
 * POST action=add&name=X&subcontractor=Y : Add new attendee
 * POST action=delete&id=X              : Remove attendee by ID
 * 
 * SECURITY: Session authentication required, project-based access control,
 *           input validation, comprehensive logging
 * 
 * TIMEZONE: Europe/London (UK Time)
 * CHARACTER SET: UTF-8
 * =========================================================================
 */

// =========================================================================
// SYSTEM INITIALIZATION AND CONFIGURATION
// =========================================================================

/**
 * Set the system timezone to UK time for all date and time operations
 * This ensures all timestamps throughout the system use UK time formatting
 */
date_default_timezone_set('Europe/London');

/**
 * Start output buffering to prevent any accidental output before headers
 * This is crucial for proper JSON response handling
 */
ob_start();

/**
 * Initialize PHP session for user authentication and project management
 * Sessions store user login status and current project information
 */
session_start();

// =========================================================================
// LOGGING SYSTEM SETUP
// =========================================================================

/**
 * Define the path for the debug log file
 * All system activities, errors, and operations are logged here for debugging
 */
$log_file = __DIR__ . '/logs/attendees_debug.log';

/**
 * Create the logs directory if it doesn't exist
 * Set proper permissions (755) for security while allowing write access
 */
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * COMPREHENSIVE LOGGING FUNCTION
 * 
 * This function handles all logging throughout the system with UK time formatting.
 * It supports multiple data types and log levels for comprehensive debugging
 * and audit trail maintenance.
 * 
 * @param string $message The main log message describing what happened
 * @param mixed $data Optional additional data (arrays, objects, strings, numbers)
 * @param string $level Log level: INFO, ERROR, DEBUG, WARNING, SUCCESS
 * @return void
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Provide detailed logging for debugging and audit purposes
 */
function write_log($message, $data = null, $level = 'INFO') {
    global $log_file;
    
    // Generate UK formatted timestamp for consistent log entries
    $uk_timestamp = date('d/m/Y H:i:s');
    
    // Build the main log entry with timestamp and level indicator
    $log_entry = "[{$uk_timestamp}] [{$level}] {$message}";
    
    // Add additional data if provided, with smart formatting for readability
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            // For complex data, use pretty printing with proper indentation
            $log_entry .= "\n" . print_r($data, true);
        } else {
            // For simple data, append inline for easy reading
            $log_entry .= " | Data: {$data}";
        }
    }
    
    // Write to log file with file locking for thread safety in multi-user environments
    file_put_contents($log_file, $log_entry . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * MODERN JSON RESPONSE HANDLER
 * 
 * This function sends properly formatted JSON responses with appropriate HTTP
 * headers and status codes. It handles output buffer cleanup and prevents
 * common issues with JSON corruption.
 * 
 * @param array $data The response data to be JSON encoded and sent
 * @param int $http_code HTTP status code (200=success, 400=bad request, etc.)
 * @return void (function exits after sending response)
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Ensure consistent, proper JSON API responses
 */
function send_json($data, $http_code = 200) {
    // Clean any existing output buffer content to prevent JSON corruption
    if (ob_get_length()) {
        ob_clean();
    }
    
    // Set the appropriate HTTP response status code
    http_response_code($http_code);
    
    // Set proper JSON content type with UTF-8 encoding for international characters
    header('Content-Type: application/json; charset=utf-8');
    
    // Prevent response caching for dynamic API content
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Encode the data as JSON with pretty printing for readability
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    // Handle JSON encoding errors gracefully
    if ($json === false) {
        write_log('JSON encoding failed', json_last_error_msg(), 'ERROR');
        echo json_encode([
            'error' => 'Internal server error',
            'message' => 'Failed to encode response data',
            'timestamp' => date('d/m/Y H:i:s')
        ]);
    } else {
        echo $json;
    }
    
    // Exit to prevent any additional output that could corrupt the JSON
    exit;
}

/**
 * AUTHENTICATION VERIFICATION SYSTEM
 * 
 * This function checks if the user is properly logged in and has valid session
 * credentials. It logs all authentication attempts for security monitoring
 * and provides appropriate error responses for unauthorized access.
 * 
 * @return void (exits if authentication fails)
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Ensure only authenticated users can access the API
 */
function check_authentication() {
    // Verify that the user session contains valid authentication credentials
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        
        // Log the unauthorized access attempt with relevant security details
        write_log('Authentication failed - unauthorized access attempt detected', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'timestamp' => date('d/m/Y H:i:s')
        ], 'WARNING');
        
        // Send unauthorized response with redirect information for client handling
        send_json([
            'error' => 'Authentication required',
            'message' => 'You must be logged in to access this resource',
            'redirect' => 'login.php',
            'timestamp' => date('d/m/Y H:i:s')
        ], 401);
    }
}

// =========================================================================
// AUTHENTICATION AND SESSION MANAGEMENT
// =========================================================================

/**
 * Perform authentication check before allowing any operations
 * This ensures all API endpoints are protected and secure
 */
check_authentication();

/**
 * Extract and validate session variables with safe defaults
 * These variables identify the current user and their active project
 */
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'unknown';

/**
 * Determine the requested action from GET or POST parameters
 * The system supports both query string and form submission methods
 * If no action is specified, default to 'list' to show all attendees
 */
$action = '';
if (isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
} else {
    // Default action: show all attendees when no specific action is requested
    $action = 'list';
}

/**
 * Log the incoming API request with comprehensive details for debugging
 * This helps track system usage and troubleshoot any issues
 */
write_log('API request initiated', [
    'action' => $action,
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'user' => $username,
    'project_id' => $project_id,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 100),
    'timestamp' => date('d/m/Y H:i:s')
]);

// =========================================================================
// DATABASE CONNECTION MANAGEMENT
// =========================================================================

/**
 * ENHANCED DATABASE CONNECTION MANAGER
 * 
 * This function establishes a secure, optimized connection to the MySQL database
 * using modern PDO with comprehensive error handling and security configurations.
 * It includes proper charset handling for international characters.
 * 
 * @return PDO The configured database connection object
 * @throws Exception If connection fails
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Provide secure, reliable database connectivity
 */
function get_database_connection() {
    try {
        // Database configuration parameters - modify these for your environment
        $config = [
            'host' => '10.35.233.124',
            'port' => '3306',
            'dbname' => 'k87747_dabs',
            'username' => 'k87747_dabs',
            'password' => 'Subaru5554346',
            'charset' => 'utf8mb4'
        ];
        
        // Build the Data Source Name (DSN) string for PDO connection
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        
        // Configure PDO options for security, performance, and reliability
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           // Enable exception handling
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Return associative arrays
            PDO::ATTR_EMULATE_PREPARES => false,                  // Use real prepared statements
            PDO::ATTR_PERSISTENT => false,                        // Disable persistent connections
            PDO::ATTR_TIMEOUT => 30,                              // Set connection timeout
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true            // Buffer query results in memory
        ];
        
        // Create the PDO database connection with error handling
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        
        write_log('Database connection established successfully', [
            'host' => $config['host'],
            'database' => $config['dbname'],
            'charset' => $config['charset'],
            'connection_time' => date('d/m/Y H:i:s')
        ]);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log database connection errors with sanitized details for security
        write_log('Database connection failed', [
            'error_code' => $e->getCode(),
            'error_message' => $e->getMessage(),
            'timestamp' => date('d/m/Y H:i:s')
        ], 'ERROR');
        
        // Send user-friendly error response without exposing sensitive details
        send_json([
            'error' => 'Database connection failed',
            'message' => 'Unable to connect to the database. Please try again later.',
            'timestamp' => date('d/m/Y H:i:s')
        ], 500);
    }
}

/**
 * Establish the database connection for use throughout the script
 * This connection will be used for all database operations
 */
$pdo = get_database_connection();

// =========================================================================
// UTILITY FUNCTIONS
// =========================================================================

/**
 * ADVANCED DATE VALIDATION AND CONVERSION FUNCTION
 * 
 * This function handles multiple date formats and provides intelligent conversion
 * between UK format (DD/MM/YYYY) and ISO format (YYYY-MM-DD) with comprehensive
 * validation to ensure data integrity and proper database storage.
 * 
 * @param string $date The date string to validate and convert
 * @return string The date in YYYY-MM-DD format for database storage
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Ensure consistent date handling across the system
 */
function validate_and_convert_date($date) {
    // Return today's date if input is empty or null
    if (empty($date)) {
        return date('Y-m-d');
    }
    
    // Handle ISO format (YYYY-MM-DD) - validate and return if already correct
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $parts = explode('-', $date);
        if (checkdate($parts[1], $parts[2], $parts[0])) {
            return $date;
        }
    }
    
    // Handle UK format (DD/MM/YYYY) - convert to ISO format for database storage
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        
        // Validate the converted date to ensure it's a real date
        if (checkdate($month, $day, $year)) {
            return "{$year}-{$month}-{$day}";
        }
    }
    
    // Attempt to parse other common date formats using PHP's strtotime function
    $timestamp = strtotime($date);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    // Log warning for invalid date format and return today's date as safe fallback
    write_log('Invalid date format provided, using current date as fallback', [
        'provided_date' => $date,
        'fallback_date' => date('Y-m-d'),
        'timestamp' => date('d/m/Y H:i:s')
    ], 'WARNING');
    
    return date('Y-m-d');
}

/**
 * DATABASE SCHEMA MANAGEMENT FUNCTION
 * 
 * This function ensures the 'dabs_attendees' table has all required columns
 * by checking for the subcontractor_name and email columns and adding them if necessary.
 * This provides backward compatibility when upgrading from older versions.
 * 
 * @return bool True if schema is current or successfully upgraded
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Maintain database schema compatibility across versions
 */
function ensure_database_schema() {
    global $pdo;
    
    try {
        // Check if the subcontractor_name column exists in the dabs_attendees table
        $stmt = $pdo->query("SHOW COLUMNS FROM dabs_attendees LIKE 'subcontractor_name'");
        
        // If the column doesn't exist, add it to maintain compatibility
        if ($stmt->rowCount() === 0) {
            write_log('Adding subcontractor_name column to dabs_attendees table');
            
            $pdo->exec("
                ALTER TABLE dabs_attendees 
                ADD COLUMN subcontractor_name VARCHAR(100) NULL 
                AFTER attendee_name
                COMMENT 'Name of the subcontractor company associated with this attendee'
            ");
            
            write_log('Database schema upgraded successfully - subcontractor_name column added');
        }
        
        // Check if the email column exists in the dabs_attendees table
        $stmt = $pdo->query("SHOW COLUMNS FROM dabs_attendees LIKE 'email'");
        
        // If the column doesn't exist, add it to maintain compatibility
        if ($stmt->rowCount() === 0) {
            write_log('Adding email column to dabs_attendees table');
            
            $pdo->exec("
                ALTER TABLE dabs_attendees 
                ADD COLUMN email VARCHAR(100) NULL 
                AFTER subcontractor_name
                COMMENT 'Email address for pre-filling reports'
            ");
            
            write_log('Database schema upgraded successfully - email column added');
        } else {
            write_log('Database schema is current - all required columns present in dabs_attendees table');
        }
        
        return true;
        
    } catch (PDOException $e) {
        write_log('Database schema check/upgrade failed for dabs_attendees table', [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'timestamp' => date('d/m/Y H:i:s')
        ], 'ERROR');
        
        return false;
    }
}

/**
 * Ensure the database schema is current before proceeding with operations
 * This prevents errors if the system is upgraded from an older version
 */
ensure_database_schema();

// =========================================================================
// API ENDPOINT HANDLERS
// =========================================================================

/**
 * LIST ACTION HANDLER - RETRIEVE ALL ATTENDEES
 * =============================================
 * 
 * This endpoint retrieves and returns ALL attendees stored in the 'dabs_attendees'
 * table for the current project. It provides comprehensive attendee information
 * including names, subcontractor associations, original briefing dates, and
 * audit trail information. Results are sorted alphabetically for optimal display.
 * 
 * HTTP Method: GET
 * Parameters: None required (action defaults to 'list' if not specified)
 * Table Used: dabs_attendees
 * 
 * Response Format:
 * {
 *   "success": true,
 *   "attendees": [
 *     {
 *       "id": 123,
 *       "attendee_name": "John Smith",
 *       "subcontractor_name": "ABC Construction Ltd",
 *       "briefing_date_uk": "24/06/2025",
 *       "added_by": "irlam",
 *       "added_at_uk": "24/06/2025 08:30:15"
 *     }
 *   ],
 *   "total": 1,
 *   "project_id": 1,
 *   "timestamp": "24/06/2025 20:36:54"
 * }
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Display all attendees from the database for the current project
 */
if ($action === 'list') {
    write_log('Processing attendee list request - retrieving all attendees from dabs_attendees table', [
        'project_id' => $project_id,
        'user' => $username,
        'request_time' => date('d/m/Y H:i:s')
    ]);
    
    try {
        // Prepare SQL query to retrieve all attendees from dabs_attendees table for current project
        $stmt = $pdo->prepare("
            SELECT 
                id,
                attendee_name,
                COALESCE(subcontractor_name, 'N/A') as subcontractor_name,
                COALESCE(email, '') as email,
                DATE_FORMAT(briefing_date, '%d/%m/%Y') as briefing_date_uk,
                COALESCE(added_by, 'Unknown') as added_by,
                DATE_FORMAT(added_at, '%d/%m/%Y %H:%i:%s') as added_at_uk,
                briefing_date as briefing_date_raw
            FROM dabs_attendees 
            WHERE project_id = ? 
            ORDER BY attendee_name ASC, briefing_date DESC
        ");
        
        // Execute the query with the current project ID to get project-specific attendees
        $stmt->execute([$project_id]);
        $attendees = $stmt->fetchAll();
        
        write_log('Attendee list retrieved successfully from dabs_attendees table', [
            'total_count' => count($attendees),
            'project_id' => $project_id,
            'retrieval_time' => date('d/m/Y H:i:s')
        ]);
        
        // Send successful response with all attendee data from the database
        send_json([
            'success' => true,
            'attendees' => $attendees,
            'total' => count($attendees),
            'project_id' => $project_id,
            'table_used' => 'dabs_attendees',
            'message' => count($attendees) > 0 ? 
                'Attendees retrieved successfully from database' : 
                'No attendees found in database for this project',
            'timestamp' => date('d/m/Y H:i:s')
        ]);
        
    } catch (PDOException $e) {
        write_log('Database error during attendee list retrieval from dabs_attendees table', [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'project_id' => $project_id,
            'table' => 'dabs_attendees'
        ], 'ERROR');
        
        send_json([
            'error' => 'Database query failed',
            'message' => 'Unable to retrieve attendee list from database. Please try again.',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 500);
    }
}

/**
 * ADD ACTION HANDLER - CREATE NEW ATTENDEE
 * ========================================
 * 
 * This endpoint creates a new attendee record in the 'dabs_attendees' table
 * with comprehensive validation, duplicate checking, and proper audit trail
 * maintenance. It supports optional subcontractor associations and allows
 * custom briefing dates while defaulting to the current date.
 * 
 * HTTP Method: POST
 * Parameters:
 *   - name: Attendee full name (required, minimum 2 characters)
 *   - subcontractor: Subcontractor company name (optional)
 *   - date: Briefing date (optional, defaults to today, accepts DD/MM/YYYY or YYYY-MM-DD)
 * Table Used: dabs_attendees
 * 
 * Response Format:
 * {
 *   "success": true,
 *   "id": 124,
 *   "name": "Jane Doe",
 *   "subcontractor": "XYZ Engineering",
 *   "briefing_date": "24/06/2025",
 *   "message": "Attendee added successfully to database",
 *   "timestamp": "24/06/2025 20:36:54"
 * }
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Add new attendees to the dabs_attendees table with validation
 */
if ($action === 'add') {
    // Extract and sanitize input parameters from the POST request
    $date = isset($_POST['date']) ? validate_and_convert_date($_POST['date']) : date('Y-m-d');
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $subcontractor = isset($_POST['subcontractor']) ? trim($_POST['subcontractor']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        write_log('Attendee email validation failed - invalid email format', ['email' => $email], 'WARNING');
        send_json([
            'error' => 'Invalid email',
            'message' => 'Please provide a valid email address',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 400);
    }
    
    // Comprehensive name validation with detailed error reporting
    if (empty($name)) {
        write_log('Attendee name validation failed - empty name provided for dabs_attendees insertion', null, 'WARNING');
        send_json([
            'error' => 'Missing attendee name',
            'message' => 'Attendee name is required and cannot be empty',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 400);
    }
    
    if (strlen($name) < 2) {
        write_log('Attendee name validation failed - name too short for dabs_attendees', ['name' => $name, 'length' => strlen($name)], 'WARNING');
        send_json([
            'error' => 'Invalid attendee name',
            'message' => 'Attendee name must be at least 2 characters long',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 400);
    }
    
    if (strlen($name) > 255) {
        write_log('Attendee name validation failed - name too long for dabs_attendees', ['name' => substr($name, 0, 50) . '...', 'length' => strlen($name)], 'WARNING');
        send_json([
            'error' => 'Invalid attendee name',
            'message' => 'Attendee name cannot exceed 255 characters',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 400);
    }
    
    write_log('Processing add attendee request for dabs_attendees table', [
        'date' => $date,
        'name' => $name,
        'subcontractor' => $subcontractor,
        'project_id' => $project_id,
        'user' => $username,
        'request_time' => date('d/m/Y H:i:s')
    ]);
    
    try {
        // Check for duplicate attendee entries in dabs_attendees table (same name, project, and date)
        $stmt = $pdo->prepare("
            SELECT id, attendee_name, subcontractor_name 
            FROM dabs_attendees 
            WHERE project_id = ? AND briefing_date = ? AND LOWER(attendee_name) = LOWER(?)
        ");
        $stmt->execute([$project_id, $date, $name]);
        $existing_attendee = $stmt->fetch();
        
        if ($existing_attendee) {
            write_log('Duplicate attendee detected in dabs_attendees table during add operation', [
                'existing_id' => $existing_attendee['id'],
                'name' => $name,
                'date' => $date,
                'project_id' => $project_id
            ], 'WARNING');
            
            send_json([
                'error' => 'Duplicate attendee',
                'message' => 'This attendee is already registered for the selected date in the database',
                'existing_attendee' => $existing_attendee,
                'table' => 'dabs_attendees',
                'timestamp' => date('d/m/Y H:i:s')
            ], 409);
        }
        
        // Insert the new attendee record into dabs_attendees table with full audit trail
        $stmt = $pdo->prepare("
            INSERT INTO dabs_attendees 
            (project_id, briefing_date, attendee_name, subcontractor_name, email, added_by, added_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$project_id, $date, $name, $subcontractor, $email, $username]);
        $new_id = $pdo->lastInsertId();
        
        write_log('Attendee added successfully to dabs_attendees table', [
            'id' => $new_id,
            'name' => $name,
            'subcontractor' => $subcontractor,
            'briefing_date' => $date,
            'project_id' => $project_id,
            'added_by' => $username,
            'success_time' => date('d/m/Y H:i:s')
        ], 'SUCCESS');
        
        // Send successful response with new attendee details
        send_json([
            'success' => true,
            'id' => $new_id,
            'name' => $name,
            'subcontractor' => $subcontractor,
            'briefing_date' => date('d/m/Y', strtotime($date)),
            'table' => 'dabs_attendees',
            'message' => 'Attendee added successfully to database',
            'timestamp' => date('d/m/Y H:i:s')
        ]);
        
    } catch (PDOException $e) {
        write_log('Database error during attendee creation in dabs_attendees table', [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'name' => $name,
            'project_id' => $project_id,
            'table' => 'dabs_attendees'
        ], 'ERROR');
        
        send_json([
            'error' => 'Database operation failed',
            'message' => 'Unable to add attendee record to database. Please try again.',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 500);
    }
}

/**
 * DELETE ACTION HANDLER - REMOVE ATTENDEE
 * =======================================
 * 
 * This endpoint removes an attendee record from the 'dabs_attendees' table by ID
 * with proper authorization checks and comprehensive logging. It ensures that
 * only attendees belonging to the current project can be deleted and maintains
 * a complete audit trail of all deletion operations.
 * 
 * HTTP Method: POST
 * Parameters:
 *   - id: Attendee ID (required, must be positive integer)
 * Table Used: dabs_attendees
 * 
 * Response Format:
 * {
 *   "success": true,
 *   "deleted": true,
 *   "id": 123,
 *   "attendee_name": "John Smith",
 *   "message": "Attendee removed successfully from database",
 *   "timestamp": "24/06/2025 20:36:54"
 * }
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Remove attendees from the dabs_attendees table with authorization
 */
if ($action === 'delete') {
    // Extract and validate the attendee ID from the request
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Validate the attendee ID parameter
    if ($id <= 0) {
        write_log('Invalid delete request - missing or invalid attendee ID for dabs_attendees table', [
            'provided_id' => $_POST['id'] ?? 'not provided',
            'converted_id' => $id
        ], 'WARNING');
        
        send_json([
            'error' => 'Invalid request',
            'message' => 'Valid attendee ID must be provided for deletion from database',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 400);
    }
    
    write_log('Processing delete attendee request for dabs_attendees table', [
        'id' => $id,
        'project_id' => $project_id,
        'user' => $username,
        'request_time' => date('d/m/Y H:i:s')
    ]);
    
    try {
        // First, retrieve attendee details from dabs_attendees table before deletion for logging
        $stmt = $pdo->prepare("
            SELECT 
                id,
                attendee_name,
                subcontractor_name,
                DATE_FORMAT(briefing_date, '%d/%m/%Y') as briefing_date_uk,
                added_by,
                DATE_FORMAT(added_at, '%d/%m/%Y %H:%i:%s') as added_at_uk
            FROM dabs_attendees 
            WHERE id = ? AND project_id = ?
        ");
        $stmt->execute([$id, $project_id]);
        $attendee_info = $stmt->fetch();
        
        // Check if the attendee exists in dabs_attendees table and belongs to current project
        if (!$attendee_info) {
            write_log('Attendee not found in dabs_attendees table for deletion or access denied', [
                'id' => $id,
                'project_id' => $project_id,
                'user' => $username
            ], 'WARNING');
            
            send_json([
                'error' => 'Attendee not found',
                'message' => 'The specified attendee does not exist in the database or does not belong to this project',
                'table' => 'dabs_attendees',
                'timestamp' => date('d/m/Y H:i:s')
            ], 404);
        }
        
        // Perform the deletion operation on dabs_attendees table
        $stmt = $pdo->prepare("
            DELETE FROM dabs_attendees 
            WHERE id = ? AND project_id = ?
        ");
        $stmt->execute([$id, $project_id]);
        
        $deleted_count = $stmt->rowCount();
        
        write_log('Delete operation completed successfully on dabs_attendees table', [
            'id' => $id,
            'attendee_name' => $attendee_info['attendee_name'],
            'subcontractor_name' => $attendee_info['subcontractor_name'],
            'briefing_date' => $attendee_info['briefing_date_uk'],
            'originally_added_by' => $attendee_info['added_by'],
            'deleted_by' => $username,
            'rows_deleted' => $deleted_count,
            'deletion_time' => date('d/m/Y H:i:s')
        ], 'SUCCESS');
        
        // Send successful deletion response
        send_json([
            'success' => true,
            'deleted' => $deleted_count > 0,
            'id' => $id,
            'attendee_name' => $attendee_info['attendee_name'],
            'subcontractor_name' => $attendee_info['subcontractor_name'],
            'briefing_date' => $attendee_info['briefing_date_uk'],
            'table' => 'dabs_attendees',
            'message' => $deleted_count > 0 ? 'Attendee removed successfully from database' : 'Attendee could not be deleted from database',
            'timestamp' => date('d/m/Y H:i:s')
        ]);
        
    } catch (PDOException $e) {
        write_log('Database error during attendee deletion from dabs_attendees table', [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'id' => $id,
            'project_id' => $project_id,
            'table' => 'dabs_attendees'
        ], 'ERROR');
        
        send_json([
            'error' => 'Database operation failed',
            'message' => 'Unable to delete attendee record from database. Please try again.',
            'table' => 'dabs_attendees',
            'timestamp' => date('d/m/Y H:i:s')
        ], 500);
    }
}

// =========================================================================
// ERROR HANDLING FOR INVALID ACTIONS
// =========================================================================

/**
 * INVALID ACTION HANDLER
 * ======================
 * 
 * This section handles requests with invalid or unsupported actions and provides
 * helpful error messages to guide users toward correct API usage. It logs all
 * invalid requests for monitoring and debugging purposes and works specifically
 * with the 'dabs_attendees' table.
 * 
 * Created: 24/06/2025 20:36:54 (UK Time)
 * Purpose: Handle invalid API requests and provide helpful guidance
 */
write_log('Invalid or unsupported action requested for dabs_attendees operations', [
    'received_action' => $action,
    'valid_actions' => ['list', 'add', 'delete'],
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'user' => $username,
    'project_id' => $project_id,
    'table' => 'dabs_attendees'
], 'WARNING');

send_json([
    'error' => 'Invalid action',
    'message' => 'The requested action is not supported by this API endpoint',
    'received_action' => $action,
    'table_used' => 'dabs_attendees',
    'valid_actions' => [
        'list' => 'Retrieve all attendees from dabs_attendees table for the current project',
        'add' => 'Add a new attendee to dabs_attendees table with name and optional subcontractor',
        'delete' => 'Remove an attendee from dabs_attendees table by ID'
    ],
    'examples' => [
        'list' => 'GET ?action=list (shows all attendees from dabs_attendees)',
        'add' => 'POST action=add&name=John%20Smith&subcontractor=ABC%20Ltd (adds to dabs_attendees)',
        'delete' => 'POST action=delete&id=123 (removes from dabs_attendees)'
    ],
    'timestamp' => date('d/m/Y H:i:s')
], 400);

?>