<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';

/** Selalu baca ulang role dan status aktif dari database, termasuk pada handler. */
function require_auth(): array
{
    if (empty($_SESSION['user_id'])) {
        redirect('auth/login.php');
    }
    $statement = db()->prepare('SELECT id, nama, username, email, role, aktif, session_version FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $_SESSION['user_id']]);
    $user = $statement->fetch();
    if (!$user || !isset($_SESSION['auth_version']) || (int) $_SESSION['auth_version'] !== (int) $user['session_version'] || !(int) $user['aktif'] || !in_array($user['role'], ['admin', 'staf_bkk'], true)) {
        destroy_app_session();
        redirect('auth/login.php');
    }
    return $user;
}
