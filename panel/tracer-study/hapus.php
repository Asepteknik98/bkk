<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-management.php';
$user=require_role('admin','staf_bkk');
if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','POST'],true)){header('Allow: GET, POST');http_response_code(405);exit('Metode tidak diizinkan.');}
$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$row=tracer_record($id);}catch(InvalidArgumentException){panel_not_found();}
$error='';
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    try{tracer_admin_delete($id,input_text($_POST,'revision'));panel_message('Data alumni berhasil dihapus.','panel/tracer-study/');}
    catch(InvalidArgumentException $exception){$error=$exception->getMessage();}
    catch(Throwable $exception){error_log('Hapus tracer gagal: '.get_class($exception));$error='Data belum dapat dihapus. Silakan coba kembali.';}
}
panel_start($user,'Hapus Data Alumni','tracer');
?>
<section class="card tracer-delete-confirm"><h1><?= panel_icon('trash') ?> Hapus data alumni</h1>
<?php if($error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endif; ?>
<p><strong><?= e($row['nama_lengkap']) ?></strong> &middot; <?= e($row['jurusan']) ?> &middot; <?= (int)$row['tahun_lulus'] ?></p>
<p>Penghapusan permanen. Tautan formulir milik respons ini ikut dihapus. Data yang terkait riwayat lamaran atau pekerjaan tidak dapat dihapus.</p>
<form method="post"><?= csrf_field() ?><input type="hidden" name="revision" value="<?= e(tracer_revision($row)) ?>"><div class="tracer-record-actions"><a href="<?= e(base_url('panel/tracer-study/detail.php?id='.$id)) ?>"><?= panel_icon('back') ?> Batal</a><button class="tracer-delete" type="submit"><?= panel_icon('trash') ?> Hapus permanen</button></div></form></section>
<?php panel_end($user); ?>
