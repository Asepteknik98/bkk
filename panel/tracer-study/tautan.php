<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/panel.php';
$user=require_role('admin','staf_bkk');
header('Referrer-Policy: no-referrer');
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
        panel_query('SELECT id FROM tracer_responses WHERE id=:id FOR UPDATE',['id'=>$id]);
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
<h1>Tautan formulir: <?= e($owner['nama_lengkap']) ?></h1>
<p>Tautan hanya memperbarui kegiatan dan kontak alumni ini, berlaku sekali, dan tidak memberikan akses dashboard. Pastikan identitas penerima sebelum membagikan. Siapa pun yang memiliki tautan dapat membuka formulir selama masih berlaku.</p>
<?php if($notice): ?><p role="status"><?= e($notice) ?></p><?php endif; ?>
<?php if($url): ?><label>Tautan (salin sekarang; tidak ditampilkan lagi)<input readonly value="<?= e($url) ?>" style="width:100%"></label><p>Jika tautan relatif, tambahkan alamat domain portal sebelum membagikan. Jangan bagikan di halaman publik.</p><?php endif; ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><label>Masa berlaku (jam, 1–720)<input type="number" name="hours" min="1" max="720" value="48" required></label><button name="action" value="create">Buat tautan baru</button><button name="action" value="revoke">Cabut tautan aktif</button></form>
<a href="<?= e(base_url('panel/tracer-study/detail.php?id='.$id)) ?>">Kembali ke detail alumni</a>
<?php panel_end($user); ?>
