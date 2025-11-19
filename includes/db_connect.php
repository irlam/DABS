<?php
/**
 * =========================================================================
 * db_connect.php - Daily Activity Briefing System (DABS) Database Connection
 * =========================================================================
 * 
 * Provides a secure, modern, and centralized PDO connection to the MySQL database
 * for the Daily Activity Briefing System (DABS). Includes helper functions for
 * fetchAll, fetchOne, insertData, updateData, and deleteData.
 * 
 * AUTHOR: irlam (System Administrator)
 * LAST UPDATED: 24/06/2025 (UK Date Format)
 * =========================================================================
 */

// Set timezone to Europe/London for UK time formatting
date_default_timezone_set('Europe/London');

// Database Configuration
$db_host = '10.35.233.124';
$db_port = 3306;
$db_name = 'k87747_dabs';
$db_user = 'k87747_dabs';
$db_pass = 'Subaru5554346';

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::ATTR_TIMEOUT => 30,
    PDO::ATTR_PERSISTENT => false,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    PDO::ATTR_STRINGIFY_FETCHES => false,
    PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL
];

// Global PDO instance
$pdo = null;

// Connect to database
function connectToDatabase() {
    global $pdo, $db_host, $db_port, $db_name, $db_user, $db_pass, $options;
    if ($pdo !== null) return $pdo;
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('[DABS DB ERROR ' . date('d/m/Y H:i:s') . '] Connection failed: ' . $e->getMessage());
        die(json_encode([
            'ok' => false,
            'error' => 'Database connection failed',
            'error_code' => 'DB_CONNECTION_FAILED',
            'message' => 'Unable to connect to the database server. Please check configuration.',
            'timestamp_uk' => date('d/m/Y H:i:s'),
            'details' => 'Contact system administrator if problem persists'
        ]));
    }
}

// Helper: fetchAll
function fetchAll($sql, $params = []) {
    $pdo = connectToDatabase();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper: fetchOne
function fetchOne($sql, $params = []) {
    $pdo = connectToDatabase();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper: insertData
function insertData($table, $data) {
    $pdo = connectToDatabase();
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    return $pdo->lastInsertId();
}

// Helper: updateData
function updateData($table, $data, $whereClause, $whereParams = []) {
    $pdo = connectToDatabase();
    $setClauses = [];
    $setParams = [];
    foreach ($data as $column => $value) {
        $setClauses[] = "`$column` = ?";
        $setParams[] = $value;
    }
    $allParams = array_merge($setParams, $whereParams);
    $sql = "UPDATE `$table` SET " . implode(', ', $setClauses) . " WHERE " . $whereClause;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($allParams);
    return $stmt->rowCount();
}

// Helper: deleteData
function deleteData($table, $whereClause, $whereParams = []) {
    $pdo = connectToDatabase();
    $sql = "DELETE FROM `$table` WHERE " . $whereClause;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($whereParams);
    return $stmt->rowCount();
}
?>