<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/jobs.php';

/** Pembacaan publik gagal dengan pemberitahuan, tanpa menampilkan detail koneksi. */
function public_query(string $sql, array $params = []): array
{
    if (!empty($GLOBALS['public_database_unavailable'])) {
        return [];
    }
    try {
        $statement = db()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement->fetchAll();
    } catch (PDOException $exception) {
        $GLOBALS['public_database_unavailable'] = true;
        error_log('Pembacaan data portal gagal; kode SQLSTATE: ' . $exception->getCode());
        return [];
    }
}

function setting(string $key, string $default = ''): string
{
    static $settings = null;
    if ($settings === null) {
        $settings = array_column(public_query('SELECT setting_key, setting_value FROM settings'), 'setting_value', 'setting_key');
    }
    $value = $settings[$key] ?? '';
    return is_string($value) && trim($value) !== '' ? $value : $default;
}

function program_categories(): array
{
    return [
        'kelas_industri' => ['title' => 'Kelas Industri dan Pasar Kerja', 'path' => 'kelas-industri/', 'description' => 'Informasi pembelajaran industri dan kebutuhan dunia kerja.', 'short' => 'Industri'],
        'ppks' => ['title' => 'Program PPKS', 'path' => 'program-ppks/', 'description' => 'Pusat Pengembangan Karir Siswa: informasi persiapan karir.', 'short' => 'Karir'],
        'kelas_melanjutkan' => ['title' => 'Kelas Melanjutkan', 'path' => 'kelas-melanjutkan/', 'description' => 'Informasi kuliah dan beasiswa di dalam maupun luar negeri.', 'short' => 'Studi lanjut'],
        'entrepreneur' => ['title' => 'Entrepreneur Program', 'path' => 'entrepreneur-program/', 'description' => 'Informasi pengembangan usaha dan kewirausahaan.', 'short' => 'Wirausaha'],
        'kelas_internasional' => ['title' => 'Kelas Internasional', 'path' => 'kelas-internasional/', 'description' => 'Informasi persiapan dan program internasional.', 'short' => 'Internasional'],
    ];
}

function program_url(array $program): string
{
    $categories=program_categories();
    if(!isset($categories[$program['kategori']])){throw new InvalidArgumentException('Kategori program tidak diizinkan.');}
    return base_url($categories[$program['kategori']]['path'].'detail.php?slug='.rawurlencode($program['slug']));
}

/** Hanya gambar lokal raster dalam uploads/assets/images; SVG unggahan tidak ditampilkan. */
function content_image(?string $path): string
{
    $path = ltrim($path ?? '', '/');
    if (preg_match('~^(?:uploads/|assets/images/)[a-zA-Z0-9_./-]+\.(?:png|jpe?g|webp|gif)$~i', $path)
        && !str_contains($path, '..')) {
        $real = realpath(APP_ROOT . '/' . $path);
        $root = realpath(APP_ROOT);
        if ($real && $root && is_file($real) && str_starts_with($real, $root . DIRECTORY_SEPARATOR)) {
            return base_url($path);
        }
    }
    return asset_url('images/placeholder.svg');
}

function demo_label(array $item): void
{
    foreach (['judul', 'slug', 'ringkasan', 'isi', 'perusahaan', 'posisi', 'deskripsi', 'nama'] as $key) {
        if (preg_match('/data[\s_-]*demo/i', (string) ($item[$key] ?? ''))) {
            echo '<span class="demo-badge">DATA DEMO · Bukan informasi resmi</span>';
            return;
        }
    }
}

function indo_date(?string $value): string
{
    if (!$value) { return 'Belum ditentukan'; }
    try { $date = new DateTimeImmutable($value); } catch (Exception) { return 'Belum ditentukan'; }
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $date->format('j') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
}

function empty_state(string $title, string $description = 'Informasi yang telah diterbitkan oleh pengelola akan tampil di sini.'): void
{
    echo '<div class="empty-state"><span class="empty-mark" aria-hidden="true">—</span><h3>' . e($title) . '</h3><p>' . e($description) . '</p></div>';
}

function news_url(array $item): string
{
    return base_url('detail-berita.php?slug=' . rawurlencode($item['slug']));
}

function news_card(array $item): void
{
    ?>
    <article class="news-card">
        <a class="image-link" href="<?= e(news_url($item)) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e(content_image($item['gambar'])) ?>" alt="" loading="lazy" width="640" height="400"></a>
        <div class="news-card-body">
            <div class="meta">Berita &amp; kegiatan <span>·</span> <?= e(indo_date($item['published_at'] ?? $item['created_at'])) ?></div>
            <?php demo_label($item); ?>
            <h3><a href="<?= e(news_url($item)) ?>"><?= e($item['judul']) ?></a></h3>
            <p class="clamp-text"><?= e($item['ringkasan']) ?></p>
            <a class="text-link" href="<?= e(news_url($item)) ?>">Baca selengkapnya <span aria-hidden="true">↗</span><span class="visually-hidden">: <?= e($item['judul']) ?></span></a>
        </div>
    </article>
    <?php
}

function job_card(array $item): void
{
    $open = job_is_open($item);
    ?>
    <article class="job-card">
        <p class="eyebrow"><?= e($item['perusahaan']) ?></p>
        <?php demo_label($item); ?>
        <span class="job-status <?= $open ? 'is-open' : 'is-closed' ?>"><?= e(job_status_label($item)) ?></span>
        <h3><a href="<?= e(job_url($item)) ?>"><?= e($item['posisi']) ?></a></h3>
        <p><?= e($item['lokasi']) ?> <span aria-hidden="true">·</span> <?= e($item['jenis_pekerjaan']) ?></p>
        <p class="job-education"><?= e($item['pendidikan']) ?></p>
        <p class="deadline<?= !$open ? ' expired' : '' ?>"><strong>Batas lamaran:</strong> <?= e(indo_date($item['batas_lamaran'])) ?><?= $item['batas_lamaran'] ? ' · 23:59:59 WIB' : '' ?></p>
        <a class="text-link" href="<?= e(job_url($item)) ?>">Lihat detail lowongan ↗</a>
    </article>
    <?php
}
