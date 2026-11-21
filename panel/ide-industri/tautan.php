<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-ideas.php';
$user=require_role('admin','staf_bkk');industry_ideas_require_ready();header('Referrer-Policy: no-referrer');header('Cache-Control: no-store');
$partnerId=positive_id($_GET['mitra_id']??'');if(isset($_GET['mitra_id'])&&$_GET['mitra_id']!==''&&!$partnerId){panel_not_found();}
$partner=$partnerId?panel_query("SELECT id,nama,aktif FROM mitra WHERE id=:id AND jenis='perusahaan'",['id'=>$partnerId])->fetch():null;if($partnerId&&!$partner){panel_not_found();}
$url=null;$message='';$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();if(!$partner){http_response_code(400);exit('Pilih perusahaan terlebih dahulu.');}
    $action=input_text($_POST,'aksi');$hours=filter_var($_POST['hours']??null,FILTER_VALIDATE_INT);
    try{
        if(!in_array($action,['buat','cabut'],true)){throw new InvalidArgumentException('Tindakan tidak valid.');}
        $token=industry_idea_link_change($partnerId,(int)$hours,(int)$user['id'],$action==='cabut');
        if($token){$url=base_url('kotak-ide/?token='.$token);}$message=$token?'Tautan baru dibuat; tautan sebelumnya dicabut.':'Tautan yang belum digunakan telah dicabut.';
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
}
$q='';$partners=[];$total=0;$page=1;$pages=1;
if(!$partner){
    $q=input_text($_GET,'q');if(!valid_text($q,100,false)){http_response_code(400);exit('Pencarian terlalu panjang atau tidak valid.');}
    $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';
    $total=(int)panel_query("SELECT COUNT(*) FROM mitra WHERE jenis='perusahaan' AND nama LIKE :nama ESCAPE '!'",['nama'=>$pattern])->fetchColumn();
    $pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
    $partners=panel_query("SELECT id,nama,aktif FROM mitra WHERE jenis='perusahaan' AND nama LIKE :nama ESCAPE '!' ORDER BY nama,id LIMIT :limit OFFSET :offset",['nama'=>$pattern,'limit'=>10,'offset'=>($page-1)*10])->fetchAll();
}
panel_start($user,'Tautan Kotak Ide Industri','ide-industri');
?>
<div class="idea-list-page idea-link-page">
<header class="partner-detail-heading"><h1><?= panel_icon('link') ?> Tautan khusus mitra</h1><nav aria-label="Navigasi kotak ide"><a href="<?= e(base_url('panel/ide-industri/')) ?>"><?= panel_icon('back') ?> Kotak Ide Industri</a></nav></header>
<?php if(!$partner): ?>
<form class="filter-form card idea-company-search" method="get"><label for="idea-company-search"><span><?= panel_icon('search') ?> Cari perusahaan</span><input id="idea-company-search" name="q" maxlength="100" value="<?= e($q) ?>" placeholder="Nama perusahaan"></label><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/ide-industri/tautan.php')) ?>"><?= panel_icon('reset') ?> Reset</a></form>
<div class="idea-table-heading"><h2><?= panel_icon('mitra') ?> Daftar perusahaan</h2><span><?= $total ?> mitra &middot; Halaman <?= $page ?> dari <?= $pages ?></span></div>
<div class="table-scroll idea-list-sheet idea-company-sheet" data-vertical-list tabindex="0" role="region" aria-label="Pilihan perusahaan, gulir untuk melihat data"><table><thead><tr><th><span><?= panel_icon('mitra') ?> Perusahaan</span></th><th><span><?= panel_icon('check-circle') ?> Status mitra</span></th><th><span><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead><tbody>
<?php foreach($partners as $item): ?><tr><td><strong><?= e($item['nama']) ?></strong></td><td><span class="partner-state <?= $item['aktif']?'is-active':'is-inactive' ?>"><?= $item['aktif']?'Aktif':'Nonaktif' ?></span></td><td><a class="idea-read" href="<?= e(base_url('panel/ide-industri/tautan.php?mitra_id='.$item['id'])) ?>"><?= panel_icon('link') ?> Kelola tautan</a></td></tr><?php endforeach; ?>
<?php if(!$partners): ?><tr><td colspan="3">Perusahaan tidak ditemukan.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman perusahaan"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/ide-industri/tautan.php?'.http_build_query(['q'=>$q,'page'=>$target]))) ?>"><?= panel_icon($target<$page?'back':'next') ?> <?= $label ?></a><?php endforeach; ?></nav>
<?php else: ?>

