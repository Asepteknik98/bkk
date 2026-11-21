<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-ideas.php';
$user=require_role('admin','staf_bkk');$ready=industry_ideas_ready();$status=input_text($_GET,'status');
if($status!==''&&!isset(industry_idea_statuses()[$status])){http_response_code(400);exit('Status tidak valid.');}
$params=[];$where='1=1';if($status!==''){$where.=' AND status=:status';$params['status']=$status;}
$total=$ready?(int)panel_query('SELECT COUNT(*) FROM industry_ideas WHERE '.$where,$params)->fetchColumn():0;$pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
// Contact and full content are only fetched by the separately guarded detail page.
$rows=$ready?panel_query('SELECT id,perusahaan,nama_pengirim,judul,status,created_at FROM industry_ideas WHERE '.$where.' ORDER BY created_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>10,'offset'=>($page-1)*10])->fetchAll():[];
panel_start($user,'Kotak Ide Industri','ide-industri');
?>
<div class="idea-list-page">
<header class="partner-detail-heading"><h1><?= panel_icon('message') ?> Kotak Ide Industri</h1><nav aria-label="Pengelolaan kotak ide"><?php if($ready): ?><a class="idea-manage" href="<?= e(base_url('panel/ide-industri/tautan.php')) ?>"><?= panel_icon('link') ?> Tautan pengiriman mitra</a><?php endif; ?></nav></header>
<p class="idea-private-note"><?= panel_icon('lock') ?> Usulan privat mitra &middot; hanya pengelola berizin.</p>
<?php if(!$ready): ?><p class="notice">Impor database/updates/023_industry_forums_ideas.sql untuk mengaktifkan modul.</p><?php endif; ?>
<form class="filter-form card" method="get"><label><span><?= panel_icon('filter') ?> Status penanganan</span><select name="status"><option value="">Semua</option><?php foreach(industry_idea_statuses() as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button type="submit"><?= panel_icon('search') ?> Terapkan</button><a href="<?= e(base_url('panel/ide-industri/')) ?>"><?= panel_icon('reset') ?> Reset</a></form>
<div class="idea-table-heading"><h2><?= panel_icon('berita') ?> Daftar usulan</h2><span><?= $total ?> usulan &middot; Halaman <?= $page ?> dari <?= $pages ?></span></div><div class="table-scroll idea-list-sheet" data-vertical-list tabindex="0" role="region" aria-label="Daftar usulan industri, gulir untuk melihat data"><table><thead><tr><th><span><?= panel_icon('message') ?> Judul</span></th><th><span><?= panel_icon('mitra') ?> Perusahaan</span></th><th><span><?= panel_icon('akun') ?> Pengirim</span></th><th><span><?= panel_icon('calendar') ?> Dikirim</span></th><th><span><?= panel_icon('check-circle') ?> Status</span></th><th><span><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?= e($row['judul']) ?></td><td><?= e($row['perusahaan']) ?></td><td><?= e($row['nama_pengirim']) ?></td><td><?= e(industry_idea_display_time($row['created_at'])) ?></td><td><span class="idea-state idea-state-<?= e($row['status']) ?>"><?= e(industry_idea_statuses()[$row['status']]) ?></span></td><td><a class="idea-read" href="<?= e(base_url('panel/ide-industri/detail.php?id='.$row['id'])) ?>"><?= panel_icon('view') ?> Baca usulan</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6"><?= $status!==''?'Tidak ada usulan sesuai status yang dipilih.':'Belum ada usulan masuk.' ?></td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman kotak ide"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/ide-industri/?'.http_build_query(['status'=>$status,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
</div>
<?php panel_end($user); ?>
