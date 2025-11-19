<?php
/**
 * =========================================================================
 * ajax_resource_stats.php
 * =========================================================================
 * DESCRIPTION:
 *   Returns live resource statistics for today for the DABS dashboard in UK format.
 *   - Total Labour (Workers): sum of labor_count for today's activities, for this project.
 *   - Active Subcontractors: number of unique 'Active' subcontractors for this project.
 *   - Labour by Contractor: array of { contractor, labour_count } for today, for this project.
 *   - Labour by Area: array of { area, labour_count } for today, for this project.
 *
 *   Requires URL parameter: project_id (e.g. ?project_id=1)
 * 
 * AUTHOR: irlam
 * LAST UPDATED: 25/06/2025 (UK Time)
 * =========================================================================
 */

date_default_timezone_set('Europe/London');

$db_host = '10.35.233.124';
$db_name = 'k87747_dabs';
$db_user = 'k87747_dabs';
$db_pass = 'Subaru5554346';
$db_charset = 'utf8mb4';

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if ($project_id < 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing or invalid project_id parameter.",
        "timestamp" => date("d/m/Y H:i:s")
    ]);
    exit;
}

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed",
        "message" => $e->getMessage(),
        "timestamp" => date("d/m/Y H:i:s")
    ]);
    exit;
}

$today = date('Y-m-d');
$uk_today = date('d/m/Y');

try {
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(labor_count), 0) AS total_labour 
         FROM activities 
         WHERE date = ? 
         AND briefing_id IN (SELECT id FROM briefings WHERE project_id = ?)"
    );
    $stmt->execute([$today, $project_id]);
    $total_labour = (int)($stmt->fetchColumn());

    $stmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT name) 
         FROM dabs_subcontractors 
         WHERE project_id = ? AND status = 'Active'"
    );
    $stmt->execute([$project_id]);
    $active_contractors = (int)($stmt->fetchColumn());

    $stmt = $pdo->prepare(
        "SELECT contractors, SUM(labor_count) AS labour_count 
         FROM activities 
         WHERE date = ? 
         AND briefing_id IN (SELECT id FROM briefings WHERE project_id = ?)
         AND contractors IS NOT NULL AND TRIM(contractors) != ''
         GROUP BY contractors
         ORDER BY labour_count DESC"
    );
    $stmt->execute([$today, $project_id]);
    $contractor_labour = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $contractor_labour[] = [
            'contractor' => $row['contractors'],
            'labour_count' => (int)$row['labour_count']
        ];
    }

    $stmt = $pdo->prepare(
        "SELECT area, SUM(labor_count) AS labour_count 
         FROM activities 
         WHERE date = ? 
         AND briefing_id IN (SELECT id FROM briefings WHERE project_id = ?)
         AND area IS NOT NULL AND TRIM(area) != ''
         GROUP BY area
         ORDER BY labour_count DESC"
    );
    $stmt->execute([$today, $project_id]);
    $area_labour = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $area_labour[] = [
            'area' => $row['area'],
            'labour_count' => (int)$row['labour_count']
        ];
    }

    $resource_stats = [
        "total_labour" => $total_labour,
        "active_contractors" => $active_contractors,
        "labour_by_contractor" => $contractor_labour,
        "labour_by_area" => $area_labour,
        "last_update" => $uk_today,
    ];
} catch (Exception $e) {
    $resource_stats = [
        "total_labour" => 0,
        "active_contractors" => 0,
        "labour_by_contractor" => [],
        "labour_by_area" => [],
        "last_update" => $uk_today,
        "error" => $e->getMessage()
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "success" => true,
    "resource_stats" => $resource_stats,
    "timestamp" => date("d/m/Y H:i:s")
]);
exit;
?>