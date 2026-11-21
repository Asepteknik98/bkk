<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/panel.php';
$user=require_role('admin','staf_bkk');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');
$id=positive_id($_POST['id']??$_GET['id']??null);
$owner=$id?panel_query('SELECT id,nama_lengkap FROM tracer_responses WHERE id=:id',['id'=>$id])->fetch():false;
if(!$owner){panel_not_found();}
$url=null;$notice='';
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    $hours=filter_var($_POST['hours']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>720]]);
    $action=$_POST['action']??'';
    if(!in_array($action,['create','revoke'],true)||($action==='create'&&!$hours)){http_response_code(400);exit('Pilihan atau masa berlaku tidak valid.');}
    db()->beginTransaction();
    try{
        if(!panel_query('SELECT id FROM tracer_responses WHERE id=:id FOR UPDATE',['id'=>$id])->fetch()){throw new InvalidArgumentException('Data alumni tidak ditemukan.');}
        panel_query('UPDATE alumni_form_links SET revoked_at=UTC_TIMESTAMP() WHERE tracer_id=:id AND used_at IS NULL AND revoked_at IS NULL',['id'=>$id]);
        if($action==='create'){
            $token=bin2hex(random_bytes(32));
            panel_query('INSERT INTO alumni_form_links(tracer_id,token_hash,expires_at,created_by) VALUES(:id,:hash,TIMESTAMPADD(HOUR,:hours,UTC_TIMESTAMP()),:actor)', ['id'=>$id,'hash'=>hash('sha256',$token),'hours'=>$hours,'actor'=>$user['id']]);
            $url=base_url('tracer-study/alumni.php?token='.$token);
        }
        db()->commit();$notice=$action==='create'?'Tautan baru dibuat. Tautan sebelumnya dicabut.':'Semua tautan aktif alumni ini dicabut.';
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}
panel_start($user,'Tautan formulir alumni','tracer');
?>

<div class="idea-list-page alumni-link-page">
<header class="partner-detail-heading"><h1><?= panel_icon('link') ?> Tautan formulir alumni</h1><nav aria-label="Navigasi alumni"><a href="<?= e(base_url('panel/tracer-study/detail.php?id='.$id)) ?>"><?= panel_icon('back') ?> Detail alumni</a></nav></header>
<div class="idea-partner-heading"><strong><?= panel_icon('akun') ?> <?= e($owner['nama_lengkap']) ?></strong></div>
<?php if($notice): ?><p class="notice" role="status"><?= e($notice) ?></p><?php endif; ?>
<div class="idea-link-controls">
<form class="idea-link-form" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><div class="idea-hours-field"><label for="alumni-link-hours"><?= panel_icon('calendar') ?> Masa berlaku (jam)</label><input id="alumni-link-hours" type="number" name="hours" min="1" max="720" value="48" required><small>1&ndash;720 jam</small></div><div class="idea-link-actions"><button type="submit" class="idea-create" name="action" value="create"><?= panel_icon('add') ?> Buat tautan</button><button type="submit" class="idea-revoke" name="action" value="revoke" formnovalidate><?= panel_icon('close') ?> Cabut tautan aktif</button></div></form>
<?php if($url): ?><div class="idea-new-link"><label for="alumni-new-link"><?= panel_icon('link') ?> Tautan baru</label><input id="alumni-new-link" readonly value="<?= e($url) ?>"><small>Salin sekarang. Tautan hanya ditampilkan saat dibuat.</small></div><script>(()=>{const field=document.getElementById('alumni-new-link');field.value=new URL(field.value,location.href).href;})();</script><?php endif; ?>
<p class="idea-link-note"><?= panel_icon('lock') ?> Sekali pakai untuk memperbarui kontak dan kegiatan alumni. Bagikan hanya kepada alumni yang bersangkutan. Tautan baru menggantikan tautan lama.</p>
</div>
</div>
<?php panel_end($user); ?>
