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
<div class="employment-type-page">
<div class="page-actions"><div><h1><?= panel_icon('lowongan') ?> Jenis hubungan kerja</h1><p>Kelola pilihan hubungan kerja untuk formulir lowongan.</p></div><a href="<?= e(base_url('panel/hubungan-kerja/')) ?>"><?= panel_icon('add') ?> Tambah jenis</a></div>
<?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<form class="editor-form card employment-type-editor" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<h2><?= panel_icon($id?'edit':'add') ?> <?= $id?'Edit jenis':'Tambah jenis' ?></h2>
<div class="employment-type-cell"><label for="jenis-nama"><?= panel_icon('briefcase') ?> Nama jenis *</label><input id="jenis-nama" name="nama" maxlength="100" value="<?= e($data['nama']) ?>" required></div>
<div class="employment-type-cell"><label for="jenis-aktif"><?= panel_icon('check') ?> Status</label><select id="jenis-aktif" name="aktif"><option value="1" <?= $data['aktif']?'selected':'' ?>>Aktif</option><option value="0" <?= !$data['aktif']?'selected':'' ?>>Nonaktif</option></select></div>
<div class="employment-type-cell employment-type-wide"><label for="jenis-penjelasan"><?= panel_icon('berita') ?> Penjelasan <small>Opsional</small></label><textarea id="jenis-penjelasan" name="penjelasan" maxlength="10000" rows="3"><?= e($data['penjelasan']) ?></textarea></div>
<div class="employment-type-cell employment-type-wide"><label for="jenis-rujukan"><?= panel_icon('link') ?> Tautan rujukan aturan resmi <small>Opsional</small></label><input id="jenis-rujukan" name="rujukan_url" type="url" maxlength="2048" value="<?= e($data['rujukan_url']??'') ?>" aria-describedby="jenis-help"><small id="jenis-help">Gunakan sumber resmi yang sudah diperiksa. Nonaktifkan jenis yang tidak dipakai; riwayat tetap tersimpan.</small></div>
<div class="form-actions"><small>* Wajib diisi</small><button type="submit"><?= panel_icon('save') ?> Simpan jenis</button><?php if($id): ?><a href="<?= e(base_url('panel/hubungan-kerja/')) ?>">Batal edit</a><?php endif; ?></div>
</form>
<div class="employment-list-heading"><h2><?= panel_icon('lowongan') ?> Daftar jenis</h2><small><?= count($types) ?> jenis</small></div>
<div class="table-scroll employment-type-sheet" data-vertical-list tabindex="0" role="region" aria-label="Daftar jenis hubungan kerja"><table><thead><tr><th scope="col">Nama</th><th scope="col">Penjelasan</th><th scope="col">Rujukan</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody><?php foreach($types as $type): ?><tr><td><strong><?= e($type['nama']) ?></strong></td><td><?= nl2br(e($type['penjelasan'])) ?></td><td><?php if($type['rujukan_url']&&http_url($type['rujukan_url'])): ?><a href="<?= e($type['rujukan_url']) ?>" target="_blank" rel="noopener noreferrer">Lihat rujukan</a><?php else: ?>Belum diisi<?php endif; ?></td><td><span class="badge <?= $type['aktif']?'is-active':'' ?>"><?= $type['aktif']?'Aktif':'Nonaktif' ?></span></td><td><div class="employment-type-actions"><a class="employment-view" href="<?= e(base_url('panel/hubungan-kerja/detail.php?id='.$type['id'])) ?>"><?= panel_icon('view') ?> Lihat</a><a class="employment-edit" href="<?= e(base_url('panel/hubungan-kerja/?id='.$type['id'])) ?>"><?= panel_icon('edit') ?> Edit</a><form method="post" action="<?= e(base_url('panel/hubungan-kerja/hapus.php')) ?>" data-confirm="Hapus jenis hubungan kerja ini? Jenis yang masih digunakan tidak dapat dihapus."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$type['id'] ?>"><input type="hidden" name="version" value="<?= (int)$type['version'] ?>"><button class="employment-delete" type="submit"><?= panel_icon('trash') ?> Hapus</button></form></div></td></tr><?php endforeach; ?><?php if(!$types): ?><tr><td colspan="5" class="empty-panel">Belum ada jenis hubungan kerja. Isi formulir di atas untuk menambahkan.</td></tr><?php endif; ?></tbody></table></div>
</div>
<?php panel_end($user); ?>
