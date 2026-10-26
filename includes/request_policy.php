<?php
declare(strict_types=1);

/** Pure, default-deny policy shared by every DABS JSON entry point. */
function dabsPermissionAllowed(array $session, string $permission): bool
{
    if (($session['authenticated'] ?? false) !== true
        || filter_var($session['user_id'] ?? 0, FILTER_VALIDATE_INT) < 1
        || filter_var($session['current_project'] ?? 0, FILTER_VALIDATE_INT) < 1) return false;
    $roles = [
        'read' => ['admin', 'manager', 'user', 'viewer'],
        'write' => ['admin', 'manager', 'user'],
        'email' => ['admin', 'manager'],
        'admin' => ['admin'],
    ];
    return in_array($session['user_role'] ?? '', $roles[$permission] ?? [], true);
}

function dabsRequestStatus(string $endpoint, string $method, array $get, array $post, array $session, string $headerToken = ''): int
{
    if (!dabsPermissionAllowed($session, 'read')) return 401;
    // Request parameters may never switch the session's project.
    foreach ([$get, $post] as $input) {
        if (isset($input['project_id']) && (string)$input['project_id'] !== (string)$session['current_project']) return 403;
    }
    $actions = [
        'ajax_activities.php' => ['list'=>'read','get'=>'read','add'=>'write','update'=>'write','delete'=>'write','import_previous_day'=>'write'],
        'ajax_notes.php' => ['get'=>'read','get_date'=>'read','history'=>'read','save'=>'write'],
        'ajax_safety.php' => ['update'=>'write'],
        'ajax_attendees.php' => ['list'=>'read','add'=>'write','delete'=>'write'],
        'ajax_subcontractors.php' => ['list'=>'read','get'=>'read','add'=>'write','update'=>'write','delete'=>'write'],
        'ajax_branding.php' => ['get'=>'admin','save'=>'admin'],
        'ajax_admin.php' => array_fill_keys(['list_users','get_user','create_user','update_user','delete_user','list_logs','read_log','get_email_settings','save_email_settings','test_email'], 'admin'),
        'ajax_resource_stats.php' => [''=>'read'],
        'email_briefing.php' => [''=>'email'],
    ];
    $default = ['ajax_attendees.php'=>'list', 'ajax_branding.php'=>'get'][$endpoint] ?? '';
    $action = $post['action'] ?? $get['action'] ?? $default;
    if (!is_string($action)) return 400;
    $permission = $actions[$endpoint][$action] ?? null;
    if ($permission === null) return 400;
    if (!dabsPermissionAllowed($session, $permission)) return 403;
    $read = $permission === 'read' || ($endpoint === 'ajax_branding.php' && $action === 'get')
        || ($endpoint === 'ajax_admin.php' && in_array($action, ['list_users','get_user','list_logs','read_log','get_email_settings'], true));
    if ($read && in_array($method, ['GET','HEAD'], true)) return 200;
    if ($method !== 'POST') return 405;
    $expected = $session['csrf_token'] ?? '';
    $supplied = $headerToken !== '' ? $headerToken : ($post['csrf_token'] ?? '');
    if (!is_string($expected) || !preg_match('/^[a-f0-9]{64}$/D', $expected)
        || !is_string($supplied) || !hash_equals($expected, $supplied)) return 403;
    return 200;
}