<div class="idea-partner-heading"><strong><?= panel_icon('mitra') ?> <?= e($partner['nama']) ?></strong><a href="<?= e(base_url('panel/ide-industri/tautan.php')) ?>"><?= panel_icon('reset') ?> Ganti perusahaan</a></div>
<?php if($message): ?><p class="notice" role="status"><?= e($message) ?></p><?php endif; ?><?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<?php if(!$partner['aktif']): ?><p class="notice">Mitra nonaktif. Pembuatan tautan dinonaktifkan; tautan lama masih dapat dicabut.</p><?php endif; ?>
<div class="idea-link-controls">
<form class="idea-link-form" method="post"><?= csrf_field() ?><div class="idea-hours-field"><label for="hours"><?= panel_icon('calendar') ?> Masa berlaku (jam)</label><input id="hours" name="hours" type="number" min="1" max="720" value="48" required><small>1&ndash;720 jam</small></div><div class="idea-link-actions"><button type="submit" name="aksi" value="buat" class="idea-create" <?= !$partner['aktif']?'disabled':'' ?>><?= panel_icon('add') ?> Buat tautan</button><button type="submit" name="aksi" value="cabut" class="idea-revoke" formnovalidate><?= panel_icon('close') ?> Cabut tautan belum dipakai</button></div></form>
<?php if($url): ?><div class="idea-new-link"><label for="industry-idea-url"><?= panel_icon('link') ?> Tautan baru</label><input id="industry-idea-url" readonly value="<?= e($url) ?>"><small>Salin sekarang. Tautan hanya ditampilkan saat dibuat.</small><script>const ideaUrl=document.getElementById('industry-idea-url');ideaUrl.value=new URL(ideaUrl.value,window.location.href).href;</script></div><?php endif; ?>
<p class="idea-link-note"><?= panel_icon('lock') ?> Sekali kirim. Bagikan secara pribadi. Membuat tautan baru mencabut tautan lama yang belum dipakai.</p>
</div>
<?php $links=panel_query('SELECT created_at,expires_at,used_at,revoked_at FROM industry_idea_links WHERE mitra_id=:id ORDER BY id DESC LIMIT 10',['id'=>$partnerId])->fetchAll(); ?>
<div class="idea-table-heading"><h2><?= panel_icon('history') ?> Riwayat tautan</h2><span>10 catatan terbaru &middot; WIB</span></div>
<div class="table-scroll idea-list-sheet idea-link-history" data-vertical-list tabindex="0" role="region" aria-label="Riwayat tautan mitra, gulir untuk melihat data"><table><thead><tr><?php foreach(['Dibuat','Kedaluwarsa','Digunakan','Dicabut'] as $label): ?><th><span><?= panel_icon('calendar') ?> <?= $label ?></span></th><?php endforeach; ?></tr></thead><tbody><?php foreach($links as $link): ?><tr><?php foreach(['created_at','expires_at','used_at','revoked_at'] as $key): ?><td><?= e(industry_idea_display_time($link[$key])) ?></td><?php endforeach; ?></tr><?php endforeach; ?><?php if(!$links): ?><tr><td colspan="4">Belum ada tautan yang dibuat.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?>

</div>
<?php panel_end($user); ?>
