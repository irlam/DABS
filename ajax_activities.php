<?php
/**
 * ============================================================================
 * ajax_activities.php - DABS Activity Schedule API (Fully Editable Backend)
 * ============================================================================
 * DESCRIPTION:
 * Backend API for DABS activity schedule, supporting:
 * - Fully editable activities (all fields, including briefing_id, contractors, etc)
 * - CRUD (Create, Read, Update, Delete) endpoints
 * - Modern, secure, UK date/time, robust error handling and logging
 * - Works with modern tabbed activity schedule and modals (frontend)
 * - Project-based isolation and session authentication
 * AUTHOR: irlam
 * LAST UPDATED: 25/06/2025 (UK Date Format)
 * ============================================================================
 */

// Set UK timezone for UK-style date/time throughout system
date_default_timezone_set('Europe/London');
require_once __DIR__ . '/includes/db_connect.php';
ob_start();
session_start();
$log_file = __DIR__ . '/logs/activities_debug.log';
if (!is_dir(__DIR__ . '/logs')) mkdir(__DIR__ . '/logs', 0755, true);

// Write a log entry with UK timestamp for debugging/troubleshooting
function write_log($message, $data = null) {
    global $log_file;
    $timestamp = date('d/m/Y H:i:s');
    $entry = "[$timestamp] $message";
    if ($data !== null) $entry .= ': ' . print_r($data, true);
    file_put_contents($log_file, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Send a JSON response and exit, always with UK timestamp
function send_json($data) {
    if (ob_get_length()) ob_clean();
    $data['timestamp_uk'] = date('d/m/Y H:i:s');
    $data['server_time'] = date('c');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Logging request for audit/debug
write_log('=== NEW API REQUEST ===');
write_log('Request', ['METHOD'=>$_SERVER['REQUEST_METHOD'], 'URI'=>$_SERVER['REQUEST_URI']]);
write_log('GET', $_GET); write_log('POST', array_keys($_POST));

// --- AUTH ---
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    send_json(['ok'=>false,'error'=>'Authentication required','redirect'=>'login.php']);
}
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;
$username = $_SESSION['user_name'] ?? 'unknown';
$user_id = $_SESSION['user_id'] ?? 1;
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- DB Connection ---
try {
    $pdo = connectToDatabase();
} catch (Exception $e) {
    send_json(['ok'=>false, 'error'=>'DB connection failed']);
}

// --- LIST ---
if ($action === 'list') {
    $date = $_GET['date'] ?? date('Y-m-d');
    $sql = "SELECT a.*, DATE_FORMAT(a.date, '%d/%m/%Y') as date_uk, TIME_FORMAT(a.time, '%H:%i') as time_uk
            FROM activities a
            LEFT JOIN briefings b ON a.briefing_id = b.id
            WHERE b.project_id = ? AND a.date = ?
            ORDER BY FIELD(a.priority,'critical','high','medium','low'), a.time ASC, a.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$project_id, $date]);
    $acts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($acts as &$a) {
        $a['id']=intval($a['id']); $a['briefing_id']=intval($a['briefing_id']); $a['labor_count']=intval($a['labor_count']);
    }
    send_json(['ok'=>true,'action'=>'list','activities'=>$acts,'count'=>count($acts),'date'=>$date]);
}

// --- GET ONE ---
if ($action === 'get') {
    $id = intval($_GET['id']??0);
    $sql = "SELECT a.*, DATE_FORMAT(a.date, '%d/%m/%Y') as date_uk, TIME_FORMAT(a.time, '%H:%i') as time_uk
            FROM activities a
            LEFT JOIN briefings b ON a.briefing_id = b.id
            WHERE a.id=? AND b.project_id=?";
    $stmt = $pdo->prepare($sql); $stmt->execute([$id, $project_id]);
    $a = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$a) send_json(['ok'=>false,'error'=>'Not found']);
    send_json(['ok'=>true,'action'=>'get','activity'=>$a]);
}

// --- ADD ---
if ($action === 'add') {
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
    if (empty($title)) send_json(['ok'=>false,'error'=>'Title is required']);
    if ($briefing_id <= 0) send_json(['ok'=>false,'error'=>'Briefing is required']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) $time = '08:00';
    $valid_priorities = ['low','medium','high','critical'];
    if (!in_array($priority, $valid_priorities)) $priority = 'medium';
    // Verify briefing belongs to project
    $b = $pdo->prepare("SELECT id FROM briefings WHERE id=? AND project_id=?");
    $b->execute([$briefing_id, $project_id]);
    if (!$b->fetch()) send_json(['ok'=>false,'error'=>'Invalid briefing']);
    $sql = "INSERT INTO activities
        (briefing_id, date, time, title, description, area, priority, labor_count, contractors, assigned_to, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $briefing_id, $date, $time, $title, $description, $area, $priority,
        $labor_count, $contractors, $assigned_to
    ]);
    send_json(['ok'=>true,'action'=>'add','id'=>intval($pdo->lastInsertId()),'title'=>$title,'date'=>$date]);
}

