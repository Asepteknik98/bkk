<?php
declare(strict_types=1);
require_once __DIR__.'/participants.php';
require_once __DIR__.'/pkl-locations.php';

function pkl_ready(): bool
{
    return (int)panel_query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('pkl_placements','pkl_journals','pkl_final_reports')")->fetchColumn()===3;
}

function pkl_require_ready(): void
{
    if(!pkl_ready()){http_response_code(503);exit('Modul PKL belum aktif. Impor database/updates/019_pkl.sql terlebih dahulu.');}
}

function pkl_date(string $value): bool
{
    if(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D',$value)){return false;}
    $date=DateTimeImmutable::createFromFormat('!Y-m-d',$value);
    return $date!==false&&$date->format('Y-m-d')===$value&&$value>='1900-01-01'&&$value<='2199-12-31';
}

function pkl_fields(): array
{
    return ['kelas'=>['Kelas',80], 'jurusan'=>['Jurusan',150], 'tanggal_mulai'=>['Mulai PKL',10],
        'tanggal_selesai'=>['Selesai PKL',10], 'tempat_pkl'=>['Tempat PKL',190],
        'alamat_lengkap'=>['Alamat lengkap tempat PKL',5000], 'guru_pembimbing'=>['Guru pembimbing (penugasan)',150],
        'pembimbing_industri'=>['Pembimbing industri',150], 'status'=>['Status',10]];
}

function pkl_input(array $input): array
{
    $data=['siswa_id'=>input_text($input,'siswa_id')];
    foreach(pkl_fields() as $key=>$meta){$data[$key]=input_text($input,$key);}
    $data['latitude']=input_text($input,'latitude');$data['longitude']=input_text($input,'longitude');
    return $data;
}

function pkl_errors(array $data): array
{
    $errors=[];
    if(!positive_id($data['siswa_id'])){$errors[]='Pilih siswa dari data peserta.';}
    foreach(pkl_fields() as $key=>[$label,$max]){
        if(!valid_text($data[$key],$max,!in_array($key,['kelas','jurusan'],true))){$errors[]=$label.' tidak valid atau terlalu panjang.';}
    }
    if($data['kelas']===''&&$data['jurusan']===''){$errors[]='Isi kelas atau jurusan siswa.';}
    if(!pkl_date($data['tanggal_mulai'])||!pkl_date($data['tanggal_selesai'])){$errors[]='Periode PKL harus berupa tanggal valid (1900–2199).';}
    elseif($data['tanggal_selesai']<$data['tanggal_mulai']){$errors[]='Tanggal selesai tidak boleh sebelum tanggal mulai.';}
    if(!in_array($data['status'],['aktif','selesai','arsip'],true)){$errors[]='Status PKL tidak valid.';}
    return $errors;
}

function pkl_find(int $id,bool $lock=false): array
{
    $row=panel_query('SELECT p.*,s.nama,s.nis FROM pkl_placements p JOIN siswa s ON s.id=p.siswa_id WHERE p.id=:id'.($lock?' FOR UPDATE':''),['id'=>$id])->fetch();
    if(!$row){throw new InvalidArgumentException('Penempatan PKL tidak ditemukan.');}return $row;
}

function pkl_save(array $data,?int $id,int $version,int $actor): int
{
    if($errors=pkl_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    $point=pkl_point_input($data);unset($data['latitude'],$data['longitude']);
    db()->beginTransaction();
    try{
        if($id){
            $old=pkl_find($id,true);
            if((int)$old['version']!==$version){throw new InvalidArgumentException('Penempatan sudah diubah pengelola lain. Muat ulang sebelum menyimpan.');}
            if((int)$old['siswa_id']!==(int)$data['siswa_id']){throw new InvalidArgumentException('Pemilik penempatan tidak dapat diganti. Buat penempatan baru untuk siswa lain.');}
            if(panel_query('SELECT id FROM pkl_journals WHERE placement_id=:id AND (tanggal<:mulai OR tanggal>:selesai) LIMIT 1',['id'=>$id,'mulai'=>$data['tanggal_mulai'],'selesai'=>$data['tanggal_selesai']])->fetch()){
                throw new InvalidArgumentException('Periode baru akan mengeluarkan jurnal yang sudah ada. Periksa periode dan jurnal terlebih dahulu.');
            }
            if(panel_query('SELECT placement_id FROM pkl_final_reports WHERE placement_id=:id AND tanggal_laporan<:mulai',['id'=>$id,'mulai'=>$data['tanggal_mulai']])->fetch()){
                throw new InvalidArgumentException('Tanggal mulai baru melewati tanggal laporan akhir yang tersimpan.');
            }
            $hasSchedules=(bool)panel_query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pkl_schedules'")->fetchColumn();
            if($hasSchedules&&panel_query("SELECT id FROM pkl_schedules WHERE placement_id=:id AND jenis='monitoring' AND (tanggal<:mulai OR tanggal>:selesai) LIMIT 1",['id'=>$id,'mulai'=>$data['tanggal_mulai'],'selesai'=>$data['tanggal_selesai']])->fetch()){
                throw new InvalidArgumentException('Periode baru akan mengeluarkan jadwal monitoring yang sudah ada. Periksa jadwal terlebih dahulu.');
            }
        }elseif(!panel_query('SELECT id FROM siswa WHERE id=:id',['id'=>(int)$data['siswa_id']])->fetch()){
            throw new InvalidArgumentException('Siswa tidak ditemukan pada data peserta.');
        }
        $data['siswa_id']=(int)$data['siswa_id'];
        if($id){
            $set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($data)));
            panel_query('UPDATE pkl_placements SET '.$set.',version=version+1,updated_by=:actor WHERE id=:id',$data+['actor'=>$actor,'id'=>$id]);
        }else{
            $keys=implode(',',array_keys($data));$params=implode(',:',array_keys($data));
            panel_query('INSERT INTO pkl_placements('.$keys.',created_by,updated_by) VALUES(:'.$params.',:creator,:editor)',$data+['creator'=>$actor,'editor'=>$actor]);$id=(int)db()->lastInsertId();
        }
        pkl_store_point($id,$point);
        db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function pkl_journal_errors(array $data,array $placement): array
{
    $errors=[];
    if(!pkl_date($data['tanggal'])||$data['tanggal']<$placement['tanggal_mulai']||$data['tanggal']>$placement['tanggal_selesai']){$errors[]='Tanggal jurnal harus berada dalam periode PKL.';}
    if(!valid_text($data['kegiatan'],10000)){$errors[]='Kegiatan wajib diisi, maksimal 10.000 karakter.';}
    if(!valid_text($data['catatan'],5000,false)){$errors[]='Catatan maksimal 5.000 karakter.';}
    return $errors;
}

function pkl_journal_change(int $placementId,?int $id,int $version,array $data,int $actor,bool $delete=false): void
{
    db()->beginTransaction();
    try{
        $placement=pkl_find($placementId,true);
        if($id){
            $old=panel_query('SELECT * FROM pkl_journals WHERE id=:id AND placement_id=:placement FOR UPDATE',['id'=>$id,'placement'=>$placementId])->fetch();
            if(!$old||(int)$old['version']!==$version){throw new InvalidArgumentException('Jurnal tidak ditemukan atau sudah berubah. Muat ulang halaman.');}
        }elseif($delete){throw new InvalidArgumentException('Pilih jurnal yang akan dihapus.');}
        if($delete){panel_query('DELETE FROM pkl_journals WHERE id=:id AND placement_id=:placement',['id'=>$id,'placement'=>$placementId]);}
        else{
            if($errors=pkl_journal_errors($data,$placement)){throw new InvalidArgumentException(implode(' ',$errors));}
            $params=$data+['placement'=>$placementId,'actor'=>$actor];
            if($id){panel_query('UPDATE pkl_journals SET tanggal=:tanggal,kegiatan=:kegiatan,catatan=:catatan,version=version+1,updated_by=:actor WHERE id=:id AND placement_id=:placement',$params+['id'=>$id]);}
            else{panel_query('INSERT INTO pkl_journals(placement_id,tanggal,kegiatan,catatan,updated_by) VALUES(:placement,:tanggal,:kegiatan,:catatan,:actor)',$params);}
        }
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function pkl_report_fields(): array
{
    return ['ringkasan'=>'Ringkasan pelaksanaan','hasil'=>'Hasil / kompetensi yang diperoleh','kendala'=>'Kendala (opsional)','saran'=>'Saran (opsional)','kesimpulan'=>'Kesimpulan'];
}

function pkl_report_errors(array $data,array $placement): array
{
    $errors=[];
    if(!pkl_date($data['tanggal_laporan'])||$data['tanggal_laporan']<$placement['tanggal_mulai']){$errors[]='Tanggal laporan harus valid dan tidak sebelum mulai PKL.';}
    foreach(pkl_report_fields() as $key=>$label){if(!valid_text($data[$key],10000,!in_array($key,['kendala','saran'],true))){$errors[]=$label.' tidak valid, maksimal 10.000 karakter.';}}
    return $errors;
}

function pkl_report_save(int $placementId,int $version,array $data,int $actor): void
{
    db()->beginTransaction();
    try{
        $placement=pkl_find($placementId,true);
        if($errors=pkl_report_errors($data,$placement)){throw new InvalidArgumentException(implode(' ',$errors));}
        $old=panel_query('SELECT version FROM pkl_final_reports WHERE placement_id=:id FOR UPDATE',['id'=>$placementId])->fetchColumn();
        if((int)$old!==$version){throw new InvalidArgumentException('Laporan sudah berubah. Muat ulang sebelum menyimpan.');}
        if($old){
            $set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($data)));
            panel_query('UPDATE pkl_final_reports SET '.$set.',version=version+1,updated_by=:actor WHERE placement_id=:id',$data+['actor'=>$actor,'id'=>$placementId]);
        }else{
            panel_query('INSERT INTO pkl_final_reports(placement_id,tanggal_laporan,ringkasan,hasil,kendala,saran,kesimpulan,updated_by) VALUES(:id,:tanggal_laporan,:ringkasan,:hasil,:kendala,:saran,:kesimpulan,:actor)',$data+['id'=>$placementId,'actor'=>$actor]);
        }
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

/** ISO calendar weeks (Monday–Sunday), including year boundaries; only recorded days. */
function pkl_weekly(array $journals): array
{
    $weeks=[];
    foreach($journals as $row){
        $date=new DateTimeImmutable($row['tanggal']);$monday=$date->modify('monday this week');$key=$monday->format('Y-m-d');
        if(!isset($weeks[$key])){$weeks[$key]=['minggu'=>$date->format('o-\WW'),'mulai'=>$key,'selesai'=>$monday->modify('+6 days')->format('Y-m-d'),'hari'=>0,'kegiatan'=>[],'catatan'=>[]];}
        $weeks[$key]['hari']++;$weeks[$key]['kegiatan'][]=$row['tanggal'].' — '.$row['kegiatan'];
        if($row['catatan']!==''){$weeks[$key]['catatan'][]=$row['tanggal'].' — '.$row['catatan'];}
    }
    ksort($weeks);return array_values($weeks);
}

function pkl_error(Throwable $exception): string
{
    if($exception instanceof InvalidArgumentException){return $exception->getMessage();}
    if($exception instanceof PDOException&&($exception->errorInfo[1]??0)===1062){return 'Data sudah ada: satu penempatan identik atau satu jurnal per tanggal. Gunakan Edit untuk memperbarui.';}
    error_log('Modul PKL gagal: '.get_class($exception));return 'Perubahan belum tersimpan. Silakan coba kembali.';
}


/** Revalidate a bounded selection on the server, never trust posted student metadata. */
function pkl_selected_students(mixed $ids, bool $lock = false): array
{
    if (!is_array($ids) || !$ids || count($ids) > 50) {
        throw new InvalidArgumentException('Pilih 1-50 siswa untuk penempatan bersama.');
    }
    $params = [];
    foreach ($ids as $value) {
        $id = positive_id($value);
        if (!$id) { throw new InvalidArgumentException('Pilihan siswa tidak valid.'); }
        $params['s'. $id] = $id;
    }
    $students = panel_query('SELECT id,nama,kelas,jurusan,tempat_pkl,tahun_pkl FROM siswa WHERE id IN ('.implode(',',array_map(static fn($key)=>':'.$key,array_keys($params))).') ORDER BY id'.($lock?' FOR UPDATE':''), $params)->fetchAll();
    if (count($students) !== count($params)) { throw new InvalidArgumentException('Ada siswa yang tidak tersedia. Pilih kembali siswa.'); }
    return $students;
}

/** All selected placements commit together; class/department remain student-specific. */
function pkl_save_batch(mixed $ids, array $input, int $actor): array
{
    db()->beginTransaction();
    try {
        $point=pkl_point_input($input);
        $students = pkl_selected_students($ids, true);
        $entries = [];
        foreach ($students as $student) {
            $entry = pkl_input($input);
            $entry['siswa_id'] = (string)$student['id'];
            $entry['kelas'] = (string)$student['kelas'];
            $entry['jurusan'] = (string)$student['jurusan'];
            if ($errors = pkl_errors($entry)) { throw new InvalidArgumentException($student['nama'].': '.implode(' ', $errors)); }
            if (panel_query('SELECT id FROM pkl_placements WHERE siswa_id=:siswa AND tanggal_mulai=:mulai AND tanggal_selesai=:selesai AND tempat_pkl=:tempat LIMIT 1', ['siswa'=>$student['id'],'mulai'=>$entry['tanggal_mulai'],'selesai'=>$entry['tanggal_selesai'],'tempat'=>$entry['tempat_pkl']])->fetchColumn()) {
                throw new InvalidArgumentException($student['nama'].' sudah memiliki penempatan dengan tempat dan periode yang sama. Tidak ada penempatan baru yang disimpan.');
            }
            $entries[] = $entry;
        }
        $saved = [];
        foreach ($entries as $entry) {
            unset($entry['latitude'],$entry['longitude']);
            $entry['siswa_id'] = (int)$entry['siswa_id'];
            $keys = implode(',', array_keys($entry));
            $values = implode(',:', array_keys($entry));
            panel_query('INSERT INTO pkl_placements('.$keys.',created_by,updated_by) VALUES(:'.$values.',:creator,:editor)', $entry+['creator'=>$actor,'editor'=>$actor]);
            $savedId=(int)db()->lastInsertId();pkl_store_point($savedId,$point);
            $saved[] = $savedId;
        }
        db()->commit();
        return $saved;
    } catch (Throwable $exception) {
        if (db()->inTransaction()) { db()->rollBack(); }
        throw $exception;
    }
}


/** Shared filters for the placement list and its Excel export. */
function pkl_list_filters(array $input): array
{
    foreach(['q','status'] as $key){if(isset($input[$key])&&!is_string($input[$key])){throw new InvalidArgumentException('Filter PKL tidak valid.');}}
    $q=input_text($input,'q');$status=input_text($input,'status');
    if(!valid_text($q,100,false)||!in_array($status,['','aktif','selesai','arsip'],true)){throw new InvalidArgumentException('Filter PKL tidak valid.');}
    $where='1=1';$params=[];
    if($q!==''){
        $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';
        $where.=" AND (s.nama LIKE :nama ESCAPE '!' OR s.nis LIKE :nis ESCAPE '!' OR p.tempat_pkl LIKE :tempat ESCAPE '!' OR p.guru_pembimbing LIKE :guru ESCAPE '!')";
        $params=['nama'=>$pattern,'nis'=>$pattern,'tempat'=>$pattern,'guru'=>$pattern];
    }
    if($status!==''){$where.=' AND p.status=:status';$params['status']=$status;}
    return [$q,$status,$where,$params];
}
