<?php
declare(strict_types=1);
require_once __DIR__.'/management.php';

function partner_profiles_ready(): bool
{
    return (bool)panel_query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mitra_profiles'")->fetchColumn();
}

function partner_profile_fields(): array
{
    return ['bidang_usaha'=>['Bidang usaha',200],'alamat_lengkap'=>['Alamat lengkap',5000],
        'negara'=>['Negara',100],'pic_nama'=>['Nama PIC (opsional)',150],
        'pic_jabatan'=>['Jabatan PIC (opsional)',150],'pic_kontak'=>['Kontak PIC: telepon / email (opsional)',250]];
}

function partner_clusters(): array
{
    return ['dalam_negeri'=>'Industri Dalam Negeri','luar_negeri'=>'Industri Luar Negeri'];
}

function partner_profile_input(array $input): array
{
    $data=['klaster'=>input_text($input,'klaster')];foreach(partner_profile_fields() as $key=>$meta){$data[$key]=input_text($input,$key);}return $data;
}

function partner_profile_errors(array $data,string $kind): array
{
    $errors=[];
    foreach(partner_profile_fields() as $key=>[$label,$max]){
        if(!valid_text($data[$key],$max,in_array($key,['alamat_lengkap','negara'],true)||($kind==='perusahaan'&&$key==='bidang_usaha'))){$errors[]=$label.' belum diisi atau tidak valid.';}
    }
    if($kind==='perusahaan'&&!isset(partner_clusters()[$data['klaster']])){$errors[]='Pilih klaster industri.';}
    $domestic=$kind==='kampus'||$data['klaster']==='dalam_negeri';
    if($domestic&&strcasecmp($data['negara'],'Indonesia')!==0){$errors[]='Kampus Dalam Negeri / Industri Dalam Negeri memakai negara Indonesia.';}
    if($kind==='perusahaan'&&$data['klaster']==='luar_negeri'&&strcasecmp($data['negara'],'Indonesia')===0){$errors[]='Industri Luar Negeri harus memakai negara selain Indonesia.';}
    return $errors;
}

function partner_find(int $id): array
{
    $row=panel_query('SELECT * FROM mitra WHERE id=:id',['id'=>$id])->fetch();
    if(!$row){throw new InvalidArgumentException('Mitra tidak ditemukan.');}
    $profile=partner_profiles_ready()?panel_query('SELECT * FROM mitra_profiles WHERE mitra_id=:id',['id'=>$id])->fetch():false;
    return array_merge($row,partner_profile_input($profile?:[]),['version'=>(int)($profile['version']??0)]);
}

function partner_save(array $data,?array $profile,?int $version,?int $id,int $actor,?array $upload): int
{
    if(!valid_text($data['nama'],200)||!valid_text($data['deskripsi'],10000,false)||!in_array($data['jenis'],['perusahaan','kampus'],true)||!in_array($data['aktif'],['0','1'],true)){
        throw new InvalidArgumentException('Nama, deskripsi, jenis, atau status mitra tidak valid.');
    }
    if($data['website']!==''&&!http_url($data['website'])){throw new InvalidArgumentException('Website harus URL http/https valid atau kosong.');}
    if($profile&&($errors=partner_profile_errors($profile,$data['jenis']))){throw new InvalidArgumentException(implode(' ',$errors));}
    return management_transaction(static function()use($data,$profile,$version,$id,$actor,$upload):int{
        $ready=partner_profiles_ready();
        if($profile&&!$ready){throw new InvalidArgumentException('Impor pembaruan database 022 terlebih dahulu.');}
        $old=$id?panel_query('SELECT * FROM mitra WHERE id=:id FOR UPDATE',['id'=>$id])->fetch():null;
        if($id&&!$old){throw new InvalidArgumentException('Mitra tidak ditemukan.');}
        $oldProfile=$ready&&$id?panel_query('SELECT * FROM mitra_profiles WHERE mitra_id=:id FOR UPDATE',['id'=>$id])->fetch():null;
        if($profile&&(int)($oldProfile['version']??0)!==$version){throw new InvalidArgumentException('Mitra sudah diubah pengelola lain. Muat ulang sebelum menyimpan.');}
        if($old&&$old['jenis']==='perusahaan'&&$data['jenis']==='kampus'){
            foreach(['industry_forum_invitees','industry_idea_links'] as $table){
                if(panel_query('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table',['table'=>$table])->fetchColumn()
                    &&panel_query('SELECT mitra_id FROM '.$table.' WHERE mitra_id=:id LIMIT 1',['id'=>$id])->fetch()){
                    throw new InvalidArgumentException('Perusahaan masih direferensikan Sarasehan atau tautan mitra. Jenis tidak dapat diganti menjadi kampus.');
                }
            }
            $hasClasses=(bool)panel_query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='industry_classes'")->fetchColumn();
            if($hasClasses&&panel_query('SELECT id FROM industry_classes WHERE mitra_id=:id LIMIT 1',['id'=>$id])->fetch()){
                throw new InvalidArgumentException('Perusahaan ini masih dipakai Kelas Industri. Jenis mitra tidak dapat diganti menjadi kampus.');
            }
        }
        $logo=management_image('logo',$old['logo']??null,$actor,$upload);$params=$data+['logo'=>$logo];$params['website']=$data['website']?:null;
        if($id){panel_query('UPDATE mitra SET nama=:nama,jenis=:jenis,website=:website,deskripsi=:deskripsi,aktif=:aktif,logo=:logo WHERE id=:id',$params+['id'=>$id]);}
        else{panel_query('INSERT INTO mitra(nama,jenis,website,deskripsi,aktif,logo) VALUES(:nama,:jenis,:website,:deskripsi,:aktif,:logo)',$params);$id=(int)db()->lastInsertId();}
        if($ready){
            // Legacy writes keep all private profile values; only advance the edit version.
            $values=$profile??partner_profile_input($oldProfile?:[]);
            if($profile&&($data['jenis']==='kampus'||$values['klaster']==='dalam_negeri')){$values['negara']='Indonesia';}
            $values['klaster']=$data['jenis']==='kampus'||$values['klaster']===''?null:$values['klaster'];
            $set=implode(',',array_map(static fn($key)=>$key.'=VALUES('.$key.')',array_keys($values)));
            $values+=['id'=>$id,'version'=>(int)($oldProfile['version']??0)+1];
            panel_query('INSERT INTO mitra_profiles(mitra_id,klaster,bidang_usaha,alamat_lengkap,negara,pic_nama,pic_jabatan,pic_kontak,version) VALUES(:id,:klaster,:bidang_usaha,:alamat_lengkap,:negara,:pic_nama,:pic_jabatan,:pic_kontak,:version) ON DUPLICATE KEY UPDATE '.$set.',version=VALUES(version)',$values);
        }
        return $id;
    });
}
