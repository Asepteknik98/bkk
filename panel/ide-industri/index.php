<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-ideas.php';
$user=require_role('admin','staf_bkk');$ready=industry_ideas_ready();$status=input_text($_GET,'status');
if($status!==''&&!isset(industry_idea_statuses()[$status])){http_response_code(400);exit('Status tidak valid.');}
$params=[];$where='1=1';if($status!==''){$where.=' AND status=:status';$params['status']=$status;}
$total=$ready?(int)panel_query('SELECT COUNT(*) FROM industry_ideas WHERE '.$where,$params)->fetchColumn():0;$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
// Contact and full content are only fetched by the separately guarded detail page.
$rows=$ready?panel_query('SELECT id,perusahaan,nama_pengirim,judul,status,created_at FROM industry_ideas WHERE '.$where.' ORDER BY created_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll():[];
panel_start($user,'Kotak Ide Industri','ide-industri');
?>
<div class="page-actions"><h1>Kotak Ide Industri</h1><a class="button" href="<?= e(base_url('panel/ide-industri/tautan.php')) ?>">Kelola tautan mitra</a></div>
<p>Masukan privat dari mitra kepada SMK Jaya Buana. Isi usulan dan kontak pengirim hanya tersedia bagi pengelola yang memiliki izin Kotak Ide Industri.</p>
<?php if(!$ready): ?><p class="notice">Impor database/updates/023_industry_forums_ideas.sql untuk mengaktifkan modul.</p><?php endif; ?>
<form class="filter-form card" method="get"><label>Status<select name="status"><option value="">Semua</option><?php foreach(industry_idea_statuses() as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button>Tampilkan</button></form>
<p><?= $total ?> usulan · Halaman <?= $page ?> dari <?= $pages ?></p><div class="table-scroll"><table><thead><tr><th>Judul</th><th>Perusahaan</th><th>Pengirim</th><th>Dikirim</th><th>Status</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?= e($row['judul']) ?></td><td><?= e($row['perusahaan']) ?></td><td><?= e($row['nama_pengirim']) ?></td><td><?= e(industry_idea_display_time($row['created_at'])) ?></td><td><?= e(industry_idea_statuses()[$row['status']]) ?></td><td><a href="<?= e(base_url('panel/ide-industri/detail.php?id='.$row['id'])) ?>">Baca usulan / kontak</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6">Belum ada usulan untuk pilihan ini.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman kotak ide"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/ide-industri/?'.http_build_query(['status'=>$status,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
<?php panel_end($user); ?>
