<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/partners.php';
$user=require_role('admin','staf_bkk');require_post_csrf();$id=positive_id($_POST['id']??null);
if(!$id){panel_not_found();}
$rawVersion=$_POST['version']??null;$version=is_string($rawVersion)&&preg_match('/^[0-9]{1,10}$/D',$rawVersion)?(int)$rawVersion:null;
try{partner_delete($id,$version);panel_message('Mitra dihapus. Logo tetap tersedia di katalog.','panel/mitra/');}
catch(Throwable $exception){if($exception instanceof PDOException&&($exception->errorInfo[1]??0)===1451){$exception=new InvalidArgumentException('Mitra masih dipakai modul lain atau mempunyai riwayat. Gunakan status Nonaktif untuk mempertahankan riwayat.');}management_error($exception,'panel/mitra/');}
