<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/mou.php';
function partner_check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$profile=partner_profile_input(['klaster'=>'dalam_negeri','bidang_usaha'=>'Teknologi','alamat_lengkap'=>'Alamat lengkap','negara'=>'Indonesia','pic_nama'=>'PIC Uji','pic_jabatan'=>'Manajer','pic_kontak'=>'pic@example.test']);
partner_check(partner_profile_errors($profile,'perusahaan')===[],'Profil industri dalam negeri diterima');
partner_check(partner_profile_errors(array_replace($profile,['klaster'=>'luar_negeri','negara'=>'Jepang']),'perusahaan')===[],'Profil industri luar negeri diterima');
partner_check(partner_profile_errors(array_replace($profile,['klaster'=>'luar_negeri']),'perusahaan')!==[],'Klaster luar negeri dengan Indonesia ditolak');
partner_check(partner_profile_errors(array_replace($profile,['klaster'=>'','bidang_usaha'=>'']),'kampus')===[],'Direktori kampus tidak memerlukan bidang/klaster industri');
partner_check(partner_profile_errors(array_replace($profile,['negara'=>'Jepang']),'kampus')!==[],'Kampus selain Indonesia tidak masuk direktori dalam negeri');
partner_check(partner_profile_errors(array_replace($profile,['klaster'=>'']),'perusahaan')!==[],'Klaster perusahaan wajib dipilih pada formulir lengkap');
partner_check(partner_profile_errors(array_replace($profile,['alamat_lengkap'=>'']),'perusahaan')!==[],'Alamat lengkap wajib diisi');
partner_check(partner_profile_errors(array_replace($profile,['pic_nama'=>str_repeat('x',151)]),'perusahaan')!==[],'Batas PIC diperiksa');
$doc=mou_input(['nomor_dokumen'=>'MOU/001','tanggal_mulai'=>'2026-01-01','tanggal_berakhir'=>'2026-11-06']);
partner_check(mou_errors($doc)===[],'MoU dengan periode valid diterima');
partner_check(mou_errors(array_replace($doc,['tanggal_berakhir'=>'2025-12-31']))!==[],'Tanggal berakhir sebelum mulai ditolak');
partner_check(mou_errors(array_replace($doc,['tanggal_mulai'=>'2026-02-29']))!==[],'Tanggal tidak valid ditolak');
partner_check(mou_errors(array_replace($doc,['previous_id'=>'../1']))!==[],'ID riwayat sebelumnya tidak valid ditolak');
partner_check(mou_status('2026-11-07','2027-11-06',new DateTimeImmutable('2026-11-06T16:59:59Z'))==='Belum Berlaku','Sehari sebelum mulai WIB belum berlaku');
partner_check(mou_status('2026-11-07','2027-11-06',new DateTimeImmutable('2026-11-06T17:00:00Z'))==='Aktif','Tepat tengah malam tanggal mulai WIB aktif');
partner_check(mou_status('2026-01-01','2026-11-06',new DateTimeImmutable('2026-11-06T16:59:59.999999Z'))==='Aktif','Akhir hari berakhir WIB masih aktif');
partner_check(mou_status('2026-01-01','2026-11-06',new DateTimeImmutable('2026-11-06T17:00:00Z'))==='Kedaluwarsa','Tengah malam hari berikutnya kedaluwarsa');
partner_check(mou_status('2026-11-06','2026-11-06',new DateTimeImmutable('2026-11-06T05:00:00Z'))==='Aktif','Periode satu hari aktif pada tanggal itu');
foreach(['../dokumen.pdf',str_repeat('a',64).'.php','dokumen.pdf'] as $filename){partner_check(mou_file_path($filename)===null,'Path berkas di luar format aman ditolak');}
partner_check(panel_route_permission(APP_ROOT.'/panel/mou/unduh.php',[])==='mou','Unduhan MoU memakai izin MoU');
partner_check(panel_route_permission(APP_ROOT.'/panel/mitra/kampus.php',[])==='mitra','Direktori kampus memakai izin Mitra');
