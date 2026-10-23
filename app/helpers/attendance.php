<?php
declare(strict_types=1);
require_once __DIR__.'/students.php';

function attendance_statuses(): array
{
    return ['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa'];
}

function attendance_input(array $input): array
{
    $data=[];foreach(['judul','tanggal','angkatan','kelas_industri','deskripsi'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function attendance_errors(array $data): array
{
    $errors=[];
    foreach(['judul'=>190,'kelas_industri'=>150,'deskripsi'=>5000] as $key=>$max){if(!valid_text($data[$key],$max,$key!=='deskripsi')){$errors[]=ucfirst(str_replace('_',' ',$key)).' tidak valid (maksimal '.$max.' karakter).';}}
    if(!preg_match('/^(19|20|21)[0-9]{2}$/D',$data['angkatan'])){$errors[]='Angkatan harus berupa tahun masuk (1900–2199).';}
    $date=DateTimeImmutable::createFromFormat('!Y-m-d',$data['tanggal']);
    if(!$date||$date->format('Y-m-d')!==$data['tanggal']||$data['tanggal']<'1900-01-01'||$data['tanggal']>'2199-12-31'){$errors[]='Tanggal kegiatan tidak valid.';}
    return $errors;
}

function attendance_filters(array $input): array
{
    [$filters]=student_filters(array_intersect_key($input,array_flip(['q','angkatan','kelas_industri'])));
    unset($filters['urut']);
    // Use only fixed SQL fragments; every value remains a bound parameter.
    $where='1=1';$params=[];
    if($filters['q']!==''){$where.=" AND e.judul LIKE :q ESCAPE '!'";$params['q']='%'.str_replace(['!','%','_'],['!!','!%','!_'],$filters['q']).'%';}
    foreach(['angkatan','kelas_industri'] as $key){if($filters[$key]!==''){$where.=" AND e.$key=:$key";$params[$key]=$filters[$key];}}
    return [$filters,$where,$params];
}

function attendance_options(): array
{
    $years=panel_query('SELECT angkatan FROM siswa UNION SELECT angkatan FROM attendance_events ORDER BY angkatan DESC')->fetchAll(PDO::FETCH_COLUMN);
    $industries=panel_query("SELECT kelas_industri FROM siswa WHERE kelas_industri<>'' UNION SELECT kelas_industri FROM attendance_events ORDER BY kelas_industri")->fetchAll(PDO::FETCH_COLUMN);
    return [$years,$industries];
}

/** Snapshot the roster at creation so subsequent student edits do not rewrite history. */
function attendance_create(array $data,int $userId): int
{
    if($errors=attendance_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    db()->beginTransaction();
    try{
        $students=panel_query('SELECT id,nis,nama,kelas FROM siswa WHERE angkatan=:angkatan AND kelas_industri=:kelas_industri ORDER BY id FOR UPDATE',['angkatan'=>$data['angkatan'],'kelas_industri'=>$data['kelas_industri']])->fetchAll();
        if(!$students){throw new InvalidArgumentException('Belum ada siswa pada angkatan dan kelas industri tersebut. Isi Data Siswa terlebih dahulu.');}
        panel_query('INSERT INTO attendance_events(judul,tanggal,angkatan,kelas_industri,deskripsi,created_by) VALUES(:judul,:tanggal,:angkatan,:kelas_industri,:deskripsi,:created_by)',$data+['created_by'=>$userId]);
        $id=(int)db()->lastInsertId();
        foreach($students as $student){panel_query('INSERT INTO attendance_records(event_id,siswa_id,nis,nama,kelas) VALUES(:event_id,:siswa_id,:nis,:nama,:kelas)',['event_id'=>$id,'siswa_id'=>(int)$student['id'],'nis'=>$student['nis'],'nama'=>$student['nama'],'kelas'=>$student['kelas']]);}
        db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function attendance_locked_event(int $id,int $version): array
{
    $event=panel_query('SELECT * FROM attendance_events WHERE id=:id FOR UPDATE',['id'=>$id])->fetch();
    if(!$event){throw new InvalidArgumentException('Kegiatan tidak tersedia.');}
    if((int)$event['version']!==$version){throw new InvalidArgumentException('Data sudah diubah pengelola lain. Muat ulang halaman sebelum menyimpan kembali.');}
    return $event;
}

function attendance_edit(int $id,int $version,array $input): void
{
    db()->beginTransaction();
    try{
        $event=attendance_locked_event($id,$version);
        $data=attendance_input($input);$data['angkatan']=(string)$event['angkatan'];$data['kelas_industri']=$event['kelas_industri'];
        if($errors=attendance_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
        panel_query('UPDATE attendance_events SET judul=:judul,tanggal=:tanggal,deskripsi=:deskripsi,version=version+1 WHERE id=:id',['judul'=>$data['judul'],'tanggal'=>$data['tanggal'],'deskripsi'=>$data['deskripsi'],'id'=>$id]);db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

/** Save exactly the page displayed by the server; reject foreign or missing records. */
function attendance_save(int $id,int $version,int $page,array $records,int $userId): void
{
    db()->beginTransaction();
    try{
        attendance_locked_event($id,$version);
        $expected=panel_query('SELECT id FROM attendance_records WHERE event_id=:id ORDER BY nama,id LIMIT :limit OFFSET :offset',['id'=>$id,'limit'=>25,'offset'=>($page-1)*25])->fetchAll(PDO::FETCH_COLUMN);
        if(!$expected||count($expected)!==count($records)){throw new InvalidArgumentException('Daftar absensi tidak lengkap. Muat ulang halaman dan isi kembali.');}
        foreach($expected as $recordId){
            $record=$records[$recordId]??null;
            if(!is_array($record)||!isset($record['status'])||!is_string($record['status'])||($record['status']!==''&&!isset(attendance_statuses()[$record['status']]))){throw new InvalidArgumentException('Status absensi tidak valid.');}
            if(!isset($record['keterangan'])||!is_string($record['keterangan'])||!valid_text($record['keterangan'],500,false)){throw new InvalidArgumentException('Keterangan maksimal 500 karakter.');}
            panel_query('UPDATE attendance_records SET status=:status,keterangan=:keterangan,updated_by=:user,updated_at=CURRENT_TIMESTAMP WHERE id=:id AND event_id=:event',['status'=>$record['status']===''?null:$record['status'],'keterangan'=>trim($record['keterangan']),'user'=>$userId,'id'=>(int)$recordId,'event'=>$id]);
        }
        panel_query('UPDATE attendance_events SET version=version+1 WHERE id=:id',['id'=>$id]);db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function attendance_filter_form(array $filters,string $path): void
{
    [$years,$industries]=attendance_options();
    if($filters['angkatan']!==''&&!in_array($filters['angkatan'],array_map('strval',$years),true)){$years[]=$filters['angkatan'];}
    if($filters['kelas_industri']!==''&&!in_array($filters['kelas_industri'],$industries,true)){$industries[]=$filters['kelas_industri'];}
    ?>
    <form class="filter-form card" method="get"><label>Cari kegiatan<input name="q" maxlength="100" value="<?= e($filters['q']) ?>"></label><label>Angkatan<select name="angkatan"><option value="">Semua angkatan</option><?php foreach($years as $year):?><option value="<?= e((string)$year) ?>" <?= $filters['angkatan']===(string)$year?'selected':'' ?>><?= e((string)$year) ?></option><?php endforeach;?></select></label><label>Kelas industri<select name="kelas_industri"><option value="">Semua kelas industri</option><?php foreach($industries as $industry):?><option value="<?= e($industry) ?>" <?= $filters['kelas_industri']===$industry?'selected':'' ?>><?= e($industry) ?></option><?php endforeach;?></select></label><button type="submit">Terapkan</button><a href="<?= e(base_url($path)) ?>">Reset filter</a></form>
    <?php
}
