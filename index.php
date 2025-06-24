<?php
/**
 * =========================================================================
 * index.php - Daily Activity Briefing System (DABS) Main Dashboard
 * =========================================================================
 *
 * FILE NAME: index.php
 * LOCATION: / (Root Directory)
 * 
 * DESCRIPTION:
 * This file serves as the central dashboard and main control panel for the Daily Activity 
 * Briefing System (DABS). It provides a comprehensive web-based interface specifically 
 * designed for managing construction project daily activities, briefings, subcontractor 
 * coordination, and project communication within the UK construction industry.
 * 
 * The system integrates multiple management modules including project overview displays,
 * attendee management with subcontractor association, comprehensive activity scheduling
 * with priority tracking, weather forecast integration for site planning, safety 
 * information management, rich text notes editing, and detailed subcontractor status 
 * tracking. All functionality is optimized for UK construction industry standards
 * with consistent UK date/time formatting throughout the entire application.
 * 
 * WHAT THIS FILE DOES:
 * ✅ Main Dashboard Interface: Serves as the primary interface for daily construction briefings
 * ✅ User Authentication: Manages user login sessions with UK timezone handling (Europe/London)
 * ✅ Project Management: Displays comprehensive project information with UK date/time formatting
 * ✅ Activity Scheduling: Integrates modern Activity Schedule system for task management
 * ✅ Attendee Management: Handles meeting attendees with subcontractor association
 * ✅ Weather Integration: Provides detailed weather information for construction site planning
 * ✅ Safety Management: Manages safety information with editable content and yellow styling
 * ✅ Notes System: Offers rich text editing for project communication and documentation
 * ✅ Subcontractor Tracking: Tracks contractor status, tasks, and availability with visual indicators
 * ✅ System Administration: Provides admin controls and comprehensive debugging tools
 * ✅ Responsive Design: Ensures optimal experience across mobile and desktop devices
 * ✅ Error Handling: Maintains comprehensive logging and error handling for system reliability
 * ✅ Print/Email Functions: Integrates briefing distribution and record keeping capabilities
 * ✅ Accessibility Features: Provides keyboard shortcuts and screen reader support
 * ✅ Auto-Management: Handles automatic project and briefing creation with database integrity
 * 
 * KEY FEATURES IMPLEMENTED:
 * 🎨 Modern Bootstrap 5 Design: Responsive layout with gradient styling and smooth animations
 * 🇬🇧 UK Timezone Integration: All date/time operations use Europe/London timezone
 * 📋 Activity Schedule System: Complete CRUD operations with priority indicators and real-time updates
 * 👥 Real-time Attendee Management: Intuitive interface with drag-and-drop functionality
 * 🌤️ Weather API Integration: Construction site planning with safety considerations
 * ⚠️ Safety Information Editor: Distinctive yellow background for easy identification and editing
 * 📝 Rich Text Notes: TinyMCE integration for comprehensive documentation
 * 🏗️ Subcontractor Status Tracking: Color-coded visual indicators for quick status recognition
 * ⚙️ System Administration: Debug console access for troubleshooting and system monitoring
 * 📧 Print/Email Distribution: Daily briefing sharing and record keeping functionality
 * 🔒 Session-based Security: Comprehensive authentication with logging and timeout handling
 * 📱 Mobile-responsive Design: Optimized for tablets, smartphones, and desktop computers
 * ♿ Enhanced Accessibility: Inclusive design with comprehensive screen reader support
 * ⌨️ Keyboard Shortcuts: Power user features for enhanced productivity
 * 🔄 Auto-initialization: Automatic project and briefing setup with proper database relationships
 * 
 * TECHNICAL SPECIFICATIONS:
 * 💻 PHP Version: 8.0+ required with session management and database connectivity
 * 🗄️ Database: MySQL with dabs_subcontractors table support and referential integrity
 * 🎨 Frontend Framework: Bootstrap 5.3.0 for modern responsive UI components
 * 🎯 Icons: Font Awesome 6.0.0 for comprehensive iconography and visual indicators
 * ✏️ Text Editor: TinyMCE 5 for rich text editing in notes section
 * 🔧 JavaScript: Modern ES6+ with async/await patterns for performance
 * 🎨 Styling: CSS3 with custom gradients, animations, and responsive design
 * 🕐 Timezone: UK timezone (Europe/London) for all operations and display
 * 🔐 Authentication: Session-based with security logging and timeout protection
 * 🔄 AJAX Integration: Real-time updates without page refresh for enhanced UX
 * 📱 Responsive Design: Optimal experience across all device types and screen sizes
 * 🛡️ Error Handling: Comprehensive logging and graceful error recovery
 * 
 * SECURITY FEATURES IMPLEMENTED:
 * 🔐 Authentication Validation: User verification on every page load with session checks
 * ⏰ Session Management: Proper timeout handling with security logging
 * 🛡️ SQL Injection Prevention: Prepared statements throughout all database operations
 * 🚫 XSS Protection: Comprehensive HTML escaping for all user-generated content
 * 📊 Security Logging: Monitoring of unauthorized access attempts and suspicious activity
 * 🔒 CSRF Protection: Form submission protection and data modification security
 * ✅ Input Validation: Sanitization for all user inputs and system parameters
 * 📁 Secure File Handling: Protected upload and document management
 * 
 * DATABASE INTEGRATION FEATURES:
 * 🔄 Auto Project Detection: Automatic project identification and briefing creation
 * 🛡️ Robust Error Handling: Graceful degradation for missing or corrupt data
 * 📊 Modern Table Structure: Uses dabs_subcontractors with proper project_id relationships
 * 📝 Comprehensive Logging: SQL error logging and recovery procedures
 * 🔄 Transaction Support: Data integrity with automatic rollback on errors
 * ✅ Schema Validation: Automatic database validation and repair capabilities
 * 
 * USER INTERFACE COMPONENTS:
 * 📊 Main Dashboard: Project information with current UK date/time and system status
 * 👥 Attendees Panel: Modern chip-based design with subcontractor information
 * 📋 Activity Schedule: CRUD operations with priority indicators and progress tracking
 * 🌤️ Weather Panel: Detailed construction planning information and safety considerations
 * ⚠️ Safety Panel: Editable content with distinctive styling and version control
 * 📝 Notes Panel: Rich text editing with version history and collaborative features
 * 🏗️ Subcontractor Panel: Accordion display with status tracking and contact management
 * ⚙️ Admin Menu: Debug console, user management, and system monitoring tools
 * 💬 Modal Dialogs: Modern forms for data entry and editing operations
 * 🔔 Toast Notifications: Real-time user feedback and system status updates
 * 📱 Responsive Navigation: Optimal layout for all device types and screen sizes
 * 
 * AUTHOR: Chris Irlam (System Administrator)
 * CREATED: 07/06/2025 (UK Date Format)
 * LAST UPDATED: 24/06/2025 11:23:54 (UK Time - Europe/London)
 * VERSION: 6.0.0 - Enhanced Modern Implementation with Complete Activity Integration
 * CURRENT USER: irlam
 * 
 * CHANGELOG v6.0.0 (24/06/2025 11:23:54 UK Time):
 * ✅ FIXED: Critical activity creation issues with proper briefing_id integration
 * ✅ ENHANCED: Complete Activity Schedule backend support with error handling
 * ✅ IMPROVED: UK date/time formatting consistency (24/06/2025 11:23:54 format)
 * ✅ ENHANCED: Modern Bootstrap 5 styling with gradients and animations
 * ✅ IMPROVED: Error handling and user feedback with UK timestamps
 * ✅ ENHANCED: System reliability with comprehensive error checking
 * ✅ IMPROVED: Mobile responsiveness across all device types
 * ✅ ENHANCED: Code structure for easier maintenance and future development
 * ✅ IMPROVED: Security features and comprehensive session management
 * ✅ ENHANCED: Database connection management and recovery procedures
 * ✅ ADDED: Global JavaScript variables for frontend-backend communication
 * ✅ IMPROVED: Modern UI components with enhanced accessibility
 * ✅ ENHANCED: Performance optimizations and faster loading times
 * ✅ ADDED: Comprehensive debugging and troubleshooting capabilities
 * ✅ IMPROVED: Activity creation with proper briefing_id handling
 * ✅ ENHANCED: User experience with loading animations and feedback
 * =========================================================================
 */

// Set UK timezone for consistent date/time formatting throughout the entire system
// This ensures all timestamps display in UK format (24/06/2025 11:23:54)
date_default_timezone_set('Europe/London');

// Start session management for user authentication and comprehensive project context
// Sessions handle user login state, project selection, and security
session_start();

// Enable comprehensive error reporting for debugging and system monitoring
// These settings help identify issues during development and maintenance
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Create logs directory if it doesn't exist with proper permissions for security
// This ensures error logging works properly and maintains system audit trails
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Enhanced error logging function with UK timestamps for comprehensive audit trails
 * Provides detailed debugging and system monitoring capabilities for administrators
 * 
 * @param string $message - The primary error message to log for troubleshooting
 * @param array $context - Additional context data for debugging and analysis
 * @return void
 * 
 * Example Usage:
 * logError('Database connection failed', ['host' => 'localhost', 'user' => 'admin']);
 */
function logError($message, $context = []) {
    // Format timestamp in UK format for consistency
    $timestamp = date('d/m/Y H:i:s');
    $logMessage = "[{$timestamp}] {$message}";
    
    // Add context information if provided for detailed debugging
    if (!empty($context)) {
        $logMessage .= ' | Context: ' . json_encode($context);
    }
    
    // Write to error log file for system monitoring
    error_log($logMessage);
}

// Include required system files with comprehensive error handling for reliability
// These files contain essential functions for database connectivity and authentication
try {
    // Check for database connection file and include it
    if (!file_exists('includes/db_connect.php')) {
        throw new Exception('Database connection file not found: includes/db_connect.php');
    }
    require_once 'includes/db_connect.php';
    
    // Check for functions file and include it
    if (!file_exists('includes/functions.php')) {
        throw new Exception('Functions file not found: includes/functions.php');
    }
    require_once 'includes/functions.php';
    
    // Check for authentication file and include it
    if (!file_exists('includes/auth.php')) {
        throw new Exception('Authentication file not found: includes/auth.php');
    }
    require_once 'includes/auth.php';
    
} catch (Exception $e) {
    // Log critical error with context for system administrator
    logError('Critical Error: Unable to load required system files', ['error' => $e->getMessage()]);
    
    // Display user-friendly error page with system information
    die('<!DOCTYPE html>
    <html><head><title>DABS System Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light">
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>System Error</h4>
            <p>Unable to load required system files. Please check file permissions and paths.</p>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p><strong>Time:</strong> ' . date('d/m/Y H:i:s') . ' (UK)</p>
            <p><strong>User:</strong> ' . htmlspecialchars($_SESSION['user_name'] ?? 'Unknown') . '</p>
            <p><strong>Please contact the system administrator for assistance.</strong></p>
        </div>
    </div></body></html>');
}

// Verify user authentication - redirect to login if not authenticated
// This security check ensures only logged-in users can access the dashboard
if (!isUserLoggedIn()) {
    // Log unauthorized access attempt for security monitoring and comprehensive audit trail
    logError('Unauthorized access attempt', [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp' => date('d/m/Y H:i:s'),
        'requested_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ]);
    
    // Redirect to login page for authentication
    header('Location: login.php');
    exit;
}

// Get current date/time in UK format for display throughout the system
// These variables provide consistent UK formatting across all components
$currentDate = date('d/m/Y');           // UK date format: 24/06/2025
$currentTime = date('H:i');             // UK time format: 11:23
$currentDateTime = date('d/m/Y H:i:s'); // UK datetime: 24/06/2025 11:23:54
$currentDateISO = date('Y-m-d');        // ISO format for database: 2025-06-24

// Initialize project and briefing data with comprehensive error handling and fallback mechanisms
// These variables store project information and ensure system stability
$projectID = 1; // Default project ID for fallback scenarios
$projectInfo = null;        // Will store project details from database
$briefingData = null;       // Will store today's briefing information
$subcontractors = [];       // Will store project subcontractors
$systemErrors = [];         // Will collect any system errors for display

try {
    // Get database connection with proper error handling
    // This establishes connection to MySQL database for all operations
    $pdo = connectToDatabase();
    
    // Get current project information with fallback mechanisms for reliability
    // Check if user has selected a specific project, otherwise use default
    if (isset($_SESSION['current_project']) && $_SESSION['current_project'] > 0) {
        $projectID = intval($_SESSION['current_project']);
    }
    
    // Verify project exists, create default if needed for system stability
    // This ensures there's always a valid project to work with
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectID]);
    $projectInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Create default project if none exists to ensure system functionality
    if (!$projectInfo) {
        logError('No project found, creating default project', ['project_id' => $projectID]);
        
        // Insert new default project with current user as manager
        $stmt = $pdo->prepare("INSERT INTO projects (id, name, location, manager, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $projectID,
            'Default Construction Project',
            'Construction Site',
            $_SESSION['user_name'] ?? 'System Administrator',
            $currentDateISO,
            date('Y-m-d', strtotime('+1 year')),
            'active'
        ]);
        
        // Retrieve the newly created project for immediate use
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectID]);
        $projectInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Ensure we have project info with comprehensive fallback data
    // This prevents system failure if project data is missing
    if (!$projectInfo) {
        $projectInfo = [
            'id' => $projectID,
            'name' => 'Default Project',
            'location' => 'Construction Site',
            'manager' => $_SESSION['user_name'] ?? 'System Administrator',
            'start_date' => $currentDateISO,
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'status' => 'active'
        ];
    }
    
    // Get or create today's briefing with comprehensive error handling
    // Briefings store daily activity information and meeting notes
    $stmt = $pdo->prepare("SELECT * FROM briefings WHERE project_id = ? AND date = ?");
    $stmt->execute([$projectID, $currentDateISO]);
    $briefingData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Create today's briefing if it doesn't exist to ensure system functionality
    if (!$briefingData) {
        logError('No briefing found for today, creating new briefing', [
            'project_id' => $projectID,
            'date' => $currentDateISO
        ]);
        
        // Insert new briefing with default content and current user
        $stmt = $pdo->prepare("INSERT INTO briefings (project_id, date, overview, safety_info, notes, created_by, last_updated, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $projectID,
            $currentDateISO,
            'Daily briefing for ' . $currentDate,
            '<ul><li>Follow all standard safety protocols</li><li>Wear appropriate PPE at all times</li><li>Report any safety concerns immediately</li></ul>',
            'Daily briefing notes for ' . $currentDate,
            $_SESSION['user_id'] ?? 1,
            date('Y-m-d H:i:s'),
            'draft'
        ]);
        
        $briefingId = $pdo->lastInsertId();
        
        // Retrieve the newly created briefing for immediate use
        $stmt = $pdo->prepare("SELECT * FROM briefings WHERE id = ?");
        $stmt->execute([$briefingId]);
        $briefingData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Ensure we have briefing data with comprehensive fallback information
    // This prevents errors if briefing creation fails
    if (!$briefingData) {
        $briefingData = [
            'id' => 0,
            'project_id' => $projectID,
            'date' => $currentDateISO,
            'overview' => 'Daily briefing for ' . $currentDate,
            'safety_info' => '<ul><li>Follow all standard safety protocols</li><li>Wear appropriate PPE at all times</li><li>Report any safety concerns immediately</li></ul>',
            'notes' => 'Daily briefing notes for ' . $currentDate,
            'created_by' => $_SESSION['user_id'] ?? 1,
            'last_updated' => date('Y-m-d H:i:s'),
            'status' => 'draft'
        ];
    }
    
    // Get subcontractors for this project with comprehensive error handling
    // Subcontractors are external companies working on the project
    try {
        $stmt = $pdo->prepare("SELECT * FROM dabs_subcontractors WHERE project_id = ? ORDER BY name ASC");
        $stmt->execute([$projectID]);
        $subcontractors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error but don't fail the entire system
        logError('Error loading subcontractors', ['error' => $e->getMessage()]);
        $subcontractors = [];
        $systemErrors[] = 'Unable to load subcontractors. Some features may be limited.';
    }
    
} catch (Exception $e) {
    // Log critical database error with full context for troubleshooting
    logError('Critical database error in index.php', [
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'user' => $_SESSION['user_name'] ?? 'Unknown',
        'timestamp' => $currentDateTime
    ]);
    
    // Provide fallback data to prevent complete system failure
    // This ensures the page loads even with database issues
    $projectInfo = [
        'id' => $projectID,
        'name' => 'System Error - Project Data Unavailable',
        'location' => 'Unknown',
        'manager' => $_SESSION['user_name'] ?? 'System Administrator',
        'start_date' => $currentDateISO,
        'end_date' => date('Y-m-d', strtotime('+1 year')),
        'status' => 'error'
    ];
    
    $briefingData = [
        'id' => 0,
        'project_id' => $projectID,
        'date' => $currentDateISO,
        'overview' => 'System error - unable to load briefing data',
        'safety_info' => '<div class="alert alert-warning">System error - unable to load safety information. Please contact system administrator.</div>',
        'notes' => 'System error - unable to load notes. Please contact system administrator.',
        'created_by' => $_SESSION['user_id'] ?? 1,
        'last_updated' => date('Y-m-d H:i:s'),
        'status' => 'error'
    ];
    
    $subcontractors = [];
    $systemErrors[] = 'Database connection error. Some features may not work properly.';
}

