<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/programs.php';
$user=require_role('admin','staf_bkk');$id=null;$category=input_text($_GET,'kategori');$data=program_input(['status'=>'draft','kategori'=>isset(program_categories()[$category])?$category:''])+['gambar'=>null];
require __DIR__.'/form.php';
