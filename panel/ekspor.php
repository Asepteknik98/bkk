<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/exports.php';
$user=require_role('admin','staf_bkk');
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
$type=input_text($_GET,'jenis');
try{
    // Build the full workbook before sending headers, using one consistent database snapshot.
    db()->beginTransaction();
    $sheets=export_sheets($type,$_GET);
    excel_download('bkk-'.$type.'-'.date('Y-m-d').'.xlsx',$sheets);
    db()->commit();
}catch(InvalidArgumentException $exception){if(db()->inTransaction()){db()->rollBack();}http_response_code(400);exit(e($exception->getMessage()));}
catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
