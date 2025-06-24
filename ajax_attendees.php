<?php
/**
 * =========================================================================
 * ajax_attendees.php - Daily Activity Briefing System (DABS)
 * =========================================================================
 *
 * Purpose: Handles all AJAX operations for the Attendees panel (listing, adding, 
 * removing) with support for subcontractor associations.
 * 
 * Author: irlamkeep
 * Date: 03/06/2025
 * Version: 2.2
 *
 * KEY FEATURES:
 * - Listing all attendees for a specific briefing date
 *   along with attendees from previous dates.
 * - Adding new attendees to a briefing with optional subcontractor name
 * - Removing attendees from a briefing
 *
 * Database: Works with dabs_attendees table
 * Date Format: All dates stored as YYYY-MM-DD but displayed as DD/MM/YYYY (UK format)
 * Timezone: Europe/London for proper UK time handling
 * 
 * This file receives AJAX requests from the Attendees panel in index.php
 * and returns JSON responses for the JavaScript to process.
 * =========================================================================
 */

// Set timezone to Europe/London for UK time
date_default_timezone_set('Europe/London');

// Start output buffering to prevent accidental output before headers
ob_start();

// Start session to access user/project info
session_start();

// Set up logging for debugging
$log_file = __DIR__ . '/logs/attendees_debug.log';

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Write a message to the debug log file
 * @param string $message - The message to log
 * @param mixed $data - Optional data to include
 */
function write_log($message, $data = null) {
    global $log_file;
    // Format the log entry with UK time
    $date = date('d/m/Y H:i:s');
    $log_entry = "[$date] $message";
    
    // Add data if provided
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_entry .= ': ' . print_r($data, true);
        } else {
            $log_entry .= ': ' . $data;
        }
    }
    
    // Write to log file with append mode
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND);
}

/**
 * Send a JSON response and exit
 * @param array $data - The data to send as JSON
 */
function send_json($data) {
    // Clean any output buffer
    if (ob_get_length()) ob_clean();
    
    // Send proper JSON header
    header('Content-Type: application/json');
    
    // Output the JSON-encoded data
    echo json_encode($data);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    write_log('Authentication failed - user not logged in');
    send_json(['error' => 'Not logged in', 'redirect' => 'login.php']);
}

// Get the current project ID from the session
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;
// Get username for tracking who made changes
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'unknown';

// Get the requested action from GET or POST
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
write_log('Action requested', $action);

// Connect to the database
try {
    // Database connection details
    $db_host = '10.35.233.124:3306';
    $db_name = 'k87747_dabs'; // Your database name
    $db_user = 'k87747_dabs'; // Your database user
    $db_pass = 'Subaru5554346'; // Your database password

    // Connect using PDO with error handling
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    write_log('Database connection successful');
} catch (PDOException $e) {
    // Log and report database connection errors
    write_log('Database connection error', $e->getMessage());
    send_json(['error' => 'Database connection failed: ' . $e->getMessage()]);
}

/**
 * Helper function to validate and convert date formats
 * Handles both UK format (DD/MM/YYYY) and ISO format (YYYY-MM-DD)
 * 
 * @param string $date - The date string to validate
 * @return string - The date in YYYY-MM-DD format
 */
function validate_date($date) {
    // If already in YYYY-MM-DD format, return as is
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    
    // If in UK format DD/MM/YYYY, convert to YYYY-MM-DD
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
    }

    // Invalid format, default to today's date
    write_log('Invalid date format, using today instead', $date);
    return date('Y-m-d');
}

/**
 * Check if the subcontractor_name column exists in the dabs_attendees table
 * If not, add it (this helps with upgrading from previous versions)
 */
function check_add_subcontractor_column() {
    global $pdo;
    
    try {
        // Check if column exists by trying to select from it
        try {
            $stmt = $pdo->query("SELECT subcontractor_name FROM dabs_attendees LIMIT 1");
            write_log('Subcontractor column already exists in attendees table');
            return true; // Column exists
        } catch (PDOException $e) {
            // Column doesn't exist, add it
            write_log('Adding subcontractor_name column to attendees table');
            $pdo->exec("ALTER TABLE dabs_attendees ADD COLUMN subcontractor_name VARCHAR(100) NULL AFTER attendee_name");
            write_log('Added subcontractor_name column successfully');
            return true;
        }
    } catch (PDOException $e) {
        write_log('Error checking/adding subcontractor column', $e->getMessage());
        return false;
    }
}

// Make sure the table has the subcontractor_name column
check_add_subcontractor_column();

/**
 * LIST action - Get attendees for a specific date as well as previous attendees
 * Endpoint: ajax_attendees.php?action=list&date=YYYY-MM-DD
 * Response: JSON object containing today's attendees and previous attendees.
 */
