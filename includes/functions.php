<?php
/**
 * includes/functions.php
 * 
 * Daily Activity Briefing System (DABS) - Utility Functions
 * 
 * This file contains essential utility functions for the Daily Activity Briefing System.
 * It provides core functionality for retrieving, formatting and manipulating data
 * related to construction site briefings. Functions handle project information,
 * activities scheduling, resource allocation, safety information, subcontractor 
 * management and email notifications.
 * 
 * All database table references updated to use "dabs_subcontractors", "dabs_project_subcontractors", etc.
 * 
 * Key functionality includes:
 * - Project data retrieval and management
 * - Daily briefing creation and updates
 * - Activity and resource scheduling
 * - UK date formatting (DD/MM/YYYY)
 * - Email generation and sending
 * - Input sanitization and validation
 * - User activity logging
 * 
 * @author irlamkeep
 * @version 1.1
 * @date 24/06/2025
 * @website dabs.defecttracker.uk
 */

// Include database connection if not already included
require_once 'db_connect.php';

/**
 * Get current project information
 */
function getProjectInfo($projectId) {
    $sql = "SELECT * FROM projects WHERE id = ?";
    $project = fetchOne($sql, [$projectId]);
    if (!$project) {
        return [
            'id' => 0,
            'name' => 'Unknown Project',
            'location' => 'Unknown Location',
            'manager' => 'Not Assigned',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year'))
        ];
    }
    $project['start_date_formatted'] = formatUKDate($project['start_date']);
    $project['end_date_formatted'] = formatUKDate($project['end_date']);
    return $project;
}

function getTodaysBriefing($projectId) {
    $today = date('Y-m-d');
    $briefing = [
        'date' => $today,
        'overview' => '',
        'activities' => [],
        'resources' => [],
        'safety' => '',
        'notes' => '',
        'weather' => []
    ];
    $headerSql = "SELECT * FROM briefings WHERE project_id = ? AND date = ?";
    $header = fetchOne($headerSql, [$projectId, $today]);
    if ($header) {
        $briefing['id'] = $header['id'];
        $briefing['overview'] = $header['overview'];
        $briefing['safety'] = $header['safety_info'];
        $briefing['notes'] = $header['notes'];
        $briefing['created_by'] = $header['created_by'];
        $briefing['last_updated'] = $header['last_updated'];
        $briefing['activities'] = getActivitiesForBriefing($header['id']);
        $briefing['resources'] = getResourcesForBriefing($header['id']);
    } else {
        $newBriefingId = createEmptyBriefing($projectId, $today);
        $briefing['id'] = $newBriefingId;
        logUserActivity('create_briefing', 'Created new briefing for ' . formatUKDate($today));
    }
    return $briefing;
}

function createEmptyBriefing($projectId, $date) {
    $userId = $_SESSION['user_id'] ?? 1;
    $data = [
        'project_id' => $projectId,
        'date' => $date,
        'overview' => '',
        'safety_info' => '',
        'notes' => '',
        'created_by' => $userId,
        'last_updated' => date('Y-m-d H:i:s'),
        'status' => 'draft'
    ];
    return insertData('briefings', $data);
}

function getActivitiesForBriefing($briefingId) {
    $sql = "SELECT * FROM activities WHERE briefing_id = ? ORDER BY time ASC";
    return fetchAll($sql, [$briefingId]);
}

function getResourcesForBriefing($briefingId) {
    $sql = "SELECT * FROM resources WHERE briefing_id = ?";
    return fetchAll($sql, [$briefingId]);
}

/**
 * Get all subcontractors for a project with their daily tasks
 * 
 * *** Table names updated to dabs_subcontractors, dabs_project_subcontractors, and dabs_subcontractor_tasks ***
 */
function getProjectSubcontractors($projectId) {
    // Get subcontractors assigned to the project
    $sql = "SELECT s.*, ps.role as trade 
            FROM dabs_subcontractors s
            JOIN dabs_project_subcontractors ps ON s.id = ps.subcontractor_id
            WHERE ps.project_id = ?
            ORDER BY s.name ASC";
    $subcontractors = fetchAll($sql, [$projectId]);
    $today = date('Y-m-d');
    foreach ($subcontractors as &$sub) {
        $taskSql = "SELECT task_description 
                    FROM dabs_subcontractor_tasks 
                    WHERE subcontractor_id = ? 
                    AND project_id = ? 
                    AND date = ?";
        $tasks = fetchAll($taskSql, [$sub['id'], $projectId, $today]);
        $sub['tasks'] = array_column($tasks, 'task_description');
    }
    return $subcontractors;
}

function formatUKDate($dateStr) {
    return date('d/m/Y', strtotime($dateStr));
}

