<?php
/**
 * ************************************************************************
 * DAILY ACTIVITY BRIEFING SYSTEM (DABS) - EMAIL REPORT GENERATOR
 * ************************************************************************
 * 
 * CREATED: 03/06/2025 21:41:00 (UK Format)
 * AUTHOR: irlamkeep
 * VERSION: 1.0
 * 
 * WHAT THIS FILE DOES:
 * This file generates a professional PDF report of the day's activities and 
 * resources, then sends it via email to specified recipients. It pulls all
 * activity data from the database, formats it into a nicely structured PDF,
 * and sends it as an email attachment. All dates use UK format (DD/MM/YYYY).
 * 
 * KEY FEATURES:
 * - Generates PDF reports of daily activities with proper formatting
 * - Includes resource breakdowns showing workers by subcontractor
 * - Sends emails with PDF attachments to multiple recipients 
 * - Validates email addresses and provides comprehensive error handling
 * - Logs all email sending activity for auditing purposes
 * - Uses UK date format (DD/MM/YYYY) throughout
 * 
 * REQUIRED LIBRARIES:
 * - TCPDF (for PDF generation) - installed via Composer
 * 
 * ************************************************************************
 */

// Load Composer autoloader for TCPDF
require_once __DIR__ . '/vendor/autoload.php';

// Set timezone to Europe/London for UK time
date_default_timezone_set('Europe/London');

// Start session to access user information
session_start();

// Check if user is logged in
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    outputResponse(['error' => 'Not authenticated', 'redirect' => 'login.php']);
    exit;
}

// Get the current user and project info
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'unknown';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;
$project_id = isset($_SESSION['current_project']) ? intval($_SESSION['current_project']) : 1;

// Get project name from database
$project_name = getProjectName($project_id);

// Set up logging for debugging
$log_file = __DIR__ . '/logs/email_log.txt';

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Logs a message to the email log file
 * Helps track email generation and sending for troubleshooting
 * 
 * @param string $message - Message to log
 * @param mixed $data - Optional data to include in log
 */
