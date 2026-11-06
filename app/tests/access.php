<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/alumni-links.php';
require_once dirname(__DIR__).'/helpers/permissions.php';
function access_check(bool $condition,string $label):void
{
    if(!$condition){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;
}
$base=APP_ROOT.'/panel/';
access_check(panel_route_permission($base.'peserta/index.php',[])==='peserta','Data peserta membutuhkan izin peserta');
access_check(panel_route_permission($base.'akun/../peserta/index.php',[])==='peserta','Alias path tidak melewati izin modul');
access_check(panel_route_permission($base.'ekspor.php',['jenis'=>'rekap'])==='absensi','Ekspor lintas modul memakai izin data sumber');
access_check(panel_route_permission($base.'ekspor.php',['jenis'=>['peserta']])==='__denied','Jenis ekspor berbentuk array ditolak');
access_check(panel_route_permission($base.'ekspor.php',['jenis'=>'users'])==='__denied','Jenis ekspor tidak dikenal ditolak');
access_check(panel_route_permission($base.'modul-baru/index.php',[])==='__denied','Modul baru tidak otomatis membuka akses staf');
$token=bin2hex(random_bytes(32));
access_check(alumni_link_hash($token)===hash('sha256',$token),'Token valid diubah menjadi hash');
foreach(['',str_repeat('a',63),str_repeat('a',65),str_repeat('G',64),$token."\n"] as $bad){access_check(alumni_link_hash($bad)===null,'Token tidak sah ditolak');}
$owner=['nama_lengkap'=>'Pemilik','nisn'=>'00123','jurusan'=>'Teknik','tahun_lulus'=>2025];
$input=alumni_bound_input(['id'=>'999','nama_lengkap'=>'Palsu','nisn'=>'999','jurusan'=>'Palsu','tahun_lulus'=>'1900','status_kegiatan'=>'mencari_kerja','nomor_telepon'=>'081234567890','persetujuan_data'=>'1'],$owner);
foreach($owner as $key=>$value){access_check($input[$key]===(string)$value,'Identitas server mengalahkan POST: '.$key);}
access_check(!array_key_exists('id',$input)&&!array_key_exists('tracer_id',$input),'ID kiriman tidak masuk data pembaruan');
