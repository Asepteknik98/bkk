<?php
declare(strict_types=1);
require_once __DIR__ . '/panel.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/jobs.php';

function vacancy_input(array $input): array
{
    $data = [];
    foreach (['perusahaan','posisi','lokasi','jenis_pekerjaan','pendidikan','persyaratan','deskripsi','batas_lamaran','tautan_pendaftaran','status','media_id'] as $key) {
        $data[$key] = input_text($input, $key);
    }
    $data['tautan_pendaftaran'] = '';
    $data['hapus_gambar'] = ($input['hapus_gambar'] ?? '') === '1';
    return $data;
}

function vacancy_errors(array $data): array
{
    $errors = [];
    foreach (['perusahaan'=>[200,'Nama perusahaan'],'posisi'=>[200,'Posisi'],'lokasi'=>[255,'Lokasi'],'jenis_pekerjaan'=>[100,'Jenis pekerjaan'],'pendidikan'=>[150,'Pendidikan minimal'],'persyaratan'=>[10000,'Persyaratan'],'deskripsi'=>[100000,'Deskripsi']] as $key=>[$limit,$label]) {
        if (!valid_text($data[$key], $limit)) { $errors[] = "$label wajib diisi dengan teks valid, maksimal $limit karakter."; }
    }
    if (!job_deadline($data['batas_lamaran'])) { $errors[] = 'Batas lamaran wajib berupa tanggal yang valid.'; }
    if (!in_array($data['status'], ['draft','published'], true)) { $errors[] = 'Status lowongan tidak valid.'; }
    if ($data['media_id'] !== '' && positive_id($data['media_id']) === null) { $errors[] = 'Pilihan poster tidak valid.'; }
    return $errors;
}

function save_vacancy(array $data, ?int $id, int $userId, ?array $upload): int
{
    return with_content_lock(static function () use ($data,$id,$userId,$upload): int {
        db()->beginTransaction();
        try {
            $old = $id ? panel_query('SELECT * FROM lowongan WHERE id=:id FOR UPDATE', ['id'=>$id])->fetch() : null;
            if ($id && !$old) { throw new InvalidArgumentException('Lowongan sudah tidak tersedia.'); }
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
            db()->commit();return $id;
        } catch (Throwable $exception) { if(db()->inTransaction()){db()->rollBack();} throw $exception; }
    });
}
