<?php
/**
 * ajax_notes.php - DABS Notes & Updates AJAX Handler
 *
 * DESCRIPTION:
 * Handles all AJAX requests for the "Notes & Updates" panel of the DABS system.
 * Provides endpoints to:
 *   - Get today's notes for the selected project
 *   - Save/update today's notes for the selected project
 *   - View notes history for the project
 *   - Get notes for a specific chosen date
 *
 * All dates and times are formatted in UK style (DD/MM/YYYY HH:MM) and use the
 * Europe/London timezone.
 *
 * Author: irlamkeep
 * Last Updated: 17/06/2025 (UK format)
 * Version: 1.1 (modernized, commented, improved logging and safety)
 */

// Set timezone for UK
date_default_timezone_set('Europe/London');

// Start output buffering to prevent accidental output before headers
ob_start();

// Start session to access authentication and project info
session_start();

// Set up logging
$log_file = __DIR__ . '/logs/notes_debug.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Write a message to the log file with a UK timestamp.
 * @param string $message
 * @param mixed $data
 */
function write_log($message, $data = null) {
    global $log_file;
    $date = date('d/m/Y H:i:s'); // UK format
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
 * @param array $data
 */
function send_json($data) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    write_log('Sending response', is_array($data) && isset($data['notes']) && strlen($data['notes']) > 200 ? '[notes output omitted]' : $data);
    exit;
}

// --- Authentication check ---
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    write_log('Authentication failed.');
    send_json(['error' => 'Not logged in']);
}

// --- Session/project/user info ---
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'unknown';

// --- Requested action ---
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
write_log('Action requested', $action);

// --- Database connection (update credentials as needed) ---
try {
    $db_host = '10.35.233.124:3306';
    $db_name = 'k87747_dabs';
    $db_user = 'k87747_dabs';
    $db_pass = 'Subaru5554346';

    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    write_log('Database connection OK');
} catch (PDOException $e) {
    write_log('Database connection error', $e->getMessage());
    send_json(['error' => 'Database connection failed: ' . $e->getMessage()]);
}

// --- Helper: Validate and convert date to database format ---
function get_database_date($input_date) {
    // Accepts 'YYYY-MM-DD' or 'DD/MM/YYYY'. Returns 'YYYY-MM-DD'.
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input_date)) {
        return $input_date;
    }
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $input_date, $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    }
    return date('Y-m-d');
}

// ==== GET TODAY'S NOTES or specified date ====
if ($action === 'get' || $action === 'get_date') {
    $date_raw = $action === 'get_date' && !empty($_GET['date']) ? $_GET['date'] : (!empty($_GET['date']) ? $_GET['date'] : date('Y-m-d'));
    $date = get_database_date($date_raw);

    write_log('Fetching notes for', ['project_id' => $project_id, 'note_date' => $date]);

    // Fetch latest notes for this project/date
    $stmt = $pdo->prepare("SELECT * FROM dabs_notes WHERE project_id = ? AND note_date = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$project_id, $date]);
    $note = $stmt->fetch();

    if ($note) {
        send_json([
            'ok' => true,
            'notes' => $note['notes'],
            'updated_at' => date('d/m/Y H:i', strtotime($note['updated_at'])),
            'updated_by' => $note['updated_by'] ?: '',
            'date' => date('d/m/Y', strtotime($note['note_date']))
        ]);
    } else {
        send_json([
            'ok' => true,
            'notes' => '',
            'updated_at' => '',
            'updated_by' => '',
            'date' => date('d/m/Y', strtotime($date))
        ]);
    }
}

// ==== SAVE (INSERT/UPDATE) NOTES ====
if ($action === 'save') {
    $date = !empty($_POST['date']) ? get_database_date($_POST['date']) : date('Y-m-d');
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $updated_by = $username;

    // Check if a record already exists for this project/date
    $stmt = $pdo->prepare("SELECT id FROM dabs_notes WHERE project_id = ? AND note_date = ?");
    $stmt->execute([$project_id, $date]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update
        $stmt = $pdo->prepare("UPDATE dabs_notes SET notes = ?, updated_at = NOW(), updated_by = ? WHERE id = ?");
        $stmt->execute([$notes, $updated_by, $existing['id']]);
        write_log('Updated notes', ['id' => $existing['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO dabs_notes (project_id, note_date, notes, created_at, updated_at, updated_by) VALUES (?, ?, ?, NOW(), NOW(), ?)");
        $stmt->execute([$project_id, $date, $notes, $updated_by]);
        write_log('Inserted new notes', ['project_id' => $project_id, 'note_date' => $date]);
    }
    send_json([
        'ok' => true,
        'updated_at' => date('d/m/Y H:i'),
        'updated_by' => $updated_by
    ]);
}

// ==== NOTES HISTORY ====
if ($action === 'history') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $stmt = $pdo->prepare("SELECT * FROM dabs_notes WHERE project_id = ? ORDER BY note_date DESC, updated_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$project_id, $limit, $offset]);
    $rows = $stmt->fetchAll();

    // Format dates for UK
    foreach ($rows as &$row) {
        $row['note_date'] = date('d/m/Y', strtotime($row['note_date']));
        $row['updated_at'] = date('d/m/Y H:i', strtotime($row['updated_at']));
        $row['notes_preview'] = mb_substr(strip_tags($row['notes']), 0, 120) . (strlen($row['notes']) > 120 ? '...' : '');
    }

    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dabs_notes WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $total = (int)$stmt->fetchColumn();

    send_json([
        'ok' => true,
        'history' => $rows,
        'total' => $total,
        'pages' => ceil($total / max($limit, 1)),
        'page' => 1 + floor($offset / max($limit, 1))
    ]);
}

// ==== NO VALID ACTION ====
write_log('Unknown action', $action);
send_json(['error' => 'Unknown action']);
?>