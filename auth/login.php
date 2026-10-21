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
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
    <title>Login Pengelola — <?= e(app_config('name')) ?></title>
    <link rel="icon" type="image/jpeg" href="<?= e(asset_url('images/ppks.jpeg')) ?>">
    <meta name="description" content="Login pengelola BKK SMK JAYA BUANA untuk Admin dan Staf BKK.">
    <link rel="stylesheet" href="<?= e(asset_url('css/login.css')) ?>">
</head>
<body>
<main class="login-shell">
    <section class="login-form-panel" aria-labelledby="login-title">
        <a class="login-brand" href="<?= e(base_url()) ?>" aria-label="Beranda BKK SMK JAYA BUANA"><span class="login-logos"><img src="<?= e(asset_url('images/jb.webp')) ?>" alt="Logo SMK JAYA BUANA" width="64" height="64"><img src="<?= e(asset_url('images/ppks.jpeg')) ?>" alt="Logo Pusat Pengembangan Karir Siswa" width="64" height="64"></span><span>BKK<span>SMK JAYA BUANA</span></span></a>
        <div class="login-content">
        <h1 id="login-title">Login Pengelola</h1>
        <p class="login-intro">Selamat datang kembali.</p>
        <?php if ($error): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form action="<?= e(base_url('auth/proses-login.php')) ?>" method="post">
        <?= csrf_field() ?>
        <label for="username">Username atau email</label>
        <input id="username" name="username" type="text" maxlength="191" autocomplete="username" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" maxlength="72" autocomplete="current-password" required>
        <button type="submit">Masuk</button>
        </form>
        <p class="login-return">Ingin melihat informasi BKK? <a href="<?= e(base_url()) ?>">Ke beranda</a></p>
        <p class="login-role">Khusus Admin dan Staf BKK/Guru BKK.</p>
        </div>
    </section>
    <aside class="login-visual" aria-label="Portal pengelola BKK">
        <img class="login-art" src="<?= e(asset_url('images/login-illustration.svg')) ?>" alt="" width="560" height="500">
        <div class="visual-caption"><h2>Satu ruang, banyak peluang.</h2><p>Kelola informasi karir, program, dan pendataan<br class="desktop-break"> alumni dalam satu tempat.</p></div>
        <div class="visual-dots" aria-hidden="true"><span></span><span class="active"></span><span></span></div>
    </aside>
</main>
</body>
</html>
