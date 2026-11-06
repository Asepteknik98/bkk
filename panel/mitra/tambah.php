<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/partners.php';
$user=require_role('admin','staf_bkk'); $id=null;
$kind=($_GET['jenis']??'')==='kampus'?'kampus':'perusahaan';
$data=['nama'=>'','jenis'=>$kind,'logo'=>null,'website'=>'','deskripsi'=>'','aktif'=>1,'version'=>0]+partner_profile_input(['klaster'=>$kind==='perusahaan'?'dalam_negeri':'','negara'=>'Indonesia']);
require __DIR__.'/form.php';
