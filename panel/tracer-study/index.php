<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');[$filters,$where,$params]=tracer_filters($_GET);
$total=(int)panel_query('SELECT COUNT(*) FROM tracer_responses WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/15));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT id,nama_lengkap,jurusan,tahun_lulus,status_kegiatan,created_at FROM tracer_responses WHERE '.$where.' ORDER BY id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>15,'offset'=>($page-1)*15])->fetchAll();
panel_start($user,'Respons Tracer Study','tracer');
?>
<div class="page-actions"><h1>Respons Tracer Study</h1><div class="row-actions"><a href="<?= e(base_url('panel/tracer-study/rekap.php?'.http_build_query($filters))) ?>">Rekap status</a><a class="button" href="<?= e(base_url('panel/tracer-study/export.php?'.http_build_query($filters))) ?>">Ekspor CSV sesuai filter</a></div></div>
<p>Data pribadi alumni hanya untuk pengelola berwenang. Gunakan sesuai tujuan persetujuan pendataan.</p><?php tracer_filter_form($filters);?><p><?= $total ?> respons · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Nama</th><th>Jurusan</th><th>Tahun lulus</th><th>Status</th><th>Dikirim</th><th>Detail</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?= e($row['nama_lengkap']) ?></td><td><?= e($row['jurusan']) ?></td><td><?= $row['tahun_lulus'] ?></td><td><?= e(tracer_statuses()[$row['status_kegiatan']]??$row['status_kegiatan']) ?></td><td><?= e($row['created_at']) ?></td><td><a href="<?= e(base_url('panel/tracer-study/detail.php?id='.$row['id'])) ?>">Lihat respons</a></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="6" class="empty-panel">Belum ada respons yang sesuai filter.</td></tr><?php endif;?></tbody></table></div>
<?php if($pages>1):?><nav class="pagination" aria-label="Halaman respons"><?php foreach(['← Sebelumnya'=>$page-1,'Berikutnya →'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/tracer-study/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav><?php endif;?>
<?php panel_end($user);?>
