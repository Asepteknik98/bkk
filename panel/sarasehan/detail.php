<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-forums.php';
$user=require_role('admin','staf_bkk');industry_forums_require_ready();$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}try{$forum=industry_forum_find($id);}catch(InvalidArgumentException){panel_not_found();}
$invites=panel_query('SELECT i.*,m.nama FROM industry_forum_invitees i JOIN mitra m ON m.id=i.mitra_id WHERE i.forum_id=:id ORDER BY m.nama,m.id',['id'=>$id])->fetchAll();
panel_start($user,'Detail Sarasehan Industri','sarasehan');
?>

<div class="forum-list-page forum-detail-page">
<header class="partner-detail-heading"><h1><?= panel_icon('calendar') ?> <?= e($forum['nama']) ?></h1><nav aria-label="Tindakan sarasehan"><a href="<?= e(base_url('panel/sarasehan/')) ?>"><?= panel_icon('back') ?> Daftar jadwal</a><a class="forum-edit" href="<?= e(base_url('panel/sarasehan/form.php?id='.$id)) ?>"><?= panel_icon('edit') ?> Edit jadwal</a><a class="forum-export" href="<?= e(base_url('panel/sarasehan/unduh.php?id='.$id)) ?>"><?= panel_icon('download') ?> Rekap Excel</a></nav></header>
<section class="forum-summary" aria-label="Informasi kegiatan"><dl>
<div><dt><?= panel_icon('calendar') ?> Tanggal</dt><dd><?= e($forum['tanggal']) ?></dd></div>
<div><dt><?= panel_icon('calendar') ?> Jam</dt><dd><?= e($forum['jam']) ?> WIB</dd></div>
<div><dt><?= panel_icon('location') ?> Lokasi</dt><dd><?= e($forum['lokasi']?:'Pertemuan daring') ?></dd></div>
<div><dt><?= panel_icon('akun') ?> PIC sekolah</dt><dd><?= e($forum['pic_sekolah']) ?></dd></div>
<div class="forum-summary-wide"><dt><?= panel_icon('link') ?> Tautan pertemuan</dt><dd><?php if($forum['tautan']&&http_url($forum['tautan'])): ?><a href="<?= e($forum['tautan']) ?>" target="_blank" rel="noopener noreferrer"><?= e($forum['tautan']) ?></a><?php else: ?><span class="forum-value-muted">Tidak diisi</span><?php endif; ?></dd></div>
</dl></section>
<div class="forum-table-heading"><h2><?= panel_icon('mitra') ?> Mitra undangan</h2><span><?= count($invites) ?> mitra</span></div>
<div class="table-scroll forum-list-sheet forum-detail-invites" data-vertical-list tabindex="0" role="region" aria-label="Mitra undangan dan PIC, gulir untuk melihat data"><table><thead><tr><th><span><?= panel_icon('mitra') ?> Perusahaan</span></th><th><span><?= panel_icon('akun') ?> PIC industri</span></th><th><span><?= panel_icon('phone') ?> Kontak</span></th></tr></thead><tbody>
<?php foreach($invites as $invite): ?><tr><td><?= e($invite['nama']) ?></td><td><?= e($invite['pic_nama']) ?></td><td><?= e($invite['pic_kontak']?:'Belum diisi') ?></td></tr><?php endforeach; ?>
<?php if(!$invites): ?><tr><td colspan="3">Belum ada mitra undangan.</td></tr><?php endif; ?>
</tbody></table></div>
</div>
<?php panel_end($user); ?>
