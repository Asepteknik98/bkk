<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/upload.php';
$user = require_role('admin', 'staf_bkk');
$id = positive_id($_GET['id'] ?? null);
$media = $id ? panel_query('SELECT * FROM media WHERE id=:id', ['id' => $id])->fetch() : false;
$path = $media ? managed_upload_path($media['path']) : null;
if (!$media || !$path) { panel_not_found(); }
session_write_close();
header('X-Content-Type-Options: nosniff');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="media-' . $id . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"; filename*=UTF-8\'\'' . rawurlencode($media['nama_asli']));
header('Content-Length: ' . filesize($path));
readfile($path);
