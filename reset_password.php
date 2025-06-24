<?php
/**
 * Daily Activity Briefing System (DABS) - Password Reset Page
 * 
 * This file handles password reset operations for the DABS application.
 * It validates the reset token received via email, ensures the token is
 * valid and not expired, then allows users to create a new password.
 * 
 * Security features include:
 * - Token validation and expiration verification
 * - Password strength requirements
 * - CSRF protection
 * - Secure password hashing
 * - Brute force prevention
 * 
 * All dates and times are displayed in UK format (DD/MM/YYYY HH:MM:SS)
 * 
 * @author irlamkeep
 * @version 1.0
 * @date 28/05/2025
 * @website dabs.defecttracker.uk
 */

// Start session for CSRF protection
session_start();

// Include essential files
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Set timezone to UTC (dates will be formatted to UK format when displayed)
date_default_timezone_set('UTC');

// Convert current UTC time to UK formatted date and time (DD/MM/YYYY HH:MM:SS)
$currentUTCDateTime = date('Y-m-d H:i:s'); // Server timestamp in UTC
$currentUKDateTime = date('d/m/Y H:i:s', strtotime($currentUTCDateTime));

// Initialize variables
$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$tokenValid = false;
$userInfo = null;
$tokenExpired = false;

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Validate the reset token
 * Checks if the token exists, is associated with a user, and hasn't expired
 */
if (!empty($token)) {
    // Get reset token information
    $sql = "SELECT pr.*, u.name, u.email, u.username FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ?";
    $tokenInfo = fetchOne($sql, [$token]);
    
    if ($tokenInfo) {
        // Check if token has expired
        if (strtotime($tokenInfo['expires_at']) > time()) {
            $tokenValid = true;
            $userInfo = $tokenInfo;
            
            // Log that a valid reset token was accessed
            logUserActivity('reset_token_valid', 'Valid password reset token used', $tokenInfo['user_id']);
        } else {
            $error = 'This password reset link has expired. Please request a new one.';
            $tokenExpired = true;
            
            // Log expired token access attempt
            logUserActivity('reset_token_expired', 'Expired password reset token used: ' . $token);
            
            // Remove expired token
            deleteData('password_resets', 'token = ?', [$token]);
        }
    } else {
        $error = 'Invalid password reset token. Please check your email or request a new link.';
        
        // Log invalid token access attempt
        logUserActivity('reset_token_invalid', 'Invalid password reset token attempted: ' . $token);
    }
}

/**
 * Process password reset form submission
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password']) && $tokenValid) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Security validation failed. Please try again.';
        
        // Log CSRF validation failure
        logUserActivity('reset_csrf_failed', 'CSRF validation failed during password reset', $userInfo['user_id']);
    } else {
        // Get the new password and confirmation
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate passwords
        if (empty($password) || empty($confirmPassword)) {
            $error = 'Please enter and confirm your new password.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error = 'Password must contain at least one lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = 'Password must contain at least one number.';
        } else {
            // Hash the new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Update the user's password
            $updateSuccess = updateData('users', ['password' => $hashedPassword], 'id = ?', [$userInfo['user_id']]);
            
            if ($updateSuccess) {
                // Password updated successfully
                $success = 'Your password has been reset successfully. You can now log in with your new password.';
                
                // Delete all reset tokens for this user
                deleteData('password_resets', 'user_id = ?', [$userInfo['user_id']]);
                
                // Log successful password reset
                logUserActivity('password_reset_success', 'Password reset completed successfully', $userInfo['user_id']);
                
                // Clear token validity to hide the form
                $tokenValid = false;
                
                // Send notification email about password change
                sendPasswordChangeNotificationEmail($userInfo['email'], $userInfo['name']);
            } else {
                $error = 'An error occurred while resetting your password. Please try again.';
                
                // Log password reset failure
                logUserActivity('password_reset_failed', 'Failed to update password in database', $userInfo['user_id']);
            }
        }
    }
}

/**
 * Send notification email about password change
 *
 * @param string $email Recipient email address
 * @param string $name Recipient name
 * @return bool Success status
 */
