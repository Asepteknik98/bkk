<?php
declare(strict_types=1);
require_once __DIR__.'/pkl.php';

function industry_ready(): bool
{
    return (int)panel_query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('industry_classes','industry_class_speakers','industry_class_participants','industry_class_meetings')")->fetchColumn()===4;
}

function industry_require_ready(): void
{
    if(!industry_ready()){http_response_code(503);exit('Impor database/updates/021_industry_classes.sql untuk mengaktifkan Kelas Industri.');}
}

function industry_ids(mixed $values): array
{
    if(!is_array($values)||count($values)>1000){throw new InvalidArgumentException('Pilihan referensi tidak valid atau melebihi 1.000 data.');}
    $ids=[];foreach($values as $value){$id=positive_id($value);if(!$id){throw new InvalidArgumentException('ID peserta/pemateri tidak valid.');}$ids[$id]=$id;}return array_values($ids);
}

function industry_fields(): array
{
    return ['nama'=>['Nama kelas industri',190],'kelas_sasaran'=>['Kelas / kelompok sasaran',200],'catatan'=>['Catatan kelas (opsional)',10000]];
}

function industry_input(array $input): array
{
    $data=[];foreach(['nama','mitra_id','kelas_sasaran','rencana_pertemuan','catatan','status'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function industry_errors(array $data,array $speakers,array $participants): array
{
    $errors=[];
    foreach(industry_fields() as $key=>[$label,$max]){if(!valid_text($data[$key],$max,$key==='nama')){$errors[]=$label.' tidak valid atau terlalu panjang.';}}
    if(!positive_id($data['mitra_id'])){$errors[]='Pilih mitra industri.';}
    if(!ctype_digit($data['rencana_pertemuan'])||(int)$data['rencana_pertemuan']<1||(int)$data['rencana_pertemuan']>500){$errors[]='Rencana pertemuan harus 1–500.';}
    if(!$speakers){$errors[]='Pilih minimal satu pemateri.';}
    if($data['kelas_sasaran']===''&&!$participants){$errors[]='Isi kelas sasaran atau pilih peserta dari data siswa.';}
    if(!in_array($data['status'],['aktif','arsip'],true)){$errors[]='Status kelas tidak valid.';}return $errors;
}

function industry_find(int $id,bool $lock=false): array
{
    $row=panel_query('SELECT c.*,m.nama AS mitra_nama FROM industry_classes c JOIN mitra m ON m.id=c.mitra_id WHERE c.id=:id'.($lock?' FOR UPDATE':''),['id'=>$id])->fetch();
    if(!$row){throw new InvalidArgumentException('Kelas industri tidak ditemukan.');}return $row;
}

function industry_refs(int $id,string $type): array
{
    $sql=match($type){'speakers'=>'SELECT speaker_id FROM industry_class_speakers WHERE class_id=:id','participants'=>'SELECT siswa_id FROM industry_class_participants WHERE class_id=:id',default=>throw new InvalidArgumentException('Referensi tidak valid.')};
    return array_map('intval',panel_query($sql,['id'=>$id])->fetchAll(PDO::FETCH_COLUMN));
}

function industry_save(array $data,array $speakers,array $participants,?int $id,int $version,int $actor): int
{
    if($errors=industry_errors($data,$speakers,$participants)){throw new InvalidArgumentException(implode(' ',$errors));}
    db()->beginTransaction();
    try{
        $old=$id?industry_find($id,true):null;
        if($old&&(int)$old['version']!==$version){throw new InvalidArgumentException('Kelas sudah diubah pengelola lain. Muat ulang halaman.');}
        if(!panel_query("SELECT id FROM mitra WHERE id=:id AND jenis='perusahaan' LOCK IN SHARE MODE",['id'=>(int)$data['mitra_id']])->fetch()){
            throw new InvalidArgumentException('Pilih mitra jenis perusahaan yang terdaftar.');
        }
        foreach(['speakers'=>$speakers,'siswa'=>$participants] as $table=>$ids){
            foreach($ids as $ref){if(!panel_query('SELECT id FROM '.$table.' WHERE id=:id LOCK IN SHARE MODE',['id'=>$ref])->fetch()){throw new InvalidArgumentException('Peserta atau pemateri yang dipilih tidak ditemukan.');}}
        }
        $planned=(int)$data['rencana_pertemuan'];$previous=(int)($old['rencana_pertemuan']??0);
        if($old&&$planned<$previous){
            $changed=panel_query("SELECT id FROM industry_class_meetings WHERE class_id=:id AND urutan>:plan AND (status<>'direncanakan' OR tanggal IS NOT NULL OR jam IS NOT NULL OR lokasi<>'' OR materi<>'' OR catatan<>'' OR pemateri_id IS NOT NULL OR version<>1) LIMIT 1",['id'=>$id,'plan'=>$planned])->fetch();
            if($changed){throw new InvalidArgumentException('Rencana tidak dapat dikurangi karena pertemuan di luar rencana baru sudah diisi. Jadwal dan riwayat tetap dipertahankan.');}
            panel_query('DELETE FROM industry_class_meetings WHERE class_id=:id AND urutan>:plan',['id'=>$id,'plan'=>$planned]);
        }
        $data['rencana_pertemuan']=$planned;$data['mitra_id']=(int)$data['mitra_id'];
        if($id){$set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($data)));panel_query('UPDATE industry_classes SET '.$set.',version=version+1,updated_by=:actor WHERE id=:id',$data+['actor'=>$actor,'id'=>$id]);}
        else{panel_query('INSERT INTO industry_classes('.implode(',',array_keys($data)).',updated_by) VALUES(:'.implode(',:',array_keys($data)).',:actor)',$data+['actor'=>$actor]);$id=(int)db()->lastInsertId();}
        foreach(['industry_class_speakers'=>['speaker_id',$speakers],'industry_class_participants'=>['siswa_id',$participants]] as $table=>[$column,$ids]){
            panel_query('DELETE FROM '.$table.' WHERE class_id=:id',['id'=>$id]);
            foreach($ids as $ref){panel_query('INSERT INTO '.$table.'(class_id,'.$column.') VALUES(:id,:ref)',['id'=>$id,'ref'=>$ref]);}
        }
        for($number=$previous+1;$number<=$planned;$number++){
            panel_query("INSERT INTO industry_class_meetings(class_id,urutan,materi,catatan) VALUES(:id,:number,'','')",['id'=>$id,'number'=>$number]);
        }
        db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function industry_meeting_statuses(): array
{
    return ['direncanakan'=>'Belum dijadwalkan','dijadwalkan'=>'Dijadwalkan','terlaksana'=>'Terlaksana','ditunda'=>'Ditunda','dibatalkan'=>'Dibatalkan'];
}

function industry_meeting_input(array $input): array
{
    $data=[];foreach(['tanggal','jam','lokasi','materi','pemateri_id','status','catatan'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function industry_meeting_errors(array $data): array
{
    $errors=[];$scheduled=in_array($data['status'],['dijadwalkan','terlaksana','ditunda'],true);
    if(!isset(industry_meeting_statuses()[$data['status']])){$errors[]='Status pertemuan tidak valid.';}
    if(($scheduled||$data['tanggal']!=='')&&!pkl_date($data['tanggal'])){$errors[]='Tanggal pertemuan wajib valid.';}
    if(($scheduled||$data['jam']!=='')&&!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$data['jam'])){$errors[]='Jam harus HH:MM (WIB).';}
    if(!valid_text($data['lokasi'],200,$scheduled)){$errors[]='Lokasi wajib diisi untuk pertemuan terjadwal, maksimal 200 karakter.';}
    foreach(['materi','catatan'] as $key){if(!valid_text($data[$key],10000,false)){$errors[]=ucfirst($key).' maksimal 10.000 karakter.';}}
    if($data['pemateri_id']!==''&&!positive_id($data['pemateri_id'])){$errors[]='Pemateri tidak valid.';}
    $today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
    if($data['status']==='terlaksana'&&pkl_date($data['tanggal'])&&$data['tanggal']>$today){$errors[]='Pertemuan pada tanggal mendatang belum dapat ditandai Terlaksana.';}
    return $errors;
}

function industry_meeting_save(int $classId,int $id,int $version,array $data,int $actor): void
{
    if($errors=industry_meeting_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    db()->beginTransaction();
    try{
        industry_find($classId,true);
        $old=panel_query('SELECT * FROM industry_class_meetings WHERE id=:id AND class_id=:class FOR UPDATE',['id'=>$id,'class'=>$classId])->fetch();
        if(!$old||(int)$old['version']!==$version){throw new InvalidArgumentException('Pertemuan tidak ditemukan atau sudah berubah. Muat ulang halaman.');}
        if($data['pemateri_id']!==''&&!panel_query('SELECT id FROM speakers WHERE id=:id LOCK IN SHARE MODE',['id'=>(int)$data['pemateri_id']])->fetch()){
            throw new InvalidArgumentException('Pemateri tidak ditemukan.');
        }
        foreach(['tanggal','jam','pemateri_id'] as $key){$data[$key]=$data[$key]===''?null:($key==='pemateri_id'?(int)$data[$key]:$data[$key]);}
        $set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($data)));
        panel_query('UPDATE industry_class_meetings SET '.$set.',version=version+1,updated_by=:actor WHERE id=:id AND class_id=:class',$data+['actor'=>$actor,'id'=>$id,'class'=>$classId]);
        db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

/** Keep selected references visible even when search results are limited. */
function industry_participant_options(string $search,array $selected): array
{
    if(!valid_text($search,100,false)){throw new InvalidArgumentException('Pencarian peserta maksimal 100 karakter.');}
    $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';
    $rows=panel_query("SELECT id,nis,nama,kelas FROM siswa WHERE nama LIKE :nama ESCAPE '!' OR nis LIKE :nis ESCAPE '!' OR kelas LIKE :kelas ESCAPE '!' ORDER BY nama,id LIMIT 50",['nama'=>$pattern,'nis'=>$pattern,'kelas'=>$pattern])->fetchAll();
    $options=[];foreach($rows as $row){$options[$row['id']]=$row;}
    if($selected){
        $params=[];foreach($selected as $index=>$id){$params['ref'.$index]=$id;}
        foreach(panel_query('SELECT id,nis,nama,kelas FROM siswa WHERE id IN (:'.implode(',:',array_keys($params)).') ORDER BY nama,id',$params)->fetchAll() as $row){$options[$row['id']]=$row;}
    }
    return array_values($options);
}
