<?php
declare(strict_types=1);
require_once __DIR__ . '/panel.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/upload.php';

/** Semua penulisan berita/media memakai lock yang sama untuk menjaga referensi berkas. */
function with_content_lock(callable $operation): mixed
{
    $name = 'bkk_content_' . substr(hash('sha256', app_config('database')['name']), 0, 32);
    if ((int) panel_query('SELECT GET_LOCK(:name, 5)', ['name' => $name])->fetchColumn() !== 1) {
        throw new RuntimeException('Data sedang diproses pengelola lain. Silakan coba kembali.');
    }
    try { return $operation(); }
    finally { panel_query('SELECT RELEASE_LOCK(:name)', ['name' => $name]); }
}

function news_input(array $input): array
{
    return ['judul' => input_text($input, 'judul'), 'slug' => input_text($input, 'slug'),
        'ringkasan' => input_text($input, 'ringkasan'), 'isi' => input_text($input, 'isi'),
        'status' => input_text($input, 'status'), 'unggulan' => ($input['unggulan'] ?? '') === '1' ? 1 : 0,
        'media_id' => input_text($input, 'media_id'), 'hapus_gambar' => ($input['hapus_gambar'] ?? '') === '1'];
}

function news_errors(array $data): array
{
    $errors = [];
    foreach (['judul' => [255, 'Judul'], 'slug' => [191, 'Slug'], 'ringkasan' => [1000, 'Ringkasan'], 'isi' => [100000, 'Isi berita']] as $key => [$limit, $label]) {
        if (!valid_text($data[$key], $limit, $key !== 'slug')) {
            $errors[] = "$label wajib diisi dengan teks UTF-8 yang valid, maksimal $limit karakter" . ($key === 'slug' ? ' (slug boleh kosong).' : '.');
        }
    }
    if (!in_array($data['status'], ['draft', 'published'], true)) { $errors[] = 'Status berita tidak valid.'; }
    if ($data['media_id'] !== '' && positive_id($data['media_id']) === null) { $errors[] = 'Pilihan gambar tidak valid.'; }
    return $errors;
}

function unique_news_slug(string $base, ?int $id): string
{
    $slug = $base;
    for ($suffix = 2; ; $suffix++) {
        $exists = panel_query('SELECT id FROM berita WHERE slug = :slug AND id <> :id LIMIT 1', ['slug' => $slug, 'id' => $id ?? 0])->fetch();
        if (!$exists) { return $slug; }
        $slug = $base . '-' . $suffix;
    }
}

function save_news(array $data, ?int $id, int $userId, ?array $upload): int
{
    return with_content_lock(static function () use ($data, $id, $userId, $upload): int {
        db()->beginTransaction();
        try {
            $old = $id ? panel_query('SELECT * FROM berita WHERE id = :id FOR UPDATE', ['id' => $id])->fetch() : null;
            if ($id && !$old) { throw new InvalidArgumentException('Berita sudah tidak tersedia.'); }
            $image = $data['hapus_gambar'] ? null : ($old['gambar'] ?? null);
            if ($upload) {
                register_media($upload, $userId);
                $image = $upload['path'];
            } elseif ($data['media_id'] !== '') {
                $media = panel_query('SELECT * FROM media WHERE id = :id FOR UPDATE', ['id' => (int) $data['media_id']])->fetch();
                if (!$media || !media_is_image($media)) { throw new InvalidArgumentException('Gambar katalog tidak tersedia atau bukan gambar yang valid.'); }
                $image = $media['path'];
            }
            $slug = unique_news_slug(slugify($data['slug'] ?: $data['judul']), $id);
            // Gunakan jam database yang sama dengan filter NOW() pada portal publik.
            $databaseNow = (string) panel_query('SELECT NOW()')->fetchColumn();
            $publishedAt = $data['status'] === 'published' ? (($old['status'] ?? '') === 'published' ? ($old['published_at'] ?? $databaseNow) : $databaseNow) : null;
            $params = ['judul' => $data['judul'], 'slug' => $slug, 'ringkasan' => $data['ringkasan'], 'isi' => $data['isi'],
                'gambar' => $image, 'status' => $data['status'], 'unggulan' => $data['unggulan'], 'published_at' => $publishedAt];
            if ($id) {
                panel_query('UPDATE berita SET judul=:judul, slug=:slug, ringkasan=:ringkasan, isi=:isi, gambar=:gambar, status=:status, unggulan=:unggulan, published_at=:published_at WHERE id=:id', $params + ['id' => $id]);
            } else {
                panel_query('INSERT INTO berita (judul,slug,ringkasan,isi,gambar,status,unggulan,published_at,penulis_id) VALUES (:judul,:slug,:ringkasan,:isi,:gambar,:status,:unggulan,:published_at,:penulis_id)', $params + ['penulis_id' => $userId]);
                $id = (int) db()->lastInsertId();
            }
            db()->commit();
            return $id;
        } catch (Throwable $exception) {
            if (db()->inTransaction()) { db()->rollBack(); }
            throw $exception;
        }
    });
}