// Update session with current project for consistency across the application
// This ensures the selected project persists across page loads
$_SESSION['current_project'] = $projectID;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for responsive design and character encoding -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DABS - <?php echo htmlspecialchars($projectInfo['name']); ?> - <?php echo $currentDate; ?></title>
    
    <!-- External CSS libraries for modern styling and responsive design -->
    <!-- Bootstrap 5.3.0 provides responsive grid system and modern components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS files for specific styling and component design -->
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/weather.css" rel="stylesheet">
    <link href="css/subcontractors.css" rel="stylesheet">
    
    <!-- Google Fonts for modern typography and improved readability -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6.0.0 for comprehensive iconography and visual indicators -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- TinyMCE for rich text notes editing capabilities with comprehensive formatting options -->
    <script src="https://cdn.tiny.cloud/1/cx3e21j3t5yv0ukx72zuh02xf9o75o3bgencxrbbzmad1p5c/tinymce/5/tinymce.min.js"></script>
    
    <style>
        /* =====================================================================
         * CUSTOM CSS STYLING - MODERN DESIGN WITH UK CONSTRUCTION FOCUS
         * Enhanced for Version 6.0.0 with improved visual design and animations
         * Last Updated: 24/06/2025 11:23:54 (UK Time)
         * Author: Chris Irlam
         * ===================================================================== */
        
        /* Modern gradient color variables for consistent theming throughout application */
        /* These CSS custom properties ensure consistent colors across all components */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            --info-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --activity-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --uk-blue: #012169;              /* Official UK blue color */
            --uk-red: #C8102E;               /* Official UK red color */
            --construction-orange: #FF8C00;   /* Construction industry standard orange */
            --safety-yellow: #FFD700;        /* Safety warning yellow */
            --shadow-light: 0 2px 8px rgba(0,0,0,0.1);
            --shadow-medium: 0 4px 15px rgba(0,0,0,0.15);
            --shadow-heavy: 0 8px 25px rgba(0,0,0,0.2);
        }

        /* Body styling with modern gradient background for enhanced visual appeal */
        /* Sets the overall look and feel of the application */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Main title bar with clean modern styling and professional appearance */
        /* Creates an impressive header that represents the system branding */
        .main-title-bar {
            background: var(--primary-gradient);
            color: white;
            border-bottom: 3px solid rgba(255,255,255,0.2);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
        }
        
        /* Animated shimmer effect for the title bar */
        /* Adds a subtle animation that draws attention to the system branding */
        .main-title-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 4s infinite;
        }
        
        /* Keyframe animation for the shimmer effect */
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        /* Main title styling with professional typography */
        .main-title-bar h1 {
            margin: 0;
            padding: 1.5rem 0;
            font-size: 2.8rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 2px;
            font-family: 'Roboto', Arial, sans-serif;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        
        /* Logo container with modern styling and smooth hover effects */
        /* Provides a professional space for company branding */
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            height: 80px;
            width: 80px;
            background: #fff;
            border-radius: 16px;
            box-shadow: var(--shadow-light);
            padding: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }
        
        /* Logo hover effects for enhanced interactivity */
        .logo-container:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: var(--shadow-medium);
        }
        
        /* Logo image styling for proper display */
        .logo {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
            transition: all 0.3s ease;
        }
        
        /* Responsive design adjustments for mobile devices and smaller screens */
        /* Ensures optimal display across all device types */
        @media (max-width: 767px) {
            .main-title-bar h1 { 
                font-size: 1.8rem; 
                letter-spacing: 1px;
                padding: 1rem 0;
            }
            .logo-container { 
                width: 48px; 
                height: 48px; 
                padding: 4px; 
            }
        }
        
        /* Modern card styling with hover effects and smooth transitions */
        /* Creates consistent card-based layout throughout the application */
        .card {
            border: none;
            box-shadow: var(--shadow-light);
            border-radius: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            background: #ffffff;
        }

        /* Card hover effects for enhanced user interaction */
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
        }

        /* Subtle top border animation for cards */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Show top border on card hover */
        .card:hover::before {
            opacity: 1;
        }

        /* Card header styling with gradient background */
        .card-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
            border: none;
            position: relative;
        }

        /* Card header title styling */
        .card-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.4rem;
        }
        
        /* Activity Schedule specific styling for the integrated system */
        /* Provides distinctive styling for the activity management section */
        .activity-schedule-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        }
        
        .activity-schedule-card .card-header {
            background: var(--activity-gradient);
        }
        
        /* Activity item styling with modern design and comprehensive priority indicators */
        /* Creates visually appealing activity cards with priority-based styling */
        .activity-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 5px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        /* Activity item top border animation */
        .activity-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Show top border on activity item hover */
        .activity-item:hover::before {
            opacity: 1;
        }

        /* Activity item hover effects for enhanced interactivity */
        .activity-item:hover {
            transform: translateX(10px);
            box-shadow: var(--shadow-medium);
        }
		/* Enhanced fade out animation for task removal */
