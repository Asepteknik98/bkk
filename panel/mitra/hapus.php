<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin','staf_bkk');require_post_csrf();$id=positive_id($_POST['id']??null);
if(!$id){panel_not_found();}
try{management_transaction(static function()use($id):void{panel_query('DELETE FROM mitra WHERE id=:id',['id'=>$id]);});panel_message('Mitra dihapus. Logo tetap tersedia di katalog.','panel/mitra/');}
catch(Throwable $exception){if($exception instanceof PDOException&&($exception->errorInfo[1]??0)===1451){$exception=new InvalidArgumentException('Mitra masih dipakai Kelas Industri atau mempunyai riwayat MoU. Gunakan status Nonaktif untuk mempertahankan riwayat.');}management_error($exception,'panel/mitra/');}
