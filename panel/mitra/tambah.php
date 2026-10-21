<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin','staf_bkk'); $id=null;
$data=['nama'=>'','jenis'=>'perusahaan','logo'=>null,'website'=>'','deskripsi'=>'','aktif'=>1];
require __DIR__.'/form.php';
