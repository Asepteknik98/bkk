<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Hanya CLI.');}
require_once dirname(__DIR__).'/helpers/tracer.php';
function tracer_check(bool $condition,string $message):void{if(!$condition){fwrite(STDERR,'Gagal: '.$message.PHP_EOL);exit(1);}}
$year=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y');
$base=['nama_lengkap'=>'FIXTURE UJI','jurusan'=>'JURUSAN UJI','tahun_lulus'=>$year,'nomor_telepon'=>'+62 812-3456-7890','status_kegiatan'=>'mencari_kerja','persetujuan_data'=>'1'];
$valid=tracer_input($base);tracer_check(tracer_errors($valid)===[],'kontak wajib, email/NISN opsional');
tracer_check($valid['nomor_telepon']==='+6281234567890','normalisasi nomor');
foreach([['tahun_lulus'=>(string)((int)$year+1)],['tahun_lulus'=>'1899'],['status_kegiatan'=>'lainnya'],['persetujuan_data'=>''],['nomor_telepon'=>'abc'],['status_kegiatan'=>'kuliah'],['status_kegiatan'=>'bekerja'],['status_kegiatan'=>'wirausaha']] as $bad){tracer_check(tracer_errors(tracer_input(array_replace($base,$bad)))!==[],'validasi status/tahun/persetujuan/detail');}
$cleared=tracer_input($base+['nama_perusahaan'=>'Tidak relevan','nama_kampus'=>'Tidak relevan','nama_usaha'=>'Tidak relevan']);
tracer_check($cleared['nama_perusahaan']===''&&$cleared['nama_kampus']===''&&$cleared['nama_usaha']==='','hapus detail status lain');
foreach(['=SUM(1,2)','+cmd','-1+2','@SUM(1)',"\t=SUM(1)","\r=SUM(1)",'  =SUM(1)',"\u{200B}=SUM(1)"] as $payload){tracer_check(str_starts_with(csv_safe_cell($payload),"'"),'formula injection');}
tracer_check(csv_safe_cell('0012345678')==='0012345678','teks biasa/angka tidak diubah');
echo "LULUS: validasi tracer, status dinamis di server, kontak opsional/wajib, dan CSV aman.\n";
