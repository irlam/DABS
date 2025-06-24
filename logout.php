<?php
/**
 * =========================================================================
 * logout.php - Daily Activity Briefing System (DABS)
 * =========================================================================
 * 
 * PURPOSE:
 * This file handles the user logout process for the DABS system. It performs
 * a secure logout by destroying the user's session, clearing cookies, logging
 * the action, and redirecting to the login page.
 * 
 * FEATURES:
 * - Destroys active user session
 * - Regenerates session ID for security
 * - Logs the logout action with UK formatted timestamp
 * - Prevents caching of the logout page
 * - Redirects to the login page after logout
 * - Displays a confirmation message
 * 
 * AUTHOR: irlamkeep
 * DATE: 2025-06-03
 * VERSION: 1.0
 * =========================================================================
 */

// Set timezone to Europe/London for UK time
date_default_timezone_set('Europe/London');

// Start the session (needed to destroy it)
session_start();

// Store username before destroying session (for logging)
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown user';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Create log file path (make sure logs directory exists)
$log_file = __DIR__ . '/logs/user_activity.log';

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Write message to log file
 * Logs user activities with timestamp in UK format
 * 
 * @param string $message - Message to log
 */
function write_log($message) {
    global $log_file;
    
    // Format date in UK format (DD/MM/YYYY HH:MM:SS)
    $date = date('d/m/Y H:i:s');
    
    // Create log entry with timestamp
    $log_entry = "[$date] $message" . PHP_EOL;
    
    // Append to log file
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Get the user's IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Log the logout action
write_log("User logged out: $username (ID: $user_id) from IP: $ip_address");

// Add logout action to database activity log if database connection exists
try {
    // Check if we have database connection details in a config file
    if (file_exists('config.php') || file_exists('includes/config.php')) {
        // Load configuration file that has DB connection details
        if (file_exists('config.php')) {
            require_once 'config.php';
        } else {
            require_once 'includes/config.php';
        }
        
        // If we have DB connection details defined
        if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
            // Connect to database
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Insert logout activity
            $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) 
                                VALUES (?, 'logout', 'User logged out successfully', ?)");
            $stmt->execute([$user_id, $ip_address]);
        }
    }
} catch (Exception $e) {
    // If there's an error with database logging, just continue with the logout process
    // We don't want DB errors to prevent users from logging out
    write_log("Database logging error during logout: " . $e->getMessage());
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
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

// Destroy the session
session_destroy();

// Prevent caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Create a clean new session to use for login page (security best practice)
session_start();
session_regenerate_id(true);

// Set logout success message for login page
$_SESSION['logout_message'] = "You have been successfully logged out.";

// Redirect to login page (with 2-second delay to show message)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - DABS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .logout-card {
            max-width: 500px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .logout-header {
            background-color: #3498db;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 20px 30px;
        }
        .logout-body {
            padding: 30px;
        }
        .logout-icon {
            font-size: 48px;
            color: #3498db;
            margin-bottom: 20px;
        }
        .countdown {
            font-weight: bold;
            color: #3498db;
        }
        .uk-time {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card logout-card">
            <div class="logout-header">
                <h3 class="mb-0">DABS System - Logout</h3>
            </div>
            <div class="logout-body text-center">
                <div class="logout-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4>You have been successfully logged out</h4>
                <p>Thank you for using the Daily Activity Briefing System.</p>
                <p class="uk-time">Current Time: <?php echo date('d/m/Y H:i:s'); ?></p>
                <div class="mt-4 mb-3">
                    <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Redirecting...</span>
                    </div>
                    Redirecting to login page in <span id="countdown" class="countdown">2</span> seconds...
                </div>
                <a href="login.php" class="btn btn-outline-primary">Login Again</a>
            </div>
        </div>
    </div>

    <script>
        // Countdown for redirect
        let seconds = 2;
        const countdownEl = document.getElementById('countdown');
        
        const countdownTimer = setInterval(function() {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdownTimer);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
</body>
</html>