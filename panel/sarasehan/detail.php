<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-forums.php';
$user=require_role('admin','staf_bkk');industry_forums_require_ready();$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}try{$forum=industry_forum_find($id);}catch(InvalidArgumentException){panel_not_found();}
$invites=panel_query('SELECT i.*,m.nama FROM industry_forum_invitees i JOIN mitra m ON m.id=i.mitra_id WHERE i.forum_id=:id ORDER BY m.nama,m.id',['id'=>$id])->fetchAll();
panel_start($user,'Detail Sarasehan Industri','sarasehan');
?>
<div class="page-actions"><h1><?= e($forum['nama']) ?></h1><a href="<?= e(base_url('panel/sarasehan/')) ?>">Daftar jadwal</a><a href="<?= e(base_url('panel/sarasehan/form.php?id='.$id)) ?>">Edit</a><a href="<?= e(base_url('panel/sarasehan/unduh.php?id='.$id)) ?>">Unduh jadwal Excel</a></div>
<section class="card"><dl class="response-details"><dt>Jadwal</dt><dd><?= e($forum['tanggal'].' '.$forum['jam'].' WIB') ?></dd><dt>Lokasi</dt><dd><?= e($forum['lokasi']?:'Pertemuan daring') ?></dd><dt>Tautan</dt><dd><?php if($forum['tautan']&&http_url($forum['tautan'])): ?><a href="<?= e($forum['tautan']) ?>" target="_blank" rel="noopener noreferrer"><?= e($forum['tautan']) ?></a><?php else: ?>Tidak diisi<?php endif; ?></dd><dt>PIC sekolah</dt><dd><?= e($forum['pic_sekolah']) ?></dd></dl></section>
<h2>Mitra yang diundang</h2><div class="table-scroll"><table><thead><tr><th>Perusahaan</th><th>PIC industri</th><th>Kontak</th></tr></thead><tbody><?php foreach($invites as $invite): ?><tr><td><?= e($invite['nama']) ?></td><td><?= e($invite['pic_nama']) ?></td><td><?= e($invite['pic_kontak']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php panel_end($user); ?>
