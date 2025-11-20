<?php
/**
 * =========================================================================
 * ajax_admin.php - Admin Panel AJAX Handler
 * =========================================================================
 * 
 * Handles AJAX requests for admin panel functionality:
 * - User management (list, create, update, delete)
 * - Log file viewing
 * - Email testing
 * 
 * AUTHOR: System
 * CREATED: 20/11/2025 (UK Date Format)
 * =========================================================================
 */

date_default_timezone_set('Europe/London');
session_start();
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check authentication and admin role
if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin role required.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = connectToDatabase();
    
    switch ($action) {
        case 'list_users':
            listUsers($pdo);
            break;
            
        case 'get_user':
            getUser($pdo);
            break;
            
        case 'create_user':
            createUser($pdo);
            break;
            
        case 'update_user':
            updateUser($pdo);
            break;
            
        case 'delete_user':
            deleteUser($pdo);
            break;
            
        case 'list_logs':
            listLogFiles();
            break;
            
        case 'read_log':
            readLogFile();
            break;
            
        case 'test_email':
            testEmailSettings();
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// ============================================================================
// User Management Functions
// ============================================================================

function listUsers($pdo) {
    $stmt = $pdo->query("SELECT id, username, name, email, role, last_login FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'users' => $users]);
}

function getUser($pdo) {
    $userId = intval($_GET['id'] ?? 0);
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, username, name, email, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}

function createUser($pdo) {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validate inputs
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    if (!in_array($role, ['user', 'manager', 'admin'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $hashedPassword, $name, $email, $role]);
    
    // Log activity
    logUserActivity('create_user', "Created user: $username (Role: $role)");
    
    echo json_encode(['success' => true, 'message' => 'User created successfully']);
}

function updateUser($pdo) {
    $userId = intval($_POST['userId'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    // Validate inputs
    if (empty($name) || empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name, username, and email are required']);
        return;
    }
    
    if (!in_array($role, ['user', 'manager', 'admin'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    
    // Check if username already exists for a different user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
    // Check if email already exists for a different user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }
    
    // Update user
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, name = ?, email = ?, role = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$username, $hashedPassword, $name, $email, $role, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, email = ?, role = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$username, $name, $email, $role, $userId]);
    }
    
    // Log activity
    logUserActivity('update_user', "Updated user: $username (ID: $userId)");
    
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
}

function deleteUser($pdo) {
    $userId = intval($_POST['id'] ?? 0);
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    if ($userId === 1) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete system administrator']);
        return;
    }
    
    // Get username for logging
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    // Log activity
    logUserActivity('delete_user', "Deleted user: {$user['username']} (ID: $userId)");
    
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
}

// ============================================================================
// Log Viewer Functions
// ============================================================================

function listLogFiles() {
    $logsDir = __DIR__ . '/logs';
    
    if (!is_dir($logsDir)) {
        echo json_encode(['success' => false, 'message' => 'Logs directory not found']);
        return;
    }
    
    $logs = [];
    $files = scandir($logsDir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $logsDir . '/' . $file;
        if (is_file($filePath)) {
            $size = filesize($filePath);
            $sizeFormatted = formatBytes($size);
            
            $logs[] = [
                'name' => $file,
                'size' => $sizeFormatted,
                'modified' => date('d/m/Y H:i:s', filemtime($filePath))
            ];
        }
    }
    
    // Sort by name
    usort($logs, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    echo json_encode(['success' => true, 'logs' => $logs]);
}

function readLogFile() {
    $filename = $_GET['file'] ?? '';
    
    if (empty($filename)) {
        echo json_encode(['success' => false, 'message' => 'No file specified']);
        return;
    }
    
    // Sanitize filename to prevent directory traversal
    $filename = basename($filename);
    $filePath = __DIR__ . '/logs/' . $filename;
    
    if (!file_exists($filePath)) {
        echo json_encode(['success' => false, 'message' => 'Log file not found']);
        return;
    }
    
    // Read last 1000 lines for performance
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    // Get last 1000 lines
    if (count($lines) > 1000) {
        $lines = array_slice($lines, -1000);
    }
    
    $content = implode("\n", $lines);
    
    echo json_encode(['success' => true, 'content' => $content, 'lines' => count($lines)]);
}

// ============================================================================
// Email Testing Functions
// ============================================================================

function testEmailSettings() {
    $email = $_POST['email'] ?? '';
    $from = $_POST['from'] ?? 'no-reply@defecttracker.uk';
    $fromName = $_POST['from_name'] ?? 'DABS';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email address required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    
    $subject = 'DABS Email Test - ' . date('d/m/Y H:i:s');
    $message = '
    <html>
    <head><title>DABS Email Test</title></head>
    <body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
        <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h2 style="color: #667eea; margin-top: 0;">DABS Email Test</h2>
            <p>This is a test email from the Daily Activity Briefing System (DABS).</p>
            <p><strong>Test Details:</strong></p>
            <ul>
                <li>Sent at: ' . date('d/m/Y H:i:s') . ' (UK Time)</li>
                <li>Sent by: ' . htmlspecialchars($_SESSION['user_name']) . '</li>
                <li>From address: ' . htmlspecialchars($from) . '</li>
            </ul>
            <p>If you received this email, your email configuration is working correctly!</p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            <p style="color: #666; font-size: 12px; margin-bottom: 0;">
                This is an automated test message from DABS. Please do not reply to this email.
            </p>
        </div>
    </body>
    </html>
    ';
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . $fromName . ' <' . $from . '>',
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Log the attempt
    $logFile = __DIR__ . '/logs/email_log.txt';
    $logEntry = '[' . date('d/m/Y H:i:s') . '] Test email attempt to: ' . $email . ' from admin panel' . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Attempt to send email
    $result = mail($email, $subject, $message, implode("\r\n", $headers));
    
    if ($result) {
        $logEntry = '[' . date('d/m/Y H:i:s') . '] Test email sent successfully to: ' . $email . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        logUserActivity('test_email', "Sent test email to: $email");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Test email sent successfully! Check the inbox (and spam folder) of ' . $email
        ]);
    } else {
        $logEntry = '[' . date('d/m/Y H:i:s') . '] Test email FAILED to: ' . $email . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to send test email. Check server mail configuration and logs.',
            'debug' => 'PHP mail() function returned false. Check server mail settings and email_log.txt'
        ]);
    }
}

// ============================================================================
// Helper Functions
// ============================================================================

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
