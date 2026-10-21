<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/vacancies.php';
$user=require_role('admin','staf_bkk');$id=null;$data=vacancy_input(['status'=>'draft'])+['gambar'=>null];
require __DIR__.'/form.php';