function sendPasswordChangeNotificationEmail($email, $name) {
    $subject = 'DABS - Your Password Has Been Changed';
    
    // Format current date and time in UK format
    $ukDateTime = date('d/m/Y H:i:s');
    
    $message = '<html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; font-size: 12px; color: #888; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Password Changed</h1>
            </div>
            <div class="content">
                <p>Hello ' . htmlspecialchars($name) . ',</p>
                <p>This email confirms that your password for the Daily Activity Briefing System has been changed successfully.</p>
                <p>If you did not make this change, please contact the system administrator immediately.</p>
                <p>Date and time of change: ' . $ukDateTime . '</p>
            </div>
            <div class="footer">
                <p>This is an automated email from the Daily Activity Briefing System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>';
    
    // Headers for HTML email
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: DABS <no-reply@defecttracker.uk>',
        'Reply-To: no-reply@defecttracker.uk',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Send email
    return mail($email, $subject, $message, implode("\r\n", $headers));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DABS - Reset Password</title>
    
    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Inline critical styles for faster loading */
        body {
            background-color: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .reset-container {
            max-width: 450px;
            width: 100%;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .password-strength {
            height: 5px;
            margin-top: 10px;
            border-radius: 5px;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 10px;
        }
        
        .requirement {
            position: relative;
            padding-left: 20px;
            margin-bottom: 5px;
        }
        
        .requirement:before {
            content: "";
            position: absolute;
            left: 0;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ccc;
        }
        
        .requirement.met:before {
            background-color: #2ecc71;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Logo and App Name -->
        <div class="logo-container">
            <img src="images/logo.png" alt="DABS Logo" class="logo">
            <h1>Reset Your Password</h1>
            <div class="date-display">
                <?php echo $currentUKDateTime; ?>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($tokenValid && $userInfo): ?>
            <!-- Reset Password Form -->
            <div class="mb-4">
                <p class="text-muted">Hello, <?php echo htmlspecialchars($userInfo['name']); ?>. Please create your new password below.</p>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                    
                    <div class="password-requirements mt-2">
                        <div class="requirement" id="req-length">At least 8 characters</div>
                        <div class="requirement" id="req-uppercase">At least one uppercase letter</div>
                        <div class="requirement" id="req-lowercase">At least one lowercase letter</div>
                        <div class="requirement" id="req-number">At least one number</div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-muted" id="passwordMatch"></div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100" name="reset_password">Reset Password</button>
            </form>
            
        <?php elseif ($tokenExpired): ?>
            <div class="text-center">
                <p>Your password reset link has expired. Please request a new one.</p>
                <a href="login.php" class="btn btn-primary mt-3">Back to Login</a>
            </div>
        <?php elseif (!$tokenValid && !$success): ?>
            <div class="text-center">
                <p>Invalid password reset link. Please check your email or request a new link.</p>
                <a href="login.php" class="btn btn-primary mt-3">Back to Login</a>
            </div>
        <?php endif; ?>
        
        <!-- System Information -->
        <div class="system-info text-center mt-4">
            <small>DABS v1.0 | &copy; 2025 DefectTracker UK</small>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility for new password
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            togglePasswordVisibility(passwordInput, icon);
        });
        
        // Toggle password visibility for confirm password
        document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            togglePasswordVisibility(confirmPasswordInput, icon);
        });
        
        // Function to toggle password visibility
        function togglePasswordVisibility(input, icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Password strength checking
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', checkPasswordStrength);
        }
        
        // Check if passwords match
        const confirmPasswordInput = document.getElementById('confirm_password');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }
        
        /**
         * Check and display password strength
         */
        function checkPasswordStrength() {
            const password = passwordInput.value;
            const strengthBar = document.getElementById('passwordStrength');
            const reqLength = document.getElementById('req-length');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqNumber = document.getElementById('req-number');
            
            // Reset requirements
            reqLength.classList.remove('met');
            reqUppercase.classList.remove('met');
            reqLowercase.classList.remove('met');
            reqNumber.classList.remove('met');
            
            let strength = 0;
            
            // Check length requirement
            if (password.length >= 8) {
                strength += 25;
                reqLength.classList.add('met');
            }
            
            // Check uppercase letter requirement
            if (/[A-Z]/.test(password)) {
                strength += 25;
                reqUppercase.classList.add('met');
            }
            
            // Check lowercase letter requirement
            if (/[a-z]/.test(password)) {
                strength += 25;
                reqLowercase.classList.add('met');
            }
            
            // Check number requirement
            if (/[0-9]/.test(password)) {
                strength += 25;
                reqNumber.classList.add('met');
            }
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Set color based on strength
            if (strength < 25) {
                strengthBar.style.backgroundColor = '#e74c3c'; // red (weak)
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = '#f39c12'; // orange (medium)
            } else {
                strengthBar.style.backgroundColor = '#2ecc71'; // green (strong)
            }
        }
        
        /**
         * Check if passwords match and display feedback
         */
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const matchFeedback = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchFeedback.textContent = '';
                matchFeedback.classList.remove('text-danger', 'text-success');
            } else if (password === confirmPassword) {
                matchFeedback.textContent = 'Passwords match!';
                matchFeedback.classList.remove('text-danger');
                matchFeedback.classList.add('text-success');
            } else {
                matchFeedback.textContent = 'Passwords do not match';
                matchFeedback.classList.remove('text-success');
                matchFeedback.classList.add('text-danger');
            }
        }
    </script>
</body>
</html>