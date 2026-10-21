<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/middleware/role.php';

function panel_query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
    }
    $statement->execute();
    return $statement;
}

function panel_start(array $user, string $pageTitle, string $activePanel): void
{
    require APP_ROOT . '/panel/layouts/header.php';
    require APP_ROOT . '/panel/layouts/navbar.php';
    echo '<div class="panel-shell">';
    require APP_ROOT . '/panel/layouts/sidebar.php';
    echo '<main id="konten" class="panel-content">';
    if (isset($_SESSION['panel_message'])) {
        echo '<p class="notice" role="status">' . e($_SESSION['panel_message']) . '</p>';
        unset($_SESSION['panel_message']);
    }
}

function panel_end(array $user): void
{
    echo '</main></div>';
    require APP_ROOT . '/panel/layouts/footer.php';
}

function panel_message(string $message, string $path): never
{
    $_SESSION['panel_message'] = $message;
    redirect($path, 303);
}

function positive_id(mixed $value): ?int
{
    if (!is_string($value) || !preg_match('/^[1-9][0-9]{0,17}$/D', $value)) {
        return null;
    }
    return (int) $value;
}

function panel_not_found(): never
{
    http_response_code(404);
    require APP_ROOT . '/404.php';
    exit;
}

/** Semua modul yang menulis referensi media harus memakai lock yang sama. */
function with_content_lock(callable $operation): mixed
{
    $name = 'bkk_content_' . substr(hash('sha256', app_config('database')['name']), 0, 32);
    if ((int) panel_query('SELECT GET_LOCK(:name, 5)', ['name' => $name])->fetchColumn() !== 1) {
        throw new RuntimeException('Data sedang diproses pengelola lain. Silakan coba kembali.');
    }
    try { return $operation(); }
    finally { panel_query('SELECT RELEASE_LOCK(:name)', ['name' => $name]); }
}