// --- UPDATE (fully editable, including briefing_id) ---
if ($action === 'update') {
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

    // Log incoming POST data for debugging
    write_log('UPDATE POST data', $_POST);

    // Validate required fields
    if ($id <= 0) send_json(['ok'=>false,'error'=>'Activity ID required']);
    if ($briefing_id <= 0) send_json(['ok'=>false,'error'=>'Briefing is required']);
    if (empty($title)) send_json(['ok'=>false,'error'=>'Title is required']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) $time = '08:00';
    $valid_priorities = ['low','medium','high','critical'];
    if (!in_array($priority, $valid_priorities)) $priority = 'medium';

    // Check activity belongs to project and exists
    $stmt = $pdo->prepare("SELECT a.id FROM activities a LEFT JOIN briefings b ON a.briefing_id=b.id WHERE a.id=? AND b.project_id=?");
    $stmt->execute([$id, $project_id]);
    if (!$stmt->fetch()) send_json(['ok'=>false,'error'=>'Not found or access denied (activity/project mismatch)']);

    // Check new briefing belongs to project
    $b = $pdo->prepare("SELECT id FROM briefings WHERE id=? AND project_id=?");
    $b->execute([$briefing_id, $project_id]);
    if (!$b->fetch()) send_json(['ok'=>false,'error'=>'Invalid briefing for this project']);

    // Update
    $sql = "UPDATE activities SET
        briefing_id=?, date=?, time=?, title=?, description=?, area=?, priority=?, labor_count=?, contractors=?, assigned_to=?, updated_at=NOW()
        WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $briefing_id, $date, $time, $title, $description, $area, $priority,
        $labor_count, $contractors, $assigned_to, $id
    ]);
    // Log affected rows for debugging
    write_log('UPDATE rowCount', $stmt->rowCount());

    // Accept "no changes" as success if row exists and IDs are valid
    send_json(['ok'=>true,'action'=>'update','id'=>$id,'title'=>$title,'date'=>$date]);
}

// --- DELETE ---
if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) send_json(['ok'=>false,'error'=>'Activity ID required']);
    $stmt = $pdo->prepare("SELECT a.id FROM activities a LEFT JOIN briefings b ON a.briefing_id=b.id WHERE a.id=? AND b.project_id=?");
    $stmt->execute([$id, $project_id]);
    if (!$stmt->fetch()) send_json(['ok'=>false,'error'=>'Not found or access denied']);
    $del = $pdo->prepare("DELETE FROM activities WHERE id=?");
    $del->execute([$id]);
    send_json(['ok'=>true,'action'=>'delete','id'=>$id]);
}

// --- IMPORT PREVIOUS DAY ---
if ($action === 'import_previous_day') {
    $current_date = trim($_POST['current_date'] ?? date('Y-m-d'));
    $previous_date = trim($_POST['previous_date'] ?? '');
    
    write_log('IMPORT PREVIOUS DAY', ['current_date'=>$current_date, 'previous_date'=>$previous_date]);
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $current_date)) {
        send_json(['ok'=>false,'error'=>'Invalid current date format']);
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $previous_date)) {
        send_json(['ok'=>false,'error'=>'Invalid previous date format']);
    }
    
    // Get or create briefing for current date
    $stmt = $pdo->prepare("SELECT id FROM briefings WHERE project_id=? AND date=?");
    $stmt->execute([$project_id, $current_date]);
    $current_briefing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_briefing) {
        // Get previous day's briefing to copy safety_info and notes for consistency
        $prev_briefing_stmt = $pdo->prepare("SELECT safety_info, notes FROM briefings WHERE project_id=? AND date=? LIMIT 1");
        $prev_briefing_stmt->execute([$project_id, $previous_date]);
        $prev_briefing = $prev_briefing_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Use previous day's values if available, otherwise use defaults
        $safety_info = '<ul><li>Follow all standard safety protocols</li><li>Wear appropriate PPE at all times</li><li>Report any safety concerns immediately</li></ul>';
        $notes = 'Daily briefing notes for ' . date('d/m/Y', strtotime($current_date));
        
        if ($prev_briefing) {
            if (!empty($prev_briefing['safety_info'])) {
                $safety_info = $prev_briefing['safety_info'];
            }
            if (!empty($prev_briefing['notes'])) {
                $notes = $prev_briefing['notes'];
            }
        }
        
        // Create briefing for current date
        $insert_briefing = $pdo->prepare("INSERT INTO briefings (project_id, date, overview, safety_info, notes, created_by, last_updated, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'draft')");
        $insert_briefing->execute([
            $project_id,
            $current_date,
            'Daily briefing for ' . date('d/m/Y', strtotime($current_date)),
            $safety_info,
            $notes,
            $user_id
        ]);
        $current_briefing_id = $pdo->lastInsertId();
    } else {
        $current_briefing_id = $current_briefing['id'];
    }
    
    // Get activities from previous day
    $stmt = $pdo->prepare("SELECT a.* FROM activities a LEFT JOIN briefings b ON a.briefing_id=b.id WHERE b.project_id=? AND a.date=?");
    $stmt->execute([$project_id, $previous_date]);
    $previous_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $imported_count = 0;
    foreach ($previous_activities as $activity) {
        // Insert copied activity with new date and briefing
        $insert_stmt = $pdo->prepare("INSERT INTO activities (briefing_id, date, time, title, description, area, priority, labor_count, contractors, assigned_to, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $insert_stmt->execute([
            $current_briefing_id,
            $current_date,
            $activity['time'],
            $activity['title'],
            $activity['description'],
            $activity['area'],
            $activity['priority'],
            $activity['labor_count'],
            $activity['contractors'],
            $activity['assigned_to']
        ]);
        $imported_count++;
    }
    
    write_log('IMPORT SUCCESS', ['count'=>$imported_count]);
    send_json(['ok'=>true,'action'=>'import_previous_day','count'=>$imported_count,'current_date'=>$current_date,'previous_date'=>$previous_date]);
}

// --- Unknown action ---
send_json([
    'ok' => false,
    'error' => 'Unknown or missing action',
    'valid_actions' => ['list', 'get', 'add', 'update', 'delete', 'import_previous_day'],
    'message' => 'Specify a valid action parameter'
]);
?>