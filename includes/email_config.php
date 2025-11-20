<?php
/**
 * ============================================================================
 * Email Configuration Class
 * ============================================================================
 * DESCRIPTION:
 * Manages email configuration and sending using PHPMailer with SMTP support.
 * Provides methods to load settings, send emails, and manage configuration.
 *
 * AUTHOR: System
 * CREATED: 20/11/2025 (UK Date Format)
 * ============================================================================
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db_connect.php';

class EmailConfig {
    private $pdo;
    private $settings;
    
    /**
     * Constructor - loads email settings from database
     */
    public function __construct() {
        try {
            $this->pdo = connectToDatabase();
            $this->loadSettings();
        } catch (Exception $e) {
            error_log("EmailConfig initialization error: " . $e->getMessage());
            // Use default settings if database connection fails
            $this->settings = $this->getDefaultSettings();
        }
    }
    
    /**
     * Load email settings from database
     */
    private function loadSettings() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM email_settings WHERE id = 1 LIMIT 1");
            $this->settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->settings) {
                // Create default settings if not exist
                $this->settings = $this->getDefaultSettings();
                $this->saveSettings($this->settings);
            }
        } catch (PDOException $e) {
            error_log("Failed to load email settings: " . $e->getMessage());
            $this->settings = $this->getDefaultSettings();
        }
    }
    
    /**
     * Get default email settings
     */
    private function getDefaultSettings() {
        return [
            'smtp_enabled' => 0,
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_auth' => 1,
            'smtp_username' => '',
            'smtp_password' => '',
            'from_email' => 'noreply@example.com',
            'from_name' => 'DABS System'
        ];
    }
    
    /**
     * Get current email settings
     */
    public function getSettings() {
        return $this->settings;
    }
    
    /**
     * Save email settings to database
     */
    public function saveSettings($settings, $userId = null) {
        try {
            // Encrypt password if provided
            $password = $settings['smtp_password'];
            if (!empty($password)) {
                // Use simple encryption - in production, use better encryption
                $password = base64_encode($password);
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE email_settings 
                SET smtp_enabled = ?, 
                    smtp_host = ?, 
                    smtp_port = ?, 
                    smtp_encryption = ?, 
                    smtp_auth = ?, 
                    smtp_username = ?, 
                    smtp_password = ?, 
                    from_email = ?, 
                    from_name = ?,
                    updated_by = ?
                WHERE id = 1
            ");
            
            $stmt->execute([
                $settings['smtp_enabled'] ? 1 : 0,
                $settings['smtp_host'],
                intval($settings['smtp_port']),
                $settings['smtp_encryption'],
                $settings['smtp_auth'] ? 1 : 0,
                $settings['smtp_username'],
                $password,
                $settings['from_email'],
                $settings['from_name'],
                $userId
            ]);
            
            // Reload settings
            $this->loadSettings();
            
            return true;
        } catch (PDOException $e) {
            error_log("Failed to save email settings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create and configure PHPMailer instance
     */
    public function createMailer() {
        $mail = new PHPMailer(true);
        
        try {
            if ($this->settings['smtp_enabled']) {
                // Use SMTP
                $mail->isSMTP();
                $mail->Host = $this->settings['smtp_host'];
                $mail->SMTPAuth = (bool)$this->settings['smtp_auth'];
                $mail->Username = $this->settings['smtp_username'];
                
                // Decrypt password
                $password = $this->settings['smtp_password'];
                if (!empty($password)) {
                    $password = base64_decode($password);
                }
                $mail->Password = $password;
                
                $mail->SMTPSecure = $this->settings['smtp_encryption'];
                $mail->Port = $this->settings['smtp_port'];
                
                // Enable verbose debug output (only in development)
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            } else {
                // Use PHP mail() function
                $mail->isMail();
            }
            
            // Set default from address
            $mail->setFrom(
                $this->settings['from_email'], 
                $this->settings['from_name']
            );
            
            // Set character encoding
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            
        } catch (Exception $e) {
            error_log("Failed to configure PHPMailer: " . $e->getMessage());
        }
        
        return $mail;
    }
    
    /**
     * Send an email using configured settings
     * 
     * @param array $to - Array of recipient emails or single email string
     * @param string $subject - Email subject
     * @param string $body - HTML email body
     * @param array $attachments - Optional array of file paths to attach
     * @return bool - True if sent successfully
     */
    public function sendEmail($to, $subject, $body, $attachments = []) {
        try {
            $mail = $this->createMailer();
            
            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email) {
                    $mail->addAddress($email);
                }
            } else {
                $mail->addAddress($to);
            }
            
            // Set email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Add plain text alternative
            $mail->AltBody = strip_tags($body);
            
            // Add attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
            
            // Send email
            $result = $mail->send();
            
            // Log success
            $this->logEmail($to, $subject, 'sent');
            
            return $result;
            
        } catch (Exception $e) {
            // Log error
            $this->logEmail($to, $subject, 'failed', $e->getMessage());
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log email sending attempt
     */
    private function logEmail($to, $subject, $status, $error = null) {
        $logFile = __DIR__ . '/../logs/email_log.txt';
        
        // Create logs directory if it doesn't exist
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $date = date('d/m/Y H:i:s');
        $toStr = is_array($to) ? implode(', ', $to) : $to;
        $logEntry = "[$date] Status: $status | To: $toStr | Subject: $subject";
        
        if ($error) {
            $logEntry .= " | Error: $error";
        }
        
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND);
    }
    
    /**
     * Test email configuration by sending a test email
     */
    public function testConfiguration($testEmail) {
        $subject = 'DABS Email Configuration Test - ' . date('d/m/Y H:i:s');
        $body = '
        <html>
        <head><title>DABS Email Test</title></head>
        <body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
            <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h2 style="color: #667eea; margin-top: 0;">DABS Email Configuration Test</h2>
                <p>This is a test email from the Daily Activity Briefing System (DABS).</p>
                <p><strong>Configuration Details:</strong></p>
                <ul>
                    <li>SMTP Enabled: ' . ($this->settings['smtp_enabled'] ? 'Yes' : 'No (using PHP mail)') . '</li>
                    <li>SMTP Host: ' . htmlspecialchars($this->settings['smtp_host']) . '</li>
                    <li>SMTP Port: ' . htmlspecialchars($this->settings['smtp_port']) . '</li>
                    <li>Encryption: ' . htmlspecialchars($this->settings['smtp_encryption']) . '</li>
                    <li>Sent at: ' . date('d/m/Y H:i:s') . ' (UK Time)</li>
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
        
        return $this->sendEmail($testEmail, $subject, $body);
    }
}
