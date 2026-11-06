<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-ideas.php';
$user=require_role('admin','staf_bkk');industry_ideas_require_ready();header('Referrer-Policy: no-referrer');
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
panel_start($user,'Tautan Kotak Ide Industri','ide-industri');
?>
<div class="page-actions"><h1>Tautan khusus mitra</h1><a href="<?= e(base_url('panel/ide-industri/')) ?>">Kotak Ide Industri</a></div>
<?php if(!$partner):$q=input_text($_GET,'q');if(!valid_text($q,100,false)){http_response_code(400);exit('Pencarian terlalu panjang.');}$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$partners=panel_query("SELECT id,nama,aktif FROM mitra WHERE jenis='perusahaan' AND nama LIKE :nama ESCAPE '!' ORDER BY nama,id LIMIT 50",['nama'=>$pattern])->fetchAll(); ?>
<form class="filter-form card" method="get"><label>Cari perusahaan<input name="q" maxlength="100" value="<?= e($q) ?>"></label><button>Cari</button></form><p>Maksimal 50 hasil. Pilih perusahaan untuk membuat atau mencabut tautannya.</p><ul><?php foreach($partners as $item): ?><li><a href="<?= e(base_url('panel/ide-industri/tautan.php?mitra_id='.$item['id'])) ?>"><?= e($item['nama']) ?></a> <?= !$item['aktif']?'(nonaktif)':'' ?></li><?php endforeach; ?></ul>
<?php else: ?>
<h2><?= e($partner['nama']) ?></h2><p>Tautan terikat ke perusahaan ini, berlaku sekali kirim, dan tidak memberikan akun atau akses dashboard. Penerima hanya dapat mengisi usulan; riwayat dan kontak tersimpan tidak dapat dibaca lewat tautan. Bagikan secara pribadi kepada perwakilan perusahaan yang tepat.</p>
<?php if($message): ?><p class="notice" role="status"><?= e($message) ?></p><?php endif; ?><?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<?php if($url): ?><label for="industry-idea-url">Tautan baru — salin sekarang<input id="industry-idea-url" readonly value="<?= e($url) ?>" style="width:100%"></label><script>const ideaUrl=document.getElementById('industry-idea-url');ideaUrl.value=new URL(ideaUrl.value,window.location.href).href;</script><?php endif; ?>
<?php if(!$partner['aktif']): ?><p>Mitra nonaktif tidak dapat menerima tautan baru atau mengirim usulan. Tautan lama tetap dapat dicabut.</p><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><label for="hours">Masa berlaku (jam, 1–720)<input id="hours" name="hours" type="number" min="1" max="720" value="48" required></label><button name="aksi" value="buat" <?= !$partner['aktif']?'disabled':'' ?>>Buat tautan baru</button><button name="aksi" value="cabut">Cabut tautan belum dipakai</button></form>
<?php $links=panel_query('SELECT created_at,expires_at,used_at,revoked_at FROM industry_idea_links WHERE mitra_id=:id ORDER BY id DESC LIMIT 10',['id'=>$partnerId])->fetchAll(); ?><h3>Sepuluh tautan terakhir</h3><div class="table-scroll"><table><thead><tr><th>Dibuat</th><th>Kedaluwarsa</th><th>Digunakan</th><th>Dicabut</th></tr></thead><tbody><?php foreach($links as $link): ?><tr><?php foreach(['created_at','expires_at','used_at','revoked_at'] as $key): ?><td><?= e(industry_idea_display_time($link[$key])) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php panel_end($user); ?>