function formatUKDateTime($dateTimeStr) {
    return date('d/m/Y H:i:s', strtotime($dateTimeStr));
}

function formatUKTime($timeStr) {
    return date('H:i', strtotime($timeStr));
}

function sanitizeInput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function logUserActivity($action, $details = '', $userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? 0;
    }
    $data = [
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    return insertData('activity_log', $data) !== false;
}

function saveBriefingChanges($briefingId, $briefingData) {
    $userId = $_SESSION['user_id'] ?? 1;
    $headerData = [
        'overview' => $briefingData['overview'],
        'safety_info' => $briefingData['safety'],
        'notes' => $briefingData['notes'],
        'last_updated' => date('Y-m-d H:i:s'),
        'updated_by' => $userId
    ];
    $success = updateData('briefings', $headerData, 'id = ?', [$briefingId]);
    if (!$success) return false;
    if (isset($briefingData['activities'])) {
        deleteData('activities', 'briefing_id = ?', [$briefingId]);
        foreach ($briefingData['activities'] as $activity) {
            $activityData = [
                'briefing_id' => $briefingId,
                'time' => $activity['time'],
                'title' => $activity['title'],
                'description' => $activity['description'],
                'priority' => $activity['priority'] ?? null,
                'assigned_to' => $activity['assigned_to'] ?? null
            ];
            insertData('activities', $activityData);
        }
    }
    if (isset($briefingData['resources'])) {
        deleteData('resources', 'briefing_id = ?', [$briefingId]);
        foreach ($briefingData['resources'] as $resource) {
            $resourceData = [
                'briefing_id' => $briefingId,
                'name' => $resource['name'],
                'type' => $resource['type'],
                'location' => $resource['location'],
                'assigned_to' => $resource['assigned_to'] ?? null
            ];
            insertData('resources', $resourceData);
        }
    }
    logUserActivity('update_briefing', 'Updated briefing #' . $briefingId);
    return true;
}

function sendBriefingEmail($briefingId, $recipients, $subject, $message = '') {
    $briefingData = getBriefingById($briefingId);
    if (!$briefingData) {
        return ['success' => false, 'message' => 'Briefing not found'];
    }
    $emailContent = createEmailContent($briefingData, $message);
    $successCount = 0;
    $failedRecipients = [];
    foreach ($recipients as $recipient) {
        if (sendEmail($recipient, $subject, $emailContent)) {
            $successCount++;
        } else {
            $failedRecipients[] = $recipient;
        }
    }
    logUserActivity(
        'send_briefing_email', 
        'Sent briefing #' . $briefingId . ' to ' . $successCount . ' recipients'
    );
    if ($successCount === count($recipients)) {
        return [
            'success' => true,
            'message' => 'Email sent successfully to all recipients',
            'sent_count' => $successCount
        ];
    } else if ($successCount > 0) {
        return [
            'success' => true, 
            'message' => 'Email sent to ' . $successCount . ' of ' . count($recipients) . ' recipients',
            'sent_count' => $successCount,
            'failed' => $failedRecipients
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to send email to any recipient',
            'sent_count' => 0,
            'failed' => $failedRecipients
        ];
    }
}

function getBriefingById($briefingId) {
    $headerSql = "SELECT b.*, p.name as project_name 
                  FROM briefings b
                  JOIN projects p ON b.project_id = p.id
                  WHERE b.id = ?";
    $header = fetchOne($headerSql, [$briefingId]);
    if (!$header) return false;
    $briefing = [
        'id' => $header['id'],
        'date' => $header['date'],
        'project_id' => $header['project_id'],
        'project_name' => $header['project_name'],
        'overview' => $header['overview'],
        'safety' => $header['safety_info'],
        'notes' => $header['notes'],
        'created_by' => $header['created_by'],
        'last_updated' => formatUKDateTime($header['last_updated']),
        'status' => $header['status']
    ];
    $briefing['activities'] = getActivitiesForBriefing($briefingId);
    $briefing['resources'] = getResourcesForBriefing($briefingId);
    return $briefing;
}

