<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__).'/helpers/permissions.php';

function require_role(string ...$roles): array
{
    $user = require_auth();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Anda tidak memiliki hak akses untuk halaman atau tindakan ini.');
    }
    $module = panel_route_permission((string)($_SERVER['SCRIPT_FILENAME'] ?? ''), $_GET);
    if ($module !== null) { require_permission($user, $module); }
    return $user;
}
