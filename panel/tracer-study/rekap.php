<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');[$filters,$where,$params]=tracer_filters($_GET);
$counts=panel_query('SELECT status_kegiatan,COUNT(*) AS jumlah FROM tracer_responses WHERE '.$where.' GROUP BY status_kegiatan',$params)->fetchAll(PDO::FETCH_KEY_PAIR);
$labels=tracer_statuses();if(isset($counts['lainnya'])){$labels['lainnya']='Lainnya (data lama)';}
panel_start($user,'Rekap Tracer Study','tracer');
?>
<div class="page-actions"><h1>Rekap Tracer Study</h1><div class="row-actions"><a href="<?= e(base_url('panel/tracer-study/?'.http_build_query($filters))) ?>">Daftar sesuai filter</a><a href="<?= e(base_url('panel/tracer-study/export.php?'.http_build_query($filters))) ?>">Ekspor CSV sesuai filter</a></div></div><?php tracer_filter_form($filters);?>
<div class="table-scroll"><table><thead><tr><th>Status kegiatan</th><th>Jumlah respons</th></tr></thead><tbody><?php foreach($labels as $status=>$label):?><tr><td><?= e($label) ?></td><td><?= (int)($counts[$status]??0) ?></td></tr><?php endforeach;?><tr><th>Total</th><td><?= array_sum($counts) ?></td></tr></tbody></table></div><p>Rekap menghitung respons sesuai filter, bukan jumlah alumni unik. Pengiriman baru yang serupa dapat tersimpan sebagai respons tambahan.</p>
<?php panel_end($user);?>
