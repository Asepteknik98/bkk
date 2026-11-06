<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/partners.php';
$user=require_role('admin','staf_bkk'); $id=positive_id($_GET['id']??null);
$data=false;if($id){try{$data=partner_find($id);}catch(InvalidArgumentException){panel_not_found();}}
if(!$data){panel_not_found();} require __DIR__.'/form.php';
