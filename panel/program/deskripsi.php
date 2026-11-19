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
<div class="bmw-description-editor">
    <header class="bmw-description-heading">
        <div class="bmw-description-title"><div><h1><?= panel_icon('program') ?> Deskripsi BMW Sakti</h1><p>Kelola pengantar program yang tampil pada halaman publik.</p></div></div>
        <nav class="bmw-description-links" aria-label="Tautan program">
            <a href="<?= e(base_url('bmw-sakti.php')) ?>"><?= panel_icon('view') ?> Halaman publik</a>
            <a href="<?= e(base_url('panel/program/')) ?>"><?= panel_icon('back') ?> Daftar program</a>
        </nav>
    </header>
    <?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
    <form class="editor-form card bmw-description-form" method="post">
        <?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
        <label for="judul"><span><?= panel_icon('edit') ?> Judul</span><input id="judul" name="judul" maxlength="190" value="<?= e($data['judul']) ?>" required></label>
        <label for="status"><span><?= panel_icon('check-circle') ?> Status publikasi</span><select id="status" name="status" aria-describedby="bmw-publication-help"><option value="draft" <?= $data['status']==='draft'?'selected':'' ?>>Draft</option><option value="published" <?= $data['status']==='published'?'selected':'' ?>>Dipublikasikan</option></select></label>
        <label for="isi" class="bmw-description-content"><span><?= panel_icon('message') ?> Deskripsi program</span><textarea id="isi" name="isi" rows="7" maxlength="15000" aria-describedby="bmw-writing-help"><?= e($data['isi']) ?></textarea></label>
        <p class="bmw-description-help" id="bmw-writing-help">Gunakan teks biasa dan baris kosong untuk memisahkan paragraf.</p>
        <div class="bmw-description-footer"><p id="bmw-publication-help"><?= panel_icon('view') ?> Hanya deskripsi berstatus Dipublikasikan yang terlihat oleh pengunjung.</p><button type="submit"><?= panel_icon('save') ?> Simpan deskripsi</button></div>
    </form>
</div>
<?php panel_end($user); ?>
