<?php
declare(strict_types=1);
require_once __DIR__.'/pkl.php';

function pkl_schedules_ready(): bool
{
    return (int)panel_query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('pkl_schedules','pkl_business_trips')")->fetchColumn()===2;
}

function pkl_schedules_require_ready(): void
{
    pkl_require_ready();
    if(!pkl_schedules_ready()){http_response_code(503);exit('Impor database/updates/020_pkl_schedules_travel.sql untuk mengaktifkan jadwal dan perjalanan dinas.');}
}

function pkl_schedule_types(): array
{
    return ['monitoring'=>'Monitoring mingguan','orang_tua'=>'Pertemuan orang tua / wali','sidang'=>'Sidang PKL'];
}

function pkl_schedule_statuses(): array
{
    return ['dijadwalkan'=>'Dijadwalkan','selesai'=>'Selesai','ditunda'=>'Ditunda','dibatalkan'=>'Dibatalkan'];
}

function pkl_schedule_fields(): array
{
    return ['tanggal'=>['Tanggal',10],'jam'=>['Jam (WIB)',5],'petugas'=>['Guru bertugas / pembimbing / penguji',250],
        'tujuan'=>['Tempat tujuan / lokasi',190],'alamat_lengkap'=>['Alamat lengkap',5000],
        'orang_tua_wali'=>['Nama orang tua / wali atau kelompok undangan',200],'agenda'=>['Agenda / keperluan',5000],
        'status'=>['Status',20],'hasil_monitoring'=>['Hasil monitoring',10000],'tindak_lanjut'=>['Tindak lanjut',10000]];
}

function pkl_schedule_input(array $input): array
{
    $data=['jenis'=>input_text($input,'jenis')];foreach(pkl_schedule_fields() as $key=>$meta){$data[$key]=input_text($input,$key);}
    if($data['jenis']!=='orang_tua'){$data['orang_tua_wali']='';}
    if($data['jenis']!=='monitoring'){$data['hasil_monitoring']='';$data['tindak_lanjut']='';}
    return $data;
}

function pkl_schedule_errors(array $data,array $placement): array
{
    $errors=[];
    if(!isset(pkl_schedule_types()[$data['jenis']])){$errors[]='Jenis jadwal tidak valid.';}
    foreach(pkl_schedule_fields() as $key=>[$label,$max]){
        $required=in_array($key,['tanggal','jam','petugas','tujuan','alamat_lengkap','agenda','status'],true)
            ||($data['jenis']==='monitoring'&&$data['status']==='selesai'&&$key==='hasil_monitoring');
        if(!valid_text($data[$key],$max,$required)){$errors[]=$label.' tidak valid atau belum diisi.';}
    }
    if(!pkl_date($data['tanggal'])){$errors[]='Tanggal jadwal tidak valid.';}
    elseif($data['jenis']==='monitoring'&&($data['tanggal']<$placement['tanggal_mulai']||$data['tanggal']>$placement['tanggal_selesai'])){$errors[]='Tanggal monitoring harus berada dalam periode PKL.';}
    if(!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$data['jam'])){$errors[]='Jam harus berupa HH:MM (00:00–23:59 WIB).';}
    if(!isset(pkl_schedule_statuses()[$data['status']])){$errors[]='Status jadwal tidak valid.';}
    return $errors;
}

function pkl_schedule_find(int $id): array
{
    $row=panel_query('SELECT * FROM pkl_schedules WHERE id=:id',['id'=>$id])->fetch();
    if(!$row){throw new InvalidArgumentException('Jadwal tidak ditemukan.');}
    $row['jam']=substr($row['jam'],0,5);return $row;
}

function pkl_schedule_save(int $placementId,?int $id,int $version,array $data,int $actor): int
{
    db()->beginTransaction();
    try{
        $placement=pkl_find($placementId,true);
        if($id){
            $old=panel_query('SELECT * FROM pkl_schedules WHERE id=:id AND placement_id=:placement FOR UPDATE',['id'=>$id,'placement'=>$placementId])->fetch();
            if(!$old||(int)$old['version']!==$version){throw new InvalidArgumentException('Jadwal tidak ditemukan atau sudah berubah. Muat ulang halaman.');}
            if($old['jenis']!==$data['jenis']){throw new InvalidArgumentException('Jenis jadwal tidak dapat diganti. Buat jadwal baru.');}
        }
        if($errors=pkl_schedule_errors($data,$placement)){throw new InvalidArgumentException(implode(' ',$errors));}
        $params=$data+['placement'=>$placementId,'actor'=>$actor];
        if($id){
            $set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($data)));
            panel_query('UPDATE pkl_schedules SET '.$set.',version=version+1,updated_by=:actor WHERE id=:id AND placement_id=:placement',$params+['id'=>$id]);
        }else{
            panel_query('INSERT INTO pkl_schedules(placement_id,'.implode(',',array_keys($data)).',updated_by) VALUES(:placement,:'.implode(',:',array_keys($data)).',:actor)',$params);$id=(int)db()->lastInsertId();
        }
        db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