function createEmailContent($briefingData, $additionalMessage = '') {
    $briefingDate = formatUKDate($briefingData['date']);
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .section { margin-bottom: 20px; padding: 15px; background-color: white; border-radius: 5px; }
            .section-title { margin-top: 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .footer { text-align: center; font-size: 12px; color: #888; padding: 20px; }
            .activity { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
            .activity:last-child { border-bottom: none; }
            .time { font-weight: bold; color: #3498db; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
            th { background-color: #f5f7fa; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Daily Activity Briefing</h1>
                <p>' . $briefingDate . '</p>
                <p>' . htmlspecialchars($briefingData['project_name']) . '</p>
            </div>
            <div class="content">';
    if (!empty($additionalMessage)) {
        $html .= '<div class="section">
            <p>' . nl2br(htmlspecialchars($additionalMessage)) . '</p>
        </div>';
    }
    $html .= '<div class="section">
        <h2 class="section-title">Overview</h2>
        <p>' . (!empty($briefingData['overview']) ? htmlspecialchars($briefingData['overview']) : 'No overview provided for today.') . '</p>
    </div>';
    $html .= '<div class="section">
        <h2 class="section-title">Today\'s Activities</h2>';
    if (!empty($briefingData['activities'])) {
        $html .= '<table>
            <tr>
                <th>Time</th>
                <th>Activity</th>
                <th>Assigned To</th>
            </tr>';
        foreach ($briefingData['activities'] as $activity) {
            $html .= '<tr>
                <td class="time">' . htmlspecialchars($activity['time']) . '</td>
                <td>
                    <strong>' . htmlspecialchars($activity['title']) . '</strong><br>
                    ' . htmlspecialchars($activity['description']) . '
                </td>
                <td>' . (!empty($activity['assigned_to']) ? htmlspecialchars($activity['assigned_to']) : 'Not assigned') . '</td>
            </tr>';
        }
        $html .= '</table>';
    } else {
        $html .= '<p>No activities scheduled for today.</p>';
    }
    $html .= '</div>';
    $html .= '<div class="section">
        <h2 class="section-title">Safety Information</h2>
        ' . (!empty($briefingData['safety']) ? $briefingData['safety'] : '<p>No specific safety information for today.</p>') . '
    </div>';
    if (!empty($briefingData['resources'])) {
        $html .= '<div class="section">
            <h2 class="section-title">Resource Allocation</h2>
            <table>
                <tr>
                    <th>Resource</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Assigned To</th>
                </tr>';
        foreach ($briefingData['resources'] as $resource) {
            $html .= '<tr>
                <td>' . htmlspecialchars($resource['name']) . '</td>
                <td>' . htmlspecialchars($resource['type']) . '</td>
                <td>' . htmlspecialchars($resource['location']) . '</td>
                <td>' . (!empty($resource['assigned_to']) ? htmlspecialchars($resource['assigned_to']) : 'Not assigned') . '</td>
            </tr>';
        }
        $html .= '</table>
        </div>';
    }
    if (!empty($briefingData['notes'])) {
        $html .= '<div class="section">
            <h2 class="section-title">Notes & Additional Information</h2>
            ' . $briefingData['notes'] . '
        </div>';
    }
    $html .= '</div>
            <div class="footer">
                <p>This is an automated email from the Daily Activity Briefing System.</p>
                <p>Please do not reply to this email.</p>
                <p>Last updated: ' . $briefingData['last_updated'] . '</p>
            </div>
        </div>
    </body>
    </html>';
    return $html;
}

function sendEmail($to, $subject, $htmlMessage) {
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: DABS <no-reply@defecttracker.uk>',
        'Reply-To: no-reply@defecttracker.uk',
        'X-Mailer: PHP/' . phpversion()
    ];
    return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
}

/**
 * Add a new activity to a briefing
 */
function addActivity($briefingId, $activityData) {
    if (empty($activityData['time']) || empty($activityData['title'])) return false;
    $data = [
        'briefing_id' => $briefingId,
        'time' => $activityData['time'],
        'title' => $activityData['title'],
        'description' => $activityData['description'] ?? '',
        'priority' => $activityData['priority'] ?? null,
        'assigned_to' => $activityData['assigned_to'] ?? null
    ];
    $newId = insertData('activities', $data);
    if ($newId) {
        logUserActivity('add_activity', 'Added activity to briefing #' . $briefingId);
    }
    return $newId;
}

function updateActivity($activityId, $activityData) {
    if (empty($activityData['time']) || empty($activityData['title'])) return false;
    $data = [
        'time' => $activityData['time'],
        'title' => $activityData['title'],
        'description' => $activityData['description'] ?? '',
        'priority' => $activityData['priority'] ?? null,
        'assigned_to' => $activityData['assigned_to'] ?? null
    ];
    $success = updateData('activities', $data, 'id = ?', [$activityId]);
    if ($success) {
        logUserActivity('update_activity', 'Updated activity #' . $activityId);
    }
    return $success;
}

function deleteActivity($activityId) {
    $activity = fetchOne("SELECT * FROM activities WHERE id = ?", [$activityId]);
    if (!$activity) return false;
    $success = deleteData('activities', 'id = ?', [$activityId]);
    if ($success) {
        logUserActivity(
            'delete_activity', 
            'Deleted activity #' . $activityId . ' ("' . $activity['title'] . '") from briefing #' . $activity['briefing_id']
        );
    }
    return $success;
}

/**
 * Get weather forecast data for a location
 */
function getWeatherForecast($location) {
    $apiKey = ''; // Add your weather API key here
    $apiUrl = "https://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$location}&days=1";
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) {
        $weatherData = json_decode($response, true);
        if ($weatherData) {
            return [
                'current' => [
                    'temp_c' => $weatherData['current']['temp_c'],
                    'condition' => $weatherData['current']['condition']['text'],
                    'icon' => $weatherData['current']['condition']['icon'],
                    'wind_kph' => $weatherData['current']['wind_kph'],
                    'wind_dir' => $weatherData['current']['wind_dir'],
                    'precipitation_mm' => $weatherData['current']['precip_mm'],
                    'humidity' => $weatherData['current']['humidity']
                ],
                'forecast' => $weatherData['forecast']['forecastday'][0]['hour']
            ];
        }
    }
    return [];
}

/**
 * Add a new subcontractor
 * 
 * Table names updated to dabs_subcontractors and dabs_project_subcontractors
 */
function addSubcontractor($subcontractorData) {
    if (empty($subcontractorData['name']) || empty($subcontractorData['contact_name']) || empty($subcontractorData['email'])) {
        return false;
    }
    $data = [
        'name' => $subcontractorData['name'],
        'contact_name' => $subcontractorData['contact_name'],
        'email' => $subcontractorData['email'],
        'phone' => $subcontractorData['phone'] ?? '',
        'address' => $subcontractorData['address'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    $newId = insertData('dabs_subcontractors', $data);
    if ($newId && !empty($subcontractorData['project_id'])) {
        $linkData = [
            'project_id' => $subcontractorData['project_id'],
            'subcontractor_id' => $newId,
            'role' => $subcontractorData['role'] ?? '',
            'added_on' => date('Y-m-d H:i:s')
        ];
        insertData('dabs_project_subcontractors', $linkData);
    }
    if ($newId) {
        logUserActivity('add_subcontractor', 'Added new subcontractor: ' . $subcontractorData['name']);
    }
    return $newId;
}

function isSystemSetUp() {
    try {
        $result = fetchOne("SHOW TABLES LIKE 'briefings'");
        return $result !== false;
    } catch (Exception $e) {
        return false;
    }
}

function getDatabaseSetupSQL() {
    return "
    -- Create users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        role ENUM('admin', 'manager', 'user') DEFAULT 'user',
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create projects table
    CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        location VARCHAR(255),
        manager VARCHAR(100),
        start_date DATE NOT NULL,
        end_date DATE,
        status ENUM('planning', 'active', 'paused', 'completed') DEFAULT 'planning',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create briefings table
    CREATE TABLE IF NOT EXISTS briefings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        date DATE NOT NULL,
        overview TEXT,
        safety_info TEXT,
        notes TEXT,
        created_by INT,
        updated_by INT,
        last_updated DATETIME,
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
        UNIQUE KEY (project_id, date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create activities table
    CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        briefing_id INT NOT NULL,
        time TIME NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        priority ENUM('low', 'medium', 'high', 'critical'),
        assigned_to VARCHAR(100),
        FOREIGN KEY (briefing_id) REFERENCES briefings(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create resources table
    CREATE TABLE IF NOT EXISTS resources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        briefing_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        type ENUM('personnel', 'equipment', 'material', 'other') NOT NULL,
        location VARCHAR(100),
        assigned_to VARCHAR(100),
        FOREIGN KEY (briefing_id) REFERENCES briefings(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create dabs_subcontractors table
    CREATE TABLE IF NOT EXISTS dabs_subcontractors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        contact_name VARCHAR(100),
        email VARCHAR(100),
        phone VARCHAR(50),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create dabs_project_subcontractors table
    CREATE TABLE IF NOT EXISTS dabs_project_subcontractors (
        project_id INT NOT NULL,
        subcontractor_id INT NOT NULL,
        role VARCHAR(100),
        added_on DATETIME,
        PRIMARY KEY (project_id, subcontractor_id),
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
        FOREIGN KEY (subcontractor_id) REFERENCES dabs_subcontractors(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create dabs_subcontractor_tasks table
    CREATE TABLE IF NOT EXISTS dabs_subcontractor_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subcontractor_id INT NOT NULL,
        project_id INT NOT NULL,
        date DATE NOT NULL,
        task_description TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subcontractor_id) REFERENCES dabs_subcontractors(id) ON DELETE CASCADE,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create activity_log table
    CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        timestamp DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
}
?>