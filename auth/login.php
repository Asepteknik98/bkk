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
    <link rel="stylesheet" href="<?= e(asset_url('css/login.css')) ?>?v=<?= (int) filemtime(APP_ROOT . '/assets/css/login.css') ?>">
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
        <div class="password-field">
            <input id="password" name="password" type="password" maxlength="72" autocomplete="current-password" required>
            <button type="button" class="password-toggle" aria-label="Lihat password" aria-controls="password" aria-pressed="false" title="Lihat password">
                <svg class="icon-eye" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="icon-eye-off" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c6 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </button>
        </div>
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
<script>
(function () {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.password-toggle');
    if (!passwordInput || !toggleBtn) return;
    const eyeIcon = toggleBtn.querySelector('.icon-eye');
    const eyeOffIcon = toggleBtn.querySelector('.icon-eye-off');

    toggleBtn.addEventListener('click', function () {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        toggleBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
        const label = isHidden ? 'Sembunyikan password' : 'Lihat password';
        toggleBtn.setAttribute('aria-label', label);
        toggleBtn.setAttribute('title', label);
        if (eyeIcon && eyeOffIcon) {
            eyeIcon.hidden = isHidden;
            eyeOffIcon.hidden = !isHidden;
        }
    });
})();
</script>
</body>
</html>
