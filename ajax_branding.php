<?php
date_default_timezone_set('Europe/London');
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/branding.php';

function brandingResponse(bool $success, string $message = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}
if (!isUserLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') brandingResponse(false, 'Administrator access required.');

try {
    $pdo = connectToDatabase();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'get';
    if ($action === 'get') brandingResponse(true, '', ['settings' => getBrandingSettings($pdo)]);
    if ($action !== 'save') brandingResponse(false, 'Invalid action.');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) brandingResponse(false, 'Your session expired. Refresh the page and try again.');

    $current = getBrandingSettings($pdo);
    $paths = ['company_logo', 'company_logo_dark', 'favicon'];
    foreach ($paths as $field) {
        if (!empty($_POST['remove_' . $field])) {
            removeManagedLogo((string)$current[$field]);
            $current[$field] = $field === 'company_logo' ? 'images/logo.png' : '';
        }
        if (!empty($_FILES[$field]['name'])) {
            $newPath = saveUploadedLogo($_FILES[$field], 'branding', $field);
            removeManagedLogo((string)$current[$field]);
            $current[$field] = $newPath;
        }
    }
    $companyName = trim($_POST['company_name'] ?? 'DABS');
    $tagline = trim($_POST['tagline'] ?? '');
    if ($companyName === '' || mb_strlen($companyName) > 150 || mb_strlen($tagline) > 255) throw new RuntimeException('Check the company name and tagline lengths.');
    $flags = ['show_login','show_dashboard','show_reports','show_emails','show_subcontractor_logos'];
    foreach ($flags as $flag) $current[$flag] = isset($_POST[$flag]) ? 1 : 0;
    $sql = "INSERT INTO branding_settings (id, company_name, tagline, company_logo, company_logo_dark, favicon, show_login, show_dashboard, show_reports, show_emails, show_subcontractor_logos, updated_by)
            VALUES (1,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE company_name=VALUES(company_name), tagline=VALUES(tagline), company_logo=VALUES(company_logo), company_logo_dark=VALUES(company_logo_dark), favicon=VALUES(favicon), show_login=VALUES(show_login), show_dashboard=VALUES(show_dashboard), show_reports=VALUES(show_reports), show_emails=VALUES(show_emails), show_subcontractor_logos=VALUES(show_subcontractor_logos), updated_by=VALUES(updated_by), updated_at=CURRENT_TIMESTAMP";
    $pdo->prepare($sql)->execute([$companyName,$tagline,$current['company_logo'],$current['company_logo_dark'],$current['favicon'],$current['show_login'],$current['show_dashboard'],$current['show_reports'],$current['show_emails'],$current['show_subcontractor_logos'],$_SESSION['user_id'] ?? null]);
    brandingResponse(true, 'Branding settings saved.', ['settings' => array_merge($current, ['company_name'=>$companyName,'tagline'=>$tagline])]);
} catch (Throwable $e) {
    brandingResponse(false, $e->getMessage());
}
