<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl.php';
require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');
pkl_require_ready();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
try{[$q,$status,$where,$params]=pkl_list_filters($_GET);}
catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$rows=panel_query('SELECT s.nama,p.kelas,p.jurusan,p.tanggal_mulai,p.tanggal_selesai,p.tempat_pkl,p.alamat_lengkap,p.guru_pembimbing,p.pembimbing_industri,p.status FROM pkl_placements p JOIN siswa s ON s.id=p.siswa_id WHERE '.$where.' ORDER BY p.tanggal_mulai DESC,p.id DESC',$params)->fetchAll();
$counts=['aktif'=>0,'selesai'=>0,'arsip'=>0];
foreach($rows as $row){if(isset($counts[$row['status']])){$counts[$row['status']]++;}}
$summary=[
    ['keterangan'=>'Jumlah penempatan','nilai'=>count($rows)],
    ['keterangan'=>'Aktif','nilai'=>$counts['aktif']],
    ['keterangan'=>'Selesai','nilai'=>$counts['selesai']],
    ['keterangan'=>'Arsip','nilai'=>$counts['arsip']],
    ['keterangan'=>'Pencarian','nilai'=>$q!==''?$q:'Semua'],
    ['keterangan'=>'Filter status','nilai'=>$status!==''?ucfirst($status):'Semua status'],
    ['keterangan'=>'Cakupan','nilai'=>'Seluruh penempatan sesuai filter, bukan hanya halaman aktif. Jumlah penempatan bukan jumlah siswa unik.'],
];
excel_download('rekap-penempatan-pkl.xlsx',[
    ['name'=>'Ringkasan','columns'=>['keterangan'=>'Keterangan','nilai'=>'Nilai'],'rows'=>$summary],
    ['name'=>'Daftar penempatan','columns'=>['nama'=>'Nama siswa','kelas'=>'Kelas','jurusan'=>'Jurusan','tanggal_mulai'=>'Mulai PKL','tanggal_selesai'=>'Selesai PKL','tempat_pkl'=>'Tempat PKL','alamat_lengkap'=>'Alamat tempat PKL','guru_pembimbing'=>'Guru pembimbing','pembimbing_industri'=>'Pembimbing industri','status'=>'Status'],'rows'=>$rows],
]);
