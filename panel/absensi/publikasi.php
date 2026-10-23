<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/event-resources.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
$event=panel_query('SELECT id,judul,tanggal,angkatan,kelas_industri FROM attendance_events WHERE id=:id',['id'=>$id])->fetch();if(!$event){panel_not_found();}
if(!panel_query("SHOW TABLES LIKE 'event_resources'")->fetchColumn()){panel_start($user,'Pretest dan poster','absensi');echo '<p class="alert">Jalankan pembaruan database 013_event_resources.sql terlebih dahulu.</p>';panel_end($user);exit;}
$resource=panel_query('SELECT * FROM event_resources WHERE event_id=:id',['id'=>$id])->fetch()?:event_resources_defaults();$data=$resource;$errors=[];$remove=false;
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$poster=null;$saved=false;
    $data['pretest_url']=input_text($_POST,'pretest_url');$data['pretest_enabled']=($_POST['pretest_enabled']??'')==='1'?1:0;$data['poster_enabled']=($_POST['poster_enabled']??'')==='1'?1:0;$data['version']=input_text($_POST,'version');$remove=($_POST['hapus_poster']??'')==='1';
    try{
        if(!preg_match('/^(0|[1-9][0-9]{0,9})$/D',$data['version'])){throw new InvalidArgumentException('Versi pengaturan tidak valid. Muat ulang halaman.');}
        if(isset($_FILES['poster'])&&!is_array($_FILES['poster'])){throw new InvalidArgumentException('Unggahan poster tidak valid.');}
        $poster=event_poster_upload($_FILES['poster']??null);
        event_resources_save($id,(int)$data['version'],$_POST,$poster,(int)$user['id']);$saved=true;
        panel_message('Pengaturan pretest dan poster berhasil disimpan.','panel/absensi/publikasi.php?id='.$id);
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
    catch(Throwable $exception){error_log('Pengaturan pretest/poster gagal: '.get_class($exception));$errors[]='Pengaturan gagal disimpan. Silakan coba kembali.';}
    finally{if($poster&&!$saved){event_poster_remove($poster['poster_file']);}}
}
panel_start($user,'Pretest dan poster: '.$event['judul'],'absensi');
?>
<div class="page-actions"><h1>Pretest dan poster</h1><a href="<?= e(base_url('panel/absensi/detail.php?id='.$id)) ?>">Kembali ke kegiatan</a><a href="<?= e(base_url('kegiatan.php?id='.$id)) ?>" target="_blank" rel="noopener">Lihat tampilan pengguna</a></div>
<p><strong><?= e($event['judul']) ?></strong> · <?= e($event['tanggal']) ?> · Angkatan <?= e((string)$event['angkatan']) ?> · <?= e($event['kelas_industri']) ?></p>
<?php if($errors):?><div class="alert" role="alert"><strong>Pengaturan belum disimpan.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><p>Pilih kembali poster jika diperlukan.</p><a href="<?= e(base_url('panel/absensi/publikasi.php?id='.$id)) ?>">Muat ulang data terbaru</a></div><?php endif;?>
<form class="editor-form card" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="version" value="<?= e((string)$data['version']) ?>">
<h2>Link pretest</h2><label for="pretest_url">URL pretest<input type="url" id="pretest_url" name="pretest_url" maxlength="2048" value="<?= e($data['pretest_url']) ?>" placeholder="https://..."></label><label class="check-label"><input type="checkbox" name="pretest_enabled" value="1" <?= $data['pretest_enabled']?'checked':'' ?>>Aktifkan pretest untuk pengguna</label><p>Saat nonaktif, link tetap tersimpan dan tombol pretest disembunyikan.</p>
<h2>Poster kegiatan</h2><?php if($resource['poster_file']):?><p>Poster saat ini: <?= e($resource['poster_name']) ?> (<?= number_format($resource['poster_size']/1024,1) ?> KB)</p><img src="<?= e(base_url('panel/absensi/poster.php?id='.$id.'&v='.$resource['version'])) ?>" alt="Poster kegiatan saat ini" style="display:block;max-width:100%;max-height:400px;object-fit:contain"><label class="check-label"><input type="checkbox" name="hapus_poster" value="1" <?= $remove?'checked':'' ?>>Hapus poster saat ini</label><?php endif;?><label for="poster">Upload / ganti poster<input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label><p>JPG, PNG, WebP maksimal 3 MB. Kosongkan untuk mempertahankan poster lama. Menghapus poster juga menonaktifkannya; unggahan baru menggantikan poster lama.</p><label class="check-label"><input type="checkbox" name="poster_enabled" value="1" <?= $data['poster_enabled']?'checked':'' ?>>Aktifkan poster untuk pengguna</label>
<p>Item yang diaktifkan tampil di halaman publik Kegiatan dan dapat dilihat tanpa login. Data peserta dan absensi tetap berada di panel pengelola.</p><button type="submit">Simpan pengaturan</button></form>
<section class="card"><h2>Status tersimpan</h2><p>Pretest: <strong><?= $resource['pretest_enabled']?'Aktif':'Nonaktif' ?></strong> · Poster: <strong><?= $resource['poster_enabled']?'Aktif':'Nonaktif' ?></strong></p><p>Tautan untuk dibagikan: <a href="<?= e(base_url('kegiatan.php?id='.$id)) ?>"><?= e(base_url('kegiatan.php?id='.$id)) ?></a></p></section>
<?php panel_end($user); ?>
