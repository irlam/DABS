<?php
/**
 * =========================================================================
 * login.php - Daily Activity Briefing System (DABS) Authentication Gateway
 * =========================================================================
 *
 * Handles user authentication for DABS, including a project dropdown at login.
 * Sets $_SESSION['current_project'] for the chosen project.
 * Includes password reset, "remember me", account lockout, and full UK time formatting.
 *
 * AUTHOR: irlam
 * LAST UPDATED: 24/06/2025 (UK Date Format)
 * =========================================================================
 */

// Show errors for debugging (remove or set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and include dependencies
ob_start();
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
date_default_timezone_set('Europe/London');

// ========================
// AUTHENTICATION HELPERS
// ========================

function isAccountLocked($username) {
    $sql = "SELECT COUNT(*) as attempt_count FROM login_attempts 
            WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
    $result = fetchOne($sql, [$username]);
    return ($result && $result['attempt_count'] >= 5);
}

function recordFailedLogin($username) {
    $data = [
        'username' => $username,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'attempt_time' => date('Y-m-d H:i:s')
    ];
    insertData('login_attempts', $data);
}

function authenticateUser($username, $password) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $user = fetchOne($sql, [$username]);
    if (!$user) return false;
    return password_verify($password, $user['password']) ? $user : false;
}

function updateLastLogin($userId) {
    $data = ['last_login' => date('Y-m-d H:i:s')];
    updateData('users', $data, 'id = ?', [$userId]);
}

function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

function storeRememberToken($userId, $token) {
    deleteData('remember_tokens', 'user_id = ?', [$userId]);
    $data = [
        'user_id' => $userId,
        'token' => $token,
        'expires_at' => date('Y-m-d H:i:s', time() + (86400 * 30))
    ];
    insertData('remember_tokens', $data);
}

function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = ?";
    return fetchOne($sql, [$email]);
}

function storeResetToken($userId, $token, $expiry) {
    deleteData('password_resets', 'user_id = ?', [$userId]);
    $data = [
        'user_id' => $userId,
        'token' => $token,
        'expires_at' => $expiry
    ];
    insertData('password_resets', $data);
}

function logAuthenticatedUserActivity($action, $details, $userId) {
    if (!$userId || !is_numeric($userId)) return;
    $data = [
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    insertData('activity_log', $data);
}

function writeToSecurityLog($action, $details) {
    if (!is_dir('logs')) mkdir('logs', 0755, true);
    $logEntry = date('d/m/Y H:i:s') . " | " . $action . " | " . $details .
                " | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents('logs/security.log', $logEntry, FILE_APPEND);
}

function writeToErrorLog($message) {
    if (!is_dir('logs')) mkdir('logs', 0755, true);
    $logEntry = date('d/m/Y H:i:s') . " | ERROR | " . $message .
                " | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents('logs/error.log', $logEntry, FILE_APPEND);
}

function sendPasswordResetEmail($email, $name, $resetLink) {
    $subject = 'DABS Password Reset Request';
    $ukDateTime = date('d/m/Y H:i:s');
    $message = '<html>
    <head><style>body{font-family:Arial,sans-serif;}</style></head>
    <body>
    <h2>Password Reset Request</h2>
    <p>Hello '.htmlspecialchars($name).',</p>
    <p>We received a request to reset your password for the Daily Activity Briefing System.</p>
    <p>To reset your password, <a href="'.$resetLink.'">click here</a>.</p>
    <p>This link will expire in 1 hour.</p>
    <p>Date/time of request: '.$ukDateTime.'</p>
    </body></html>';
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: DABS <no-reply@defecttracker.uk>',
        'Reply-To: no-reply@defecttracker.uk',
        'X-Mailer: PHP/' . phpversion()
    ];
    return mail($email, $subject, $message, implode("\r\n", $headers));
}

// ========================
// PROJECT FETCH FOR DROPDOWN
// ========================

function getActiveProjects() {
    $sql = "SELECT id, name FROM projects WHERE status = 'active' ORDER BY name ASC";
    return fetchAll($sql);
}

// ========================
// MAIN LOGIC
// ========================

$username = '';
$error = '';
$success = '';
$showResetForm = false;
$debugMessages = [];
$currentDateTime = date('d/m/Y H:i:s');
$selectedProjectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : '';

