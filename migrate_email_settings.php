<?php
/**
 * ============================================================================
 * Database Migration Script - Email Settings Table
 * ============================================================================
 * DESCRIPTION:
 * Creates the email_settings table in the DABS database.
 * Run this script once to set up the SMTP email configuration feature.
 *
 * USAGE:
 * 1. Access this file via browser: http://your-domain/migrate_email_settings.php
 * 2. Or run via CLI: php migrate_email_settings.php
 *
 * CREATED: 20/11/2025 (UK Date Format)
 * ============================================================================
 */

// Set timezone
date_default_timezone_set('Europe/London');

// Include database connection
require_once __DIR__ . '/includes/db_connect.php';

// Check if running from CLI or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    // For web access, require admin authentication
    session_start();
    if (!isset($_SESSION['authenticated']) || $_SESSION['user_role'] !== 'admin') {
        die('Access denied. This script can only be run by administrators.');
    }
    echo "<!DOCTYPE html><html><head><title>Email Settings Migration</title>
    <style>body{font-family:Arial,sans-serif;padding:20px;max-width:800px;margin:0 auto;}
    .success{color:green;background:#e8f5e9;padding:10px;border-radius:5px;}
    .error{color:red;background:#ffebee;padding:10px;border-radius:5px;}
    pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;}
    </style></head><body>";
    echo "<h1>Email Settings Database Migration</h1>";
}

try {
    // Connect to database
    $pdo = connectToDatabase();
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database/email_settings.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolons to get individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        // Skip comments
        if (strpos(trim($statement), '--') === 0) continue;
        
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = $e->getMessage();
            }
        }
    }
    
    // Check if table was created successfully
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'email_settings'")->fetch();
    
    if ($tableCheck) {
        $message = "✅ Migration completed successfully!";
        $details = [
            "Table 'email_settings' has been created or already exists.",
            "Executed $successCount SQL statements.",
            "Default email settings have been inserted."
        ];
        
        if ($isCLI) {
            echo $message . "\n";
            foreach ($details as $detail) {
                echo "- " . $detail . "\n";
            }
        } else {
            echo "<div class='success'>";
            echo "<h2>$message</h2>";
            echo "<ul>";
            foreach ($details as $detail) {
                echo "<li>$detail</li>";
            }
            echo "</ul>";
            echo "<p><a href='admin_panel.php'>Go to Admin Panel</a> to configure email settings.</p>";
            echo "</div>";
        }
        
        if (!empty($errors)) {
            if ($isCLI) {
                echo "\nWarnings:\n";
                foreach ($errors as $error) {
                    echo "- $error\n";
                }
            } else {
                echo "<div class='error'>";
                echo "<h3>Warnings:</h3><ul>";
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                echo "</ul></div>";
            }
        }
    } else {
        throw new Exception("Table creation failed. Please check database permissions.");
    }
    
} catch (Exception $e) {
    $errorMessage = "❌ Migration failed: " . $e->getMessage();
    
    if ($isCLI) {
        echo $errorMessage . "\n";
        exit(1);
    } else {
        echo "<div class='error'>";
        echo "<h2>Migration Failed</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>Please check database connection and permissions.</p>";
        echo "</div>";
    }
}

if (!$isCLI) {
    echo "</body></html>";
}
