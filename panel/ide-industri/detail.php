<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-ideas.php';
$user=require_role('admin','staf_bkk');industry_ideas_require_ready();$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
$row=panel_query('SELECT * FROM industry_ideas WHERE id=:id',['id'=>$id])->fetch();if(!$row){panel_not_found();}$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$status=input_text($_POST,'status');$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    if(!isset(industry_idea_statuses()[$status])){$errors[]='Status usulan tidak valid.';}
    else{
        $update=panel_query('UPDATE industry_ideas SET status=:status,version=version+1,updated_by=:actor,updated_at=UTC_TIMESTAMP() WHERE id=:id AND version=:version',['status'=>$status,'actor'=>$user['id'],'id'=>$id,'version'=>$version]);
        if($update->rowCount()!==1){$errors[]='Usulan sudah diperbarui pengelola lain. Muat ulang halaman.';}else{panel_message('Status usulan diperbarui.','panel/ide-industri/detail.php?id='.$id);}
    }
}
panel_start($user,'Detail usulan industri','ide-industri');
?>
<div class="page-actions"><h1><?= e($row['judul']) ?></h1><a href="<?= e(base_url('panel/ide-industri/')) ?>">Kotak Ide Industri</a></div>
<section class="card"><dl class="response-details"><?php foreach(['perusahaan'=>'Perusahaan saat pengiriman','nama_pengirim'=>'Pengirim','jabatan'=>'Jabatan','email'=>'Email','telepon'=>'Telepon / WhatsApp'] as $key=>$label): ?><dt><?= e($label) ?></dt><dd><?= e($row[$key]?:'Tidak diisi') ?></dd><?php endforeach; ?><dt>Dikirim</dt><dd><?= e(industry_idea_display_time($row['created_at'])) ?></dd><dt>Persetujuan</dt><dd><?= e($row['versi_persetujuan']) ?> · <?= e(industry_idea_display_time($row['persetujuan_at'])) ?></dd></dl><h2>Isi usulan</h2><p><?= nl2br(e($row['isi'])) ?></p></section>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= (int)$row['version'] ?>"><label for="status">Status penanganan<select id="status" name="status"><?php foreach(industry_idea_statuses() as $key=>$label): ?><option value="<?= $key ?>" <?= $row['status']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button>Simpan status</button></form>
<?php panel_end($user); ?>
