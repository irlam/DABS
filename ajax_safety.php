<?php
/**
 * =========================================================================
 * ajax_safety.php - Daily Activity Briefing System (DABS)
 * =========================================================================
 *
 * Purpose: Handles AJAX requests to update the Safety Information for today's briefing.
 * It uses a PDO database connection (similar to ajax_attendees.php) to update the 
 * "safety_info" column in the "briefings" table, as well as the "updated_by" (foreign key to users)
 * and "last_updated" timestamp, for the current project and today's date.
 *
 * All times are in UK format. This file logs errors to the logs/safety_errors.log file.
 *
 * Changes:
 * - Uses PDO for database connection (as per ajax_attendees.php).
 * - Retrieves "updated_by" from the session as the user id rather than the username,
 *   so that it satisfies the foreign key constraint in the briefings table.
 *
 * Author: irlamkeep / Chris Irlam
 * Date: 07/06/2025
 */

date_default_timezone_set('Europe/London');
ob_start();
session_start();

$log_file = __DIR__ . '/logs/safety_errors.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Write a message to the error log file.
 *
 * @param string $message - The message to log.
 * @param mixed $data - Optional data to include.
 */
function write_log($message, $data = null) {
    global $log_file;
    $date = date('d/m/Y H:i:s');
    $log_entry = "[$date] $message";
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_entry .= ': ' . print_r($data, true);
        } else {
            $log_entry .= ': ' . $data;
        }
    }
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND);
}

/**
 * Send a JSON response and exit.
 *
 * @param array $data - The data to send as JSON.
 */
function send_json($data) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Check if the user is logged in.
 */
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    write_log('Authentication failed - user not logged in');
    send_json(['error' => 'Not logged in', 'redirect' => 'login.php']);
}

$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;
// Retrieve the user id for updated_by; this should be stored in session after login.
$updated_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// Get the action from POST.
$action = isset($_POST['action']) ? $_POST['action'] : '';
write_log('Action requested', $action);

// Connect to the database using PDO (same as in ajax_attendees.php).
try {
    $db_host = '10.35.233.124:3306';
    $db_name = 'k87747_dabs';
    $db_user = 'k87747_dabs';
    $db_pass = 'Subaru5554346';
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    write_log('Database connection successful');
} catch (PDOException $e) {
    write_log('Database connection error', $e->getMessage());
    send_json(['error' => 'Database connection failed: ' . $e->getMessage()]);
}

/**
 * Update Safety Information action.
 * Expects POST parameter:
 *   - action: update
 *   - safety: (the updated safety info)
 *
 * Updates the safety_info column in the briefings table for the current project and today's date.
 */
if ($action === 'update') {
    $safety = isset($_POST['safety']) ? $_POST['safety'] : '';
    $today = date('Y-m-d');

    try {
        // Prepare the UPDATE statement.
        $stmt = $pdo->prepare("UPDATE briefings 
                               SET safety_info = :safety_info, updated_by = :updated_by, last_updated = NOW() 
                               WHERE project_id = :project_id AND date = :date");

        $stmt->bindValue(':safety_info', $safety);
        $stmt->bindValue(':updated_by', $updated_by, PDO::PARAM_INT);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindValue(':date', $today);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            send_json(['ok' => true]);
        } else {
            $msg = "No briefing found for project_id $project_id on date $today to update.";
            write_log($msg);
            send_json(['error' => $msg]);
        }
    } catch (PDOException $e) {
        $errorMsg = "Database update failed: " . $e->getMessage();
        write_log($errorMsg);
        send_json(['error' => $errorMsg]);
    }
} else {
    $msg = "Invalid action";
    write_log($msg, $action);
    send_json(['error' => $msg, 'received' => $action, 'valid_actions' => ['update']]);
}
?>