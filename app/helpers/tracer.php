<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/bootstrap.php';
require_once __DIR__.'/validation.php';

const TRACER_CONSENT_VERSION='tracer-v1';
const TRACER_CONSENT='Saya menyetujui pemrosesan identitas, kontak, dan informasi kegiatan yang saya isi oleh pengelola BKK untuk pendataan alumni, evaluasi layanan karir, serta tindak lanjut terkait kegiatan alumni. Data pribadi saya tidak ditampilkan di portal publik. Saya dapat menghubungi kontak BKK untuk meminta koreksi data.';

function tracer_statuses():array
{
    return ['bekerja'=>'Bekerja','kuliah'=>'Kuliah','wirausaha'=>'Berwirausaha','mencari_kerja'=>'Mencari kerja'];
}

function tracer_fields():array
{
    return ['nama_lengkap'=>'Nama lengkap','nisn'=>'NISN','jurusan'=>'Jurusan','tahun_lulus'=>'Tahun lulus','nomor_telepon'=>'Nomor WhatsApp','email'=>'Email','status_kegiatan'=>'Status kegiatan','nama_perusahaan'=>'Nama perusahaan','posisi_pekerjaan'=>'Posisi pekerjaan','lokasi_pekerjaan'=>'Lokasi pekerjaan','nama_kampus'=>'Nama kampus','program_studi'=>'Program studi','nama_usaha'=>'Nama usaha','bidang_usaha'=>'Bidang usaha','keterangan'=>'Keterangan'];
}

function tracer_input(array $input):array
{
    $data=[];foreach(tracer_fields() as $key=>$label){$data[$key]=input_text($input,$key);}
    $data['nomor_telepon']=preg_replace('/[\s().-]+/','',$data['nomor_telepon']);
    $active=match($data['status_kegiatan']){'bekerja'=>['nama_perusahaan','posisi_pekerjaan','lokasi_pekerjaan'],'kuliah'=>['nama_kampus','program_studi'],'wirausaha'=>['nama_usaha','bidang_usaha'],default=>[]};
    foreach(['nama_perusahaan','posisi_pekerjaan','lokasi_pekerjaan','nama_kampus','program_studi','nama_usaha','bidang_usaha'] as $field){if(!in_array($field,$active,true)){$data[$field]='';}}
    $data['persetujuan_data']=($input['persetujuan_data']??'')==='1';return $data;
}

function tracer_errors(array $data):array
{
    $errors=[];$year=(int)(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y');
    foreach(['nama_lengkap'=>150,'jurusan'=>150] as $field=>$max){if(!valid_text($data[$field],$max)){$errors[]=tracer_fields()[$field]." wajib diisi, maksimal $max karakter.";}}
    if($data['nisn']!==''&&!preg_match('/^[0-9]{1,20}$/D',$data['nisn'])){$errors[]='NISN opsional hanya boleh berisi angka, maksimal 20 digit.';}
    if(!preg_match('/^[0-9]{4}$/D',$data['tahun_lulus'])||(int)$data['tahun_lulus']<1900||(int)$data['tahun_lulus']>min($year,2155)){$errors[]="Tahun lulus harus antara 1900 dan $year.";}
    if(!preg_match('/^\+?[0-9]{8,15}$/D',$data['nomor_telepon'])){$errors[]='Nomor WhatsApp wajib berisi 8–15 digit; boleh diawali +.';}
    if($data['email']!==''&&(strlen($data['email'])>191||!filter_var($data['email'],FILTER_VALIDATE_EMAIL))){$errors[]='Email tidak valid; boleh dikosongkan.';}
    if(!array_key_exists($data['status_kegiatan'],tracer_statuses())){$errors[]='Pilih status kegiatan yang tersedia.';}
    $required=match($data['status_kegiatan']){'bekerja'=>['nama_perusahaan','posisi_pekerjaan'],'kuliah'=>['nama_kampus','program_studi'],'wirausaha'=>['nama_usaha','bidang_usaha'],default=>[]};
    foreach(['nama_perusahaan'=>200,'posisi_pekerjaan'=>150,'lokasi_pekerjaan'=>255,'nama_kampus'=>200,'program_studi'=>150,'nama_usaha'=>200,'bidang_usaha'=>150,'keterangan'=>5000] as $field=>$max){if(!valid_text($data[$field],$max,in_array($field,$required,true))){$errors[]=tracer_fields()[$field]." harus berupa teks valid, maksimal $max karakter".(in_array($field,$required,true)?' dan wajib diisi.':'.');}}
    if(!$data['persetujuan_data']){$errors[]='Persetujuan pemrosesan data wajib diberikan sebelum mengirim.';}return $errors;
}

function tracer_nonce():string
{
    $pending=$_SESSION['tracer_forms']??[];
    foreach($pending as $token=>$created){if($created<time()-7200){unset($pending[$token]);}}
    while(count($pending)>=5){array_shift($pending);}
    $token=bin2hex(random_bytes(32));$pending[$token]=time();$_SESSION['tracer_forms']=$pending;return $token;
}

function tracer_same_response(array $row,array $data):bool
{
    foreach(tracer_fields() as $field=>$label){if((string)($row[$field]??'')!==(string)$data[$field]){return false;}}
    return true;
}

function csv_safe_cell(mixed $value):string
{
    $text=(string)($value??'');
    if(preg_match('/^[\p{Z}\p{C}\s]*[=+\-@]/u',$text)||preg_match('/^[\t\r\n]/',$text)){$text="'".$text;}
    return $text;
}
