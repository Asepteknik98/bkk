<?php
declare(strict_types=1);

function permission_modules(): array
{
    return ['berita'=>'Berita & kegiatan','lowongan'=>'Lowongan','lamaran'=>'Lamaran & berkas pribadi',
        'program'=>'Program BMW SAKTI','tracer-study'=>'Data alumni & tautan formulir','media'=>'Media publik',
        'mitra'=>'Mitra','whatsapp'=>'Kontak & pesan WhatsApp','peserta'=>'Data siswa / PKL',
        'pkl'=>'Praktik Kerja Lapangan (PKL)','capaian-pembelajaran'=>'Capaian pembelajaran','absensi'=>'Absensi & data peserta',
        'pemateri'=>'Pemateri & dokumen','kelas-industri'=>'Kelas Industri (operasional)','program-minat'=>'Minat & pertanyaan',
        'mou'=>'Riwayat & dokumen MoU','sarasehan'=>'Sarasehan Industri','ide-industri'=>'Kotak Ide Industri (privat)','biaya-perjalanan'=>'Perjalanan dinas & biaya'];
}

function user_can(array $user, string $module): bool
{
    if ($user['role'] === 'admin') { return true; }
    if ($user['role'] !== 'staf_bkk' || !isset(permission_modules()[$module])) { return false; }
    // Fail closed when the migration is missing; never grant access on a database error.
    try {
        $query = db()->prepare('SELECT 1 FROM user_permissions WHERE user_id=:id AND module=:module');
        $query->execute(['id'=>$user['id'], 'module'=>$module]);
        return (bool)$query->fetchColumn();
    } catch (PDOException $exception) {
        if (($exception->errorInfo[1] ?? null) === 1146) { return false; }
        throw $exception;
    }
}

function require_permission(array $user, string $module): void
{
    $dependencies = ['whatsapp'=>['lamaran','tracer-study'], 'absensi'=>['peserta'], 'pkl'=>['peserta'], 'kelas-industri'=>['peserta'], 'mou'=>['mitra'], 'sarasehan'=>['mitra'], 'ide-industri'=>['mitra'], 'biaya-perjalanan'=>['pkl','peserta']];
    $allowed=user_can($user, $module);
    foreach ($dependencies[$module] ?? [] as $required) { $allowed=$allowed && user_can($user,$required); }
    if (!$allowed) {
        http_response_code(403);
        exit('Anda tidak memiliki izin untuk modul ini. Hubungi Admin BKK.');
    }
}

/** Resolve the executed file, never a user-controlled URL or hidden form module. */
function panel_route_permission(string $file, array $query): ?string
{
    $root = str_replace('\\', '/', realpath(APP_ROOT) ?: APP_ROOT).'/panel/';
    $file = str_replace('\\', '/', realpath($file) ?: $file);
    if (!str_starts_with(strtolower($file), strtolower($root))) { return null; }
    $route = substr($file, strlen($root));
    if ($route === 'index.php' || str_starts_with($route, 'akun/')) { return null; }
    if ($route === 'ekspor.php') {
        return match (is_string($query['jenis'] ?? null) ? $query['jenis'] : '') {
            'peserta'=>'peserta', 'capaian'=>'capaian-pembelajaran', 'pemateri'=>'pemateri',
            'absensi','rekap'=>'absensi', default=>'__denied',
        };
    }
    $module = explode('/', $route)[0];
    return isset(permission_modules()[$module]) ? $module : '__denied';
}

function user_permission_selection(int $id): array
{
    $query=db()->prepare('SELECT module FROM user_permissions WHERE user_id=:id');
    $query->execute(['id'=>$id]);
    return $query->fetchAll(PDO::FETCH_COLUMN);
}