/** Shared exact placement-period and calendar-week filters; no cost tables queried. */
function pkl_schedule_filters(array $input,bool $travel=false): array
{
    $filters=[];foreach(['periode'=>21,'minggu'=>10,'placement_id'=>18,'jenis'=>20] as $key=>$max){
        if(isset($input[$key])&&!is_string($input[$key])){throw new InvalidArgumentException('Filter jadwal tidak valid.');}
        $filters[$key]=input_text($input,$key);if(!valid_text($filters[$key],$max,false)){throw new InvalidArgumentException('Filter jadwal terlalu panjang.');}
    }
    $where='1=1';$params=[];
    if($filters['periode']!==''){
        $dates=explode('_',$filters['periode']);
        if(count($dates)!==2||!pkl_date($dates[0])||!pkl_date($dates[1])||$dates[1]<$dates[0]){throw new InvalidArgumentException('Periode PKL tidak valid.');}
        $where.=' AND p.tanggal_mulai=:mulai AND p.tanggal_selesai=:selesai';$params+=['mulai'=>$dates[0],'selesai'=>$dates[1]];
    }
    if($filters['placement_id']!==''){
        $id=positive_id($filters['placement_id']);if(!$id){throw new InvalidArgumentException('Penempatan tidak valid.');}
        $where.=' AND a.placement_id=:placement';$params['placement']=$id;
    }
    if($filters['minggu']!==''){
        if(!pkl_date($filters['minggu'])){throw new InvalidArgumentException('Tanggal minggu tidak valid.');}
        $monday=(new DateTimeImmutable($filters['minggu']))->modify('monday this week');
        $where.=' AND a.tanggal BETWEEN :senin AND :minggu';$params+=['senin'=>$monday->format('Y-m-d'),'minggu'=>$monday->modify('+6 days')->format('Y-m-d')];
    }
    if(!$travel&&$filters['jenis']!==''){
        if(!isset(pkl_schedule_types()[$filters['jenis']])){throw new InvalidArgumentException('Jenis jadwal tidak valid.');}
        $where.=' AND a.jenis=:jenis';$params['jenis']=$filters['jenis'];
    }
    if($travel){$filters['jenis']='';}
    return [$filters,$where,$params];
}

function pkl_period_options(): array
{
    return panel_query('SELECT DISTINCT tanggal_mulai,tanggal_selesai FROM pkl_placements ORDER BY tanggal_mulai DESC,tanggal_selesai DESC')->fetchAll();
}

/** Money remains a decimal string throughout input/storage/export. No float rounding. */
function pkl_money(string $raw): ?string
{
    $raw=str_replace(',','.',trim($raw));
    if(!preg_match('/^(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?$/D',$raw)){return null;}
    $parts=explode('.',$raw);return $parts[0].'.'.str_pad($parts[1]??'',2,'0');
}

function pkl_rupiah(string $raw): string
{
    $parts=explode('.',$raw);return 'Rp '.preg_replace('/\B(?=(\d{3})+(?!\d))/','.',$parts[0]).','.str_pad($parts[1]??'',2,'0');
}

function pkl_trip_input(array $input): array
{
    $data=[];foreach(['monitoring_id','tujuan','petugas','tanggal','nominal','sumber_biaya'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function pkl_trip_errors(array $data): array
{
    $errors=[];
    foreach(['tujuan'=>190,'petugas'=>250] as $key=>$max){if(!valid_text($data[$key],$max)){$errors[]=ucfirst($key).' wajib diisi, maksimal '.$max.' karakter.';}}
    if(!pkl_date($data['tanggal'])){$errors[]='Tanggal perjalanan tidak valid.';}
    if(pkl_money($data['nominal'])===null){$errors[]='Nominal harus 0–9999999999999,99; tanpa pemisah ribuan, maksimal dua desimal.';}
    if(!in_array($data['sumber_biaya'],['sekolah','pribadi'],true)){$errors[]='Sumber biaya harus sekolah atau pribadi.';}
    if($data['monitoring_id']!==''&&!positive_id($data['monitoring_id'])){$errors[]='Kunjungan monitoring tidak valid.';}
    return $errors;
}

function pkl_trip_save(int $placementId,?int $id,int $version,array $data,int $actor): int
{
    if($errors=pkl_trip_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    db()->beginTransaction();
    try{
        pkl_find($placementId,true);
        if($id){
            $old=panel_query('SELECT version FROM pkl_business_trips WHERE id=:id AND placement_id=:placement FOR UPDATE',['id'=>$id,'placement'=>$placementId])->fetchColumn();
            if(!$old||(int)$old!==$version){throw new InvalidArgumentException('Perjalanan tidak ditemukan atau sudah berubah. Muat ulang halaman.');}
        }
        if($data['monitoring_id']!==''){
            $visit=panel_query("SELECT id FROM pkl_schedules WHERE id=:id AND placement_id=:placement AND jenis='monitoring' FOR UPDATE",['id'=>(int)$data['monitoring_id'],'placement'=>$placementId])->fetchColumn();
            if(!$visit){throw new InvalidArgumentException('Pilih monitoring dari penempatan PKL yang sama.');}
        }
        $data['monitoring_id']=$data['monitoring_id']===''?null:(int)$data['monitoring_id'];$data['nominal']=pkl_money($data['nominal']);
        $params=$data+['placement'=>$placementId,'actor'=>$actor];
        if($id){
            $set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($data)));
            panel_query('UPDATE pkl_business_trips SET '.$set.',version=version+1,updated_by=:actor WHERE id=:id AND placement_id=:placement',$params+['id'=>$id]);
        }else{
            panel_query('INSERT INTO pkl_business_trips(placement_id,'.implode(',',array_keys($data)).',updated_by) VALUES(:placement,:'.implode(',:',array_keys($data)).',:actor)',$params);$id=(int)db()->lastInsertId();
        }
        db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}
