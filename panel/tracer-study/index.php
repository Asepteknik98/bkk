<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');[$filters,$where,$params]=tracer_filters($_GET);
$total=(int)panel_query('SELECT COUNT(*) FROM tracer_responses WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT id,nama_lengkap,jurusan,tahun_lulus,status_kegiatan,created_at FROM tracer_responses WHERE '.$where.' ORDER BY tahun_lulus DESC,nama_lengkap ASC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>10,'offset'=>($page-1)*10])->fetchAll();
panel_start($user,'Respons Tracer Study','tracer');
?>
<div class="alumni-list"><div class="page-actions"><h1><?= panel_icon('tracer') ?> Data Alumni</h1><div class="row-actions"><a class="button" href="<?= e(base_url('panel/tracer-study/tambah.php')) ?>">+ Tambah data alumni</a><a href="<?= e(base_url('panel/tracer-study/rekap.php?'.http_build_query($filters))) ?>">Rekap status</a><a class="button" href="<?= e(base_url('panel/tracer-study/export.php?'.http_build_query($filters))) ?>">Ekspor CSV sesuai filter</a></div></div>
<p>Data pribadi alumni hanya untuk pengelola berwenang. Gunakan sesuai tujuan persetujuan pendataan.</p><?php tracer_filter_form($filters);?><p><strong><?= $filters['tahun_lulus']!==''?'Angkatan '.e($filters['tahun_lulus']):'Semua angkatan' ?></strong> &middot; <?= $total ?> respons · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll alumni-list-viewport" data-vertical-list id="alumni-list" tabindex="0" role="region" aria-label="Daftar alumni, gulir ke bawah atau geser ke samping"><table><thead><tr><th><?= panel_icon('akun') ?> Nama</th><th><?= panel_icon('program') ?> Jurusan</th><th><?= panel_icon('calendar') ?> Angkatan</th><th><?= panel_icon('lowongan') ?> Status</th><th><?= panel_icon('calendar') ?> Dikirim</th><th><span><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?= e($row['nama_lengkap']) ?></td><td><?= e($row['jurusan']) ?></td><td><?= $row['tahun_lulus'] ?></td><td><?= e(tracer_statuses()[$row['status_kegiatan']]??$row['status_kegiatan']) ?></td><td><?= e($row['created_at']) ?></td><td><div class="tracer-record-actions"><a href="<?= e(base_url('panel/tracer-study/detail.php?id='.$row['id'])) ?>" class="alumni-view-link"><?= panel_icon('view') ?> Lihat</a><a class="tracer-edit" href="<?= e(base_url('panel/tracer-study/edit.php?id='.$row['id'])) ?>" aria-label="<?= e('Edit '.$row['nama_lengkap']) ?>"><?= panel_icon('edit') ?> Edit</a><a class="tracer-delete" href="<?= e(base_url('panel/tracer-study/hapus.php?id='.$row['id'])) ?>" aria-label="<?= e('Hapus '.$row['nama_lengkap']) ?>"><?= panel_icon('trash') ?> Hapus</a></div></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="6" class="empty-panel">Belum ada respons yang sesuai filter.</td></tr><?php endif;?></tbody></table></div>
<div class="alumni-scroll-controls" hidden><span>Gulir daftar</span><button type="button" data-scroll="-1" aria-controls="alumni-list" aria-label="Gulir daftar ke atas"><?= panel_icon('up') ?> Naik</button><button type="button" data-scroll="1" aria-controls="alumni-list" aria-label="Gulir daftar ke bawah"><?= panel_icon('down') ?> Turun</button></div>
<?php $pageStart=max(1,min($page-4,$pages-9));$pageEnd=min($pages,$pageStart+9); ?>
<nav class="pagination news-page-slider" aria-label="Halaman daftar respons">
<span class="news-page-summary"><?= $total?($page-1)*10+1:0 ?>&ndash;<?= min($total,$page*10) ?> dari <?= $total ?> respons</span>
<?php if($page>1): ?><a class="news-page-arrow" rel="prev" href="<?= e(base_url('panel/tracer-study/?'.http_build_query($filters+['page'=>$page-1]))) ?>"><?= panel_icon('back') ?><span>Sebelumnya</span></a><?php else: ?><span class="news-page-arrow is-disabled" aria-disabled="true"><?= panel_icon('back') ?><span>Sebelumnya</span></span><?php endif; ?>
<div class="news-page-numbers">
<?php for($number=$pageStart;$number<=$pageEnd;$number++): ?><a class="news-page-number" href="<?= e(base_url('panel/tracer-study/?'.http_build_query($filters+['page'=>$number]))) ?>" aria-label="Halaman <?= $number ?>" <?= $number===$page?'aria-current="page"':'' ?>><?= $number ?></a><?php endfor; ?>
</div>
<?php if($page<$pages): ?><a class="news-page-arrow" rel="next" href="<?= e(base_url('panel/tracer-study/?'.http_build_query($filters+['page'=>$page+1]))) ?>"><span>Berikutnya</span><?= panel_icon('next') ?></a><?php else: ?><span class="news-page-arrow is-disabled" aria-disabled="true"><span>Berikutnya</span><?= panel_icon('next') ?></span><?php endif; ?>
</nav>

</div>
<script src="<?= e(asset_url('js/alumni-list.js')) ?>?v=<?= (int)filemtime(APP_ROOT.'/assets/js/alumni-list.js') ?>" defer></script>
<?php panel_end($user);?>
