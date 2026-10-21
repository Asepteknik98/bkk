<?php
declare(strict_types=1);
if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); }
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — <?= e(app_config('name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/panel.css')) ?>">
</head>
<body>
<a class="skip-link" href="#konten">Lewati ke konten</a>
