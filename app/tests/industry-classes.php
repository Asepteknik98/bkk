<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/industry-classes.php';
require_once dirname(__DIR__).'/helpers/bmw.php';
function industry_check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$data=industry_input(['nama'=>'Kelas Uji','mitra_id'=>'1','kelas_sasaran'=>'XI TKJ','rencana_pertemuan'=>'4','status'=>'aktif']);
industry_check(industry_errors($data,[1],[])===[],'Sasaran kelas tanpa daftar siswa diterima');
industry_check(industry_errors(array_replace($data,['kelas_sasaran'=>'']),[1],[1])===[],'Peserta terpilih dapat menggantikan sasaran kelas');
industry_check(industry_errors(array_replace($data,['kelas_sasaran'=>'']),[1],[])!==[],'Kelas memerlukan sasaran atau peserta');
industry_check(industry_errors($data,[],[1])!==[],'Pemateri wajib dipilih');
foreach(['0','501','1.5','-1','1e2'] as $plan){industry_check(industry_errors(array_replace($data,['rencana_pertemuan'=>$plan]),[1],[])!==[],'Rencana tidak valid ditolak: '.$plan);}
industry_check(industry_ids(['1','2','1'])===[1,2],'ID referensi dideduplikasi');
foreach(['1',['0'],[['1']],array_fill(0,1001,'1')] as $input){$rejected=false;try{industry_ids($input);}catch(InvalidArgumentException){$rejected=true;}industry_check($rejected,'Referensi tidak sah ditolak');}
$meeting=industry_meeting_input(['status'=>'direncanakan']);industry_check(industry_meeting_errors($meeting)===[],'Slot kosong awal diterima');
industry_check(industry_meeting_errors(array_replace($meeting,['status'=>'dijadwalkan']))!==[],'Jadwal memerlukan tanggal/jam/lokasi');
$meeting=industry_meeting_input(['status'=>'terlaksana','tanggal'=>'2020-02-29','jam'=>'08:30','lokasi'=>'Ruang uji']);
industry_check(industry_meeting_errors($meeting)===[],'Pertemuan terlaksana dengan tanggal valid diterima');
foreach(['2021-02-29','2199-01-01'] as $date){industry_check(industry_meeting_errors(array_replace($meeting,['tanggal'=>$date]))!==[],'Tanggal tidak sah/mendatang tidak dihitung terlaksana');}
industry_check(industry_meeting_errors(array_replace($meeting,['jam'=>'24:00']))!==[],'Jam salah ditolak');
industry_check(industry_meeting_errors(array_replace($meeting,['status'=>'selesai']))!==[],'Status di luar pilihan ditolak');
industry_check(bmw_errors(['judul'=>'BMW','isi'=>'','status'=>'draft'])===[],'Deskripsi kosong dapat disimpan draft');
industry_check(bmw_errors(['judul'=>'BMW','isi'=>'','status'=>'published'])!==[],'Deskripsi kosong tidak dapat dipublikasikan');
industry_check(bmw_errors(['judul'=>'BMW','isi'=>str_repeat('x',15001),'status'=>'published'])!==[],'Batas isi sesuai tabel settings');
industry_check(panel_route_permission(APP_ROOT.'/panel/program/deskripsi.php',[])==='program','Deskripsi mengikuti izin program');
industry_check(panel_route_permission(APP_ROOT.'/panel/kelas-industri/unduh.php',[])==='kelas-industri','Unduhan kelas mengikuti izin industri');
