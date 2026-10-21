<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/news.php';
$user = require_role('admin', 'staf_bkk');
$id = null;
$data = ['judul' => '', 'slug' => '', 'ringkasan' => '', 'isi' => '', 'status' => 'draft', 'unggulan' => 0, 'gambar' => null, 'media_id' => '', 'hapus_gambar' => false];
require __DIR__ . '/form.php';