if ($action === 'list') {
    // Get date from query parameter, default to today
    $date = isset($_GET['date']) ? validate_date($_GET['date']) : date('Y-m-d');
    write_log('Listing attendees for date', ['project_id' => $project_id, 'date' => $date]);

    try {
        // Query to get today's attendees for this project and date
        $stmt = $pdo->prepare("SELECT id, attendee_name, subcontractor_name FROM dabs_attendees 
                              WHERE project_id = ? AND briefing_date = ? 
                              ORDER BY added_at");
        $stmt->execute([$project_id, $date]);
        $today_attendees = $stmt->fetchAll();

        // Query to get previous attendees (all records with briefing dates before the given date)
        $stmt = $pdo->prepare("SELECT id, attendee_name, subcontractor_name, briefing_date FROM dabs_attendees 
                              WHERE project_id = ? AND briefing_date < ? 
                              ORDER BY briefing_date DESC, added_at ASC");
        $stmt->execute([$project_id, $date]);
        $previous_attendees = $stmt->fetchAll();
        
        write_log('Found today attendees and previous attendees', [
            'today_count' => count($today_attendees),
            'previous_count' => count($previous_attendees)
        ]);
        send_json([
            'ok' => true,
            'date' => date('d/m/Y', strtotime($date)), // UK format
            'today_attendees' => $today_attendees,
            'previous_attendees' => $previous_attendees,
            'total_today' => count($today_attendees),
            'total_previous' => count($previous_attendees)
        ]);
    } catch (PDOException $e) {
        write_log('Database error when listing attendees', $e->getMessage());
        send_json(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * ADD action - Add a new attendee
 * Endpoint: POST to ajax_attendees.php with action=add, date=YYYY-MM-DD, name=Attendee Name, subcontractor=Subcontractor Name
 * Response: JSON confirmation with attendee details
 */
if ($action === 'add') {
    // Get parameters from POST
    $date = isset($_POST['date']) ? validate_date($_POST['date']) : date('Y-m-d');
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $subcontractor = isset($_POST['subcontractor']) ? trim($_POST['subcontractor']) : '';
    
    // Validate the attendee name
    if (empty($name) || strlen($name) < 2) {
        write_log('Invalid attendee name (too short)', $name);
        send_json(['error' => 'Invalid attendee name. Please use at least 2 characters.']);
    }
    
    write_log('Adding attendee', [
        'project_id' => $project_id, 
        'date' => $date, 
        'name' => $name,
        'subcontractor' => $subcontractor
    ]);
    
    try {
        // Check if this attendee already exists for this date/project
        $stmt = $pdo->prepare("SELECT id FROM dabs_attendees 
                              WHERE project_id = ? AND briefing_date = ? AND attendee_name = ?");
        $stmt->execute([$project_id, $date, $name]);
        if ($stmt->fetch()) {
            write_log('Attendee already exists', $name);
            send_json([
                'ok' => false,
                'error' => 'This attendee is already added for today.'
            ]);
        }
        
        // Insert the new attendee
        $stmt = $pdo->prepare("INSERT INTO dabs_attendees 
                              (project_id, briefing_date, attendee_name, subcontractor_name, added_by, added_at) 
                              VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$project_id, $date, $name, $subcontractor, $username]);
        
        $id = $pdo->lastInsertId();
        write_log('Added attendee successfully', [
            'id' => $id, 
            'name' => $name,
            'subcontractor' => $subcontractor
        ]);
        send_json([
            'ok' => true,
            'id' => $id,
            'name' => $name,
            'subcontractor' => $subcontractor,
            'date' => date('d/m/Y', strtotime($date))
        ]);
    } catch (PDOException $e) {
        write_log('Database error when adding attendee', $e->getMessage());
        send_json(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * DELETE action - Remove an attendee
 * Endpoint: POST to ajax_attendees.php with action=delete, date=YYYY-MM-DD, name=Attendee Name
 * Response: JSON confirmation of deletion
 */
if ($action === 'delete') {
    // Get parameters from POST
    $date = isset($_POST['date']) ? validate_date($_POST['date']) : date('Y-m-d');
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    
    if ($id <= 0 && empty($name)) {
        write_log('Missing attendee ID or name for delete operation');
        send_json(['error' => 'Missing attendee identification']);
    }
    
    write_log('Deleting attendee', [
        'project_id' => $project_id, 
        'date' => $date, 
        'id' => $id,
        'name' => $name
    ]);
    
    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM dabs_attendees 
                                  WHERE id = ? AND project_id = ? AND briefing_date = ?");
            $stmt->execute([$id, $project_id, $date]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM dabs_attendees 
                                  WHERE project_id = ? AND briefing_date = ? AND attendee_name = ?");
            $stmt->execute([$project_id, $date, $name]);
        }
        $deleted = $stmt->rowCount();
        write_log('Deleted attendee result', [
            'id' => $id,
            'name' => $name, 
            'rows_affected' => $deleted
        ]);
        send_json([
            'ok' => true,
            'deleted' => $deleted > 0,
            'id' => $id,
            'name' => $name,
            'message' => $deleted > 0 ? 'Attendee removed' : 'Attendee not found'
        ]);
    } catch (PDOException $e) {
        write_log('Database error when deleting attendee', $e->getMessage());
        send_json(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// If we get here, the action was not recognized
write_log('Unknown action requested', $action);
send_json([
    'error' => 'Unknown action', 
    'received' => $action, 
    'valid_actions' => ['list', 'add', 'delete']
]);
?>