function logMessage($message, $data = null) {
    global $log_file;
    $date = date('d/m/Y H:i:s');
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
 * Output a JSON response to the client
 * Used for AJAX communication with the frontend
 * 
 * @param array $data - Data to send as JSON
 */
function outputResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Connect to database and get project name
 * 
 * @param int $project_id - ID of the project
 * @return string - Name of the project or "Unknown Project"
 */
function getProjectName($project_id) {
    try {
        // Database connection details
        $db_host = '10.35.233.124:3306';
        $db_name = 'k87747_dabs';
        $db_user = 'k87747_dabs';
        $db_pass = 'Subaru5554346';
        
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $stmt = $pdo->prepare("SELECT name FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $result = $stmt->fetch();
        
        return $result ? $result['name'] : "Unknown Project";
    } catch (PDOException $e) {
        logMessage("Database error when getting project name", $e->getMessage());
        return "Unknown Project";
    }
}

/**
 * Get activities for a specific date
 * Retrieves all activity data needed for the PDF report
 * 
 * @param string $date - Date in YYYY-MM-DD format
 * @return array - Activities and resources for the date
 */
function getActivitiesForDate($date) {
    global $project_id;
    
    try {
        // Database connection details
        $db_host = '10.35.233.124:3306';
        $db_name = 'k87747_dabs';
        $db_user = 'k87747_dabs';
        $db_pass = 'Subaru5554346';
        
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // First check if a briefing exists
        $stmt = $pdo->prepare("SELECT id FROM briefings WHERE project_id = ? AND date = ?");
        $stmt->execute([$project_id, $date]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return [
                'activities' => [],
                'total_labor' => 0,
                'total_contractors' => 0,
                'date' => date('d/m/Y', strtotime($date))
            ];
        }
        
        $briefing_id = $result['id'];
        
        // Get all activities for this briefing
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.description, a.priority, a.assigned_to, a.area,
                   r.id AS resource_id, r.type AS resource_type, r.name AS resource_name
            FROM activities a
            LEFT JOIN resources r ON r.briefing_id = a.briefing_id
            WHERE a.briefing_id = ?
            ORDER BY a.area ASC, a.priority DESC
        ");
        $stmt->execute([$briefing_id]);
        
        $raw_activities = $stmt->fetchAll();
        $activities = [];
        
        // Process activities and resources
        foreach ($raw_activities as $row) {
            $activity_id = $row['id'];
            
            // If this is a new activity, add it to our list
            if (!isset($activities[$activity_id])) {
                $activities[$activity_id] = [
                    'id' => $activity_id,
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'priority' => $row['priority'],
                    'assigned_to' => $row['assigned_to'],
                    'area' => $row['area'],
                    'resources' => [],
                    'labor_count' => 0,
                    'contractors' => []
                ];
            }
            
            // If there's a resource attached, add it to the activity
            if (!empty($row['resource_id'])) {
                $resource = [
                    'id' => $row['resource_id'],
                    'name' => $row['resource_name'],
                    'type' => $row['resource_type']
                ];
                
                $activities[$activity_id]['resources'][] = $resource;
                
                // Count labor and track contractors
                if ($row['resource_type'] == 'personnel') {
                    $activities[$activity_id]['labor_count']++;
                }
                if ($row['resource_type'] == 'subcontractor' && !in_array($row['resource_name'], $activities[$activity_id]['contractors'])) {
                    $activities[$activity_id]['contractors'][] = $row['resource_name'];
                }
            }
        }
        
        // Convert to indexed array and count totals
        $activities = array_values($activities);
        $total_labor = 0;
        $total_contractors = [];
        
        foreach ($activities as $activity) {
            $total_labor += $activity['labor_count'];
            foreach ($activity['contractors'] as $contractor) {
                if (!in_array($contractor, $total_contractors)) {
                    $total_contractors[] = $contractor;
                }
            }
        }
        
        // Get subcontractor breakdown
        $stmt = $pdo->prepare("
            SELECT 
                r.name as contractor_name,
                COUNT(r.id) as resource_count,
                MAX(s.trade) as trade
            FROM briefings b
            JOIN resources r ON r.briefing_id = b.id
            LEFT JOIN dabs_subcontractors s ON s.name = r.name AND s.project_id = b.project_id
            WHERE b.project_id = ?
            AND b.date = ?
            AND r.type = 'subcontractor'
            GROUP BY r.name
            ORDER BY resource_count DESC
        ");
        $stmt->execute([$project_id, $date]);
        $subcontractors = $stmt->fetchAll();
        
        // Group activities by area
        $activities_by_area = [];
        foreach ($activities as $activity) {
            $area = $activity['area'] ?: 'Unspecified';
            if (!isset($activities_by_area[$area])) {
                $activities_by_area[$area] = [];
            }
            $activities_by_area[$area][] = $activity;
        }
        
        return [
            'activities' => $activities,
            'activities_by_area' => $activities_by_area,
            'total_labor' => $total_labor,
            'total_contractors' => count($total_contractors),
            'contractor_names' => $total_contractors,
            'date' => date('d/m/Y', strtotime($date)),
            'subcontractors' => $subcontractors
        ];
    } catch (PDOException $e) {
        logMessage("Database error when getting activities", $e->getMessage());
        return [
            'error' => 'Database error: ' . $e->getMessage(),
            'activities' => [],
            'total_labor' => 0,
            'total_contractors' => 0
        ];
    }
}
/**
 * Generate PDF report of activities
 * Creates a professionally formatted PDF with all activities and resources
 * 
 * @param array $data - Activity data for PDF
 * @return string - Path to generated PDF file
 */
function generatePDF($data) {
    global $project_name, $username;
    
    // TCPDF is now loaded via Composer autoloader
    if (!class_exists('TCPDF')) {
        logMessage("ERROR: TCPDF class not found. Please run 'composer install'.");
        throw new Exception("PDF generation library (TCPDF) not found. Please run 'composer install'.");
    }
    
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('DABS System');
    $pdf->SetAuthor($username);
    $pdf->SetTitle('Daily Activities Report - ' . $data['date']);
    $pdf->SetSubject('Daily Activities Briefing');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, $project_name . ' - Daily Activity Briefing', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Date: ' . $data['date'], 0, 1, 'C');
    
    // Add resource summary
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Resource Summary', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->MultiCell(90, 10, 'Total Workers: ' . $data['total_labor'], 0, 'L', 0, 0);
    $pdf->MultiCell(90, 10, 'Total Contractors: ' . $data['total_contractors'], 0, 'L', 0, 1);
    
    // Subcontractor breakdown
    if (!empty($data['subcontractors'])) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Subcontractor Breakdown', 0, 1);
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(80, 7, 'Subcontractor', 1, 0, 'L');
        $pdf->Cell(70, 7, 'Trade', 1, 0, 'L');
        $pdf->Cell(30, 7, 'Workers', 1, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 10);
        foreach ($data['subcontractors'] as $sub) {
            $pdf->Cell(80, 7, $sub['contractor_name'], 1, 0, 'L');
            $pdf->Cell(70, 7, $sub['trade'] ?: 'Unknown', 1, 0, 'L');
            $pdf->Cell(30, 7, $sub['resource_count'], 1, 1, 'C');
        }
    }
    
    // Activities by Area
    if (!empty($data['activities_by_area'])) {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Activities by Area', 0, 1);
        
        foreach ($data['activities_by_area'] as $area => $activities) {
            // Area header
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $area, 0, 1);
            
            foreach ($activities as $activity) {
                // Get priority color
                $priorityColors = [
                    'critical' => [255, 0, 0],
                    'high' => [255, 153, 0],
                    'medium' => [0, 102, 204],
                    'low' => [0, 153, 0]
                ];
                $priorityColor = isset($priorityColors[$activity['priority']]) ? 
                                $priorityColors[$activity['priority']] : [128, 128, 128];
                
                // Activity title with priority
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetTextColor($priorityColor[0], $priorityColor[1], $priorityColor[2]);
                $pdf->Cell(0, 8, $activity['title'] . ' (' . ucfirst($activity['priority']) . ' Priority)', 0, 1);
                $pdf->SetTextColor(0, 0, 0);
                
                // Description
                if (!empty($activity['description'])) {
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 6, 'Description: ' . $activity['description'], 0, 'L');
                }
                
                // Resources
                $pdf->SetFont('helvetica', '', 9);
                if ($activity['labor_count'] > 0) {
                    $pdf->Cell(0, 6, 'Workers: ' . $activity['labor_count'], 0, 1);
                }
                
                if (!empty($activity['contractors'])) {
                    $pdf->Cell(0, 6, 'Contractors: ' . implode(', ', $activity['contractors']), 0, 1);
                }
                
                if (!empty($activity['assigned_to'])) {
                    $pdf->Cell(0, 6, 'Assigned to: ' . $activity['assigned_to'], 0, 1);
                }
                
                // Add some space between activities
                $pdf->Ln(3);
            }
            
            // Add space between areas
            $pdf->Ln(5);
        }
    }
    
    // Add generation details at bottom
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Generated by: ' . $username . ' on ' . date('d/m/Y H:i'), 0, 1, 'R');
    
    // Create directory for reports if it doesn't exist
    $report_dir = __DIR__ . '/reports';
    if (!is_dir($report_dir)) {
        mkdir($report_dir, 0755, true);
    }
    
    // Save PDF to file
    $filename = 'DABS_Report_' . date('Ymd_His') . '.pdf';
    $filepath = $report_dir . '/' . $filename;
    $pdf->Output($filepath, 'F');
    
    return $filepath;
}

