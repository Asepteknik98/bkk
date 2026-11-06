<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/pkl-schedules.php';
function schedule_check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$placement=['tanggal_mulai'=>'2026-12-28','tanggal_selesai'=>'2027-01-10'];
$data=pkl_schedule_input(['jenis'=>'monitoring','tanggal'=>'2027-01-03','jam'=>'08:30','petugas'=>'Guru Uji','tujuan'=>'Mitra Uji','alamat_lengkap'=>'Alamat lengkap uji','agenda'=>'Monitoring minggu pertama','status'=>'dijadwalkan']);
schedule_check(pkl_schedule_errors($data,$placement)===[],'Monitoring dalam periode diterima');
schedule_check(pkl_schedule_errors(array_replace($data,['tanggal'=>'2027-01-11']),$placement)!==[],'Monitoring di luar periode ditolak');
schedule_check(pkl_schedule_errors(array_replace($data,['jam'=>'24:00']),$placement)!==[],'Jam tidak valid ditolak');
schedule_check(pkl_schedule_errors(array_replace($data,['status'=>'selesai']),$placement)!==[],'Monitoring selesai membutuhkan hasil');
schedule_check(pkl_schedule_errors(array_replace($data,['status'=>'selesai','hasil_monitoring'=>'Hasil tercatat']),$placement)===[],'Monitoring selesai dengan hasil diterima');
schedule_check(pkl_schedule_errors(array_replace($data,['jenis'=>'sidang','tanggal'=>'2027-02-01']),$placement)===[],'Sidang setelah tanggal praktik tetap terkait periode');
schedule_check(pkl_schedule_errors(array_replace($data,['jenis'=>'orang_tua','tanggal'=>'2026-12-01']),$placement)===[],'Pertemuan sebelum praktik dapat dicatat');
schedule_check(pkl_schedule_errors(array_replace($data,['status'=>'publik']),$placement)!==[],'Status tak dikenal ditolak');
[$filters,$where,$params]=pkl_schedule_filters(['periode'=>'2026-12-28_2027-01-10','minggu'=>'2027-01-03','jenis'=>'monitoring']);
schedule_check($params['senin']==='2026-12-28'&&$params['minggu']==='2027-01-03','Minggu lintas tahun mengikuti Senin–Minggu');
schedule_check($params['mulai']==='2026-12-28'&&$params['selesai']==='2027-01-10','Filter memakai periode penempatan');
foreach([['periode'=>'2027-02-01_2026-12-01'],['minggu'=>'2026-02-29'],['jenis'=>['sidang']]] as $input){$rejected=false;try{pkl_schedule_filters($input);}catch(InvalidArgumentException){$rejected=true;}schedule_check($rejected,'Filter salah ditolak');}
foreach(['0'=>'0.00','150000,5'=>'150000.50','9999999999999.99'=>'9999999999999.99','123.45'=>'123.45'] as $raw=>$expected){schedule_check(pkl_money((string)$raw)===$expected,'Nominal desimal tepat: '.$raw);}
foreach(['-1','1e6','1.000.000','100,123','10000000000000','NaN',''] as $raw){schedule_check(pkl_money($raw)===null,'Nominal tidak valid ditolak');}
schedule_check(pkl_rupiah('9999999999999.99')==='Rp 9.999.999.999.999,99','Format rupiah tanpa pembulatan float');
$trip=pkl_trip_input(['tujuan'=>'Mitra','petugas'=>'Guru','tanggal'=>'2027-01-03','nominal'=>'100000','sumber_biaya'=>'sekolah']);
schedule_check(pkl_trip_errors($trip)===[],'Perjalanan tanpa monitoring diterima');
schedule_check(pkl_trip_errors(array_replace($trip,['sumber_biaya'=>'sponsor']))!==[],'Sumber biaya selain sekolah/pribadi ditolak');
schedule_check(panel_route_permission(APP_ROOT.'/panel/biaya-perjalanan/unduh.php',[])==='biaya-perjalanan','Unduhan biaya memakai izin tersendiri');
schedule_check(panel_route_permission(APP_ROOT.'/panel/pkl/jadwal-unduh.php',[])==='pkl','Unduhan jadwal memakai izin PKL');
