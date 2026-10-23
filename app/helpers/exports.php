<?php
declare(strict_types=1);
require_once __DIR__.'/attendance.php';
require_once __DIR__.'/speakers.php';
require_once __DIR__.'/excel.php';

function export_rows(string $sql,array $params,?callable $map=null): Generator
{
    $statement=panel_query($sql,$params);
    try{while($row=$statement->fetch()){yield $map?$map($row):$row;}}finally{$statement->closeCursor();}
}

function export_sheets(string $type,array $input): array
{
    if($type==='siswa'){
        [$filters,$where,$params,$order]=student_filters($input);
        return [['name'=>'Data siswa','columns'=>['id'=>'ID siswa']+student_columns()+['created_at'=>'Dibuat','updated_at'=>'Diperbarui'],'rows'=>export_rows('SELECT * FROM siswa WHERE '.$where.' ORDER BY '.$order,$params)]];
    }
    if($type==='capaian'){
        [$filters,$where,$params]=learning_filters($input);
        return [['name'=>'Capaian pembelajaran','columns'=>['id'=>'ID dokumen','judul'=>'Judul','deskripsi'=>'Deskripsi','angkatan'=>'Angkatan (tahun masuk)','kelas_industri'=>'Kelas industri','nama_asli'=>'Nama file PDF','ukuran'=>'Ukuran (byte)','uploaded_by'=>'ID pengunggah','pengunggah'=>'Nama pengunggah','created_at'=>'Dibuat','updated_at'=>'Diperbarui','tautan'=>'Tautan download PDF'],'rows'=>export_rows('SELECT d.*,u.nama AS pengunggah FROM learning_documents d LEFT JOIN users u ON u.id=d.uploaded_by WHERE '.$where.' ORDER BY d.updated_at DESC,d.id DESC',$params,static function($row){$row['tautan']=base_url('panel/capaian-pembelajaran/download.php?id='.$row['id']);return $row;})]];
    }
    if($type==='pemateri'){
        [$filters,$where,$params]=speaker_filters($input);
        return [
            ['name'=>'Pemateri','columns'=>['id'=>'ID pemateri','nama'=>'Nama','instansi'=>'Instansi','keahlian'=>'Bidang keahlian','kelas_industri'=>'Kelas industri utama','bio'=>'Profil singkat','cv_nama'=>'Nama file CV','cv_size'=>'Ukuran CV (byte)','cv_tautan'=>'Tautan download CV','foto_tautan'=>'Tautan foto','foto_mime'=>'Format foto','foto_size'=>'Ukuran foto (byte)','kegiatan'=>'Jumlah kegiatan','created_at'=>'Dibuat','updated_at'=>'Diperbarui'],'rows'=>export_rows('SELECT s.*,(SELECT COUNT(*) FROM speaker_events se WHERE se.speaker_id=s.id) AS kegiatan FROM speakers s WHERE '.$where.' ORDER BY s.nama,s.id',$params,static function($row){$row['kegiatan']=(int)$row['kegiatan'];$row['cv_tautan']=$row['cv_file']?base_url('panel/pemateri/berkas.php?id='.$row['id'].'&jenis=cv'):'';$row['foto_tautan']=$row['foto_file']?base_url('panel/pemateri/berkas.php?id='.$row['id'].'&jenis=foto'):'';return $row;})],
            ['name'=>'Kegiatan pemateri','columns'=>['speaker_id'=>'ID pemateri','nama'=>'Nama pemateri','instansi'=>'Instansi','event_id'=>'ID kegiatan','judul'=>'Kegiatan','tanggal'=>'Tanggal','angkatan'=>'Angkatan (tahun masuk)','kelas_industri'=>'Kelas industri kegiatan'],'rows'=>export_rows('SELECT s.id AS speaker_id,s.nama,s.instansi,e.id AS event_id,e.judul,e.tanggal,e.angkatan,e.kelas_industri FROM speakers s JOIN speaker_events se ON se.speaker_id=s.id JOIN attendance_events e ON e.id=se.event_id WHERE '.$where.' ORDER BY s.nama,s.id,e.tanggal DESC,e.id DESC',$params)]
        ];
    }
    if(!in_array($type,['absensi','rekap'],true)){throw new InvalidArgumentException('Jenis ekspor tidak valid.');}
    [$filters,$where,$params]=attendance_filters($input);
    if(isset($input['id'])&&$input['id']!==''){$id=positive_id($input['id']);if(!$id){throw new InvalidArgumentException('Kegiatan tidak valid.');}if(!panel_query('SELECT id FROM attendance_events WHERE id=:id',['id'=>$id])->fetch()){throw new InvalidArgumentException('Kegiatan tidak tersedia.');}$where.=' AND e.id=:event_id';$params['event_id']=$id;}
    if($type==='rekap'){
        $from=' FROM attendance_records r JOIN attendance_events e ON e.id=r.event_id JOIN siswa s ON s.id=r.siswa_id WHERE '.$where;
        return [['name'=>'Rekap absensi siswa','columns'=>['id'=>'ID siswa']+student_columns()+['kegiatan'=>'Jumlah kegiatan','hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa','belum'=>'Belum diisi'],'rows'=>export_rows("SELECT s.*,COUNT(*) AS kegiatan,COALESCE(SUM(r.status='hadir'),0) AS hadir,COALESCE(SUM(r.status='izin'),0) AS izin,COALESCE(SUM(r.status='sakit'),0) AS sakit,COALESCE(SUM(r.status='alpa'),0) AS alpa,SUM(r.status IS NULL) AS belum".$from.' GROUP BY s.id,s.nis,s.nisn,s.nama,s.kelas,s.jurusan,s.angkatan,s.kelas_industri,s.telepon,s.email,s.tempat_pkl,s.tahun_pkl,s.created_at,s.updated_at ORDER BY s.nama,s.id',$params,static function($row){foreach(['kegiatan','hadir','izin','sakit','alpa','belum'] as $key){$row[$key]=(int)$row[$key];}return $row;})]];
    }
    return [
        ['name'=>'Kegiatan','columns'=>['id'=>'ID kegiatan','judul'=>'Kegiatan','tanggal'=>'Tanggal','angkatan'=>'Angkatan (tahun masuk)','kelas_industri'=>'Kelas industri','deskripsi'=>'Keterangan kegiatan','peserta'=>'Jumlah peserta','hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa','belum'=>'Belum diisi','created_at'=>'Dibuat','updated_at'=>'Diperbarui'],'rows'=>export_rows("SELECT e.*,COUNT(r.id) AS peserta,COALESCE(SUM(r.status='hadir'),0) AS hadir,COALESCE(SUM(r.status='izin'),0) AS izin,COALESCE(SUM(r.status='sakit'),0) AS sakit,COALESCE(SUM(r.status='alpa'),0) AS alpa,SUM(r.id IS NOT NULL AND r.status IS NULL) AS belum FROM attendance_events e LEFT JOIN attendance_records r ON r.event_id=e.id WHERE ".$where.' GROUP BY e.id,e.judul,e.tanggal,e.angkatan,e.kelas_industri,e.deskripsi,e.version,e.created_by,e.created_at,e.updated_at ORDER BY e.tanggal DESC,e.id DESC',$params,static function($row){foreach(['peserta','hadir','izin','sakit','alpa','belum'] as $key){$row[$key]=(int)$row[$key];}return $row;})],
        ['name'=>'Absensi siswa','columns'=>['event_id'=>'ID kegiatan','judul'=>'Kegiatan','tanggal'=>'Tanggal','angkatan'=>'Angkatan kegiatan','kelas_industri'=>'Kelas industri kegiatan','siswa_id'=>'ID siswa','nis'=>'NIS (riwayat kegiatan)','nisn'=>'NISN','nama'=>'Nama (riwayat kegiatan)','kelas'=>'Kelas (riwayat kegiatan)','jurusan'=>'Jurusan saat ini','telepon'=>'Telepon','email'=>'Email','status_label'=>'Status','keterangan'=>'Keterangan absensi','updated_by'=>'ID pencatat','pencatat'=>'Nama pencatat','updated_at'=>'Waktu pencatatan'],'rows'=>export_rows('SELECT e.id AS event_id,e.judul,e.tanggal,e.angkatan,e.kelas_industri,r.siswa_id,r.nis,r.nama,r.kelas,r.status,r.keterangan,r.updated_by,r.updated_at,s.nisn,s.jurusan,s.telepon,s.email,u.nama AS pencatat FROM attendance_events e JOIN attendance_records r ON r.event_id=e.id JOIN siswa s ON s.id=r.siswa_id LEFT JOIN users u ON u.id=r.updated_by WHERE '.$where.' ORDER BY e.tanggal DESC,e.id DESC,r.nama,r.id',$params,static function($row){$row['status_label']=$row['status']===null?'Belum diisi':attendance_statuses()[$row['status']];return $row;})]
    ];
}
