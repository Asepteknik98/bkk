<?php
declare(strict_types=1);
require_once __DIR__.'/employment.php';
require_once __DIR__.'/alumni-links.php';

function career_can(array $user): bool
{
    return user_can($user,'hasil-alumni')&&user_can($user,'tracer-study');
}

function career_result_labels(): array
{
    return ['belum_mengisi'=>'Belum Mengisi','dalam_proses'=>'Dalam Proses','lolos'=>'Lolos','tidak_lolos'=>'Tidak Lolos'];
}

function career_result_label(array $row): string
{
    if(!empty($row['seleksi_selesai'])&&!in_array($row['hasil'],['lolos','tidak_lolos'],true)){return 'Belum Mengisi';}
    return career_result_labels()[$row['hasil']]??'Belum Mengisi';
}

function career_selection_errors(string $result,bool $finished): array
{
    if(!in_array($result,['dalam_proses','lolos','tidak_lolos'],true)){return ['Pilih hasil seleksi Anda.'];}
    if($finished&&!in_array($result,['lolos','tidak_lolos'],true)){return ['Proses seleksi sudah selesai. Alumni wajib mengisi Lolos atau Tidak Lolos.'];}return [];
}

function career_work_input(array $input): array
{
    $data=[];foreach(['status_kerja','kerja_perusahaan','kerja_posisi','tanggal_mulai_kerja','hubungan_kerja_id'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function career_work_errors(array $data): array
{
    $errors=[];
    if(!in_array($data['status_kerja'],['belum_bekerja','sudah_bekerja'],true)){$errors[]='Pilih status kerja.';}
    if($data['status_kerja']==='sudah_bekerja'){
        if(!valid_text($data['kerja_perusahaan'],200)||!valid_text($data['kerja_posisi'],200)){$errors[]='Perusahaan dan posisi kerja wajib diisi, maksimal 200 karakter.';}
        $date=job_deadline($data['tanggal_mulai_kerja']);$today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
        if(!$date||$data['tanggal_mulai_kerja']<'1900-01-01'||$data['tanggal_mulai_kerja']>$today){$errors[]='Tanggal mulai bekerja harus valid dan tidak di masa mendatang.';}
        if(!positive_id($data['hubungan_kerja_id'])){$errors[]='Pilih jenis hubungan kerja Anda.';}
    }
    return $errors;
}

function career_work_form_data(int $tracerId,array $owner=[]): array
{
    $row=panel_query('SELECT * FROM alumni_work_profiles WHERE tracer_id=:id',['id'=>$tracerId])->fetch();
    return ['status_kerja'=>$row['status_kerja']??(($owner['status_kegiatan']??'')==='bekerja'?'sudah_bekerja':'belum_bekerja'),
        'kerja_perusahaan'=>$row['perusahaan']??$owner['nama_perusahaan']??'','kerja_posisi'=>$row['posisi']??$owner['posisi_pekerjaan']??'',
        'tanggal_mulai_kerja'=>$row['tanggal_mulai']??'','hubungan_kerja_id'=>isset($row['employment_type_id'])?(string)$row['employment_type_id']:''];
}

/** Caller holds the alumni row lock and transaction. */
function career_work_save_locked(int $tracerId,array $data): void
{
    if($errors=career_work_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    $old=panel_query('SELECT * FROM alumni_work_profiles WHERE tracer_id=:id FOR UPDATE',['id'=>$tracerId])->fetch();
    if($data['status_kerja']==='sudah_bekerja'){
        $type=employment_validate_type((int)$data['hubungan_kerja_id'],isset($old['employment_type_id'])?(int)$old['employment_type_id']:null);
        $values=['company'=>$data['kerja_perusahaan'],'position'=>$data['kerja_posisi'],'start'=>$data['tanggal_mulai_kerja'],'type'=>$type['id']];
    }else{
        // Preserve the previous employer fields as history of the last reported job.
        $values=['company'=>$old['perusahaan']??'','position'=>$old['posisi']??'','start'=>$old['tanggal_mulai']??null,'type'=>$old['employment_type_id']??null];
    }
    panel_query('INSERT INTO alumni_work_profiles(tracer_id,status_kerja,perusahaan,posisi,tanggal_mulai,employment_type_id,updated_at) VALUES(:id,:status,:company,:position,:start,:type,UTC_TIMESTAMP(6)) ON DUPLICATE KEY UPDATE status_kerja=VALUES(status_kerja),perusahaan=VALUES(perusahaan),posisi=VALUES(posisi),tanggal_mulai=VALUES(tanggal_mulai),employment_type_id=VALUES(employment_type_id),updated_at=VALUES(updated_at)',$values+['id'=>$tracerId,'status'=>$data['status_kerja']]);
}

function career_form_key(string $hash): string
{
    $key=bin2hex(random_bytes(24));$_SESSION['career_form']= ['key'=>$key,'hash'=>$hash,'expires'=>time()+7200];return $key;
}

function career_check_form(string $key,string $hash): void
{
    $form=$_SESSION['career_form']??[];
    if(!is_string($form['key']??null)||!is_string($form['hash']??null)||($form['expires']??0)<time()||!hash_equals($form['key'],$key)||!hash_equals($form['hash'],$hash)){
        http_response_code(403);exit('Formulir tidak sesuai tautan alumni. Muat ulang formulir yang benar.');
    }
}

/** Caller locks tracer_responses; only explicit assignments bind legacy applications. */
function career_assign_locked(array $owner,int $jobId): int
{
    $exists=panel_query('SELECT lamaran_id FROM alumni_application_results WHERE tracer_id=:tracer AND lowongan_id=:job',['tracer'=>$owner['id'],'job'=>$jobId])->fetchColumn();
    if($exists){return (int)$exists;}
    $job=panel_query("SELECT * FROM lowongan WHERE id=:id AND status='published' LOCK IN SHARE MODE",['id'=>$jobId])->fetch();
    if(!$job){throw new InvalidArgumentException('Pilih lowongan yang dipublikasikan.');}
    panel_query("INSERT INTO lamaran(lowongan_id,posisi,perusahaan,nama,email,telepon,pendidikan,alamat,pesan,submission_key,status) VALUES(:job,:posisi,:perusahaan,:nama,:email,:telepon,:pendidikan,'','Pencatatan lamaran melalui tautan alumni.',:key,'baru')",[
        'job'=>$jobId,'posisi'=>$job['posisi'],'perusahaan'=>$job['perusahaan'],'nama'=>$owner['nama_lengkap'],'email'=>$owner['email']??'','telepon'=>$owner['nomor_telepon']??'',
        'pendidikan'=>'Alumni SMK - '.($owner['jurusan']??''),'key'=>bin2hex(random_bytes(32)),
    ]);
    $id=(int)db()->lastInsertId();
    panel_query('INSERT INTO alumni_application_results(lamaran_id,tracer_id,lowongan_id,updated_at) VALUES(:id,:tracer,:job,UTC_TIMESTAMP())',['id'=>$id,'tracer'=>$owner['id'],'job'=>$jobId]);return $id;
}

function career_assign(int $tracerId,int $jobId): void
{
    db()->beginTransaction();try{
        $owner=panel_query('SELECT * FROM tracer_responses WHERE id=:id FOR UPDATE',['id'=>$tracerId])->fetch();if(!$owner){throw new InvalidArgumentException('Alumni tidak ditemukan.');}
        career_assign_locked($owner,$jobId);db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function career_save_link(string $hash,?int $applicationId,?int $jobId,string $result,bool $finished,int $version,array $work): void
{
    if($errors=career_work_errors($work)){throw new InvalidArgumentException(implode(' ',$errors));}
    db()->beginTransaction();try{
        $owner=alumni_link_find($hash,true);if(!$owner){throw new InvalidArgumentException('Tautan tidak berlaku lagi.');}
        if(!$applicationId){if(!$jobId){throw new InvalidArgumentException('Pilih lowongan untuk mencatat lamaran.');}$applicationId=career_assign_locked($owner,$jobId);}
        $row=panel_query('SELECT * FROM alumni_application_results WHERE lamaran_id=:id AND tracer_id=:tracer FOR UPDATE',['id'=>$applicationId,'tracer'=>$owner['tracer_id']])->fetch();
        if(!$row){throw new InvalidArgumentException('Lamaran tidak ditemukan pada alumni pemilik tautan.');}
        if($version>0&&(int)$row['version']!==$version){throw new InvalidArgumentException('Data seleksi sudah berubah. Muat ulang formulir.');}
        $finished=$finished||(bool)$row['seleksi_selesai']||in_array($result,['lolos','tidak_lolos'],true);
        if($errors=career_selection_errors($result,$finished)){throw new InvalidArgumentException(implode(' ',$errors));}
        career_work_save_locked((int)$owner['tracer_id'],$work);
        panel_query('UPDATE alumni_application_results SET hasil=:hasil,seleksi_selesai=:done,reported_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP(),version=version+1 WHERE lamaran_id=:id',['hasil'=>$result,'done'=>(int)$finished,'id'=>$applicationId]);
        $mapped=['dalam_proses'=>'diproses','lolos'=>'diterima','tidak_lolos'=>'ditolak'];panel_query('UPDATE lamaran SET status=:status WHERE id=:id',['status'=>$mapped[$result],'id'=>$applicationId]);
        panel_query('UPDATE alumni_form_links SET used_at=UTC_TIMESTAMP() WHERE id=:id',['id'=>$owner['link_id']]);db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function career_application_guard(array $user,int $id): bool
{
    $linked=career_application_links_ready()&&(bool)panel_query('SELECT lamaran_id FROM alumni_application_results WHERE lamaran_id=:id',['id'=>$id])->fetchColumn();
    if($linked){require_permission($user,'hasil-alumni');}return $linked;
}

function career_legacy_visibility(array $user): string
{
    return career_application_links_ready()&&!career_can($user)?' AND NOT EXISTS (SELECT 1 FROM alumni_application_results ar WHERE ar.lamaran_id=lamaran.id)':'';
}

function career_application_links_ready(): bool
{
    // Existing private application links stay protected even if another new table is unavailable.
    return (bool)panel_query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='alumni_application_results'")->fetchColumn();
}

/** Internal only: unique NISN when supplied; otherwise a separate tracer record. */
function career_dashboard(string $year=''): array
{
    $people=panel_query('SELECT id,nisn,nama_lengkap,jurusan,tahun_lulus FROM tracer_responses ORDER BY id')->fetchAll();$families=[];$keys=[];
    foreach($people as $person){$key=trim((string)$person['nisn'])!==''?'nisn:'.trim($person['nisn']):'tracer:'.$person['id'];$keys[$person['id']]=$key;$families[$key]=['person'=>$person,'applications'=>[],'work'=>null];}
    foreach(panel_query('SELECT r.*,l.posisi,l.perusahaan FROM alumni_application_results r JOIN lamaran l ON l.id=r.lamaran_id ORDER BY r.updated_at,r.lamaran_id') as $row){$key=$keys[$row['tracer_id']]??null;if($key!==null){$families[$key]['applications'][]=$row;}}
    foreach(panel_query('SELECT w.*,e.nama AS jenis FROM alumni_work_profiles w LEFT JOIN employment_types e ON e.id=w.employment_type_id ORDER BY w.updated_at,w.tracer_id') as $work){$key=$keys[$work['tracer_id']]??null;if($key!==null){$families[$key]['work']=$work;}}
    $today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');$total=0;$working=0;$passed=0;$rows=[];
    foreach($families as $family){if($year!==''&&(string)$family['person']['tahun_lulus']!==$year){continue;}$total++;
        $work=$family['work'];$started=$work&&$work['status_kerja']==='sudah_bekerja'&&job_deadline($work['tanggal_mulai'])&&$work['tanggal_mulai']<=$today&&$work['perusahaan']!==''&&$work['posisi']!==''&&$work['employment_type_id'];
        if($started){$working++;}if(array_filter($family['applications'],static fn($row)=>$row['hasil']==='lolos')){$passed++;}
        $family['started']=(bool)$started;$rows[]=$family;
    }
    usort($rows,static fn($a,$b)=>strcasecmp($a['person']['nama_lengkap'],$b['person']['nama_lengkap'])?:((int)$a['person']['id']<=>(int)$b['person']['id']));
    return ['total'=>$total,'working'=>$working,'passed'=>$passed,'percentage'=>$total?round($working/$total*100,1):0,'rows'=>$rows];
}
