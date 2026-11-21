<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/tracer-management.php';
function check_tracer_management(bool $ok,string $message): void
{
    if(!$ok){throw new RuntimeException($message);}echo 'PASS '.$message.PHP_EOL;
}
$row=['id'=>1,'nama_lengkap'=>'Alumni uji','updated_at'=>'2026-01-01 10:00:00'];
$revision=tracer_revision($row);tracer_check_revision($row,$revision);
check_tracer_management($revision===tracer_revision(array_reverse($row,true)),'Revision stabil terhadap urutan kolom');
$row['nama_lengkap']='Koreksi pada detik yang sama';
try{tracer_check_revision($row,$revision);throw new RuntimeException('Konflik tidak ditolak');}
catch(InvalidArgumentException){check_tracer_management(true,'Perubahan bersamaan ditolak meski timestamp sama');}
$data=tracer_input(['nama_lengkap'=>'Alumni uji','jurusan'=>'Jurusan lama','tahun_lulus'=>'2021','nomor_telepon'=>'081234567890','status_kegiatan'=>'kuliah','nama_kampus'=>'Kampus uji','program_studi'=>'Prodi uji','nama_perusahaan'=>'Data status lama','persetujuan_data'=>'1']);
check_tracer_management(tracer_errors($data)===[],'Koreksi data valid dan jurusan lama diterima');
check_tracer_management($data['nama_perusahaan']==='','Detail status lain dibersihkan');
$data['tahun_lulus']='9999';check_tracer_management(tracer_errors($data)!==[],'Tahun di luar rentang ditolak');
$data['tahun_lulus']='2021';$data['nama_kampus']='';check_tracer_management(tracer_errors($data)!==[],'Detail status aktif wajib diisi');
