<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
$id = positive_id($_GET['id'] ?? null);
$data = $id ? panel_query('SELECT * FROM berita WHERE id=:id', ['id' => $id])->fetch() : false;
if (!$data) { panel_not_found(); }
$data += ['media_id' => '', 'hapus_gambar' => false];
require __DIR__ . '/form.php';
