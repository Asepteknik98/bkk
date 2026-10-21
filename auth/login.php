<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
if (!empty($_SESSION['user_id'])) {
    redirect('panel/index.php');
}
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Pengelola — <?= e(app_config('name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/panel.css')) ?>">
</head>
<body class="login-page">
<main class="login-card">
    <p class="eyebrow"><?= e(app_config('name')) ?></p>
    <h1>Login Pengelola</h1>
    <p>Untuk Admin dan Staf BKK/Guru BKK.</p>
    <?php if ($error): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form action="<?= e(base_url('auth/proses-login.php')) ?>" method="post">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="191" autocomplete="username" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" maxlength="72" autocomplete="current-password" required>
        <button type="submit">Masuk</button>
    </form>
    <a href="<?= e(base_url()) ?>">Kembali ke beranda</a>
</main>
</body>
</html>
