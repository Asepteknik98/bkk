<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin','staf_bkk'); require_post_csrf();
$raw=$_POST['id']??''; $id=$raw===''?null:positive_id($raw); if($raw!==''&&!$id){panel_not_found();}
$data=[];foreach(['nama','jenis','website','deskripsi','aktif'] as $key){$data[$key]=input_text($_POST,$key);} $upload=null;
try {
    if(!valid_text($data['nama'],200)||!valid_text($data['deskripsi'],10000,false)||!in_array($data['jenis'],['perusahaan','kampus'],true)||!in_array($data['aktif'],['0','1'],true)) { throw new InvalidArgumentException('Nama wajib maksimal 200 karakter, deskripsi maksimal 10.000 karakter, jenis dan status harus valid.'); }
    if($data['website']!==''&&!http_url($data['website'])){throw new InvalidArgumentException('Website harus URL http/https valid atau kosong.');}
    $upload=management_upload('logo');
    management_transaction(static function() use($user,$id,$data,$upload): void {
        $old=$id?panel_query('SELECT * FROM mitra WHERE id=:id FOR UPDATE',['id'=>$id])->fetch():null;
        if($id&&!$old){throw new InvalidArgumentException('Mitra tidak ditemukan.');}
        $logo=management_image('logo',$old['logo']??null,(int)$user['id'],$upload);
        $params=$data; $params['website']=$data['website']?:null; $params['logo']=$logo;
        if($id){panel_query('UPDATE mitra SET nama=:nama,jenis=:jenis,website=:website,deskripsi=:deskripsi,aktif=:aktif,logo=:logo WHERE id=:id',$params+['id'=>$id]);}
        else{panel_query('INSERT INTO mitra(nama,jenis,website,deskripsi,aktif,logo) VALUES(:nama,:jenis,:website,:deskripsi,:aktif,:logo)',$params);}
    });
    unset($_SESSION['mitra_form']);panel_message('Mitra berhasil disimpan.','panel/mitra/');
} catch(Throwable $exception){discard_upload($upload);$_SESSION['mitra_form']=['id'=>$id,'data'=>$data];management_error($exception,$id?'panel/mitra/edit.php?id='.$id:'panel/mitra/tambah.php');}
