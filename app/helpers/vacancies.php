<?php
declare(strict_types=1);
require_once __DIR__ . '/panel.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/jobs.php';
require_once __DIR__ . '/employment.php';

function vacancy_input(array $input): array
{
    $data = [];
    foreach (['perusahaan','posisi','lokasi','jenis_pekerjaan','pendidikan','persyaratan','deskripsi','batas_lamaran','tautan_pendaftaran','status','media_id'] as $key) {
        $data[$key] = input_text($input, $key);
    }
    $data['employment_type_id']=input_text($input,'employment_type_id');
    $data['official_confirmed']=($input['official_confirmed']??'')==='1';
    $data['hapus_gambar'] = ($input['hapus_gambar'] ?? '') === '1';
    return $data;
}

function vacancy_errors(array $data): array
{
    $errors = [];
    foreach (['perusahaan'=>[200,'Nama perusahaan'],'posisi'=>[200,'Posisi'],'lokasi'=>[255,'Lokasi'],'pendidikan'=>[150,'Pendidikan minimal'],'persyaratan'=>[10000,'Persyaratan'],'deskripsi'=>[100000,'Deskripsi']] as $key=>[$limit,$label]) {
        if (!valid_text($data[$key], $limit)) { $errors[] = "$label wajib diisi dengan teks valid, maksimal $limit karakter."; }
    }
    if (!job_deadline($data['batas_lamaran'])) { $errors[] = 'Batas lamaran wajib berupa tanggal yang valid.'; }
    if(!http_url($data['tautan_pendaftaran'])){$errors[]='Tautan halaman rekrutmen resmi perusahaan atau posisi wajib diisi dan harus URL http/https valid.';}
    if(empty($data['official_confirmed'])){$errors[]='Konfirmasikan bahwa tautan sudah diperiksa sebagai halaman rekrutmen resmi.';}
    if(!positive_id($data['employment_type_id']??'')){$errors[]='Pilih jenis hubungan kerja dari data induk.';}
    if (!in_array($data['status'], ['draft','published'], true)) { $errors[] = 'Status lowongan tidak valid.'; }
    if ($data['media_id'] !== '' && positive_id($data['media_id']) === null) { $errors[] = 'Pilihan poster tidak valid.'; }
    return $errors;
}

function save_vacancy(array $data, ?int $id, int $userId, ?array $upload): int
{
    if(!employment_ready()){throw new InvalidArgumentException('Impor pembaruan database 024 terlebih dahulu.');}
    if($errors=vacancy_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
    return with_content_lock(static function () use ($data,$id,$userId,$upload): int {
        db()->beginTransaction();
        try {
            $old = $id ? panel_query('SELECT * FROM lowongan WHERE id=:id FOR UPDATE', ['id'=>$id])->fetch() : null;
            if ($id && !$old) { throw new InvalidArgumentException('Lowongan sudah tidak tersedia.'); }
            $oldType=$id?panel_query('SELECT employment_type_id FROM vacancy_details WHERE lowongan_id=:id',['id'=>$id])->fetchColumn():null;
            $type=employment_validate_type((int)$data['employment_type_id'],$oldType?(int)$oldType:null);$data['jenis_pekerjaan']=$type['nama'];
            $image = $data['hapus_gambar'] ? null : ($old['gambar'] ?? null);
            if ($upload) { register_media($upload,$userId); $image=$upload['path']; }
            elseif ($data['media_id'] !== '') {
                $media=panel_query('SELECT * FROM media WHERE id=:id FOR UPDATE',['id'=>(int)$data['media_id']])->fetch();
                if (!$media || !media_is_image($media)) { throw new InvalidArgumentException('Poster katalog tidak tersedia atau bukan gambar yang valid.'); }
                $image=$media['path'];
            }
            // Pertahankan URL ketika posisi/perusahaan diedit.
            $base=slugify($data['posisi'].' '.$data['perusahaan']); $slug=$old['slug'] ?? $base;
            for ($suffix=2; panel_query('SELECT id FROM lowongan WHERE slug=:slug AND id<>:id LIMIT 1',['slug'=>$slug,'id'=>$id ?? 0])->fetch(); $suffix++) { $slug=$base.'-'.$suffix; }
            $params=array_intersect_key($data,array_flip(['perusahaan','posisi','lokasi','jenis_pekerjaan','pendidikan','persyaratan','deskripsi','batas_lamaran','tautan_pendaftaran','status']));
            $params += ['slug'=>$slug,'gambar'=>$image];
            if ($id) {
                panel_query('UPDATE lowongan SET perusahaan=:perusahaan,posisi=:posisi,lokasi=:lokasi,jenis_pekerjaan=:jenis_pekerjaan,pendidikan=:pendidikan,persyaratan=:persyaratan,deskripsi=:deskripsi,batas_lamaran=:batas_lamaran,tautan_pendaftaran=:tautan_pendaftaran,status=:status,slug=:slug,gambar=:gambar WHERE id=:id',$params+['id'=>$id]);
            } else {
                panel_query('INSERT INTO lowongan (perusahaan,posisi,lokasi,jenis_pekerjaan,pendidikan,persyaratan,deskripsi,batas_lamaran,tautan_pendaftaran,status,slug,gambar,penulis_id) VALUES (:perusahaan,:posisi,:lokasi,:jenis_pekerjaan,:pendidikan,:persyaratan,:deskripsi,:batas_lamaran,:tautan_pendaftaran,:status,:slug,:gambar,:penulis_id)',$params+['penulis_id'=>$userId]);
                $id=(int)db()->lastInsertId();
            }
            panel_query('INSERT INTO vacancy_details(lowongan_id,employment_type_id,verified_by,verified_at) VALUES(:id,:type,:actor,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE employment_type_id=VALUES(employment_type_id),verified_by=VALUES(verified_by),verified_at=VALUES(verified_at)',['id'=>$id,'type'=>$type['id'],'actor'=>$userId]);
            db()->commit();return $id;
        } catch (Throwable $exception) { if(db()->inTransaction()){db()->rollBack();} throw $exception; }
    });
}
