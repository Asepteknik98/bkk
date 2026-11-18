<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/employment.php';
$user=require_role('admin','staf_bkk');employment_require_ready();$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=$id?panel_query('SELECT * FROM employment_types WHERE id=:id',['id'=>$id])->fetch():null;if($id&&!$old){panel_not_found();}
$data=$old?:['nama'=>'','penjelasan'=>'','rujukan_url'=>'','aktif'=>'1'];$version=(int)($old['version']??0);$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();foreach(['nama','penjelasan','rujukan_url','aktif'] as $key){$data[$key]=input_text($_POST,$key);}$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{employment_type_save(array_intersect_key($data,array_flip(['nama','penjelasan','rujukan_url','aktif'])),$id,$version,(int)$user['id']);panel_message('Jenis hubungan kerja disimpan.','panel/hubungan-kerja/');}
    catch(Throwable $exception){$errors[]=$exception instanceof InvalidArgumentException?$exception->getMessage():'Data belum tersimpan. Nama tidak boleh sama dengan jenis lain.';}
}
$types=panel_query('SELECT * FROM employment_types ORDER BY nama,id')->fetchAll();panel_start($user,'Jenis hubungan kerja','hubungan-kerja');
?>
<div class="page-actions"><h1>Jenis hubungan kerja</h1><a href="<?= e(base_url('panel/hubungan-kerja/')) ?>">Tambah jenis</a></div><p>Isi penjelasan dan tautan aturan resmi yang sudah diperiksa pengelola. Nonaktifkan jenis yang tidak digunakan; riwayat tetap tersimpan.</p>
<?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>"><label>Nama<input name="nama" maxlength="100" value="<?= e($data['nama']) ?>" required></label><label>Penjelasan (opsional)<textarea name="penjelasan" maxlength="10000" rows="4"><?= e($data['penjelasan']) ?></textarea></label><label>Tautan rujukan aturan resmi (opsional)<input name="rujukan_url" type="url" maxlength="2048" value="<?= e($data['rujukan_url']??'') ?>"></label><label>Status<select name="aktif"><option value="1" <?= $data['aktif']?'selected':'' ?>>Aktif</option><option value="0" <?= !$data['aktif']?'selected':'' ?>>Nonaktif</option></select></label><button>Simpan jenis</button></form>
<div class="table-scroll"><table><thead><tr><th>Nama</th><th>Penjelasan</th><th>Rujukan</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach($types as $type): ?><tr><td><?= e($type['nama']) ?></td><td><?= nl2br(e($type['penjelasan'])) ?></td><td><?php if($type['rujukan_url']&&http_url($type['rujukan_url'])): ?><a href="<?= e($type['rujukan_url']) ?>" target="_blank" rel="noopener noreferrer">Rujukan yang diisi pengelola</a><?php endif; ?></td><td><?= $type['aktif']?'Aktif':'Nonaktif' ?></td><td><a href="<?= e(base_url('panel/hubungan-kerja/?id='.$type['id'])) ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php panel_end($user); ?>
