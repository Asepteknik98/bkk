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
<div class="dashboard-view"><header class="dashboard-heading"><h1><?= panel_icon('dashboard') ?> Dashboard</h1><p>Selamat datang, <?= e($user['nama']) ?>. Berikut ringkasan data BKK saat ini.</p></header>
<div class="summary-grid">
<?php foreach(['berita'=>'Berita','lowongan'=>'Lowongan','program'=>'Program'] as $key=>$label): ?><section class="card"><span class="stat-icon"><?= panel_icon($key) ?></span><h2><a href="<?= e(base_url('panel/'.$key.'/')) ?>"><?= $label ?></a></h2><p><strong><?= array_sum($counts[$key]) ?></strong> total</p><p><?= (int)($counts[$key]['published']??0) ?> published · <?= (int)($counts[$key]['draft']??0) ?> draft</p></section><?php endforeach; ?>
<section class="card"><span class="stat-icon"><?= panel_icon('lowongan') ?></span><h2><a href="<?= e(base_url('panel/lowongan/?status=open')) ?>">Lowongan dibuka</a></h2><p><strong><?= $open ?></strong> aktif</p><p>Published, batas lamaran belum lewat (WIB).</p></section>
<section class="card"><span class="stat-icon"><?= panel_icon('mitra') ?></span><h2><a href="<?= e(base_url('panel/mitra/')) ?>">Mitra aktif</a></h2><p><strong><?= $activePartners ?></strong> mitra</p></section>
<section class="card"><span class="stat-icon"><?= panel_icon('media') ?></span><h2><a href="<?= e(base_url('panel/media/')) ?>">Media</a></h2><p><strong><?= $mediaCount ?></strong> berkas dalam katalog</p></section>
<?php if($users!==null): ?><section class="card"><span class="stat-icon"><?= panel_icon('pengguna') ?></span><h2><a href="<?= e(base_url('panel/pengguna/')) ?>">Pengguna aktif</a></h2><p><strong><?= $users ?></strong> akun</p></section><?php endif; ?>
</div>
<div class="dashboard-charts"><section class="card section-space tracer-summary" aria-labelledby="tracer-chart-title">
    <div class="tracer-summary-heading"><h2 id="tracer-chart-title"><?= panel_icon('tracer') ?> Ringkasan Tracer Study</h2><span class="badge"><?= (int) array_sum($tracer) ?> respons</span></div>
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
<section class="card tracer-visual" aria-labelledby="tracer-visual-title">
    <h2 id="tracer-visual-title"><?= panel_icon('tracer') ?> Komposisi kegiatan alumni</h2>
    <p class="muted">Arahkan atau pilih kategori untuk melihat jumlah respons.</p>
    <?php $tracerTotal = (int) array_sum($tracer); $offset = 0; $categoryIndex = 0; ?>
    <div class="tracer-donut-layout">
        <div class="tracer-donut" data-total="<?= $tracerTotal ?>">
            <svg viewBox="0 0 120 120" role="img" aria-label="Komposisi respons Tracer Study: <?= $tracerTotal ?> respons">
                <circle class="donut-track" cx="60" cy="60" r="46" fill="none" stroke-width="12"/>
                <?php foreach ($labels as $status => $label): $value = (int)($tracer[$status] ?? 0); $share = $tracerTotal ? $value / $tracerTotal * 100 : 0; ?>
                <circle class="donut-segment category-<?= $categoryIndex ?>" data-category="<?= $categoryIndex ?>" cx="60" cy="60" r="46" pathLength="100" fill="none" stroke-width="12" stroke-dasharray="<?= $share ?> <?= 100-$share ?>" stroke-dashoffset="<?= -$offset ?>" transform="rotate(-90 60 60)"><title><?= e($label) ?>: <?= $value ?> respons</title></circle>
                <?php $offset += $share; $categoryIndex++; endforeach; ?>
            </svg>
            <div class="donut-center" aria-live="polite"><strong><?= $tracerTotal ?></strong><span>Total respons</span></div>
        </div>
        <div class="donut-legend" aria-label="Kategori kegiatan">
            <?php $categoryIndex = 0; foreach ($labels as $status => $label): $value = (int)($tracer[$status] ?? 0); ?>
            <button type="button" class="category-<?= $categoryIndex ?>" data-category="<?= $categoryIndex ?>" data-label="<?= e($label) ?>" data-value="<?= $value ?>"><span class="legend-dot" aria-hidden="true"></span><span><?= e($label) ?></span><strong><?= $value ?></strong></button>
            <?php $categoryIndex++; endforeach; ?>
        </div>
    </div>
    <?php if (!$tracerTotal): ?><p class="muted">Grafik akan terisi setelah ada respons alumni.</p><?php endif; ?>
</section></div>

</div>
<?php panel_end($user); ?>