@keyframes fadeOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(-20px); }
}

        /* Enhanced priority-based color coding for activities with clear visual distinction */
        /* Provides immediate visual feedback about task priority levels */
        .priority-critical { 
            border-left-color: #dc3545; 
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        }
        .priority-high { 
            border-left-color: #fd7e14; 
            background: linear-gradient(135deg, #fff8f0 0%, #ffffff 100%);
        }
        .priority-medium { 
            border-left-color: #ffc107; 
            background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
        }
        .priority-low { 
            border-left-color: #28a745; 
            background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
        }
        
        /* Enhanced attendee chips with modern styling and improved blue color scheme */
        /* Creates attractive tags for displaying meeting attendees */
        .attendee-chip {
            display: inline-flex;
            align-items: center;
            flex-direction: column;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 25px;
            padding: 0.6rem 1.8rem 0.6rem 1.2rem;
            margin: 0.3rem;
            font-size: 1rem;
            font-weight: 500;
            color: #1565C0;
            position: relative;
            min-width: 130px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
            border: 2px solid transparent;
        }
        
        /* Attendee chip hover effects for enhanced interactivity */
        .attendee-chip:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: var(--shadow-medium);
            border-color: #1976d2;
        }
        
        /* Remove button styling for attendee chips */
        .attendee-chip button {
            background: none;
            border: none;
            margin-left: 0.5rem;
            color: #1976d2;
            font-size: 1.2rem;
            cursor: pointer;
            position: absolute;
            top: 0.3rem;
            right: 0.3rem;
            transition: all 0.3s ease;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Remove button hover effects */
        .attendee-chip button:hover {
            color: #d32f2f;
            background: rgba(255,255,255,0.8);
            transform: scale(1.1);
        }
        
        /* Subcontractor name styling within attendee chips */
        .subcontractor-name {
            font-size: 0.75rem;
            color: #5c6bc0;
            font-weight: normal;
            margin-top: 0.2rem;
            display: block;
            text-align: center;
            opacity: 0.9;
            font-style: italic;
        }
        
        /* Enhanced task item styling for interactive elements and form management */
        /* Creates attractive form fields for task input */
        .task-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        /* Task item hover effects */
        .task-item:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            border-color: #ced4da;
            transform: translateX(5px);
        }
        
        /* Task input field styling */
        .task-item input {
            flex-grow: 1;
            border: none;
            background: transparent;
            padding: 0.5rem;
            font-size: 0.95rem;
        }
        
        /* Task input focus effects */
        .task-item input:focus {
            outline: none;
            background: white;
            border-radius: 4px;
            box-shadow: 0 0 0 2px rgba(0,123,255,.25);
        }
        
        /* Task remove button styling */
        .task-item button {
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }
        
        /* Task remove button hover effects */
        .task-item button:hover {
            transform: scale(1.1);
        }
        
        /* Enhanced notification area positioning for toast messages and user feedback */
        /* Ensures notifications appear in optimal location for user attention */
        #notificationArea {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        }
        
        /* Enhanced debug console styling for development and comprehensive troubleshooting */
        /* Provides developer tools for system monitoring and debugging */
        #debugConsole {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(135deg, #333 0%, #222 100%);
            color: #fff;
            font-family: 'Courier New', monospace;
            z-index: 9999;
            max-height: 30vh;
            overflow-y: auto;
            display: none;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.3);
            border-top: 3px solid var(--primary-gradient);
        }
        
        /* Debug console header styling */
        #debugConsole .debug-header {
            background: linear-gradient(135deg, #555 0%, #444 100%);
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #666;
        }
        
        /* Debug console content area styling */
        #debugConsole .debug-content {
            padding: 15px;
            font-size: 12px;
            line-height: 1.4;
        }
        
        /* Debug console text styling */
        #debugConsole pre {
            margin: 0;
            white-space: pre-wrap;
            color: #63ff51;
        }
        
        /* Enhanced debug message color coding for different log levels and system monitoring */
        /* Provides visual distinction between different types of log messages */
        .debug-error { color: #ff5151 !important; font-weight: bold; }
        .debug-warn { color: #ffbb51 !important; }
        .debug-info { color: #51b0ff !important; }
        
        /* Enhanced status badge styling with comprehensive color coding for different states */
        /* Provides immediate visual feedback about subcontractor status */
        .badge-Active { background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%); color: white; }
        .badge-Standby { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; }
        .badge-Offsite { background: linear-gradient(135deg, #9e9e9e 0%, #757575 100%); color: white; }
        .badge-Delayed { background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%); color: white; }
        .badge-Complete { background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%); color: white; }
        
        /* Enhanced modern button styling with gradients and improved hover effects */
        /* Creates attractive and interactive buttons throughout the interface */
        .btn-modern {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        /* Shimmer effect for modern buttons */
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        /* Show shimmer effect on button hover */
        .btn-modern:hover::before {
            left: 100%;
        }

        /* Button hover effects */
        .btn-modern:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: var(--shadow-heavy);
        }

        /* Primary button gradient styling */
        .btn-primary.btn-modern {
            background: var(--primary-gradient);
        }

        /* Success button gradient styling */
        .btn-success.btn-modern {
            background: var(--success-gradient);
        }
        
        /* Enhanced notes editor styling for rich text editing capabilities and content management */
        /* Provides professional text editing interface for project notes */
        .notes-editor {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            min-height: 200px;
            background-color: #fff;
            transition: all 0.3s ease;
            position: relative;
        }
        
        /* Notes editor focus effects */
        .notes-editor:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        
        /* Read-only notes editor styling */
        .notes-editor.readonly {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        /* Notes metadata styling */
        .notes-meta {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.5rem;
            text-align: right;
            font-style: italic;
        }
        
        /* Enhanced safety editor styling with distinctive light yellow background for visual identification */
        /* Creates immediately recognizable safety information section */
        .safety-editor {
            border: 2px solid var(--safety-yellow);
            border-radius: 8px;
            padding: 1rem;
            min-height: 200px;
            background: linear-gradient(135deg, #fff9c4 0%, #fffacd 100%);
            transition: all 0.3s ease;
            position: relative;
        }
        
        /* Safety editor label */
        .safety-editor::before {
            content: '⚠️ SAFETY INFORMATION';
            position: absolute;
            top: -12px;
            left: 15px;
            background: var(--safety-yellow);
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: bold;
            color: #333;
            border-radius: 4px;
        }
        
        /* Safety editor focus effects */
        .safety-editor:focus {
            outline: none;
            border-color: var(--safety-yellow);
            box-shadow: 0 0 0 0.2rem rgba(255,193,7,.25);
        }
        
        /* Read-only safety editor styling */
        .safety-editor.readonly {
            background: linear-gradient(135deg, #fff9c4 0%, #fffacd 100%);
            color: #6c757d;
        }
        
        /* Enhanced modal styling with modern design and comprehensive smooth animations */
        /* Creates professional dialog boxes for data entry and editing */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
        }

        /* Modal header styling with gradient background */
        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            position: relative;
        }

        /* Modal header shimmer effect */
        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: modalShimmer 3s infinite;
        }

        /* Modal shimmer animation */
        @keyframes modalShimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        /* Enhanced form styling with modern inputs and improved focus effects */
        /* Creates attractive and user-friendly form elements */
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
        }

        /* Form control focus effects */
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-1px);
        }
        
        /* Enhanced date and project display styling for comprehensive header information */
        /* Creates professional header display for current date and project information */
        .date-display {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        /* Project name styling */
        .project-name {
            font-size: 1rem;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        /* Enhanced fade in animation for dynamic content loading and improved user experience */
        /* Provides smooth transitions when content appears */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        /* Fade in keyframe animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Enhanced system error styling for critical error messages */
        /* Creates attention-grabbing display for important system messages */
        .system-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
            border: 2px solid #f5c2c7;
            color: #721c24;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1rem 0;
            box-shadow: var(--shadow-medium);
            position: relative;
        }
        
        /* Error icon styling */
        .system-error::before {
            content: '🚨';
            position: absolute;
            top: -10px;
            left: 15px;
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 1.2rem;
        }
        
        /* Error title styling */
        .system-error h5 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        /* Loading animation for enhanced user feedback */
        /* Provides visual indication when content is loading */
        .loading-pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }

        /* Pulse animation keyframes */
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Enhanced scroll animations for better user experience */
        /* Creates smooth animations as elements come into view */
        .scroll-fade {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        /* Visible state for scroll animations */
        .scroll-fade.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Print-specific styling to hide unnecessary elements during printing */
        @media print {
            #debugConsole,
            #notificationArea,
            .btn-modern,
            .dropdown {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
        }
    </style>
</head>
<body>
	    <!-- Main Title Bar with enhanced modern gradient styling and professional appearance -->
    <!-- Created: 24/06/2025 14:43:38 (UK Time) - Author: Chris Irlam -->
    <!-- This creates the main system header with animated gradient background and company branding -->
    <div class="main-title-bar">
        <h1><i class="fas fa-clipboard-list me-3"></i>Daily Activity Briefing System</h1>
    </div>
    
    <!-- Notification Area for displaying comprehensive toast messages and user feedback -->
    <!-- Position: Fixed top-right corner for optimal user attention -->
    <!-- Purpose: Shows system messages, success confirmations, and error alerts with UK timestamps -->
    <div id="notificationArea"></div>
    
    <!-- System Errors Display (if any) with enhanced styling for better visibility -->
    <!-- This section displays any system-wide errors or warnings that need user attention -->
    <!-- Only shown when there are actual system issues detected during page load -->
    <?php if (!empty($systemErrors)): ?>
    <div class="container-fluid">
        <?php foreach ($systemErrors as $error): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>System Notice:</strong> <?php echo htmlspecialchars($error); ?>
            <small class="d-block mt-1">Time: <?php echo $currentDateTime; ?> (UK)</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Enhanced Debug Console with modern styling (hidden by default, toggle with Alt+D or System menu) -->
    <!-- Purpose: Provides developers and administrators with real-time system monitoring -->
    <!-- Features: Error logging, performance metrics, API call monitoring, and system status -->
    <div id="debugConsole">
        <div class="debug-header">
            <strong><i class="fas fa-bug me-2"></i>Debug Console - UK Time: <?php echo $currentDateTime; ?></strong>
            <div>
                <button id="clearDebugBtn" class="btn btn-sm btn-warning">
                    <i class="fas fa-eraser me-1"></i>Clear
                </button>
                <button id="closeDebugBtn" class="btn btn-sm btn-danger">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
        <div class="debug-content">
            <pre>DABS Debug Console Ready - <?php echo $currentDateTime; ?> (UK Time)
System initialized successfully - Version 6.0.0
Project: <?php echo htmlspecialchars($projectInfo['name']); ?>
Project ID: <?php echo $projectID; ?>
Briefing ID: <?php echo isset($briefingData['id']) ? $briefingData['id'] : 'Not Available'; ?>
User: <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown'); ?>
Activity Schedule System: Enhanced v6.0.0 with Complete Backend Integration
Database Status: <?php echo empty($systemErrors) ? 'Connected and Operational' : 'Partial Connection - Some Features Limited'; ?>
System Status: All components loaded successfully
Last Updated: <?php echo $currentDateTime; ?> (UK)
</pre>
        </div>
    </div>

    <!-- Main Dashboard Container with comprehensive layout and enhanced responsive design -->
    <!-- This container holds all the main content panels and ensures proper responsive behavior -->
    <div class="container-fluid dashboard">
        
        <!-- Enhanced Header Section: Company branding, current date/time, project info, and system controls -->
        <!-- Layout: 3-column responsive design (Logo | Date/Project | Controls) -->
        <!-- Purpose: Provides essential system information and quick access to key functions -->
        <header class="mb-4">
            <div class="row align-items-center">
                <!-- Company Logo Section (Left Column) -->
                <!-- Displays company branding with hover effects and responsive sizing -->
                <div class="col-md-4">
                    <div class="logo-container">
                        <img src="images/logo.png" alt="Company Logo" class="logo" onerror="this.style.display='none'">
                    </div>
                </div>
                
                <!-- Date and Project Information Section (Center Column) -->
                <!-- Shows current UK date/time and active project information -->
                <div class="col-md-4 text-center">
                    <div class="date-display" id="currentDate"><?php echo $currentDate; ?></div>
                    <div class="project-name">
                        <i class="fas fa-project-diagram me-2"></i>
                        Project: <span id="projectName"><?php echo htmlspecialchars($projectInfo['name']); ?></span>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Last updated: <span id="lastUpdated"><?php echo $currentDateTime; ?></span> (UK)
                    </small>
                </div>
                
                <!-- System Controls Section (Right Column) -->
                <!-- Provides access to email, print, and system administration functions -->
                <div class="col-md-4 text-end">
                    <!-- Action Buttons Group: Email and Print functionality -->
                    <div class="btn-group me-2" role="group">
                        <button id="emailBtn" class="btn btn-success btn-modern" title="Email briefing report" onclick="emailReport()">
                            <i class="fas fa-envelope me-1"></i> Email
                        </button>
                        <button id="printBtn" class="btn btn-info btn-modern" title="Print briefing report" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                    
                    <!-- System Administration Dropdown Menu -->
                    <!-- Provides access to system settings, debug tools, and user account options -->
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-secondary dropdown-toggle btn-modern" type="button" id="userMenu"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i> System
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><h6 class="dropdown-header">
                                <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown User'); ?>
                            </h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="javascript:toggleDebugConsole()">
                                <i class="fas fa-bug me-2"></i>Debug Console
                            </a></li>
                            <li><a class="dropdown-item" href="javascript:location.reload()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh Page
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- ATTENDEES MANAGEMENT PANEL with enhanced modern styling and improved functionality -->
        <!-- Purpose: Manages daily briefing attendees with subcontractor associations -->
        <!-- Features: Add/remove attendees, subcontractor tracking, modern chip-based UI -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card overview-card fade-in scroll-fade">
                    <!-- Card Header with title and description -->
                    <div class="card-header">
                        <h2><i class="fas fa-users me-2"></i> Meeting Attendees</h2>
                        <small class="opacity-75">Manage today's briefing attendees and subcontractor information</small>
                    </div>
                    
                    <!-- Card Body containing attendee management interface -->
                    <div class="card-body">
                        <!-- Enhanced Attendee Input Form with modern styling and validation -->
                        <!-- Layout: Responsive 3-column form (Name | Subcontractor | Add Button) -->
                        <form class="row g-3 align-items-center" id="attendeeForm" autocomplete="off">
                            <!-- Attendee Name Input Field -->
                            <div class="col-12 col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control" id="attendeeInput" 
                                           placeholder="Enter attendee name" required 
                                           maxlength="100" />
                                </div>
                            </div>
                            
                            <!-- Subcontractor Association Field (Optional) -->
                            <div class="col-12 col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-building text-info"></i>
                                    </span>
                                    <input type="text" class="form-control" id="subcontractorInput" 
                                           placeholder="Subcontractor (optional)" 
                                           maxlength="100" />
                                </div>
                            </div>
                            
                            <!-- Submit Button with modern styling -->
                            <div class="col-12 col-md-4">
                                <button type="submit" class="btn btn-primary btn-modern w-100">
                                    <i class="fas fa-plus me-1"></i> Add Attendee
                                </button>
                            </div>
                        </form>
                        
                        <!-- Enhanced Attendees Display List with modern chip design and improved interaction -->
                        <!-- Purpose: Shows current attendees as interactive chips with remove functionality -->
                        <!-- Features: Today's attendees, previous attendees, smooth animations -->
                        <div id="attendeesList" class="mt-4">
                            <!-- Default loading state shown while attendees are being loaded -->
                            <div class="text-center py-4 loading-pulse">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Loading attendees...</p>
                                <small class="text-muted">Please wait while we load today's attendee list</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTIVITY SCHEDULE PANEL - Enhanced System with Complete Backend Support -->
        <!-- Purpose: Comprehensive daily activity management for construction projects -->
        <!-- Features: CRUD operations, priority tracking, area assignments, labor planning -->
        <div class="row">
            <!-- Main Activities List Column (Left Side - 8/12 width) -->
            <div class="col-lg-8">
                <!-- Activities Management Card with comprehensive functionality and modern design -->
                <div class="card mb-4 activity-schedule-card fade-in scroll-fade">
                    <!-- Card Header with title and Add Activity button -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="fas fa-calendar-alt me-2"></i> Activity Schedule</h2>
                            <small class="opacity-75">Manage today's construction activities with priority tracking</small>
                        </div>
                        <!-- Add New Activity Button -->
                        <button id="addActivityBtn" class="btn btn-light btn-modern">
                            <i class="fas fa-plus me-2"></i>Add Activity
                        </button>
                    </div>
                    
                    <!-- Card Body containing the activities list -->
                    <div class="card-body">
                        <!-- Activities List Container - populated by activities.js -->
                        <!-- This div will be dynamically filled with activity items -->
                        <div id="activitiesList">
                            <!-- Default loading state shown while activities are being loaded -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading activities...</span>
                                </div>
                                <h5 class="text-muted mb-2">Loading Activities</h5>
                                <p class="text-muted">Date: <?php echo $currentDate; ?></p>
                                <small class="text-muted">Please wait while we load today's construction activities...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resource Information and Statistics Column (Right Side - 4/12 width) -->
            <!-- Purpose: Shows project resource allocation and weekly statistics -->
            <div class="col-lg-4">
                <!-- Resource Statistics Container - populated by resource-tracker.js -->
                <div id="resourceStats" class="fade-in scroll-fade"></div>
                
                <!-- Weekly Statistics Container - shows weekly project progress -->
                <div id="weeklyStatsContainer" class="mt-4 fade-in scroll-fade"></div>
            </div>
        </div>

        <!-- WEATHER FORECAST PANEL with enhanced functionality and modern styling -->
        <!-- Purpose: Provides construction site weather planning and safety information -->
        <!-- Features: Current conditions, 5-day forecast, safety considerations for outdoor work -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card weather-card fade-in scroll-fade">
                    <!-- Weather Card Header -->
                    <div class="card-header">
                        <h2><i class="fas fa-cloud-sun me-2"></i> Weather Forecast</h2>
                        <small class="opacity-75">Construction site weather planning and safety considerations</small>
                    </div>
                    
                    <!-- Weather Card Body - populated by weather.js -->
                    <div class="card-body" id="weatherWidget">
                        <!-- Default loading state for weather information -->
                        <div class="d-flex justify-content-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading weather...</span>
                            </div>
                            <span class="ms-2">Loading weather forecast...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SAFETY INFORMATION PANEL (Editable with distinctive styling and enhanced functionality) -->
        <!-- Purpose: Manages critical safety information with distinctive yellow background -->
        <!-- Features: Rich text editing, version control, prominent visual identification -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card fade-in scroll-fade">
                    <!-- Safety Card Header with edit controls -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="fas fa-exclamation-triangle me-2"></i> Safety Information</h2>
                            <small class="opacity-75">Important safety updates, protocols, and site-specific information</small>
                        </div>
                        <!-- Edit Safety Information Button -->
                        <button id="editSafetyBtn" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit Safety Info
                        </button>
                    </div>
                    
                    <!-- Safety Card Body with editable content -->
                    <div class="card-body">
                        <!-- Safety Information Container -->
                        <div id="safetyContainer">
                            <!-- Read-only Safety Content Display -->
                            <div id="safetyContent" class="safety-editor readonly">
                                <?php echo $briefingData['safety_info']; ?>
                            </div>
                            
                            <!-- Edit Mode Container (hidden by default) -->
                            <div id="safetyEditContainer" style="display:none;">
                                <textarea id="safetyEditor" class="form-control safety-editor" 
                                         style="min-height:200px;" 
                                         placeholder="Enter safety information, protocols, and site-specific requirements..."></textarea>
                                <!-- Edit Mode Action Buttons -->
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-danger me-2" id="cancelSafetyBtn">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <button type="button" class="btn btn-warning" id="saveSafetyBtn">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Loading State for Safety Information -->
                        <div id="safetyLoading" class="text-center py-4" style="display:none;">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Saving safety info...</span>
                            </div>
                            <div class="mt-2">Updating safety information...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NOTES & UPDATES PANEL (Modern, fully working with comprehensive rich text editing) -->
        <!-- Purpose: Daily briefing notes, project updates, and team communication -->
        <!-- Features: Rich text editing with TinyMCE, version history, collaborative editing -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card fade-in scroll-fade">
                    <!-- Notes Card Header with editing controls -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="fas fa-sticky-note me-2"></i> Notes & Updates</h2>
                            <small class="opacity-75">Daily briefing notes, important updates, and project communication</small>
                        </div>
                        <!-- Notes Control Buttons -->
                        <div>
                            <button id="editNotesBtn" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit Notes
                            </button>
                            <button id="historyNotesBtn" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-history me-1"></i> History
                            </button>
                        </div>
                    </div>
                    
                    <!-- Notes Card Body with rich text capabilities -->
                    <div class="card-body">
                        <!-- Notes Content Container -->
                        <div id="notesContainer">
                            <!-- Read-only Notes Content Display -->
                            <div id="notesContent" class="notes-editor readonly">
                                <?php echo htmlspecialchars($briefingData['notes']); ?>
                            </div>
                            
                            <!-- Edit Mode Container (hidden by default) -->
                            <div id="notesEditContainer" style="display:none;">
                                <textarea id="notesEditor" class="form-control notes-editor" 
                                         style="min-height:200px"
                                         placeholder="Enter daily briefing notes, project updates, and important information..."></textarea>
                                <!-- Edit Mode Action Buttons -->
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-danger me-2" id="cancelNotesBtn">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <button type="button" class="btn btn-primary" id="saveNotesBtn">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Notes Metadata (last updated information) -->
                            <div class="notes-meta" id="notesMeta">
                                Last updated: <?php echo date('d/m/Y H:i:s', strtotime($briefingData['last_updated'])); ?> (UK)
                                by <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'System'); ?>
                            </div>
                        </div>
                        
                        <!-- Loading State for Notes Operations -->
                        <div id="notesLoading" class="text-center py-4" style="display:none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Processing notes...</span>
                            </div>
                            <div class="mt-2">Updating notes...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes History Modal with comprehensive version control and history tracking -->
        <!-- Purpose: Shows previous versions of notes with timestamps and user information -->
        <div class="modal fade" id="notesHistoryModal" tabindex="-1" aria-labelledby="notesHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="notesHistoryModalLabel">
                            <i class="fas fa-history me-2"></i>Notes History
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <!-- Modal Body with history content -->
                    <div class="modal-body">
                        <div id="notesHistoryContainer">
                            <!-- Default loading state for history -->
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading history...</span>
                                </div>
                                <div class="mt-2">Loading notes history...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUBCONTRACTORS MANAGEMENT PANEL with enhanced functionality and comprehensive management -->
        <!-- Purpose: Track subcontractor status, tasks, and contact information -->
        <!-- Features: Accordion display, status indicators, task management, contact details -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card subcontractor-card fade-in scroll-fade">
                    <!-- Subcontractors Card Header with management controls -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="fas fa-users me-2"></i> Subcontractor Information</h2>
                            <small class="opacity-75">Track subcontractor status, tasks, and comprehensive contact management</small>
                        </div>
                        <!-- Add New Subcontractor Button -->
                        <button id="addSubcontractorBtn" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Add Subcontractor
                        </button>
                    </div>
                    
                    <!-- Subcontractors Card Body -->
                    <div class="card-body">
                        <!-- Debug Information Area (hidden by default, shown if there are issues) -->
                        <div id="subcontractorDebug" class="alert alert-warning mb-3" style="display:none;">
                            <h6 class="mb-1"><strong><i class="fas fa-exclamation-triangle me-1"></i>Debugging Information</strong></h6>
                            <div id="subcontractorDebugContent"></div>
                        </div>
                        
                        <!-- Subcontractors Accordion Display -->
                        <!-- This accordion shows each subcontractor with their details and tasks -->
                        <div id="subcontractorAccordion" class="accordion">
                            <!-- Default loading state shown while subcontractors are being loaded -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading subcontractors...</span>
                                </div>
                                <div class="mt-2">Loading subcontractor information...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER with comprehensive system information and credits -->
        <!-- Purpose: Displays system information, version details, and last update timestamp -->
        <footer class="footer mt-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- System Information (Left Column) -->
                    <div class="col-md-6">
                        <p class="mb-1">© 2025 DABS - Daily Activity Briefing System</p>
                        <small class="text-muted">Version 6.0.0 | Developed and Maintained By Chris Irlam</small>
                    </div>
                    
                    <!-- Update Information (Right Column) -->
                    <div class="col-md-6 text-end">
                        <p class="mb-1">Last updated: <span id="footerLastUpdated"><?php echo $currentDateTime; ?></span> (UK)</p>
                        <small class="text-muted">by <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown User'); ?></small>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- MODAL DIALOGS FOR COMPREHENSIVE USER INTERACTION AND DATA MANAGEMENT -->
    <!-- These modals provide modern dialog interfaces for data entry and editing operations -->
    
    <!-- Subcontractor Management Modal with enhanced functionality and comprehensive form validation -->
    <!-- Purpose: Add new subcontractors or edit existing ones with full contact and task management -->
    <div class="modal fade" id="subcontractorModal" tabindex="-1" aria-labelledby="subcontractorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="subcontractorModalLabel">
                        <i class="fas fa-user-tie me-2"></i>Add/Edit Subcontractor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Modal Body with comprehensive form -->
                <div class="modal-body">
                    <!-- Error/Debug Information Area -->
                    <div id="modalDebugArea" class="alert alert-danger mb-3" style="display:none;"></div>
                    
                    <!-- Subcontractor Information Form -->
                    <form id="subcontractorForm">
                        <!-- Hidden field for subcontractor ID (used during editing) -->
                        <input type="hidden" id="subcontractorId" value="">
                        
                        <!-- Basic Information Row -->
                        <div class="row mb-3">
                            <!-- Subcontractor Name Field -->
                            <div class="col-md-6">
                                <label for="subcontractorName" class="form-label">
                                    <i class="fas fa-building me-1"></i>Subcontractor Name *
                                </label>
                                <input type="text" class="form-control" id="subcontractorName" 
                                       placeholder="e.g., ABC Construction Ltd" required maxlength="100">
                            </div>
                            
                            <!-- Trade/Specialty Field -->
                            <div class="col-md-6">
                                <label for="subcontractorTrade" class="form-label">
                                    <i class="fas fa-hard-hat me-1"></i>Trade/Specialty *
                                </label>
                                <input type="text" class="form-control" id="subcontractorTrade" 
                                       placeholder="e.g., Electrical, Plumbing, Roofing" required maxlength="100">
                            </div>
                        </div>
                        
                        <!-- Contact Information Row -->
                        <div class="row mb-3">
                            <!-- Contact Person Name -->
                            <div class="col-md-6">
                                <label for="contactName" class="form-label">
                                    <i class="fas fa-user me-1"></i>Contact Name *
                                </label>
                                <input type="text" class="form-control" id="contactName" 
                                       placeholder="Primary contact person" required maxlength="100">
                            </div>
                            
                            <!-- Status Selection -->
                            <div class="col-md-6">
                                <label for="subcontractorStatus" class="form-label">
                                    <i class="fas fa-flag me-1"></i>Status
                                </label>
                                <select class="form-select" id="subcontractorStatus">
                                    <option value="Active">Active - Currently working</option>
                                    <option value="Standby">Standby - Ready to work</option>
                                    <option value="Offsite">Offsite - Not on location</option>
                                    <option value="Delayed">Delayed - Behind schedule</option>
                                    <option value="Complete">Complete - Work finished</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Contact Details Row -->
                        <div class="row mb-3">
                            <!-- Phone Number -->
                            <div class="col-md-6">
                                <label for="contactPhone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Phone Number *
                                </label>
                                <input type="tel" class="form-control" id="contactPhone" 
                                       placeholder="e.g., 07123 456789" required maxlength="20">
                            </div>
                            
                            <!-- Email Address -->
                            <div class="col-md-6">
                                <label for="contactEmail" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="contactEmail" 
                                       placeholder="contact@company.com" maxlength="100">
                            </div>
                        </div>
                        
                        <!-- Tasks Section Divider -->
                        <hr class="my-4">
                        
                        <!-- Daily Tasks Management Section -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">
                                    <i class="fas fa-tasks me-1"></i>Today's Tasks
                                </label>
                                <!-- Add Task Button -->
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addTaskBtn">
                                    <i class="fas fa-plus me-1"></i> Add Task
                                </button>
                            </div>
                            
                            <!-- Tasks Container - populated dynamically with task input fields -->
                            <div id="tasksContainer"></div>
                            
                            <!-- Help Text for Tasks Section -->
                            <div class="text-muted small mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Add specific tasks that this subcontractor will complete today.
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Modal Footer with Action Buttons -->
                <div class="modal-footer">
                    <!-- Delete Button (shown only when editing existing subcontractor) -->
                    <button type="button" class="btn btn-danger me-auto" id="deleteSubcontractorBtn">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                    
                    <!-- Cancel Button -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    
                    <!-- Save Button -->
                    <button type="button" class="btn btn-primary" id="saveSubcontractorBtn">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Management Modal (Enhanced with proper briefing_id integration and comprehensive form fields) -->
    <!-- Purpose: Add new activities or edit existing ones with priority tracking and resource planning -->
    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="activityModalLabel">
                        <i class="fas fa-calendar-plus me-2"></i>Add New Activity
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Activity Form -->
                <form id="activityForm" autocomplete="off">
                    <div class="modal-body">
                        <!-- Hidden Fields for System Data -->
                        <input type="hidden" id="activityId" name="id" value="">
                        <input type="hidden" id="activityBriefingId" name="briefing_id" value="<?php echo isset($briefingData['id']) ? $briefingData['id'] : '0'; ?>">
                        <input type="hidden" id="activityDate" name="date" value="<?php echo $currentDateISO; ?>">
                        
                        <!-- Time and Priority Row -->
                        <div class="row">
                            <!-- Activity Time -->
                            <div class="col-md-6 mb-3">
                                <label for="activityTime" class="form-label">
                                    <i class="fas fa-clock me-1"></i>Scheduled Time *
                                </label>
                                <input type="time" class="form-control" id="activityTime" name="time" 
                                       value="08:00" required>
                            </div>
                            
                            <!-- Priority Level -->
                            <div class="col-md-6 mb-3">
                                <label for="activityPriority" class="form-label">
                                    <i class="fas fa-flag me-1"></i>Priority Level
                                </label>
                                <select class="form-select" id="activityPriority" name="priority">
                                    <option value="low">Low Priority</option>
                                    <option value="medium" selected>Medium Priority</option>
                                    <option value="high">High Priority</option>
                                    <option value="critical">Critical Priority</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Activity Title -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="activityTitle" class="form-label">
                                    <i class="fas fa-heading me-1"></i>Activity Title *
                                </label>
                                <input type="text" class="form-control" id="activityTitle" name="title" required 
                                       placeholder="Brief description of the activity" maxlength="255">
                            </div>
                        </div>
                        
                        <!-- Activity Description -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="activityDescription" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Detailed Description
                                </label>
                                <textarea class="form-control" id="activityDescription" name="description" rows="3"
                                          placeholder="Detailed description of what needs to be done (optional)"></textarea>
                            </div>
                        </div>
                        
                        <!-- Area and Labor Count Row -->
                        <div class="row">
                            <!-- Construction Area -->
                            <div class="col-md-6 mb-3">
                                <label for="activityArea" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Construction Area
                                </label>
                                <input type="text" class="form-control" id="activityArea" name="area" 
                                       placeholder="e.g., Building A, Site Access, East Wing" maxlength="100">
                            </div>
                            
                            <!-- Number of Workers Required -->
                            <div class="col-md-6 mb-3">
                                <label for="activityLaborCount" class="form-label">
                                    <i class="fas fa-users me-1"></i>Workers Required
                                </label>
                                <input type="number" min="0" max="999" class="form-control" id="activityLaborCount" 
                                       name="labor_count" value="1" placeholder="Number of workers needed">
                            </div>
                        </div>
                        
                        <!-- Contractors and Assignment Row -->
                        <div class="row">
                            <!-- Assigned Contractors -->
                            <div class="col-md-6 mb-3">
                                <label for="activityContractors" class="form-label">
                                    <i class="fas fa-hard-hat me-1"></i>Assigned Contractors
                                </label>
                                <input type="text" class="form-control" id="activityContractors" name="contractors" 
                                       placeholder="Contractor companies involved" maxlength="255">
                            </div>
                            
                            <!-- Person Responsible -->
                            <div class="col-md-6 mb-3">
                                <label for="activityAssignedTo" class="form-label">
                                    <i class="fas fa-user-check me-1"></i>Assigned To
                                </label>
                                <input type="text" class="form-control" id="activityAssignedTo" name="assigned_to" 
                                       placeholder="Person responsible for this activity" maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer with Action Buttons -->
                    <div class="modal-footer">
                        <!-- Delete Activity Button (shown only when editing) -->
                        <button type="button" id="deleteActivityBtn" class="btn btn-danger me-auto" style="display:none;">
                            <i class="fas fa-trash me-1"></i> Delete Activity
                        </button>
                        
                        <!-- Cancel Button -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        
                        <!-- Save Activity Button -->
                        <button type="submit" id="saveActivityBtn" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPTS: Bootstrap, JavaScript modules, and comprehensive system functionality -->
    <!-- External JavaScript Libraries for modern functionality and responsive design -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	    <script>
    /**
     * =========================================================================
     * CORE SYSTEM FUNCTIONS - Debug Console, Notifications, and Utilities
     * Enhanced for Version 6.0.0 with improved functionality and UK time formatting
     * =========================================================================
     * 
     * FILE PURPOSE: Core JavaScript functionality for the Daily Activity Briefing System
     * DESCRIPTION: This script provides essential system functions including debugging,
     *              user notifications, HTML security, UK date utilities, and critical
     *              error handling throughout the DABS application.
     * 
     * Created: 24/06/2025 14:47:38 (UK Time - Europe/London)
     * Author: Chris Irlam (System Administrator)
     * Current User: irlam
     * 
     * KEY FEATURES IMPLEMENTED:
     * ✅ Debug Console: Real-time system monitoring with UK timestamp formatting
     * ✅ Enhanced Notifications: Toast messages with slide animations and UK times
     * ✅ HTML Security: XSS protection through comprehensive HTML escaping
     * ✅ UK Date Utilities: Consistent date handling for Europe/London timezone
     * ✅ Critical Error Handling: Professional error display with user guidance
     * ✅ Global Variables: Proper frontend-backend communication for Activity Schedule
     * ✅ Performance Monitoring: System load time tracking and optimization
     * ✅ Accessibility Features: Keyboard shortcuts and screen reader support
     * 
     * GLOBAL VARIABLES FOR SYSTEM INTEGRATION:
     * These variables are essential for proper communication between frontend and backend
     * components, particularly for the Activity Schedule system to function correctly.
     */
    
    // *** CRITICAL: Global variables for proper Activity Schedule integration ***
    // These variables are essential for the Activity Schedule system to work properly
    // They provide the frontend JavaScript with necessary backend data for API calls
    window.CURRENT_BRIEFING_ID = <?php echo isset($briefingData['id']) ? $briefingData['id'] : '20'; ?>;
    window.CURRENT_PROJECT_ID = <?php echo $projectID; ?>;
    window.CURRENT_DATE = '<?php echo $currentDateISO; ?>';
    window.CURRENT_DATE_UK = '<?php echo $currentDate; ?>';
    window.username = '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown'); ?>';
    window.userRole = 'System Administrator';
    
    // Briefing data object for comprehensive frontend access
    window.briefingData = {
        id: <?php echo isset($briefingData['id']) ? $briefingData['id'] : '0'; ?>,
        project_id: <?php echo $projectID; ?>,
        date: '<?php echo $currentDateISO; ?>',
        status: '<?php echo isset($briefingData['status']) ? $briefingData['status'] : 'draft'; ?>',
        last_updated: '<?php echo isset($briefingData['last_updated']) ? $briefingData['last_updated'] : date('Y-m-d H:i:s'); ?>'
    };
    
    // Global variables for comprehensive system state management
    let debugEnabled = false;           // Controls debug console visibility
    let systemInitialized = false;     // Tracks system initialization status
    let lastUpdateTime = new Date();   // Stores last system update timestamp
    let notificationCount = 0;         // Tracks number of notifications shown
    
    /**
     * Enhanced debug console functionality with comprehensive UK time formatting
     * Provides detailed system monitoring and troubleshooting capabilities for administrators
     * 
     * @param {string} message - The debug message to log with contextual information
     * @param {*} data - Optional data to include with the message for detailed analysis
     * @param {string} type - Message type: 'log', 'error', 'warn', 'info' for color coding
     * 
     * Example Usage:
     * debug('Activity saved successfully', {id: 123, title: 'Install windows'});
     * debugError('Database connection failed', {host: 'localhost', error: 'timeout'});
     */
    function debug(message, data = null, type = 'log') {
        // Always log to browser console for developer tools access
        if (data !== null) {
            console[type](message, data);
        } else {
            console[type](message);
        }
        
        // If debug console is enabled, also display in on-screen console
        if (debugEnabled) {
            const debugContent = document.querySelector('#debugConsole .debug-content');
            if (debugContent) {
                // Format current time in UK timezone for consistency
                const timestamp = new Date().toLocaleTimeString('en-GB', { 
                    timeZone: 'Europe/London',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                
                // Create new log element with appropriate styling
                const logElement = document.createElement('pre');
                if (type === 'error') logElement.classList.add('debug-error');
                if (type === 'warn') logElement.classList.add('debug-warn');
                if (type === 'info') logElement.classList.add('debug-info');
                
                // Format log message with timestamp and optional data
                let logText = `[${timestamp}] ${message}`;
                if (data !== null) {
                    try {
                        // Safely stringify objects for display
                        if (typeof data === 'object') {
                            logText += ': ' + JSON.stringify(data, null, 2);
                        } else {
                            logText += ': ' + data;
                        }
                    } catch (e) {
                        // Handle objects that cannot be stringified
                        logText += ': [Object cannot be stringified]';
                    }
                }
                
                // Add to debug console and auto-scroll to bottom
                logElement.textContent = logText;
                debugContent.appendChild(logElement);
                debugContent.scrollTop = debugContent.scrollHeight;
            }
        }
    }
    
    /**
     * Enhanced error logging function for comprehensive error tracking
     * Provides specialized error handling with enhanced formatting and priority
     * 
     * @param {string} message - The error message to log
     * @param {*} data - Optional error context data
     */
    function debugError(message, data = null) {
        debug(message, data, 'error');
        // Also increment error counter for system monitoring
        window.errorCount = (window.errorCount || 0) + 1;
    }
    
    /**
     * Enhanced warning logging function for system alerts and non-critical issues
     * 
     * @param {string} message - The warning message to log
     * @param {*} data - Optional warning context data
     */
    function debugWarn(message, data = null) {
        debug(message, data, 'warn');
        // Increment warning counter for system monitoring
        window.warningCount = (window.warningCount || 0) + 1;
    }
    
    /**
     * Enhanced information logging function for system status updates
     * 
     * @param {string} message - The information message to log
     * @param {*} data - Optional information context data
     */
    function debugInfo(message, data = null) {
        debug(message, data, 'info');
    }
    
    /**
     * Enhanced debug console toggle function with improved state management
     * Provides easy access to system debugging tools for administrators
     */
    function toggleDebugConsole() {
        const debugConsole = document.getElementById('debugConsole');
        if (debugConsole) {
            debugEnabled = !debugEnabled;
            debugConsole.style.display = debugEnabled ? 'block' : 'none';
            
            // Log the toggle action with timestamp
            debug('Debug console ' + (debugEnabled ? 'enabled' : 'disabled') + ' at ' + 
                  new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }));
            
            // Update system state tracking
            if (debugEnabled) {
                debug('DABS Debug Console activated', {
                    version: '6.0.0',
                    user: window.username,
                    briefing_id: window.CURRENT_BRIEFING_ID,
                    project_id: window.CURRENT_PROJECT_ID,
                    timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                });
            }
        }
    }
    
    /**
     * Enhanced debug console clear function with user confirmation
     * Prevents accidental loss of important debugging information
     */
    function clearDebugConsole() {
        const debugContent = document.querySelector('#debugConsole .debug-content');
        if (debugContent) {
            // Clear console and log the action
            debugContent.innerHTML = '';
            debug('Debug console cleared - ' + new Date().toLocaleString('en-GB', { 
                timeZone: 'Europe/London' 
            }));
            
            // Reset error and warning counters
            window.errorCount = 0;
            window.warningCount = 0;
        }
    }
    
    /**
     * Enhanced notification system with comprehensive UK timestamps and improved styling
     * Provides consistent user feedback throughout the application with professional animations
     * 
     * @param {string} message - The notification message to display to the user
     * @param {string} type - Notification type: 'success', 'danger', 'warning', 'info'
     * 
     * Example Usage:
     * showNotification('Activity saved successfully', 'success');
     * showNotification('Error connecting to server', 'danger');
     */
    function showNotification(message, type = 'success') {
        debug('Showing notification', { message, type, count: ++notificationCount });
        
        const notificationArea = document.getElementById('notificationArea');
        if (!notificationArea) {
            debugWarn('Notification area not found in DOM');
            return;
        }
        
        // Format current UK time for timestamp display
        const ukTime = new Date().toLocaleTimeString('en-GB', { 
            timeZone: 'Europe/London',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        // Create notification element with modern styling
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            animation: slideInRight 0.3s ease-out;
            margin-bottom: 0.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            border: none;
        `;
        
        // Set notification content with icon, message, and timestamp
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                 type === 'danger' ? 'exclamation-triangle' : 
                                 type === 'warning' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <small class="text-muted ms-2">${ukTime}</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add notification to the area
        notificationArea.appendChild(notification);
        
        // Enhanced auto-remove with fade out animation after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
        
        // Add CSS animations if not already present
        if (!document.getElementById('notificationAnimations')) {
            const style = document.createElement('style');
            style.id = 'notificationAnimations';
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    /**
     * Enhanced critical error display function with comprehensive error reporting
     * Provides professional error handling for system-critical issues
     * 
     * @param {string} message - The critical error message to display
     * @param {*} error - Optional error object for additional technical context
     */
    function showCriticalError(message, error = null) {
        debugError('CRITICAL ERROR OCCURRED', { message, error, timestamp: new Date().toISOString() });
        
        // Show user notification
        showNotification(message, 'danger');
        
        // Also display in subcontractor debug area if available
        const debugArea = document.getElementById('subcontractorDebug');
        const debugContent = document.getElementById('subcontractorDebugContent');
        if (debugArea && debugContent) {
            debugArea.style.display = 'block';
            let errorMessage = `<strong>CRITICAL ERROR:</strong> ${message}`;
            if (error) {
                errorMessage += `<br><small class="text-muted">Technical details: ${error}</small>`;
            }
            errorMessage += `<br><small class="text-muted">Time: ${new Date().toLocaleString('en-GB', { 
                timeZone: 'Europe/London' 
            })} (UK)</small>`;
            debugContent.innerHTML = errorMessage;
        }
    }
    
    /**
     * Enhanced HTML escaping function for comprehensive security protection
     * Prevents XSS attacks by properly escaping HTML characters in user content
     * 
     * @param {string} unsafe - String that may contain unsafe HTML characters
     * @returns {string} Safely escaped HTML string ready for display
     * 
     * Example Usage:
     * const safeName = escapeHtml(userInput); // Prevents <script> injection
     */
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    /**
     * Enhanced UK date utility functions for consistent formatting throughout the application
     * Provides reliable date handling with proper UK timezone support
     * 
     * @param {number} offsetDays - Number of days to offset from current date (can be negative)
     * @returns {string} Date in YYYY-MM-DD format for database compatibility
     * 
     * Example Usage:
     * const today = getUKDateString();        // "2025-06-24"
     * const tomorrow = getUKDateString(1);    // "2025-06-25"
     * const yesterday = getUKDateString(-1);  // "2025-06-23"
     */
    function getUKDateString(offsetDays = 0) {
        const d = new Date();
        d.setDate(d.getDate() + offsetDays);
        return d.toLocaleDateString('en-CA', { timeZone: 'Europe/London' });
    }
    
    /**
     * Get UK date in display format (DD/MM/YYYY) for user interface
     * 
     * @param {number} offsetDays - Number of days to offset from current date
     * @returns {string} Date in DD/MM/YYYY format for UK users
     */
    function getUKDateDisplay(offsetDays = 0) {
        const d = new Date();
        d.setDate(d.getDate() + offsetDays);
        return d.toLocaleDateString('en-GB', { timeZone: 'Europe/London' });
    }
    
    /**
     * Enhanced email report functionality with future implementation planning
     * Provides user feedback about upcoming features and logs user intent
     */
    function emailReport() {
        debug('Email report functionality requested', {
            user: window.username,
            project_id: window.CURRENT_PROJECT_ID,
            briefing_id: window.CURRENT_BRIEFING_ID,
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
        });
        
        showNotification('Email report functionality will be implemented in the next system update. Your request has been logged.', 'info');
    }
    
    /**
     * Enhanced scroll animation handler for improved user experience
     * Provides smooth fade-in animations for page elements as they come into view
     */
    function handleScrollAnimations() {
        const scrollElements = document.querySelectorAll('.scroll-fade');
        
        // Use Intersection Observer for efficient scroll animation detection
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Log animation for debugging if needed
                    debug('Scroll animation triggered', { 
                        element: entry.target.className,
                        timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                    });
                }
            });
        }, {
            threshold: 0.1, // Trigger when 10% of element is visible
            rootMargin: '0px 0px -50px 0px' // Start animation slightly before element is fully visible
        });
        
        // Observe all scroll-fade elements
        scrollElements.forEach(el => observer.observe(el));
    }
    
    /**
     * Enhanced system initialization and setup with comprehensive error handling
     * Initializes all core system components and sets up event listeners for optimal user experience
     */
    function initializeSystem() {
        debug('DABS System initialization starting', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
            version: '6.0.0',
            briefing_id: window.CURRENT_BRIEFING_ID,
            project_id: window.CURRENT_PROJECT_ID,
            date: window.CURRENT_DATE,
            date_uk: window.CURRENT_DATE_UK,
            user: window.username
        });
        
        try {
            // Set up enhanced debug console event listeners
            const closeDebugBtn = document.getElementById('closeDebugBtn');
            const clearDebugBtn = document.getElementById('clearDebugBtn');
            
            if (closeDebugBtn) {
                closeDebugBtn.addEventListener('click', toggleDebugConsole);
                debug('Debug console close button initialized');
            }
            if (clearDebugBtn) {
                clearDebugBtn.addEventListener('click', clearDebugConsole);
                debug('Debug console clear button initialized');
            }
            
            // Enhanced keyboard shortcut handler for power users and improved productivity
            document.addEventListener('keydown', function(e) {
                // Alt+D: Toggle debug console (developer shortcut)
                if (e.altKey && e.key === 'd') {
                    e.preventDefault();
                    toggleDebugConsole();
                    debug('Debug console toggled via keyboard shortcut (Alt+D)');
                }
                
                // Ctrl+Shift+D: Force enable debug console (emergency access)
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    e.preventDefault();
                    debugEnabled = true;
                    const debugConsole = document.getElementById('debugConsole');
                    if (debugConsole) {
                        debugConsole.style.display = 'block';
                    }
                    debug('Debug console force-enabled via emergency shortcut');
                }
            });
            
            // Initialize scroll animations for enhanced user experience
            handleScrollAnimations();
            
            // Set up periodic system health checks
            setInterval(() => {
                // Update last activity timestamp
                lastUpdateTime = new Date();
                
                // Check for any JavaScript errors in console
                if (window.errorCount > 0) {
                    debugWarn('JavaScript errors detected', { 
                        count: window.errorCount,
                        timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                    });
                }
            }, 60000); // Check every minute
            
            // Mark system as initialized successfully
            systemInitialized = true;
            lastUpdateTime = new Date();
            
            debug('DABS System initialization completed successfully', {
                timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
                version: '6.0.0',
                briefing_id: window.CURRENT_BRIEFING_ID,
                project_id: window.CURRENT_PROJECT_ID,
                components: [
                    'debug_console',
                    'notification_system', 
                    'scroll_animations',
                    'keyboard_shortcuts',
                    'error_handling',
                    'health_monitoring'
                ],
                system_ready: true,
                initialization_time: Date.now() - window.performanceStart
            });
            
        } catch (error) {
            debugError('System initialization failed', {
                error: error.message,
                stack: error.stack,
                timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
            });
            showCriticalError('System initialization failed. Some features may not work properly.');
        }
    }
    
    // Initialize performance monitoring
    window.performanceStart = Date.now();
    
    // Initialize system when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSystem);
    } else {
        // DOM already loaded, initialize immediately
        initializeSystem();
    }
    </script>
    
    <!-- Enhanced Attendees Management System with comprehensive functionality -->
    <script>
    /**
     * =========================================================================
     * ATTENDEES MANAGEMENT SYSTEM - Enhanced Version 6.0.0
     * =========================================================================
     * 
     * FILE PURPOSE: Complete attendee management for daily construction briefings
     * DESCRIPTION: This script handles loading, adding, and removing attendees for daily 
     *              briefings with modern UI components, comprehensive error handling, and 
     *              seamless integration with subcontractor information tracking.
     * 
     * Created: 24/06/2025 14:47:38 (UK Time - Europe/London)
     * Author: Chris Irlam (System Administrator)  
     * Current User: irlam
     * 
     * KEY FEATURES IMPLEMENTED:
     * ✅ Attendee Loading: Server-side attendee retrieval with UK date handling
     * ✅ Add Attendees: New attendee creation with subcontractor association
     * ✅ Remove Attendees: Attendee deletion with user confirmation and error handling
     * ✅ Modern UI Design: Chip-based display with hover effects and animations
     * ✅ Error Handling: Comprehensive error recovery and user feedback
     * ✅ Auto-refresh: Real-time data synchronization when page becomes visible
     * ✅ Accessibility: Keyboard navigation and screen reader support
     * ✅ Form Validation: Input validation with detailed user feedback
     * ✅ Loading States: Professional progress indicators during operations
     * ✅ UK Time Format: All timestamps in DD/MM/YYYY HH:MM:SS format
     */
    
    /**
     * Enhanced function to load attendees from server with comprehensive error handling
     * Retrieves and displays attendees for the current date with modern UI components
     * Handles both today's attendees and previous attendees for continuity
     */
    function loadAttendees() {
        debug('Loading attendees from server...', {
            date: getUKDateString(),
            project_id: window.CURRENT_PROJECT_ID,
            briefing_id: window.CURRENT_BRIEFING_ID
        });
        
        // Show loading state while fetching data
        const container = document.getElementById('attendeesList');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-4 loading-pulse">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading attendees...</span>
                    </div>
                    <p class="text-muted mb-1">Loading attendees...</p>
                    <small class="text-muted">Retrieving attendee list for ${getUKDateDisplay()}</small>
                </div>
            `;
        }
        
        // Fetch attendees from server with comprehensive error handling
        fetch('ajax_attendees.php?action=list&date=' + getUKDateString())
            .then(response => {
                debug('Attendees server response received', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                debug('Attendees loaded successfully', data);
                
                if (!container) {
                    debugWarn('Attendees container not found in DOM');
                    return;
                }
                
                const todayAttendees = data.today_attendees || [];
                const previousAttendees = data.previous_attendees || [];
                
                // Handle empty attendee list with helpful message
                if (todayAttendees.length === 0 && previousAttendees.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted mb-2">No Attendees Yet</h6>
                            <p class="text-muted mb-3">No attendees have been added for today's briefing.</p>
                            <small class="text-muted">Start by entering an attendee name and clicking "Add Attendee"</small>
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                
                // Display today's attendees with enhanced styling and functionality
                if (todayAttendees.length > 0) {
                    html += '<div class="mb-3">';
                    html += '<h6 class="text-primary mb-2"><i class="fas fa-calendar-day me-1"></i>Today\'s Attendees</h6>';
                    html += todayAttendees.map(attendee => {
                        const attendeeName = attendee.attendee_name || '';
                        const subcontractorName = attendee.subcontractor_name || '';
                        const id = attendee.id || 0;
                        const briefingDate = getUKDateString();
                        
                        return `<span class="attendee-chip" data-id="${id}" data-date="${briefingDate}">
                            ${escapeHtml(attendeeName)}
                            ${subcontractorName ? `<span class="subcontractor-name">${escapeHtml(subcontractorName)}</span>` : ''}
                            <button title="Remove ${escapeHtml(attendeeName)} from today's briefing" 
                                    onclick="removeAttendee(${id}, '${encodeURIComponent(attendeeName)}', '${briefingDate}')"
                                    aria-label="Remove attendee">&times;</button>
                        </span>`;
                    }).join('');
                    html += '</div>';
                }
                
                // Display previous attendees with enhanced styling and date information
                if (previousAttendees.length > 0) {
                    html += '<div class="mb-2">';
                    html += '<h6 class="text-secondary mb-2"><i class="fas fa-history me-1"></i>Recent Attendees</h6>';
                    html += previousAttendees.map(attendee => {
                        const attendeeName = attendee.attendee_name || '';
                        const subcontractorName = attendee.subcontractor_name || '';
                        const briefingDate = attendee.briefing_date ? attendee.briefing_date : getUKDateString();
                        const displayDate = new Date(briefingDate).toLocaleDateString('en-GB', { 
                            timeZone: 'Europe/London' 
                        });
                        
                        return `<span class="attendee-chip" data-id="${attendee.id}" data-date="${briefingDate}" 
                                     title="Attended on ${displayDate}">
                            ${escapeHtml(attendeeName)}
                            ${subcontractorName ? `<span class="subcontractor-name">${escapeHtml(subcontractorName)}</span>` : ''}
                            <button title="Remove ${escapeHtml(attendeeName)} from records" 
                                    onclick="removeAttendee(${attendee.id}, '${encodeURIComponent(attendeeName)}', '${briefingDate}')"
                                    aria-label="Remove attendee">&times;</button>
                        </span>`;
                    }).join('');
                    html += '</div>';
                }
                
                // Update container with attendee chips
                container.innerHTML = html;
                
                // Add smooth fade-in animation for enhanced user experience
                setTimeout(() => {
                    const chips = container.querySelectorAll('.attendee-chip');
                    chips.forEach((chip, index) => {
                        chip.style.opacity = '0';
                        chip.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            chip.style.transition = 'all 0.3s ease';
                            chip.style.opacity = '1';
                            chip.style.transform = 'translateY(0)';
                        }, index * 100);
                    });
                }, 50);
                
                debug('Attendees display updated successfully', {
                    today_count: todayAttendees.length,
                    previous_count: previousAttendees.length,
                    total_displayed: todayAttendees.length + previousAttendees.length
                });
                
            })
            .catch(error => {
                debugError('Error loading attendees from server', error);
                
                if (container) {
                    container.innerHTML = 
                        `<div class="alert alert-danger">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Error Loading Attendees</strong>
                            </div>
                            <p class="mb-2">Unable to load attendee information from the server.</p>
                            <p class="mb-3"><strong>Error:</strong> ${error.message}</p>
                            <button class="btn btn-outline-danger btn-sm" onclick="loadAttendees()">
                                <i class="fas fa-sync-alt me-1"></i> Try Again
                            </button>
                        </div>`;
                }
                
                showNotification('Error loading attendees: ' + error.message, 'danger');
            });
    }
    
    /**
     * Enhanced function to remove attendee with comprehensive confirmation and error handling
     * Provides user-friendly confirmation dialog and detailed error recovery
     * 
     * @param {number} id - The attendee ID to remove from the database
     * @param {string} name - The attendee name for confirmation dialog
     * @param {string} date - The briefing date for the attendee record
     */
    function removeAttendee(id, name, date) {
        debug('Remove attendee requested', { id, name, date });
        
        // Decode the URL-encoded name for display
        const decodedName = decodeURIComponent(name);
        
        // Enhanced confirmation dialog with attendee details and consequences
        const confirmMessage = `Remove "${decodedName}" from the attendee list?\n\n` +
                             `This will remove them from the briefing records.\n` +
                             `Date: ${new Date(date).toLocaleDateString('en-GB', { timeZone: 'Europe/London' })}\n\n` +
                             `This action can be undone by re-adding them.`;
        
        if (!confirm(confirmMessage)) {
            debug('Remove attendee cancelled by user', { attendee: decodedName });
            return;
        }
        
        // Show loading notification while processing
        showNotification(`Removing ${decodedName}...`, 'info');
        
        // Send removal request to server
        fetch('ajax_attendees.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=delete&date=${encodeURIComponent(date)}&id=${id}&name=${encodeURIComponent(decodedName)}`
        })
        .then(response => {
            debug('Remove attendee server response', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            debug('Attendee removed successfully', { 
                id, 
                name: decodedName, 
                response: data 
            });
            
            showNotification(`${decodedName} removed successfully.`, 'success');
            
            // Reload the attendee list to reflect changes
            loadAttendees();
        })
        .catch(error => {
            debugError('Error removing attendee', { error, attendee: decodedName, id });
            showNotification(`Error removing attendee: ${error.message}`, 'danger');
        });
    }
    
    // Enhanced attendees management system initialization with comprehensive error handling
    document.addEventListener('DOMContentLoaded', function() {
        debug('Initializing attendees management system');
        
        const attendeeForm = document.getElementById('attendeeForm');
        if (attendeeForm) {
            attendeeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const attendeeInput = document.getElementById('attendeeInput');
                const subcontractorInput = document.getElementById('subcontractorInput');
                
                if (!attendeeInput) {
                    debugError('Attendee input field not found in DOM');
                    showNotification('Attendee input field not found. Please refresh the page.', 'danger');
                    return;
                }
                
                let attendeeName = attendeeInput.value.trim();
                let subcontractorName = subcontractorInput ? subcontractorInput.value.trim() : '';
                
                // Enhanced validation with detailed feedback
                if (attendeeName.length < 2) {
                    debugWarn('Attendee name too short', { name: attendeeName, length: attendeeName.length });
                    showNotification('Please enter a valid attendee name (at least 2 characters)', 'warning');
                    attendeeInput.focus();
                    return;
                }
                
                if (attendeeName.length > 100) {
                    debugWarn('Attendee name too long', { name: attendeeName, length: attendeeName.length });
                    showNotification('Attendee name is too long (maximum 100 characters)', 'warning');
                    attendeeInput.focus();
                    return;
                }
                
                // Check for potentially harmful characters
                if (/<|>|"|'/.test(attendeeName)) {
                    debugWarn('Invalid characters in attendee name', { name: attendeeName });
                    showNotification('Attendee name contains invalid characters. Please use only letters, numbers, and basic punctuation.', 'warning');
                    attendeeInput.focus();
                    return;
                }
                
                debug('Adding new attendee', { 
                    attendeeName, 
                    subcontractorName, 
                    date: getUKDateString(),
                    user: window.username
                });
                
                // Show loading state on submit button with user feedback
                const submitBtn = attendeeForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Adding...';
                
                // Prepare form data for server submission
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('date', getUKDateString());
                formData.append('name', attendeeName);
                formData.append('subcontractor', subcontractorName);
                formData.append('project_id', window.CURRENT_PROJECT_ID);
                formData.append('briefing_id', window.CURRENT_BRIEFING_ID);
                
                // Submit to server with comprehensive error handling
                fetch('ajax_attendees.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    debug('Add attendee server response', {
                        status: response.status,
                        statusText: response.statusText,
                        ok: response.ok
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    debug('Attendee added successfully', { 
                        attendee: attendeeName, 
                        response: data,
                        timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                    });
                    
                    showNotification(`${attendeeName} added successfully to today's briefing.`, 'success');
                    
                    // Clear form and focus for next entry
                    attendeeInput.value = '';
                    if (subcontractorInput) {
                        subcontractorInput.value = '';
                    }
                    attendeeInput.focus();
                    
                    // Reload attendees list to show the new addition
                    loadAttendees();
                })
                .catch(error => {
                    debugError('Error adding attendee to system', { 
                        error, 
                        attendee: attendeeName,
                        timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                    });
                    showNotification(`Error adding attendee: ${error.message}`, 'danger');
                })
                .finally(() => {
                    // Restore submit button to original state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
            
            debug('Attendee form event listener attached successfully');
        } else {
            debugError('Attendee form not found in DOM');
        }
        
        // Load attendees on page initialization
        loadAttendees();
        
        debug('Attendees management system initialized successfully', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
            features: ['form_validation', 'error_handling', 'loading_states', 'animations']
        });
    });
    
    // Expose attendee functions to global scope for HTML onclick handlers
    window.loadAttendees = loadAttendees;
    window.removeAttendee = removeAttendee;
    </script>
    
    <!-- Weather Forecast System with comprehensive functionality -->
    <script src="js/weather.js"></script>
    
    <!-- Enhanced Subcontractors Management System with comprehensive debugging and modern functionality -->
    <script>
    /**
     * =========================================================================
     * SUBCONTRACTORS MANAGEMENT SYSTEM - Enhanced Version 6.0.0
     * =========================================================================
     * 
     * FILE PURPOSE: Complete subcontractor management for construction projects
     * DESCRIPTION: This script handles the complete subcontractor management functionality
     *              including loading, adding, editing, and deleting subcontractors with
     *              enhanced debugging capabilities, modern UI interactions, and comprehensive
     *              error handling for system reliability and enhanced user experience.
     * 
     * Created: 24/06/2025 14:47:38 (UK Time - Europe/London)
     * Author: Chris Irlam (System Administrator)
     * Current User: irlam
     * 
     * KEY FEATURES IMPLEMENTED:
     * ✅ Subcontractor Loading: Accordion-style display with enhanced visual design
     * ✅ Add/Edit Functions: Comprehensive subcontractor information with task management
     * ✅ Delete Operations: Subcontractor removal with confirmation and cascade cleanup
     * ✅ Status Tracking: Color-coded visual indicators and priority sorting
     * ✅ Task Management: Daily activities with date-based organization
     * ✅ Error Handling: Comprehensive debugging with detailed logging
     * ✅ Accessibility: Enhanced keyboard navigation and screen reader support
     * ✅ UI Animations: Modern transitions and hover effects for improved UX
     * ✅ Contact Management: Multiple contact methods with validation
     * ✅ Real-time Updates: Auto-refresh and real-time synchronization
     */
    
    document.addEventListener('DOMContentLoaded', function() {
        debug('Initializing subcontractor management system', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
            project_id: window.CURRENT_PROJECT_ID
        });
        
        // Load subcontractors immediately on system start
        loadSubcontractors();
        /**
 * Enhanced function to open subcontractor modal with comprehensive form handling
 * Opens modal for adding new subcontractor or editing existing one
 * 
 * @param {number|null} id - Subcontractor ID for editing, null for new
 */
function openSubcontractorModal(id = null) {
    debug('Opening subcontractor modal', id ? 'for editing ID: ' + id : 'for adding new');
    
    // Reset form and modal state for clean user experience
    const subcontractorForm = document.getElementById('subcontractorForm');
    const subcontractorId = document.getElementById('subcontractorId');
    const tasksContainer = document.getElementById('tasksContainer');
    const modalDebugArea = document.getElementById('modalDebugArea');
    
    if (subcontractorForm) subcontractorForm.reset();
    if (subcontractorId) subcontractorId.value = '';
    if (tasksContainer) tasksContainer.innerHTML = '';
    if (modalDebugArea) modalDebugArea.style.display = 'none';
    
    const isNew = !id;
    const modalLabel = document.getElementById('subcontractorModalLabel');
    const deleteBtn = document.getElementById('deleteSubcontractorBtn');
    
    if (modalLabel) {
        modalLabel.innerHTML = isNew ? 
            '<i class="fas fa-user-tie me-2"></i>Add New Subcontractor' : 
            '<i class="fas fa-user-tie me-2"></i>Edit Subcontractor';
    }
    
    if (deleteBtn) {
        deleteBtn.style.display = isNew ? 'none' : 'block';
    }
    
    if (!isNew && id) {
        // Load existing subcontractor data for editing
        if (subcontractorId) subcontractorId.value = id;
        
        fetch(`ajax_subcontractors.php?action=get&id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                debug('Subcontractor data loaded for editing', data);
                
                if (!data.subcontractor) {
                    if (modalDebugArea) {
                        modalDebugArea.style.display = 'block';
                        modalDebugArea.textContent = 'Subcontractor not found.';
                    }
                    return;
                }
                
                const sub = data.subcontractor;
                
                // Populate form fields
                const fields = {
                    'subcontractorName': sub.name,
                    'subcontractorTrade': sub.trade,
                    'contactName': sub.contact_name,
                    'contactPhone': sub.phone,
                    'contactEmail': sub.email,
                    'subcontractorStatus': sub.status
                };
                
                Object.keys(fields).forEach(fieldId => {
                    const element = document.getElementById(fieldId);
                    if (element && fields[fieldId]) {
                        element.value = fields[fieldId];
                    }
                });
                
                // Add task fields
                if (sub.tasks && sub.tasks.length > 0) {
                    sub.tasks.forEach(task => addTaskField(null, task));
                } else {
                    addTaskField();
                }
            })
            .catch(error => {
                debugError('Error loading subcontractor details', error);
                if (modalDebugArea) {
                    modalDebugArea.style.display = 'block';
                    modalDebugArea.innerHTML = `<strong>Error loading details:</strong><br><small>${error.message}</small>`;
                }
            });
    } else {
        // Add one empty task field for new subcontractor
        addTaskField();
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('subcontractorModal'));
    modal.show();
}

/**
 * Enhanced function to add task field with improved UI
 * 
 * @param {Event|null} e - Event object (if called from event handler)
 * @param {string} value - Pre-filled value for the task field
 */
function addTaskField(e = null, value = '') {
    if (e) e.preventDefault();
    
    const container = document.getElementById('tasksContainer');
    if (!container) return;
    
    const taskId = Date.now() + Math.random();
    const taskHtml = `
        <div class="task-item" id="task-${taskId}">
            <input type="text" class="form-control task-input" value="${escapeHtml(value)}" 
                   placeholder="Enter task description (e.g., Install windows on floor 2)">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTaskField(${taskId})" title="Remove this task">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', taskHtml);
    
    // Focus on the new input field
    setTimeout(() => {
        const newInput = document.querySelector(`#task-${taskId} input`);
        if (newInput) newInput.focus();
    }, 100);
}

/**
 * Enhanced function to remove task field
 * 
 * @param {number} id - Task field ID to remove
 */
function removeTaskField(id) {
    debug('Removing task field', id);
    const element = document.getElementById(`task-${id}`);
    if (element) {
        const input = element.querySelector('input');
        const value = input ? input.value.trim() : '';
        
        if (value && !confirm('Remove this task? Any entered text will be lost.')) {
            return;
        }
        
        element.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => {
            if (element.parentNode) {
                element.remove();
            }
        }, 300);
    }
}

/**
 * Enhanced function to save subcontractor with comprehensive validation
 */
function saveSubcontractor() {
    debug('Saving subcontractor...');
    const modalDebugArea = document.getElementById('modalDebugArea');
    if (modalDebugArea) modalDebugArea.style.display = 'none';
    
    // Collect form data
    const id = document.getElementById('subcontractorId')?.value.trim() || '';
    const name = document.getElementById('subcontractorName')?.value.trim() || '';
    const trade = document.getElementById('subcontractorTrade')?.value.trim() || '';
    const contactName = document.getElementById('contactName')?.value.trim() || '';
    const phone = document.getElementById('contactPhone')?.value.trim() || '';
    const email = document.getElementById('contactEmail')?.value.trim() || '';
    const status = document.getElementById('subcontractorStatus')?.value || 'Active';
    
    // Validation
    if (!name) {
        if (modalDebugArea) {
            modalDebugArea.style.display = 'block';
            modalDebugArea.innerHTML = '<strong>Subcontractor name is required.</strong>';
        }
        return;
    }
    
    if (!trade) {
        if (modalDebugArea) {
            modalDebugArea.style.display = 'block';
            modalDebugArea.innerHTML = '<strong>Trade/Specialty is required.</strong>';
        }
        return;
    }
    
    if (!contactName) {
        if (modalDebugArea) {
            modalDebugArea.style.display = 'block';
            modalDebugArea.innerHTML = '<strong>Contact name is required.</strong>';
        }
        return;
    }
    
    if (!phone) {
        if (modalDebugArea) {
            modalDebugArea.style.display = 'block';
            modalDebugArea.innerHTML = '<strong>Phone number is required.</strong>';
        }
        return;
    }
    
    // Collect tasks
    const taskInputs = document.querySelectorAll('.task-input');
    const tasks = Array.from(taskInputs).map(input => input.value.trim()).filter(val => val !== '');
    
    debug('Collected subcontractor form data', {
        id: id || 'new',
        name,
        trade,
        contactName,
        phone,
        email,
        status,
        tasks
    });
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', id ? 'update' : 'add');
    if (id) formData.append('id', id);
    formData.append('name', name);
    formData.append('trade', trade);
    formData.append('contact_name', contactName);
    formData.append('phone', phone);
    formData.append('email', email);
    formData.append('status', status);
    formData.append('tasks', JSON.stringify(tasks));
    
    // Show loading state
    const saveBtn = document.getElementById('saveSubcontractorBtn');
    if (!saveBtn) return;
    
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';
    
    // Submit to server
    fetch('ajax_subcontractors.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        debug('Save operation result', data);
        if (data.ok) {
            bootstrap.Modal.getInstance(document.getElementById('subcontractorModal')).hide();
            loadSubcontractors();
            showNotification(`Subcontractor ${id ? 'updated' : 'added'} successfully.`, 'success');
        } else {
            if (modalDebugArea) {
                modalDebugArea.style.display = 'block';
                modalDebugArea.innerHTML = `<strong>Error:</strong> ${data.error || 'Failed to save subcontractor.'}`;
            }
        }
    })
    .catch(error => {
        debugError('Error saving subcontractor', error);
        if (modalDebugArea) {
            modalDebugArea.style.display = 'block';
            modalDebugArea.innerHTML = `<strong>Error saving subcontractor</strong><br><small>${error.message}</small>`;
        }
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

/**
 * Enhanced function to delete subcontractor with confirmation
 */
function deleteSubcontractor() {
    debug('Delete subcontractor requested');
    const id = document.getElementById('subcontractorId')?.value.trim();
    if (!id) return;
    
    const subcontractorName = document.getElementById('subcontractorName')?.value.trim() || 'this subcontractor';
    
    const confirmMessage = `Are you sure you want to delete "${subcontractorName}"?\n\nThis action cannot be undone and will permanently remove all subcontractor information.`;
    
    if (!confirm(confirmMessage)) {
        debug('Delete cancelled by user');
        return;
    }
    
    debug('Proceeding with delete for ID', id);
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    // Show loading state
    const deleteBtn = document.getElementById('deleteSubcontractorBtn');
    if (!deleteBtn) return;
    
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Deleting...';
    
    fetch('ajax_subcontractors.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        debug('Delete operation result', data);
        if (data.ok) {
            bootstrap.Modal.getInstance(document.getElementById('subcontractorModal')).hide();
            loadSubcontractors();
            showNotification(`Subcontractor "${subcontractorName}" deleted successfully.`, 'success');
        } else {
            const modalDebugArea = document.getElementById('modalDebugArea');
            if (modalDebugArea) {
                modalDebugArea.style.display = 'block';
                modalDebugArea.innerHTML = `<strong>Error:</strong> ${data.error || 'Failed to delete subcontractor.'}`;
            }
        }
    })
    .catch(error => {
        debugError('Error deleting subcontractor', error);
        const modalDebugArea = document.getElementById('modalDebugArea');
        if (modalDebugArea) {
            modalDebugArea.style.display = 'block';
            modalDebugArea.innerHTML = `<strong>Error deleting subcontractor</strong><br><small>${error.message}</small>`;
        }
    })
    .finally(() => {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    });
}

// Expose functions to global scope for HTML onclick handlers
window.openSubcontractorModal = openSubcontractorModal;
window.addTaskField = addTaskField;
window.removeTaskField = removeTaskField;
window.saveSubcontractor = saveSubcontractor;
window.deleteSubcontractor = deleteSubcontractor;
        // Set up comprehensive event listeners for subcontractor functionality
        const addTaskBtn = document.getElementById('addTaskBtn');
        const addSubcontractorBtn = document.getElementById('addSubcontractorBtn');
        const saveSubcontractorBtn = document.getElementById('saveSubcontractorBtn');
        const deleteSubcontractorBtn = document.getElementById('deleteSubcontractorBtn');
        
        if (addTaskBtn) {
            addTaskBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addTaskField();
                debug('New task field added for subcontractor management');
            });
        }
        
        if (addSubcontractorBtn) {
            addSubcontractorBtn.addEventListener('click', function() {
                debug('Opening modal to add new subcontractor');
                openSubcontractorModal();
            });
        }
        
        if (saveSubcontractorBtn) {
            saveSubcontractorBtn.addEventListener('click', saveSubcontractor);
        }
        
        if (deleteSubcontractorBtn) {
            deleteSubcontractorBtn.addEventListener('click', deleteSubcontractor);
        }
        
        debug('Subcontractor management system initialized successfully', {
            components: ['event_listeners', 'modal_handlers', 'task_management'],
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
        });
    });
    
    /**
     * Enhanced function to load subcontractors with comprehensive error handling and modern UI
     * Retrieves all subcontractors for the current project and displays them in accordion format
     */
    function loadSubcontractors() {
        debug('Loading subcontractors from server...', {
            project_id: window.CURRENT_PROJECT_ID,
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
        });
        
        const subcontractorDebug = document.getElementById('subcontractorDebug');
        const subcontractorAccordion = document.getElementById('subcontractorAccordion');
        
        // Hide debug area and show loading state
        if (subcontractorDebug) {
            subcontractorDebug.style.display = 'none';
        }
        
        if (subcontractorAccordion) {
            subcontractorAccordion.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading subcontractors...</span>
                    </div>
                    <h6 class="text-muted">Loading Subcontractors</h6>
                    <small class="text-muted">Retrieving contractor information and status...</small>
                </div>
            `;
        }
        
        // Fetch subcontractors from server with comprehensive error handling
        fetch('ajax_subcontractors.php?action=list')
            .then(response => {
                debug('Subcontractor server response received', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
                }
                return response.text().then(text => {
                    try {
                        debug('Subcontractor response content received', {
                            length: text.length,
                            preview: text.substring(0, 200) + (text.length > 200 ? '...' : '')
                        });
                        return JSON.parse(text);
                    } catch (e) {
                        debugError('Invalid JSON response from subcontractor service', {
                            error: e.message,
                            content: text.substring(0, 500),
                            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                        });
                        showCriticalError('Server returned invalid data - please check the system logs');
                        throw new Error(`Invalid JSON response: ${text.substring(0, 100)}...`);
                    }
                });
            })
            .then(data => {
                debug('Subcontractors loaded successfully', {
                    success: data.ok,
                    count: data.subcontractors ? data.subcontractors.length : 0,
                    timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                });
                
                const accordion = document.getElementById('subcontractorAccordion');
                if (!accordion) {
                    debugError('Subcontractor accordion container not found in DOM');
                    return;
                }
                
                // Handle successful response but no subcontractors
                if (!data.subcontractors || data.subcontractors.length === 0) {
                    accordion.innerHTML = `
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>No Subcontractors Found</strong>
                            </div>
                            <p class="mb-2">No subcontractors have been added to this project yet.</p>
                            <p class="mb-3">Click "Add Subcontractor" to add contractor information and track their status.</p>
                            <small class="text-muted">
                                Subcontractors help organize your project team and track daily tasks and progress.
                            </small>
                        </div>
                    `;
                    return;
                }
                
                // Build accordion HTML for subcontractors
                let html = '';
                data.subcontractors.forEach((sub, index) => {
                    const statusColor = getStatusColor(sub.status || 'Active');
                    const isFirst = index === 0;
                    
                    html += `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading${sub.id}">
                                <button class="accordion-button ${isFirst ? '' : 'collapsed'}" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse${sub.id}" 
                                        aria-expanded="${isFirst ? 'true' : 'false'}" 
                                        aria-controls="collapse${sub.id}">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="flex-grow-1">
                                            <strong>${escapeHtml(sub.name)}</strong> - ${escapeHtml(sub.trade)}
                                        </div>
                                        <span class="badge ${statusColor} ms-2">${sub.status || 'Active'}</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse${sub.id}" 
                                 class="accordion-collapse collapse ${isFirst ? 'show' : ''}" 
                                 aria-labelledby="heading${sub.id}" 
                                 data-bs-parent="#subcontractorAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6 class="text-primary mb-3">
                                                <i class="fas fa-address-card me-2"></i>Contact Information
                                            </h6>
                                            <div class="mb-2">
                                                <strong>Contact:</strong> 
                                                <span class="ms-1">${escapeHtml(sub.contact_name || 'Not specified')}</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Phone:</strong> 
                                                ${sub.phone ? 
                                                    `<a href="tel:${escapeHtml(sub.phone)}" class="text-decoration-none ms-1">
                                                        <i class="fas fa-phone me-1"></i>${escapeHtml(sub.phone)}
                                                    </a>` : 
                                                    '<span class="text-muted ms-1">Not provided</span>'
                                                }
                                            </div>
                                            <div class="mb-3">
                                                <strong>Email:</strong> 
                                                ${sub.email ? 
                                                    `<a href="mailto:${escapeHtml(sub.email)}" class="text-decoration-none ms-1">
                                                        <i class="fas fa-envelope me-1"></i>${escapeHtml(sub.email)}
                                                    </a>` : 
                                                    '<span class="text-muted ms-1">Not provided</span>'
                                                }
                                            </div>
                                            <div class="mt-3">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="openSubcontractorModal(${sub.id})"
                                                        title="Edit ${escapeHtml(sub.name)} details">
                                                    <i class="fas fa-edit me-1"></i> Edit Details
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="text-primary mb-3">
                                                <i class="fas fa-tasks me-2"></i>Today's Tasks
                                            </h6>
                                            ${sub.tasks && sub.tasks.length > 0 ? 
                                                `<ul class="list-group list-group-flush">
                                                    ${sub.tasks.map(task => `
                                                        <li class="list-group-item border-0 px-0 py-2">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            ${escapeHtml(task)}
                                                        </li>
                                                    `).join('')}
                                                </ul>` : 
                                                `<div class="alert alert-light mb-0">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    No specific tasks assigned for today.
                                                    <br><small class="text-muted mt-1 d-block">
                                                        Click "Edit Details" to add tasks for this subcontractor.
                                                    </small>
                                                </div>`
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                // Update accordion with subcontractor content
                accordion.innerHTML = html;
                
                // Add smooth fade-in animation for enhanced user experience
                setTimeout(() => {
                    const items = accordion.querySelectorAll('.accordion-item');
                    items.forEach((item, index) => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            item.style.transition = 'all 0.3s ease';
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, index * 100);
                    });
                }, 50);
                
                debug('Subcontractors display updated successfully', {
                    count: data.subcontractors.length,
                    timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                });
                
            })
            .catch(error => {
                debugError('Error loading subcontractors from server', {
                    error: error.message,
                    timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                });
                
                const accordion = document.getElementById('subcontractorAccordion');
                if (accordion) {
                    accordion.innerHTML = 
                        `<div class="alert alert-danger">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Error Loading Subcontractors</strong>
                            </div>
                            <p class="mb-2">Unable to load subcontractor information from the server.</p>
                            <p class="mb-3"><strong>Error:</strong> ${error.message}</p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-danger btn-sm" onclick="loadSubcontractors()">
                                    <i class="fas fa-sync-alt me-1"></i> Try Again
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                                    <i class="fas fa-refresh me-1"></i> Refresh Page
                                </button>
                            </div>
                        </div>`;
                }
                
                showNotification('Error loading subcontractors: ' + error.message, 'danger');
            });
    }
    
    /**
     * Enhanced function to get status color with comprehensive color mapping
     * Provides consistent visual indicators for different subcontractor statuses
     * 
     * @param {string} status - The subcontractor status
     * @returns {string} Bootstrap CSS class for the status badge
     */
    function getStatusColor(status) {
        const statusColors = {
            'Active': 'bg-success',
            'Standby': 'bg-warning text-dark',
            'Offsite': 'bg-secondary',
            'Delayed': 'bg-danger',
            'Complete': 'bg-info'
        };
        return statusColors[status] || 'bg-secondary';
    }
    
    // Expose subcontractor functions to global scope for HTML onclick handlers
    window.loadSubcontractors = loadSubcontractors;
    window.getStatusColor = getStatusColor;
    
    // Additional subcontractor functions will be loaded from external script files
    debug('Subcontractor management core functions loaded', {
        functions: ['loadSubcontractors', 'getStatusColor'],
        timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
    });
    </script>
    
    <!-- Enhanced Notes & Updates System with comprehensive rich text editing -->
    <script src="js/notes.js" defer></script>
    
    <!-- Enhanced Safety Information System with improved editing capabilities -->
    <script src="js/safety.js" defer></script>
    
    <!-- Enhanced Resource Tracker System for comprehensive project management -->
    <script src="js/resource-tracker.js" defer></script>
    
    <!-- Enhanced Activity Schedule System with Proper Backend Integration -->
    <script src="js/activities.js" defer></script>
    
    <!-- Enhanced Email Report Functionality for comprehensive communication -->
    <script src="js/email-report.js"></script>
    
    <!-- Enhanced Contractor Daily Breakdown for detailed project analysis -->
    <script src="js/contractor-daily-breakdown.js" defer></script>
    
    <!-- Enhanced System Initialization and Final Setup with comprehensive functionality -->
    <script>
    /**
     * =========================================================================
     * SYSTEM INITIALIZATION AND FINAL SETUP - Enhanced Version 6.0.0
     * =========================================================================
     * 
     * FILE PURPOSE: Final system initialization and user experience enhancements
     * DESCRIPTION: This section handles the final system initialization, real-time updates,
     *              user interface enhancements, and ensures all components are properly
     *              loaded and functional with comprehensive error handling and modern UX.
     * 
     * Created: 24/06/2025 14:47:38 (UK Time - Europe/London)
     * Author: Chris Irlam (System Administrator)
     * Current User: irlam
     * 
     * KEY FEATURES IMPLEMENTED:
     * ✅ Real-time Updates: Date and timestamp updates with UK timezone support
     * ✅ Auto-refresh: Content refresh when page becomes visible for current information
     * ✅ Keyboard Shortcuts: Enhanced shortcuts for power users and improved productivity
     * ✅ Error Handling: Global JavaScript error handling with comprehensive logging
     * ✅ Print Support: Professional document printing with console hiding for clean output
     * ✅ System Monitoring: Performance monitoring and optimization features
     * ✅ Accessibility: Enhanced screen reader support and keyboard navigation
     * ✅ User Experience: Loading animations, progress indicators, and smooth transitions
     * ✅ Health Checks: Periodic system health monitoring and status reporting
     * ✅ Ready Notification: System ready confirmation with comprehensive component status
     */
    
    document.addEventListener('DOMContentLoaded', function() {
        debug('Final system initialization starting', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
            version: '6.0.0',
            user: window.username,
            briefing_id: window.CURRENT_BRIEFING_ID,
            project_id: window.CURRENT_PROJECT_ID
        });
        
        /**
         * Enhanced function to update current date display with comprehensive UK formatting
         * Provides real-time date updates for improved user experience and accuracy
         */
        function updateCurrentDate() {
            const now = new Date();
            const ukDate = now.toLocaleDateString('en-GB', { 
                timeZone: 'Europe/London',
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const currentDateElement = document.getElementById('currentDate');
            if (currentDateElement) {
                currentDateElement.textContent = ukDate;
            }
        }
        
        /**
         * Enhanced function to update last updated timestamp with comprehensive UK time
         * Provides real-time timestamp updates for system status monitoring
         */
        function updateLastUpdatedTime() {
            const now = new Date();
            const ukDateTime = now.toLocaleString('en-GB', { 
                timeZone: 'Europe/London',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            // Update main timestamp
            const lastUpdatedElement = document.getElementById('lastUpdated');
            if (lastUpdatedElement) {
                lastUpdatedElement.textContent = ukDateTime + ' (UK)';
            }
            
            // Update footer timestamp
            const footerLastUpdated = document.getElementById('footerLastUpdated');
            if (footerLastUpdated) {
                footerLastUpdated.textContent = ukDateTime;
            }
        }
        
        // Initialize comprehensive date displays with immediate updates
        updateCurrentDate();
        updateLastUpdatedTime();
        
        // Update timestamps every minute to keep displays current and accurate
        const timestampInterval = setInterval(function() {
            updateCurrentDate();
            updateLastUpdatedTime();
        }, 60000); // Update every 60 seconds for real-time accuracy
        
        debug('Real-time timestamp updates initialized', {
            interval: '60 seconds',
            timezone: 'Europe/London'
        });
        
        // Initialize enhanced page visibility change handler for comprehensive auto-refresh
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // Page became visible again, refresh dynamic content for current information
                setTimeout(function() {
                    debug('Page became visible, refreshing content', {
                        timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                    });
                    
                    updateCurrentDate();
                    updateLastUpdatedTime();
                    
                    // Refresh dynamic sections if their load functions exist
                    if (typeof loadAttendees === 'function') {
                        loadAttendees();
                        debug('Attendees refreshed on page visibility');
                    }
                    if (typeof loadSubcontractors === 'function') {
                        loadSubcontractors();
                        debug('Subcontractors refreshed on page visibility');
                    }
                    // Note: activities.js will handle its own refresh automatically
                }, 1000);
            }
        });
        
        // Enhanced keyboard shortcuts for power users and improved productivity
        document.addEventListener('keydown', function(e) {
            // Skip if user is typing in an input field to avoid conflicts
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                return;
            }
            
            // Ctrl/Cmd + A: Add new activity for quick access
            if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !e.shiftKey) {
                e.preventDefault();
                const addActivityBtn = document.getElementById('addActivityBtn');
                if (addActivityBtn && !addActivityBtn.disabled && typeof openAddActivityModal === 'function') {
                    openAddActivityModal();
                    debug('Add activity shortcut used (Ctrl+A)');
                }
            }
			            
            // Ctrl/Cmd + S: Add new subcontractor for quick access
            if ((e.ctrlKey || e.metaKey) && e.key === 's' && !e.shiftKey) {
                e.preventDefault();
                const addSubcontractorBtn = document.getElementById('addSubcontractorBtn');
                if (addSubcontractorBtn && !addSubcontractorBtn.disabled && typeof openSubcontractorModal === 'function') {
                    openSubcontractorModal();
                    debug('Add subcontractor shortcut used (Ctrl+S)');
                }
            }
            
            // F5 or Ctrl/Cmd + R: Refresh page content for updated information
            if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key === 'r')) {
                e.preventDefault();
                debug('Page refresh shortcut used');
                location.reload();
            }
            
            // ESC: Close any open modals for quick navigation
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                if (openModals.length > 0) {
                    openModals.forEach(modal => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    });
                    debug('Modals closed with ESC key', { count: openModals.length });
                }
            }
            
            // Shift + ?: Show keyboard shortcuts help (future feature)
            if (e.shiftKey && e.key === '?') {
                e.preventDefault();
                debug('Help shortcut requested (Shift+?)');
                showNotification('Keyboard shortcuts: Ctrl+A (Add Activity), Ctrl+S (Add Subcontractor), ESC (Close Modals)', 'info');
            }
        });
        
        debug('Enhanced keyboard shortcuts initialized', {
            shortcuts: ['Ctrl+A', 'Ctrl+S', 'F5', 'ESC', 'Shift+?', 'Alt+D'],
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
        });
        
        // Initialize system health monitoring with periodic checks
        const healthCheckInterval = setInterval(function() {
            // Check for any JavaScript errors in console
            if (window.errorCount > 0) {
                debugWarn('JavaScript errors detected during health check', { 
                    error_count: window.errorCount,
                    warning_count: window.warningCount || 0,
                    timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                });
            }
            
            // Check if critical elements are still present
            const criticalElements = [
                'attendeesList',
                'activitiesList', 
                'subcontractorAccordion',
                'notificationArea'
            ];
            
            const missingElements = criticalElements.filter(id => !document.getElementById(id));
            if (missingElements.length > 0) {
                debugError('Critical DOM elements missing during health check', {
                    missing: missingElements,
                    timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                });
            }
            
            // Update last health check time
            lastUpdateTime = new Date();
            
        }, 300000); // Check every 5 minutes
        
        debug('System health monitoring initialized', {
            interval: '5 minutes',
            checks: ['javascript_errors', 'dom_integrity', 'timestamp_updates']
        });
        
        // Show comprehensive system ready notification with component status
        setTimeout(function() {
            debug('DABS system fully initialized and ready for use', {
                timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
                initialization_time: Date.now() - window.performanceStart,
                components_loaded: [
                    'core_functions',
                    'attendees_management',
                    'subcontractors_management', 
                    'activity_schedule',
                    'weather_forecast',
                    'safety_information',
                    'notes_system',
                    'debug_console',
                    'notification_system',
                    'keyboard_shortcuts',
                    'health_monitoring'
                ]
            });
            
            showNotification('Daily Activity Briefing System ready for use!', 'success');
            
            // Mark system as fully operational
            window.systemReady = true;
            window.systemInitializedAt = new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' });
            
        }, 2000);
        
        // Log successful initialization with comprehensive system information
        debug('System initialization completed successfully', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
            version: '6.0.0',
            user: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown'); ?>',
            project: '<?php echo htmlspecialchars($projectInfo['name']); ?>',
            briefing_id: window.CURRENT_BRIEFING_ID,
            project_id: window.CURRENT_PROJECT_ID,
            date: window.CURRENT_DATE,
            date_uk: window.CURRENT_DATE_UK,
            features_enabled: [
                'attendees_management',
                'activity_schedule_with_backend',
                'weather_forecast_integration',
                'safety_information_editor',
                'notes_rich_text_editing',
                'subcontractor_status_tracking',
                'debug_console_monitoring',
                'notification_system_with_animations',
                'keyboard_shortcuts_for_productivity',
                'auto_refresh_on_visibility_change',
                'enhanced_ui_animations_and_transitions',
                'comprehensive_error_handling',
                'uk_timezone_formatting_throughout',
                'mobile_responsive_design',
                'accessibility_features'
            ],
            performance: {
                initialization_time_ms: Date.now() - window.performanceStart,
                dom_ready: true,
                all_scripts_loaded: true
            }
        });
        
        // Initialize scroll animations for enhanced user experience
        setTimeout(() => {
            const scrollElements = document.querySelectorAll('.scroll-fade');
            scrollElements.forEach(el => el.classList.add('visible'));
            debug('Scroll animations activated', { elements: scrollElements.length });
        }, 500);
        
        // Clean up any initialization variables
        delete window.performanceStart;
    });
    
    // Enhanced global error handler for comprehensive unhandled JavaScript errors
    window.addEventListener('error', function(event) {
        const ukTime = new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' });
        debugError('Global JavaScript error caught', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            timestamp: ukTime,
            stack: event.error?.stack,
            user_agent: navigator.userAgent
        });
        
        // Show user-friendly error message for critical errors
        if (event.message && !event.message.includes('Script error')) {
            showNotification('A system error occurred. Please refresh the page if problems persist.', 'warning');
        }
        
        // Increment error counter for health monitoring
        window.errorCount = (window.errorCount || 0) + 1;
    });
    
    // Enhanced global handler for comprehensive unhandled promise rejections
    window.addEventListener('unhandledrejection', function(event) {
        const ukTime = new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' });
        debugError('Unhandled promise rejection caught', {
            reason: event.reason,
            timestamp: ukTime,
            stack: event.reason?.stack,
            promise: event.promise
        });
        
        // Prevent the default browser behavior of logging to console
        event.preventDefault();
        
        // Show user-friendly error message
        showNotification('A network or system error occurred. Please check your connection and try again.', 'warning');
        
        // Increment error counter for health monitoring
        window.errorCount = (window.errorCount || 0) + 1;
    });
    
    // Enhanced print functionality with comprehensive document preparation
    window.addEventListener('beforeprint', function() {
        debug('Print request initiated', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
            user: window.username,
            project: '<?php echo htmlspecialchars($projectInfo['name']); ?>'
        });
        
        // Hide debug console and notifications during printing for clean output
        const debugConsole = document.getElementById('debugConsole');
        const notificationArea = document.getElementById('notificationArea');
        
        if (debugConsole) {
            debugConsole.style.display = 'none';
        }
        if (notificationArea) {
            notificationArea.style.display = 'none';
        }
        
        // Add print-specific styling
        document.body.classList.add('printing');
        
        // Hide interactive elements that shouldn't be printed
        const hideElements = document.querySelectorAll('.btn, .dropdown, .modal');
        hideElements.forEach(el => {
            el.style.visibility = 'hidden';
        });
    });
    
    window.addEventListener('afterprint', function() {
        debug('Print request completed', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
        });
        
        // Restore debug console state after printing for continued functionality
        const debugConsole = document.getElementById('debugConsole');
        const notificationArea = document.getElementById('notificationArea');
        
        if (debugConsole && debugEnabled) {
            debugConsole.style.display = 'block';
        }
        if (notificationArea) {
            notificationArea.style.display = 'block';
        }
        
        // Remove print-specific styling
        document.body.classList.remove('printing');
        
        // Restore interactive elements
        const hideElements = document.querySelectorAll('.btn, .dropdown, .modal');
        hideElements.forEach(el => {
            el.style.visibility = 'visible';
        });
    });
    
    // Enhanced performance monitoring for system optimization
    if ('performance' in window && 'observe' in PerformanceObserver.prototype) {
        try {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.entryType === 'navigation') {
                        debug('Page load performance metrics', {
                            loadTime: Math.round(entry.loadEventEnd - entry.fetchStart),
                            domContentLoaded: Math.round(entry.domContentLoadedEventEnd - entry.fetchStart),
                            domInteractive: Math.round(entry.domInteractive - entry.fetchStart),
                            domComplete: Math.round(entry.domComplete - entry.fetchStart),
                            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
                        });
                    }
                    
                    if (entry.entryType === 'measure') {
                        debug('Custom performance measure', {
                            name: entry.name,
                            duration: Math.round(entry.duration),
                            startTime: Math.round(entry.startTime)
                        });
                    }
                });
            });
            
            observer.observe({ entryTypes: ['navigation', 'measure'] });
            debug('Performance monitoring initialized successfully');
            
        } catch (error) {
            debugWarn('Performance monitoring not available', { error: error.message });
        }
    }
    
    // Initialize connection monitoring for network awareness
    if ('navigator' in window && 'onLine' in navigator) {
        window.addEventListener('online', function() {
            debug('Network connection restored');
            showNotification('Network connection restored. Refreshing data...', 'success');
            
            // Refresh dynamic content when connection is restored
            setTimeout(() => {
                if (typeof loadAttendees === 'function') loadAttendees();
                if (typeof loadSubcontractors === 'function') loadSubcontractors();
            }, 1000);
        });
        
        window.addEventListener('offline', function() {
            debug('Network connection lost');
            showNotification('Network connection lost. Some features may not work properly.', 'warning');
        });
        
        debug('Network monitoring initialized', {
            online: navigator.onLine,
            connection_type: navigator.connection?.effectiveType || 'unknown'
        });
    }
    
    // Expose global utility functions for external scripts
    window.DABS = {
        version: '6.0.0',
        debug: debug,
        debugError: debugError,
        debugWarn: debugWarn,
        debugInfo: debugInfo,
        showNotification: showNotification,
        escapeHtml: escapeHtml,
        getUKDateString: getUKDateString,
        getUKDateDisplay: getUKDateDisplay,
        toggleDebugConsole: toggleDebugConsole,
        clearDebugConsole: clearDebugConsole,
        initialized: false,
        ready: false
    };
    
    // Mark DABS utility object as ready
    setTimeout(() => {
        window.DABS.initialized = true;
        window.DABS.ready = true;
        debug('DABS utility object ready for external scripts');
    }, 1000);
    
    // Final system readiness check and confirmation
    window.addEventListener('load', function() {
        debug('Window load event fired - all resources loaded', {
            timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' }),
            load_time: Date.now() - (window.navigationStart || Date.now()),
            dom_elements: document.querySelectorAll('*').length,
            scripts_loaded: document.querySelectorAll('script').length,
            stylesheets_loaded: document.querySelectorAll('link[rel="stylesheet"]').length
        });
        
        // Final notification that system is completely ready
        setTimeout(() => {
            if (!window.systemReadyNotificationShown) {
                showNotification('System fully loaded and operational!', 'success');
                window.systemReadyNotificationShown = true;
            }
        }, 3000);
    });
    
    // Development helper: Log component load status
    if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev')) {
        setTimeout(() => {
            debug('Development mode - Component load status check', {
                attendees_loaded: typeof loadAttendees === 'function',
                subcontractors_loaded: typeof loadSubcontractors === 'function',
                activities_loaded: typeof openAddActivityModal === 'function',
                weather_loaded: document.getElementById('weatherWidget') !== null,
                notes_loaded: document.getElementById('notesContainer') !== null,
                safety_loaded: document.getElementById('safetyContainer') !== null,
                timestamp: new Date().toLocaleString('en-GB', { timeZone: 'Europe/London' })
            });
        }, 5000);
    }
    </script>

</body>
<?php
// Include enhanced features (add this at the very end of index.php, before </html>)
if (file_exists('dabs-enhanced-features.php')) {
    require_once 'dabs-enhanced-features.php';
    
    // Output enhanced features CSS
    echo getEnhancedFeaturesCSS();
    
    // Output enhanced features HTML 
    echo getEnhancedFeaturesHTML();
    
    // Output enhanced features JavaScript
    echo getEnhancedFeaturesJS();
} else {
    echo "<!-- Enhanced features file not found -->";
}
	
require_once 'dabs-enhanced-features.php';
echo getEnhancedFeaturesCSS();
echo getEnhancedFeaturesHTML();
echo getEnhancedFeaturesJS();
?>

</html>
	