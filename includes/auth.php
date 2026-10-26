<?php
/**
 * auth.php
 * 
 * Authentication and authorization functions for DABS system
 * 
 * This file contains functions related to user authentication, session management,
 * and permission verification throughout the DABS system.
 * 
 * Current Date and Time (UK Format): 29/05/2025 14:10:45
 * Current User's Login: irlamkeep
 * 
 * @author irlamkeep
 * @version 1.0
 * @date 29/05/2025
 */

require_once __DIR__ . '/request_policy.php';

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is currently logged in
 * Verifies session contains valid authentication data
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isUserLoggedIn() {
    return dabsPermissionAllowed($_SESSION, 'read');
}

/**
 * Check if user has specific permission
 * Verifies user has rights to access a particular feature
 * 
 * @param string $permission The permission to check for
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permission) {
    return dabsPermissionAllowed($_SESSION, (string)$permission);
}

/**
 * Log user out by destroying session
 * Terminates current user session and removes cookies
 * 
 * @return void
 */
function logoutUser() {
    // Clear session variables
    $_SESSION = [];
    
    // If a session cookie exists, destroy it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}


/** Reject requests before loading configuration, connecting to a database or logging data. */
function dabsRequireEndpoint(string $endpoint): void {
    $status = dabsRequestStatus($endpoint, $_SERVER['REQUEST_METHOD'] ?? '', $_GET, $_POST, $_SESSION, $_SERVER['HTTP_X_DABS_CSRF'] ?? '');
    if ($status === 200) return;
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    if ($status === 405) header('Allow: POST');
    echo json_encode(['ok'=>false,'success'=>false,'error'=>$status === 401 ? 'Authentication required' : 'Request not permitted']);
    exit;
}

function dabsCsrfToken(): string {
    if (!preg_match('/^[a-f0-9]{64}$/D', (string)($_SESSION['csrf_token'] ?? ''))) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
