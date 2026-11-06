<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/pkl.php';
function pkl_check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$data=pkl_input(['siswa_id'=>'1','kelas'=>'XI TKJ','jurusan'=>'Teknik Komputer dan Jaringan','tanggal_mulai'=>'2026-12-28','tanggal_selesai'=>'2027-01-10','tempat_pkl'=>'Mitra Uji','alamat_lengkap'=>'Jl. Uji 1, Desa Uji, Kecamatan Uji, Kabupaten Uji, Provinsi Uji, 12345','guru_pembimbing'=>'Guru Uji','pembimbing_industri'=>'Pembimbing Uji','status'=>'aktif']);
pkl_check(pkl_errors($data)===[],'Penempatan lengkap lintas tahun diterima');
pkl_check(pkl_errors(array_replace($data,['kelas'=>'']))===[],'Jurusan dapat mewakili kelas');
pkl_check(pkl_errors(array_replace($data,['kelas'=>'','jurusan'=>'']))!==[],'Kelas atau jurusan wajib diisi');
foreach(['2026-02-29','2026-13-01','2026-1-01',"2026-01-01\n",'0000-01-01'] as $bad){pkl_check(!pkl_date($bad),'Tanggal tidak valid ditolak: '.trim($bad));}
pkl_check(pkl_date('2028-02-29'),'Tahun kabisat diterima');
pkl_check(pkl_errors(array_replace($data,['tanggal_selesai'=>'2026-12-27']))!==[],'Periode terbalik ditolak');
foreach(['alamat_lengkap','guru_pembimbing','pembimbing_industri','tempat_pkl'] as $key){pkl_check(pkl_errors(array_replace($data,[$key=>'']))!==[],'Penugasan/penempatan wajib: '.$key);}
$journal=['tanggal'=>'2027-01-03','kegiatan'=>'Kegiatan uji','catatan'=>''];
pkl_check(pkl_journal_errors($journal,$data)===[],'Jurnal dalam periode diterima');
foreach(['2026-12-27','2027-01-11','2026-02-29'] as $date){pkl_check(pkl_journal_errors(array_replace($journal,['tanggal'=>$date]),$data)!==[],'Jurnal di luar periode/tanggal salah ditolak');}
pkl_check(pkl_journal_errors(array_replace($journal,['kegiatan'=>'']),$data)!==[],'Jurnal tanpa kegiatan ditolak');
$weeks=pkl_weekly([
    ['tanggal'=>'2027-01-04','kegiatan'=>'Senin baru','catatan'=>'Satu'],
    ['tanggal'=>'2027-01-03','kegiatan'=>'Minggu','catatan'=>''],
    ['tanggal'=>'2026-12-28','kegiatan'=>'Senin lama','catatan'=>'Dua'],
]);
pkl_check(count($weeks)===2&&$weeks[0]['minggu']==='2026-W53'&&$weeks[1]['minggu']==='2027-W01','Minggu ISO melintasi pergantian tahun');
pkl_check($weeks[0]['mulai']==='2026-12-28'&&$weeks[0]['selesai']==='2027-01-03'&&$weeks[0]['hari']===2,'Batas Senin–Minggu dan jumlah hari jurnal tepat');
pkl_check(count($weeks[0]['catatan'])===1&&pkl_weekly([])===[],'Catatan kosong dan minggu tanpa jurnal tidak direka');
$report=['tanggal_laporan'=>'2027-01-12','ringkasan'=>'Pelaksanaan','hasil'=>'Kompetensi','kendala'=>'','saran'=>'','kesimpulan'=>'Selesai'];
pkl_check(pkl_report_errors($report,$data)===[],'Laporan setelah periode diterima');
pkl_check(pkl_report_errors(array_replace($report,['tanggal_laporan'=>'2026-12-27']),$data)!==[],'Laporan sebelum mulai PKL ditolak');
pkl_check(pkl_report_errors(array_replace($report,['hasil'=>'']),$data)!==[],'Hasil laporan wajib diisi');
pkl_check(panel_route_permission(APP_ROOT.'/panel/pkl/unduh.php',[])==='pkl','Unduhan PKL mengikuti izin PKL');