/**
 * Send email with PDF attachment
 * Sends the generated report to all specified recipients using EmailConfig class
 * 
 * @param array $recipients - Email addresses to send to
 * @param string $pdf_path - Path to PDF file
 * @param string $subject - Email subject
 * @param string $message - Email message
 * @return bool - True if email sent successfully
 */
function sendEmail($recipients, $pdf_path, $subject, $message) {
    global $project_name, $username;
    
    // Validate inputs
    if (empty($recipients) || !file_exists($pdf_path)) {
        logMessage("Invalid email parameters", [
            'recipients' => $recipients,
            'pdf_exists' => file_exists($pdf_path),
            'pdf_path' => $pdf_path
        ]);
        return false;
    }
    
    try {
        // Use EmailConfig class for sending
        require_once __DIR__ . '/includes/email_config.php';
        $emailConfig = new EmailConfig();
        
        // Build HTML email body
        $htmlBody = "<html><body style='font-family: Arial, sans-serif;'>";
        $htmlBody .= "<h2>$project_name - Daily Activity Briefing</h2>";
        $htmlBody .= "<p>Please find attached the daily activity briefing report.</p>";
        
        if (!empty($message)) {
            $htmlBody .= "<p><strong>Message from $username:</strong></p>";
            $htmlBody .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
        }
        
        $htmlBody .= "<p>This report was automatically generated by the DABS system.</p>";
        $htmlBody .= "<hr style='border: 1px solid #ccc; margin: 20px 0;'>";
        $htmlBody .= "<p style='color: #666; font-size: 12px;'>Daily Activity Briefing System</p>";
        $htmlBody .= "</body></html>";
        
        // Send email with attachment
        $success = $emailConfig->sendEmail(
            $recipients,
            $subject,
            $htmlBody,
            [$pdf_path]
        );
        
        logMessage("Email sending " . ($success ? "successful" : "failed"), [
            'to' => is_array($recipients) ? implode(', ', $recipients) : $recipients,
            'subject' => $subject,
            'attachment' => basename($pdf_path)
        ]);
        
        return $success;
        
    } catch (Exception $e) {
        logMessage("Email sending failed with exception", $e->getMessage());
        return false;
    }
}
// Now handle the actual request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST parameters
    $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
    $recipients = isset($_POST['recipients']) ? $_POST['recipients'] : [];
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Daily Activity Briefing - ' . date('d/m/Y', strtotime($date));
    
    // Validate email recipients
    if (empty($recipients) || !is_array($recipients)) {
        outputResponse(['error' => 'No valid recipients provided']);
        exit;
    }
    
    // Filter invalid email addresses
    $valid_recipients = array_filter($recipients, function($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    });
    
    if (empty($valid_recipients)) {
        outputResponse(['error' => 'No valid email addresses provided']);
        exit;
    }
    
    try {
        // Get activities for the date
        $activities_data = getActivitiesForDate($date);
        
        if (isset($activities_data['error'])) {
            throw new Exception($activities_data['error']);
        }
        
        // Generate PDF
        $pdf_path = generatePDF($activities_data);
        
        // Send email
        $email_success = sendEmail(
            $valid_recipients, 
            $pdf_path, 
            $subject, 
            $message
        );
        
        if (!$email_success) {
            throw new Exception("Failed to send email");
        }
        
        // Record email sending in activity log
        try {
            // Database connection details
            $db_host = '10.35.233.124:3306';
            $db_name = 'k87747_dabs';
            $db_user = 'k87747_dabs';
            $db_pass = 'Subaru5554346';
            
            $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            $details = "Sent DABS report for " . date('d/m/Y', strtotime($date)) . 
                      " to " . count($valid_recipients) . " recipients";
            
            $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                'email_report',
                $details,
                $_SERVER['REMOTE_ADDR']
            ]);
        } catch (PDOException $e) {
            // Just log this error but don't fail the whole operation
            logMessage("Failed to log email activity", $e->getMessage());
        }
        
        // Return success response
        outputResponse([
            'success' => true,
            'message' => 'Report sent successfully to ' . count($valid_recipients) . ' recipients',
            'recipients' => $valid_recipients,
            'date' => date('d/m/Y', strtotime($date)),
            'filename' => basename($pdf_path)
        ]);
    } catch (Exception $e) {
        logMessage("Error in email report process", $e->getMessage());
        outputResponse([
            'error' => $e->getMessage()
        ]);
    }
} else {
    // If not a POST request, return error
    outputResponse(['error' => 'Invalid request method']);
}
?>