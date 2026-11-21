<?php
declare(strict_types=1);
require_once __DIR__.'/participants.php';

function report_ready(): bool
{
    return (int)panel_query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('student_learning_reports','student_learning_report_items')")->fetchColumn()===2;
}
function report_require_ready(): void
{
    if(!report_ready()){http_response_code(503);exit('Rapor belum aktif. Impor database/updates/025_student_learning_reports.sql terlebih dahulu.');}
}
function report_predicates(): array { return ['kompeten'=>'Kompeten','belum_kompeten'=>'Belum Kompeten']; }
/** Canonical spacing prevents duplicate student/period pairs from punctuation variants. */
function report_period(string $value): string
{
    $value=str_replace(["\u{2013}","\u{2014}"],'-',$value);
    $value=preg_replace('/\s*\/\s*/u','/',$value)??$value;
    $value=preg_replace('/\s*-\s*/u',' - ',$value)??$value;
    return trim(preg_replace('/\s+/u',' ',$value)??$value);
}
function report_input(array $input): array
{
    $data=[];foreach(['siswa_id','periode','tanggal','pembimbing','catatan','status'] as $key){$data[$key]=input_text($input,$key);}return $data;
}
function report_items(array $input): array
{
    if(!isset($input['items'])||!is_array($input['items'])||count($input['items'])>50){throw new InvalidArgumentException('Isi 1 sampai 50 kompetensi.');}
    $items=[];
    foreach($input['items'] as $row){
        if(!is_array($row)){throw new InvalidArgumentException('Baris kompetensi tidak valid.');}
        $item=[];foreach(['kompetensi','nilai','predikat','deskripsi'] as $field){
            if(isset($row[$field])&&!is_string($row[$field])){throw new InvalidArgumentException('Isi kompetensi tidak valid.');}
            $item[$field]=input_text($row,$field);
        }
        if(implode('',$item)===''){continue;}
        if(!valid_text($item['kompetensi'],190)||!valid_text($item['deskripsi'],2000,false)){throw new InvalidArgumentException('Kompetensi wajib diisi (maksimal 190 karakter), deskripsi maksimal 2.000 karakter.');}
        $item['nilai']=str_replace(',','.',$item['nilai']);
        if($item['nilai']!==''&&!preg_match('/^(?:100(?:\.0{1,2})?|[0-9]{1,2}(?:\.[0-9]{1,2})?)$/D',$item['nilai'])){throw new InvalidArgumentException('Nilai harus 0 sampai 100, maksimal dua angka desimal.');}
        if($item['predikat']!==''&&!isset(report_predicates()[$item['predikat']])){throw new InvalidArgumentException('Predikat tidak valid.');}
        $items[]=$item;
    }
    if(!$items){throw new InvalidArgumentException('Isi minimal satu kompetensi.');}return $items;
}
function report_find(int $id,bool $lock=false): array
{
    $row=panel_query('SELECT * FROM student_learning_reports WHERE id=:id'.($lock?' FOR UPDATE':''),['id'=>$id])->fetch();
    if(!$row){throw new InvalidArgumentException('Rapor tidak ditemukan.');}return $row;
}
function report_save(array $data,array $items,?int $id,int $version,int $actor): int
{
    $data['periode']=report_period($data['periode']);
    if(!positive_id($data['siswa_id'])||!valid_text($data['periode'],80)||!valid_text($data['pembimbing'],150)||!valid_text($data['catatan'],5000,false)||!in_array($data['status'],['draft','final'],true)){throw new InvalidArgumentException('Lengkapi siswa, periode, pembimbing, dan status rapor.');}
    $date=DateTimeImmutable::createFromFormat('!Y-m-d',$data['tanggal']);
    if(!$date||$date->format('Y-m-d')!==$data['tanggal']||$data['tanggal']<'1900-01-01'||$data['tanggal']>'2199-12-31'){throw new InvalidArgumentException('Tanggal penilaian tidak valid.');}
    $items=report_items(['items'=>$items]);
    if($data['status']==='final'){foreach($items as $item){if($item['predikat']===''){throw new InvalidArgumentException('Isi predikat setiap kompetensi sebelum memfinalkan rapor.');}}}
    db()->beginTransaction();
    try{
        $student=panel_query('SELECT id,nama,kelas,jurusan,angkatan FROM siswa WHERE id=:id FOR UPDATE',['id'=>(int)$data['siswa_id']])->fetch();
        if(!$student){throw new InvalidArgumentException('Siswa tidak ditemukan. Tambahkan melalui Data Peserta.');}
        $existing=panel_query('SELECT id,periode FROM student_learning_reports WHERE siswa_id=:student AND id<>:id FOR UPDATE',['student'=>(int)$data['siswa_id'],'id'=>$id??0])->fetchAll();
        foreach($existing as $other){if(mb_strtolower(report_period($other['periode']))===mb_strtolower($data['periode'])){
            throw new InvalidArgumentException('Rapor siswa untuk periode ini sudah ada. Gunakan Edit pada rapor tersebut.');
        }}
        $values=array_intersect_key($data,array_flip(['periode','tanggal','pembimbing','catatan','status']));
        if($id){
            $old=report_find($id,true);
            if((int)$old['version']!==$version){throw new InvalidArgumentException('Rapor sudah diubah pengelola lain. Muat ulang sebelum menyimpan.');}
            if((int)$old['siswa_id']!==(int)$data['siswa_id']){throw new InvalidArgumentException('Siswa pada rapor tidak dapat diganti. Buat rapor baru.');}
            panel_query('UPDATE student_learning_reports SET periode=:periode,tanggal=:tanggal,pembimbing=:pembimbing,catatan=:catatan,status=:status,version=version+1,updated_by=:actor WHERE id=:id',$values+['actor'=>$actor,'id'=>$id]);
            panel_query('DELETE FROM student_learning_report_items WHERE report_id=:id',['id'=>$id]);
        }else{
            panel_query('INSERT INTO student_learning_reports(siswa_id,nama,kelas,jurusan,angkatan,periode,tanggal,pembimbing,catatan,status,created_by,updated_by) VALUES(:siswa_id,:nama,:kelas,:jurusan,:angkatan,:periode,:tanggal,:pembimbing,:catatan,:status,:creator,:editor)',$values+['siswa_id'=>(int)$student['id'],'nama'=>$student['nama'],'kelas'=>$student['kelas'],'jurusan'=>$student['jurusan'],'angkatan'=>$student['angkatan'],'creator'=>$actor,'editor'=>$actor]);
            $id=(int)db()->lastInsertId();
        }
        foreach($items as $index=>$item){panel_query('INSERT INTO student_learning_report_items(report_id,urutan,kompetensi,nilai,predikat,deskripsi) VALUES(:report,:urutan,:kompetensi,:nilai,:predikat,:deskripsi)',['report'=>$id,'urutan'=>$index+1,'kompetensi'=>$item['kompetensi'],'nilai'=>$item['nilai']===''?null:$item['nilai'],'predikat'=>$item['predikat']===''?null:$item['predikat'],'deskripsi'=>$item['deskripsi']]);}
        db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}if($exception instanceof PDOException&&($exception->errorInfo[1]??0)===1062){throw new InvalidArgumentException('Rapor siswa untuk periode ini sudah ada. Gunakan Edit pada rapor tersebut.');}throw $exception;}
}
function report_delete(int $id,int $version): void
{
    db()->beginTransaction();try{$row=report_find($id,true);if((int)$row['version']!==$version){throw new InvalidArgumentException('Rapor telah berubah. Muat ulang sebelum menghapus.');}panel_query('DELETE FROM student_learning_reports WHERE id=:id',['id'=>$id]);db()->commit();}catch(Throwable $e){if(db()->inTransaction()){db()->rollBack();}throw $e;}
}
