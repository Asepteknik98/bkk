<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/bmw.php';
$user=require_role('admin','staf_bkk');$data=bmw_description();$version=$data['version'];$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();foreach(['judul','isi','status'] as $key){$data[$key]=input_text($_POST,$key);}
    $version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{bmw_save($data,$version);panel_message('Deskripsi BMW Sakti berhasil disimpan.','panel/program/deskripsi.php');}
    catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
}
panel_start($user,'Deskripsi BMW Sakti','program');
?>
<div class="page-actions"><h1>Deskripsi Program BMW Sakti</h1><a href="<?= e(base_url('bmw-sakti.php')) ?>">Lihat halaman publik</a><a href="<?= e(base_url('panel/program/')) ?>">Daftar program</a></div>
<p>Isi penjelasan resmi sekolah. Hanya deskripsi berstatus Dipublikasikan yang tampil untuk pengunjung.</p>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>"><label for="judul">Judul<input id="judul" name="judul" maxlength="190" value="<?= e($data['judul']) ?>" required></label><label for="isi">Deskripsi<textarea id="isi" name="isi" rows="12" maxlength="15000"><?= e($data['isi']) ?></textarea></label><p>Teks biasa; HTML tidak dijalankan. Gunakan baris kosong untuk memisahkan paragraf.</p><label for="status">Status<select id="status" name="status"><option value="draft" <?= $data['status']==='draft'?'selected':'' ?>>Draft</option><option value="published" <?= $data['status']==='published'?'selected':'' ?>>Dipublikasikan</option></select></label><button>Simpan deskripsi</button></form>
<?php panel_end($user); ?>