if (!is_dir('logs')) { mkdir('logs', 0755, true); }

function addDebugMessage($message) {
    global $debugMessages;
    $debugMessages[] = date('d/m/Y H:i:s') . ' - ' . $message;
    $logEntry = date('d/m/Y H:i:s') . " | DEBUG | " . $message .
        " | IP: " . $_SERVER['REMOTE_ADDR'] . " | User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
    file_put_contents('logs/login_debug.log', $logEntry, FILE_APPEND);
}

addDebugMessage('Login page loaded');
addDebugMessage('Request Method: ' . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    addDebugMessage('POST data: ' . json_encode(array_keys($_POST)));
}

$projects = getActiveProjects();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['remember_me']);
    $selectedProjectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;

    addDebugMessage("Login attempt for username: $username");

    if (empty($username) || empty($password) || $selectedProjectId <= 0) {
        $error = 'Please enter username, password, and select a project.';
        addDebugMessage("Login validation failed - missing fields");
    } else {
        if (isAccountLocked($username)) {
            $error = 'Too many failed login attempts. Please try again after 15 minutes or reset your password.';
            addDebugMessage("Account locked for username: $username");
            writeToSecurityLog('login_locked', "Account locked: $username");
        } else {
            $user = authenticateUser($username, $password);

            if ($user) {
                addDebugMessage("Authentication successful for user ID: {$user['id']}");
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['authenticated'] = true;
                $_SESSION['last_activity'] = time();
                $_SESSION['current_project'] = $selectedProjectId;
                addDebugMessage("Set current_project to: $selectedProjectId");

                if ($rememberMe) {
                    $token = generateRememberToken();
                    storeRememberToken($user['id'], $token);
                    setcookie(
                        'dabs_remember',
                        $user['id'] . ':' . $token,
                        [
                            'expires' => time() + (86400 * 30),
                            'path' => '/',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]
                    );
                    addDebugMessage("Remember me cookie set for user ID: {$user['id']}");
                }

                updateLastLogin($user['id']);
                writeToSecurityLog('login_success', "User ID {$user['id']} ({$username}) logged in successfully");

                try { logAuthenticatedUserActivity('login_success', "User logged in successfully", $user['id']); }
                catch (Exception $e) { addDebugMessage("DB logging failed: " . $e->getMessage()); }

                ob_clean();
                file_put_contents('logs/redirect_log.log', date('d/m/Y H:i:s') . " - Redirecting user {$user['id']} to index.php\n", FILE_APPEND);
                header("Location: index.php");
                echo "<script>window.location.href = 'index.php';</script>";
                exit;
            } else {
                recordFailedLogin($username);
                $error = 'Invalid username or password.';
                addDebugMessage("Authentication failed for username: $username");
                writeToSecurityLog('login_failed', "Failed login attempt for username: $username");
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset') {
    $resetEmail = trim($_POST['reset_email']);
    addDebugMessage("Password reset requested for email: $resetEmail");
    if (empty($resetEmail)) {
        $error = 'Please enter your email address.';
        addDebugMessage("Reset validation failed - empty email");
    } else {
        $user = getUserByEmail($resetEmail);
        if ($user) {
            addDebugMessage("Valid reset request for user ID: {$user['id']}");
            $resetToken = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            storeResetToken($user['id'], $resetToken, $tokenExpiry);
            $resetLink = 'https://dabs.defecttracker.uk/reset_password.php?token=' . $resetToken;
            $emailSent = sendPasswordResetEmail($resetEmail, $user['name'], $resetLink);

            if ($emailSent) {
                $success = 'Password reset instructions have been sent to your email.';
                addDebugMessage("Reset email sent successfully");
                writeToSecurityLog('password_reset_requested', "Reset request for user ID {$user['id']} (email: $resetEmail)");
            } else {
                $error = 'Could not send password reset email. Please contact support.';
                addDebugMessage("Failed to send reset email to: $resetEmail");
                writeToErrorLog("Failed to send reset email to: $resetEmail");
            }
        } else {
            $success = 'If your email address exists in our system, you will receive password reset instructions.';
            addDebugMessage("Reset requested for unknown email: $resetEmail");
            writeToSecurityLog('password_reset_invalid_email', "Reset attempted for unknown email: $resetEmail");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DABS - Login</title>
    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', Arial, sans-serif;
        }
        .login-container {
            max-width: 440px;
            width: 100%;
            padding: 36px 32px 28px 32px;
            background: rgba(255,255,255,0.97);
            border-radius: 16px;
            box-shadow: 0 10px 32px rgba(44,62,80,0.14);
            transition: box-shadow 0.3s;
        }
        .login-container:hover {
            box-shadow: 0 16px 48px rgba(44,62,80,0.18);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo {
            max-height: 70px;
            margin-bottom: 10px;
        }
        .system-title {
            font-size: 1.55rem;
            font-weight: 700;
            color: #273c75;
            letter-spacing: 1px;
        }
        .date-display {
            font-size: 1rem;
            color: #888;
            text-align: center;
            margin-bottom: 12px;
        }
        .toggle-form {
            color: #487eb0;
            cursor: pointer;
            font-weight: 500;
        }
        .toggle-form:hover { text-decoration: underline; }
        .debug-container {
            margin-top: 22px;
            padding: 14px;
            border: 1px dashed #ccc;
            background-color: #f8f9fa;
            font-family: monospace;
            font-size: 12px;
            border-radius: 6px;
        }
        .debug-header {
            font-weight: bold;
            margin-bottom: 7px;
            color: #6c757d;
        }
        .debug-message {
            margin: 0;
            padding: 2px 0;
        }
        .btn-primary {
            background: linear-gradient(120deg, #487eb0 0%, #4078c0 100%);
            border: none;
            font-weight: 600;
        }
        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(120deg, #4078c0 0%, #273c75 100%);
        }
        .input-group-text, .btn-outline-secondary {
            background: #f1f1f1;
        }
        .form-label {
            color: #273c75;
            font-weight: 500;
        }
        .login-container small {
            color: #888;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="images/logo.png" alt="DABS Logo" class="logo">
            <div class="system-title">Daily Activity Briefing System</div>
            <div class="date-display">
                <i class="fas fa-clock me-1"></i> <?php echo $currentDateTime; ?>
            </div>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <div id="loginForm" class="<?php echo $showResetForm ? 'd-none' : ''; ?>">
            <form method="POST" action="login.php" autocomplete="off">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="<?php echo htmlspecialchars($username); ?>" autocomplete="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="project_id" class="form-label">Select Project</label>
                    <select class="form-select" id="project_id" name="project_id" required>
                        <option value="">-- Choose a Project --</option>
                        <?php foreach ($projects as $proj): ?>
                            <option value="<?php echo $proj['id']; ?>" <?php if ($proj['id'] == $selectedProjectId) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($proj['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 mb-2">Log In</button>
                <div class="mt-2 text-center">
                    <a href="#" class="toggle-form" id="showResetForm">Forgot password?</a>
                </div>
            </form>
        </div>
        <div id="resetForm" class="<?php echo $showResetForm ? '' : 'd-none'; ?>">
            <form method="POST" action="login.php" autocomplete="off">
                <input type="hidden" name="action" value="reset">
                <div class="mb-3">
                    <label for="resetEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="resetEmail" name="reset_email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 mb-2">Request Password Reset</button>
                <div class="mt-2 text-center">
                    <a href="#" class="toggle-form" id="showLoginForm">Back to login</a>
                </div>
            </form>
        </div>
        <div class="text-center mt-4">
            <small>DABS v1.0 | &copy; 2025 DefectTracker UK</small>
        </div>
        <?php if (!empty($debugMessages)): ?>
        <div class="debug-container">
            <div class="debug-header">Debug Information (Dev Mode Only)</div>
            <?php foreach($debugMessages as $message): ?>
                <p class="debug-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endforeach; ?>
            <div class="debug-header mt-3">Session Data</div>
            <pre><?php print_r($_SESSION); ?></pre>
            <div class="debug-header mt-3">Environment</div>
            <p class="debug-message">PHP Version: <?php echo phpversion(); ?></p>
            <p class="debug-message">Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
            <p class="debug-message">Script Path: <?php echo $_SERVER['SCRIPT_NAME']; ?></p>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        document.getElementById('showResetForm').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loginForm').classList.add('d-none');
            document.getElementById('resetForm').classList.remove('d-none');
        });
        document.getElementById('showLoginForm').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('resetForm').classList.add('d-none');
            document.getElementById('loginForm').classList.remove('d-none');
        });
    </script>
</body>
</html>