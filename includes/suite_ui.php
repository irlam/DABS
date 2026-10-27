<?php
declare(strict_types=1);

/** Presentation only: permissions continue to be enforced by the Suite gateway. */
function dabsSuiteNavigation(array $session, bool $dedicated = false, string $project = ''): string
{
    $e = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $name = $e((string)($session['user_name'] ?? 'Project user'));
    $role = $e(ucwords(str_replace('_', ' ', (string)($session['user_role'] ?? 'viewer'))));
    $project = $e($project !== '' ? $project : 'Daily activity briefings');
    $logout = '<a class="dabs-shell-link" href="logout.php">Sign out</a>';
    if ($dedicated) {
        $token = $e((string)($session['csrf_token'] ?? ''));
        $logout = '<form method="post" action="/logout.php"><input type="hidden" name="csrf_token" value="'.$token.'"><button class="dabs-shell-link" type="submit">Sign out of DABS</button></form>';
    }
    return '<a class="dabs-skip-link" href="#dabs-workspace">Skip to briefing</a>'
        .'<nav class="dabs-suite-bar" aria-label="Construction Suite">'
        .'<a class="dabs-suite-brand" href="https://suite.defecttracker.uk/"><span class="dabs-suite-mark" aria-hidden="true">CS</span><span><strong>Construction</strong><small>Suite</small></span></a>'
        .'<span class="dabs-module-label">DABS <span>Daily briefings</span></span>'
        .'<div class="dabs-suite-account"><span class="dabs-user-name">'.$name.'</span><span class="dabs-role-pill">'.$role.'</span>'.$logout.'</div></nav>'
        .'<div class="dabs-breadcrumb"><a href="https://suite.defecttracker.uk/">← Back to Suite</a><span aria-hidden="true">/</span><span>'.$project.'</span></div>';
}
