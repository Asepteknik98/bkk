<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/panel.php';
require_once APP_ROOT.'/app/helpers/application-files.php';
$user = require_role('admin','staf_bkk');
$id = positive_id($_GET['id'] ?? '');
$file = $id ? panel_query('SELECT b.* FROM lamaran_berkas b INNER JOIN lamaran l ON l.id=b.lamaran_id WHERE b.id=:id',['id'=>$id])->fetch() : null;
if (!$file || !preg_match('/^[a-f0-9]{48}\.(pdf|jpg|png)$/D',$file['nama_simpan'])) { panel_not_found(); }
$path = application_file_directory().'/'.$file['nama_simpan'];
if (!is_file($path)) { panel_not_found(); }
session_write_close();
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Disposition: attachment; filename="berkas-lamaran-'.(int)$file['id'].'.'.pathinfo($path,PATHINFO_EXTENSION).'"; filename*=UTF-8\'\''.rawurlencode($file['nama_asli']));
header('Content-Length: '.filesize($path));
readfile($path);
