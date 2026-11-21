<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
$user=require_role('admin','staf_bkk');
require_post_csrf();pkl_require_ready();
header('Content-Type: application/json; charset=UTF-8');header('Cache-Control: no-store');
try{
    $mode=input_text($_POST,'mode');
    if($mode==='stored'){
        $q=input_text($_POST,'q');if(!valid_text($q,190,false)){throw new InvalidArgumentException('Pencarian terlalu panjang.');}
        $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';
        $join=pkl_points_ready()?' LEFT JOIN pkl_location_points c ON c.placement_id=p.id ':'';
        $coords=pkl_points_ready()?'c.latitude,c.longitude':'NULL AS latitude,NULL AS longitude';
        $rows=panel_query("SELECT DISTINCT p.tempat_pkl AS name,p.alamat_lengkap AS address,$coords FROM pkl_placements p $join WHERE p.tempat_pkl LIKE :nama ESCAPE '!' OR p.alamat_lengkap LIKE :alamat ESCAPE '!' ORDER BY p.tempat_pkl LIMIT 20",['nama'=>$pattern,'alamat'=>$pattern])->fetchAll();
    }elseif(in_array($mode,['search','reverse'],true)){$rows=pkl_map_lookup($mode,$_POST);}
    else{throw new InvalidArgumentException('Pilihan pencarian tidak valid.');}
    echo json_encode(['results'=>$rows],JSON_THROW_ON_ERROR);
}catch(InvalidArgumentException $e){http_response_code(422);echo json_encode(['error'=>$e->getMessage()]);}
catch(Throwable $e){error_log('Peta PKL gagal: '.get_class($e));http_response_code(503);echo json_encode(['error'=>'Peta belum dapat diakses. Gunakan tempat tersimpan atau isi alamat manual.']);}
