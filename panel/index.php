<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/panel.php';
require_once APP_ROOT.'/app/helpers/tracer.php';
$user=require_role('admin','staf_bkk');
$counts=[];
foreach(['berita','lowongan','program'] as $table){
    $counts[$table]=panel_query("SELECT status,COUNT(*) AS jumlah FROM $table GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
}
$today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
$open=(int)panel_query("SELECT COUNT(*) FROM lowongan WHERE status='published' AND batas_lamaran>=:today",['today'=>$today])->fetchColumn();
$activePartners=(int)panel_query('SELECT COUNT(*) FROM mitra WHERE aktif=1')->fetchColumn();
$mediaCount=(int)panel_query('SELECT COUNT(*) FROM media')->fetchColumn();
$tracer=panel_query('SELECT status_kegiatan,COUNT(*) AS jumlah FROM tracer_responses GROUP BY status_kegiatan')->fetchAll(PDO::FETCH_KEY_PAIR);
$users=$user['role']==='admin'?(int)panel_query('SELECT COUNT(*) FROM users WHERE aktif=1')->fetchColumn():null;
panel_start($user,'Dashboard','dashboard');
?>
<h1>Dashboard</h1><p>Selamat datang, <?= e($user['nama']) ?>. Ringkasan berikut berasal dari data yang tersimpan saat halaman dibuka.</p>
<div class="summary-grid">
<?php foreach(['berita'=>'Berita','lowongan'=>'Lowongan','program'=>'Program'] as $key=>$label): ?><section class="card"><h2><a href="<?= e(base_url('panel/'.$key.'/')) ?>"><?= $label ?></a></h2><p><strong><?= array_sum($counts[$key]) ?></strong> total</p><p><?= (int)($counts[$key]['published']??0) ?> published · <?= (int)($counts[$key]['draft']??0) ?> draft</p></section><?php endforeach; ?>
<section class="card"><h2>Lowongan masih dibuka</h2><p><strong><?= $open ?></strong> lowongan published dengan batas lamaran hari ini atau setelahnya (WIB).</p></section>
<section class="card"><h2><a href="<?= e(base_url('panel/mitra/')) ?>">Mitra aktif</a></h2><p><strong><?= $activePartners ?></strong> mitra</p></section>
<section class="card"><h2><a href="<?= e(base_url('panel/media/')) ?>">Media</a></h2><p><strong><?= $mediaCount ?></strong> berkas dalam katalog</p></section>
<?php if($users!==null): ?><section class="card"><h2><a href="<?= e(base_url('panel/pengguna/')) ?>">Pengguna aktif</a></h2><p><strong><?= $users ?></strong> akun</p></section><?php endif; ?>
</div>
<section class="card section-space tracer-summary" aria-labelledby="tracer-chart-title">
    <div class="tracer-summary-heading"><h2 id="tracer-chart-title">Ringkasan Tracer Study</h2><span class="badge"><?= (int) array_sum($tracer) ?> respons</span></div>
    <p class="muted">Jumlah respons berdasarkan kegiatan alumni, bukan jumlah alumni unik.</p>
    <?php
    $labels = tracer_statuses();
    if (isset($tracer['lainnya'])) { $labels['lainnya'] = 'Lainnya (data lama)'; }
    $chartMax = max(1, ...array_map(static fn($status): int => (int) ($tracer[$status] ?? 0), array_keys($labels)));
    ?>
    <dl class="tracer-chart" aria-label="Grafik jumlah respons per kegiatan; panjang batang dibandingkan kategori terbanyak">
        <?php foreach ($labels as $status => $label): $value = (int) ($tracer[$status] ?? 0); ?>
        <div class="tracer-chart-row">
            <dt><?= e($label) ?></dt>
            <dd><span class="tracer-chart-track" aria-hidden="true"><span class="tracer-chart-bar" style="width:<?= (int) round($value / $chartMax * 100) ?>%"></span></span><strong><?= $value ?><span class="sr-only"> respons</span></strong></dd>
        </div>
        <?php endforeach; ?>
    </dl>
    <?php if (array_sum($tracer) === 0): ?><p class="muted">Belum ada respons. Grafik akan terisi setelah alumni mengirim formulir.</p><?php endif; ?>
    <a href="<?= e(base_url('panel/tracer-study/rekap.php')) ?>">Lihat rekap dan filter &rarr;</a>
</section>
<?php panel_end($user); ?>
