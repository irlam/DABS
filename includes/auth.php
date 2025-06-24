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
    // Debug: Log function call and session data to diagnose issues
    error_log("isUserLoggedIn() called. Session data: " . json_encode($_SESSION));
    
    // Check if authenticated flag is set and is true
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        // Also verify we have a valid user_id
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
            return true;
        }
    }
    
    // Not authenticated
    return false;
}

/**
 * Check if user has specific permission
 * Verifies user has rights to access a particular feature
 * 
 * @param string $permission The permission to check for
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permission) {
    // Not implemented yet - always return true for now
    return true;
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