<?php
declare(strict_types=1);
require_once __DIR__.'/partners.php';
require_once __DIR__.'/pkl.php';

function industry_forums_ready(): bool
{
    return (int)panel_query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('industry_forums','industry_forum_invitees')")->fetchColumn()===2;
}

function industry_forums_require_ready(): void
{
    if(!industry_forums_ready()){http_response_code(503);exit('Impor database/updates/023_industry_forums_ideas.sql untuk mengaktifkan Sarasehan Industri.');}
}

function industry_forum_input(array $input): array
{
    $data=[];foreach(['nama','tanggal','jam','lokasi','tautan','pic_sekolah'] as $key){$data[$key]=input_text($input,$key);}return $data;
}

function industry_forum_errors(array $data): array
{
    $errors=[];foreach(['nama'=>190,'lokasi'=>500,'pic_sekolah'=>250] as $key=>$max){if(!valid_text($data[$key],$max,$key!=='lokasi')){$errors[]=ucfirst(str_replace('_',' ',$key)).' tidak valid atau belum diisi.';}}
    if(!pkl_date($data['tanggal'])){$errors[]='Tanggal kegiatan tidak valid.';}
    if(!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$data['jam'])){$errors[]='Jam harus HH:MM (WIB).';}
    if(!valid_text($data['tautan'],2048,false)||($data['tautan']!==''&&!http_url($data['tautan']))){$errors[]='Tautan pertemuan harus URL http/https valid.';}
    if($data['lokasi']===''&&$data['tautan']===''){$errors[]='Isi lokasi atau tautan pertemuan.';}return $errors;
}

function industry_forum_invitees(array $input): array
{
    $ids=$input['mitra_ids']??[];$names=$input['pic_nama']??[];$contacts=$input['pic_kontak']??[];
    if(!is_array($ids)||!is_array($names)||!is_array($contacts)||count($ids)>200){throw new InvalidArgumentException('Pilihan undangan tidak valid; maksimal 200 mitra.');}
    $result=[];foreach($ids as $raw){
        $id=positive_id($raw);if(!$id){throw new InvalidArgumentException('ID mitra undangan tidak valid.');}
        $name=input_text($names,(string)$id);$contact=input_text($contacts,(string)$id);
        if(!valid_text($name,150)||!valid_text($contact,250,false)){throw new InvalidArgumentException('Nama PIC industri wajib diisi, maksimal 150 karakter; kontak maksimal 250 karakter.');}
        $result[$id]=['mitra_id'=>$id,'pic_nama'=>$name,'pic_kontak'=>$contact];
    }
    if(!$result){throw new InvalidArgumentException('Pilih minimal satu mitra industri yang diundang.');}return $result;
}

function industry_forum_find(int $id,bool $lock=false): array
{
    $row=panel_query('SELECT * FROM industry_forums WHERE id=:id'.($lock?' FOR UPDATE':''),['id'=>$id])->fetch();
    if(!$row){throw new InvalidArgumentException('Sarasehan tidak ditemukan.');}$row['jam']=substr($row['jam'],0,5);return $row;
}

function industry_forum_save(array $data,array $invitees,?int $id,int $version,int $actor): int
{
    if($errors=industry_forum_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    if(!$invitees){throw new InvalidArgumentException('Pilih mitra industri undangan.');}
    db()->beginTransaction();
    try{
        if($id){$old=industry_forum_find($id,true);if((int)$old['version']!==$version){throw new InvalidArgumentException('Jadwal sudah diubah pengelola lain. Muat ulang halaman.');}}
        foreach($invitees as $invitee){if(!panel_query("SELECT id FROM mitra WHERE id=:id AND jenis='perusahaan' LOCK IN SHARE MODE",['id'=>$invitee['mitra_id']])->fetch()){throw new InvalidArgumentException('Undangan harus memakai mitra perusahaan yang terdaftar.');}}
        $data['tautan']=$data['tautan']?:null;
        if($id){$set=implode(',',array_map(static fn($key)=>$key.'=:'.$key,array_keys($data)));panel_query('UPDATE industry_forums SET '.$set.',version=version+1,updated_by=:actor WHERE id=:id',$data+['actor'=>$actor,'id'=>$id]);}
        else{panel_query('INSERT INTO industry_forums(nama,tanggal,jam,lokasi,tautan,pic_sekolah,updated_by) VALUES(:nama,:tanggal,:jam,:lokasi,:tautan,:pic_sekolah,:actor)',$data+['actor'=>$actor]);$id=(int)db()->lastInsertId();}
        panel_query('DELETE FROM industry_forum_invitees WHERE forum_id=:id',['id'=>$id]);
        foreach($invitees as $invitee){panel_query('INSERT INTO industry_forum_invitees(forum_id,mitra_id,pic_nama,pic_kontak) VALUES(:forum,:mitra_id,:pic_nama,:pic_kontak)',$invitee+['forum'=>$id]);}
        db()->commit();return $id;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function industry_forum_partner_options(string $search,array $selected): array
{
    if(!valid_text($search,100,false)){throw new InvalidArgumentException('Pencarian maksimal 100 karakter.');}
    $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';
    $rows=panel_query("SELECT id,nama FROM mitra WHERE jenis='perusahaan' AND nama LIKE :nama ESCAPE '!' ORDER BY nama,id LIMIT 50",['nama'=>$pattern])->fetchAll();
    $result=[];foreach($rows as $row){$result[$row['id']]=$row;}
    foreach($selected as $id){if(!isset($result[$id])){$row=panel_query("SELECT id,nama FROM mitra WHERE id=:id AND jenis='perusahaan'",['id'=>$id])->fetch();if($row){$result[$id]=$row;}}}
    return array_values($result);
}
