<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/careers.php';require_once dirname(__DIR__).'/helpers/vacancies.php';
function career_check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$job=vacancy_input(['perusahaan'=>'Perusahaan','posisi'=>'Teknisi','lokasi'=>'Tangerang','pendidikan'=>'SMK','persyaratan'=>'Kualifikasi','deskripsi'=>'Deskripsi','batas_lamaran'=>'2099-01-01','tautan_pendaftaran'=>'https://example.test/rekrutmen/teknisi','employment_type_id'=>'1','official_confirmed'=>'1','status'=>'published']);
career_check($job['tautan_pendaftaran']==='https://example.test/rekrutmen/teknisi'&&vacancy_errors($job)===[],'URL resmi tidak dikosongkan dan lowongan valid diterima');
career_check(vacancy_errors(array_replace($job,['tautan_pendaftaran'=>'']))!==[],'Tautan rekrutmen wajib');
career_check(vacancy_errors(array_replace($job,['tautan_pendaftaran'=>'javascript:alert(1)']))!==[],'URL berbahaya ditolak');
career_check(vacancy_errors(array_replace($job,['official_confirmed'=>false]))!==[],'Konfirmasi sumber resmi wajib');
career_check(vacancy_errors(array_replace($job,['employment_type_id'=>'']))!==[],'Jenis hubungan kerja harus dari data induk');
career_check(career_selection_errors('dalam_proses',false)===[],'Dalam proses sebelum seleksi selesai diterima');
career_check(career_selection_errors('dalam_proses',true)!==[],'Seleksi selesai tidak boleh tetap Dalam Proses');
career_check(career_selection_errors('',true)!==[]&&career_selection_errors('belum_mengisi',true)!==[],'Hasil akhir tidak boleh kosong atau Belum Mengisi');
career_check(career_selection_errors('lolos',true)===[]&&career_selection_errors('tidak_lolos',true)===[],'Dua hasil akhir sah diterima');
career_check(career_result_label(['hasil'=>'dalam_proses','seleksi_selesai'=>1])==='Belum Mengisi','Proses selesai tanpa hasil akhir tampil Belum Mengisi');
$work=career_work_input(['status_kerja'=>'sudah_bekerja','kerja_perusahaan'=>'Perusahaan','kerja_posisi'=>'Teknisi','tanggal_mulai_kerja'=>'2020-02-29','hubungan_kerja_id'=>'1']);
career_check(career_work_errors($work)===[],'Data kerja lengkap dengan tanggal valid diterima');
foreach(['kerja_perusahaan','kerja_posisi','tanggal_mulai_kerja','hubungan_kerja_id'] as $key){career_check(career_work_errors(array_replace($work,[$key=>'']))!==[],'Sudah bekerja memerlukan '.$key);}
career_check(career_work_errors(array_replace($work,['tanggal_mulai_kerja'=>'2199-01-01']))!==[],'Mulai mendatang belum dihitung sudah bekerja');
career_check(career_work_errors(career_work_input(['status_kerja'=>'belum_bekerja']))===[],'Belum bekerja tidak memerlukan rincian pekerjaan');
career_check(career_work_errors(career_work_input([]))!==[],'Status kerja wajib diisi');
$type=['nama'=>'PKWT','penjelasan'=>'Penjelasan sekolah','rujukan_url'=>'https://example.test/rujukan','aktif'=>'1'];
career_check(employment_type_errors($type)===[],'Kolom penjelasan dan rujukan dapat diisi');
career_check(employment_type_errors(array_replace($type,['rujukan_url'=>'data:text/html,test']))!==[],'Rujukan berbahaya ditolak');
career_check(panel_route_permission(APP_ROOT.'/panel/hasil-alumni/lamaran.php',[])==='hasil-alumni','Hasil alumni memakai izin tersendiri');
career_check(panel_route_permission(APP_ROOT.'/panel/hubungan-kerja/index.php',[])==='hubungan-kerja','Data jenis memakai izin tersendiri');
