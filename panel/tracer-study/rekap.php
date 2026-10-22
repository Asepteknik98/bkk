<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');[$filters,$where,$params]=tracer_filters($_GET);
$counts=panel_query('SELECT status_kegiatan,COUNT(*) AS jumlah FROM tracer_responses WHERE '.$where.' GROUP BY status_kegiatan',$params)->fetchAll(PDO::FETCH_KEY_PAIR);
$labels=tracer_statuses();if(isset($counts['lainnya'])){$labels['lainnya']='Lainnya (data lama)';}
panel_start($user,'Rekap Tracer Study','tracer');
?>
<?php $totalResponses = (int) array_sum($counts); $icons = ['bekerja'=>'lowongan','kuliah'=>'program','wirausaha'=>'mitra','mencari_kerja'=>'pengguna']; ?>
<div class="tracer-report">
<div class="page-actions"><div><p class="eyebrow">Pendataan alumni</p><h1><?= panel_icon('tracer') ?> Rekap Tracer Study</h1></div><div class="row-actions"><a class="button" href="<?= e(base_url('panel/tracer-study/tambah.php')) ?>">+ Tambah data alumni</a><a class="button secondary" href="<?= e(base_url('panel/tracer-study/?'.http_build_query($filters))) ?>"><?= panel_icon('pengguna') ?> Daftar respons</a><a class="button" href="<?= e(base_url('panel/tracer-study/export.php?'.http_build_query($filters))) ?>"><?= panel_icon('media') ?> Ekspor CSV</a></div></div>
<?php tracer_filter_form($filters); ?>
<div class="report-stats">
<?php foreach ($labels as $status=>$label): $value = (int)($counts[$status]??0); ?>
<section class="report-stat"><span class="report-stat-icon"><?= panel_icon($icons[$status]??'tracer') ?></span><h2><?= e($label) ?></h2><strong><?= $value ?></strong><span><?= $totalResponses ? number_format($value/$totalResponses*100,1,',','.') : '0' ?>% dari respons</span></section>
<?php endforeach; ?>
</div>
<div class="report-panels">
<section class="card report-chart"><div class="report-panel-heading"><h2><?= panel_icon('tracer') ?> Sebaran kegiatan</h2><span class="badge"><?= $totalResponses ?> respons</span></div>
<p class="muted">Persentase dari total respons sesuai filter.</p>
<?php foreach ($labels as $status=>$label): $value=(int)($counts[$status]??0); $percent=$totalResponses?$value/$totalResponses*100:0; ?>
<div class="report-bar-row"><div><span><?= e($label) ?></span><strong><?= $value ?> <small>(<?= number_format($percent,1,',','.') ?>%)</small></strong></div><div class="report-bar-track"><span style="width:<?= $percent ?>%"></span></div></div>
<?php endforeach; ?>
<?php if (!$totalResponses): ?><p class="report-empty">Belum ada respons untuk filter ini.</p><?php endif; ?>
</section>
<section class="card report-table"><div class="report-panel-heading"><h2><?= panel_icon('berita') ?> Rincian respons</h2></div><div class="table-scroll"><table><thead><tr><th>Status kegiatan</th><th>Respons</th></tr></thead><tbody><?php foreach($labels as $status=>$label):?><tr><td><?= e($label) ?></td><td><?= (int)($counts[$status]??0) ?></td></tr><?php endforeach;?><tr><th>Total</th><td><strong><?= $totalResponses ?></strong></td></tr></tbody></table></div></section>
</div>
<p class="report-note">Rekap menghitung respons sesuai filter, bukan jumlah alumni unik. Pengiriman tambahan dapat tersimpan sebagai respons baru.</p>
</div>
<?php panel_end($user); ?>
