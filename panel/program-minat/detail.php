<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/programs.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');$row=$id?panel_query('SELECT * FROM program_inquiries WHERE id=:id',['id'=>$id])->fetch():null;if(!$row){panel_not_found();}
$statuses=['baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai'];$error='';
if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
    require_post_csrf();$reply=input_text($_POST,'balasan');$status=input_text($_POST,'status');
    if(!valid_text($reply,5000,false)||!isset($statuses[$status])){$error='Balasan atau status tidak valid.';http_response_code(422);}
    else{panel_query('UPDATE program_inquiries SET balasan=:reply,status=:status,replied_by=:user,updated_at=:at WHERE id=:id',['reply'=>$reply,'status'=>$status,'user'=>(int)$user['id'],'at'=>date('Y-m-d H:i:s'),'id'=>$id]);panel_message('Balasan disimpan dan dapat dilihat pengunjung pada tautan statusnya.','panel/program-minat/detail.php?id='.$id);}
}
panel_start($user,'Balas Minat Program','program-minat');
?>
<div class="program-interest-admin"><div class="page-actions"><h1><?= panel_icon('message') ?> Pesan program</h1><a href="<?= e(base_url('panel/program-minat/?kategori='.$row['kategori'])) ?>">Kembali</a></div><section class="card"><h2><?= e($row['nama']) ?></h2><p><?= e(program_categories()[$row['kategori']]['title']) ?><?= $row['program_judul']?' / '.e($row['program_judul']):'' ?></p><dl class="response-details"><dt>Email</dt><dd><?= e($row['email']) ?></dd><dt>WhatsApp</dt><dd><?= e($row['telepon']) ?></dd><dt>Dikirim (WIB)</dt><dd><?= e($row['created_at']) ?></dd></dl><h2>Minat / pertanyaan</h2><p class="interest-message"><?= e($row['pesan']) ?></p>
<?php if($error):?><p role="alert"><?= e($error) ?></p><?php endif;?>
<form class="editor-form interest-reply" method="post"><?= csrf_field() ?><label for="interest-reply">Balasan untuk pengunjung</label><textarea id="interest-reply" name="balasan" rows="4" maxlength="5000"><?= e($error?$reply:($row['balasan']??'')) ?></textarea><small>Balasan muncul di halaman status pribadi yang diterima pengunjung setelah mengirim formulir.</small><label for="interest-status">Status</label><select id="interest-status" name="status"><?php foreach($statuses as $key=>$label):?><option value="<?= $key ?>" <?= ($error?$status:$row['status'])===$key?'selected':'' ?>><?= $label ?></option><?php endforeach;?></select><button type="submit">Simpan balasan</button></form></section></div>
<?php panel_end($user); ?>
