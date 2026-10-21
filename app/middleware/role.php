<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function require_role(string ...$roles): array
{
    $user = require_auth();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Anda tidak memiliki hak akses untuk halaman atau tindakan ini.');
    }
    return $user;
}
