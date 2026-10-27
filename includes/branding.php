<?php
/** Centralised company branding and safe logo upload helpers. */

function defaultBrandingSettings(): array {
    return [
        'company_name' => 'DABS', 'tagline' => '',
        'company_logo' => 'images/logo.png', 'company_logo_dark' => '', 'favicon' => '',
        'show_login' => 1, 'show_dashboard' => 1, 'show_reports' => 1,
        'show_emails' => 1, 'show_subcontractor_logos' => 1
    ];
}

function getBrandingSettings(?PDO $pdo = null): array {
    static $cached = null;
    if ($cached !== null && $pdo === null) return $cached;
    $settings = defaultBrandingSettings();
    try {
        $pdo = $pdo ?: connectToDatabase();
        $row = $pdo->query("SELECT * FROM branding_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        if ($row) $settings = array_merge($settings, $row);
    } catch (Throwable $e) {
        // Keep the application usable until the supplied SQL update is imported.
    }
    $cached = $settings;
    return $settings;
}

function brandingAssetUrl(string $path, string $fallback = ''): string {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if ($path === '' || str_contains($path, '..') || !is_file(dirname(__DIR__) . '/' . $path)) {
        $path = $fallback;
    }
    return $path;
}

function companyLogoUrl(?array $branding = null, bool $dark = false): string {
    $branding = $branding ?: getBrandingSettings();
    $preferred = $dark && !empty($branding['company_logo_dark'])
        ? $branding['company_logo_dark'] : $branding['company_logo'];
    return brandingAssetUrl((string)$preferred, 'images/suite-dabs.svg');
}

function saveUploadedLogo(array $file, string $category, string $prefix): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please select a valid image file.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Logo files must be 5 MB or smaller.');
    }
    $info = @getimagesize($file['tmp_name']);
    $allowed = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($allowed[$info[2]]) || $info[0] > 3000 || $info[1] > 3000) {
        throw new RuntimeException('Use a PNG, JPG or WebP image no larger than 3000 x 3000 pixels.');
    }
    $directory = dirname(__DIR__) . '/uploads/' . trim($category, '/');
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The logo upload directory could not be created.');
    }
    $filename = preg_replace('/[^a-z0-9-]/i', '-', $prefix) . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('The uploaded logo could not be saved.');
    }
    return 'uploads/' . trim($category, '/') . '/' . $filename;
}

function removeManagedLogo(string $path): void {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if (str_starts_with($path, 'uploads/') && !str_contains($path, '..')) {
        $file = dirname(__DIR__) . '/' . $path;
        if (is_file($file)) @unlink($file);
    }
}
