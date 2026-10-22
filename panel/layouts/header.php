<?php
declare(strict_types=1);
if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); }
require_once __DIR__.'/icons.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — <?= e(app_config('name')) ?></title>
    <link rel="icon" type="image/jpeg" href="<?= e(asset_url('images/ppks.jpeg')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/panel.css')) ?>?v=<?= (int) filemtime(APP_ROOT . '/assets/css/panel.css') ?>">
    <script>try{document.documentElement.dataset.theme=localStorage.getItem('bkk.panel.theme')==='dark'?'dark':'light';document.documentElement.dataset.sidebar=localStorage.getItem('bkk.panel.sidebar')==='collapsed'?'collapsed':'expanded';}catch(e){}</script>
    <link rel="stylesheet" href="<?= e(asset_url('css/panel-theme.css')) ?>?v=<?= (int) filemtime(APP_ROOT . '/assets/css/panel-theme.css') ?>">
    <script src="<?= e(asset_url('js/panel.js')) ?>?v=<?= (int) filemtime(APP_ROOT . '/assets/js/panel.js') ?>" defer></script>
</head>
<body class="panel-page">
<a class="skip-link" href="#konten">Lewati ke konten</a>
