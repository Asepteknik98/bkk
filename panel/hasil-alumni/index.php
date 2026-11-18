<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/careers.php';
$user=require_role('admin','staf_bkk');panel_start($user,'Hasil seleksi dan kerja alumni','hasil-alumni');
require APP_ROOT.'/app/layouts/career-dashboard.php';panel_end($user);
