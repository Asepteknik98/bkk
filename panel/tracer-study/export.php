<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');[$filters,$where,$params]=tracer_filters($_GET);
$fields=['id'=>'ID respons']+tracer_fields()+['persetujuan_data'=>'Persetujuan data','persetujuan_at'=>'Waktu persetujuan','versi_persetujuan'=>'Versi persetujuan','created_at'=>'Dikirim','updated_at'=>'Diperbarui'];
db()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
$statement=panel_query('SELECT '.implode(',',array_keys($fields)).' FROM tracer_responses WHERE '.$where.' ORDER BY tahun_lulus DESC,nama_lengkap ASC,id DESC',$params);
session_write_close();
header('Content-Type: text/csv; charset=UTF-8');header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');
header('Content-Disposition: attachment; filename="tracer-study-'.date('Ymd-His').'.csv"');
$output=fopen('php://output','wb');fwrite($output,"\xEF\xBB\xBF");fputcsv($output,array_values($fields),',','"','');
while($row=$statement->fetch()){
    $cells=[];foreach($fields as $field=>$label){$value=$field==='status_kegiatan'?(tracer_statuses()[$row[$field]]??$row[$field]):$row[$field];$cells[]=csv_safe_cell($value);}
    fputcsv($output,$cells,',','"','');
}
fclose($output);
