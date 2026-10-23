<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/learning-documents.php';
$user=require_role('admin','staf_bkk');
try{[$filters,$where,$params]=student_filters($_GET);}
catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
unset($filters['urut']);
// Student search uses nama/NIS; documents search their title and description instead.
$where=str_replace(['nama LIKE :nama','nis LIKE :nis'],['judul LIKE :nama','deskripsi LIKE :nis'],$where);
$listPath='panel/capaian-pembelajaran/?'.http_build_query($filters);
$ready=(bool)panel_query("SHOW TABLES LIKE 'learning_documents'")->fetchColumn();
$errors=[];$data=learning_input([]);$id=positive_id($_GET['id']??'');$document=null;
if($ready&&$id){$document=panel_query('SELECT * FROM learning_documents WHERE id=:id',['id'=>$id])->fetch();if(!$document){panel_not_found();}$data=learning_input($document);}
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$file=null;$saved=false;
    try{
        if(!$ready){throw new InvalidArgumentException('Pembaruan database capaian pembelajaran belum dijalankan.');}
        $action=input_text($_POST,'aksi');
        if($action==='hapus'){
            $deleteId=positive_id($_POST['id']??'');
            if(!$deleteId){throw new InvalidArgumentException('Dokumen tidak valid.');}
            learning_delete($deleteId);panel_message('Dokumen capaian pembelajaran dihapus.',$listPath);
        }
        if($action!=='simpan'){throw new InvalidArgumentException('Tindakan tidak valid.');}
        $data=learning_input($_POST);$errors=learning_errors($data);
        if(!$errors){
            $file=learning_upload(isset($_FILES['dokumen'])&&is_array($_FILES['dokumen'])?$_FILES['dokumen']:null);
            learning_save($data,$id,(int)$user['id'],$file);$saved=true;
            panel_message('Capaian pembelajaran berhasil disimpan.',$listPath);
        }
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
    catch(Throwable $exception){error_log('Penyimpanan capaian pembelajaran gagal: '.get_class($exception));$errors[]='Dokumen gagal diproses. Silakan coba kembali.';}
    finally{if($file&&!$saved){learning_remove_file($file['nama_simpan']);}}
}
$years=$ready?panel_query('SELECT DISTINCT angkatan FROM learning_documents ORDER BY angkatan DESC')->fetchAll(PDO::FETCH_COLUMN):[];
$industries=$ready?panel_query('SELECT DISTINCT kelas_industri FROM learning_documents ORDER BY kelas_industri')->fetchAll(PDO::FETCH_COLUMN):[];
if(panel_query("SHOW TABLES LIKE 'siswa'")->fetchColumn()){
    $years=array_merge($years,panel_query('SELECT DISTINCT angkatan FROM siswa')->fetchAll(PDO::FETCH_COLUMN));
    $industries=array_merge($industries,panel_query("SELECT DISTINCT kelas_industri FROM siswa WHERE kelas_industri<>''")->fetchAll(PDO::FETCH_COLUMN));
}
if($filters['angkatan']!==''){$years[]=$filters['angkatan'];}
if($filters['kelas_industri']!==''){$industries[]=$filters['kelas_industri'];}
$years=array_unique(array_map('strval',$years));rsort($years,SORT_NUMERIC);
$industries=array_unique($industries);sort($industries,SORT_NATURAL|SORT_FLAG_CASE);
$total=$ready?(int)panel_query('SELECT COUNT(*) FROM learning_documents WHERE '.$where,$params)->fetchColumn():0;
$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=$ready?panel_query('SELECT * FROM learning_documents WHERE '.$where.' ORDER BY updated_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll():[];
panel_start($user,'Capaian pembelajaran','capaian');
?>
<div class="page-actions"><h1>Capaian pembelajaran</h1><a href="<?= e(base_url($listPath)) ?>">Tambah dokumen</a><a href="<?= e(base_url('panel/siswa/')) ?>">Data siswa</a></div>
<p>Kelola dokumen materi dan target pembelajaran per kelas industri dan angkatan. Dokumen tersedia bagi admin dan staf BKK yang masuk ke panel.</p>
<?php if(!$ready):?><p class="alert">Fitur belum aktif. Jalankan pembaruan database 010_learning_documents.sql.</p><?php endif;?>
<?php if($errors):?><div class="alert" role="alert"><strong>Perubahan belum disimpan.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><p>Pilih kembali berkas jika diperlukan.</p></div><?php endif;?>
<form class="editor-form card" method="post" enctype="multipart/form-data">
<?= csrf_field() ?><input type="hidden" name="aksi" value="simpan">
<h2><?= $id?'Edit capaian pembelajaran':'Upload capaian pembelajaran' ?></h2>
<label for="judul">Judul<input id="judul" name="judul" maxlength="190" value="<?= e($data['judul']) ?>" required></label>
<label for="deskripsi">Deskripsi (opsional)<textarea id="deskripsi" name="deskripsi" maxlength="5000" rows="3"><?= e($data['deskripsi']) ?></textarea></label>
<label for="angkatan">Angkatan (tahun masuk)<input id="angkatan" name="angkatan" maxlength="4" pattern="(?:19|20|21)[0-9]{2}" inputmode="numeric" list="learning-years" value="<?= e($data['angkatan']) ?>" required></label>
<datalist id="learning-years"><?php foreach($years as $year):?><option value="<?= e($year) ?>"><?php endforeach;?></datalist>
<label for="kelas_industri">Kelas industri<input id="kelas_industri" name="kelas_industri" maxlength="150" list="learning-industries" value="<?= e($data['kelas_industri']) ?>" required></label>
<datalist id="learning-industries"><?php foreach($industries as $industry):?><option value="<?= e($industry) ?>"><?php endforeach;?></datalist>
<?php if($document):?><p>Dokumen saat ini: <a href="<?= e(base_url('panel/capaian-pembelajaran/download.php?id='.$document['id'])) ?>"><?= e($document['nama_asli']) ?></a>. Kosongkan unggahan untuk mempertahankan dokumen.</p><?php endif;?>
<label for="dokumen"><?= $id?'Ganti dokumen PDF (opsional)':'Dokumen PDF' ?><input id="dokumen" name="dokumen" type="file" accept=".pdf,application/pdf" <?= !$id?'required':'' ?>></label>
<p>PDF maksimal 5 MB. Satu dokumen dapat memuat beberapa capaian pembelajaran.</p>
<div class="form-actions"><button type="submit" <?= !$ready?'disabled':'' ?>>Simpan dokumen</button><?php if($id):?><a href="<?= e(base_url($listPath)) ?>">Batal edit</a><?php endif;?></div>
</form>
<form class="filter-form card" method="get">
<label>Cari judul / deskripsi<input name="q" maxlength="100" value="<?= e($filters['q']) ?>"></label>
<label>Angkatan<select name="angkatan"><option value="">Semua angkatan</option><?php foreach($years as $year):?><option value="<?= e($year) ?>" <?= $filters['angkatan']===$year?'selected':'' ?>><?= e($year) ?></option><?php endforeach;?></select></label>
<label>Kelas industri<select name="kelas_industri"><option value="">Semua kelas industri</option><?php foreach($industries as $industry):?><option value="<?= e($industry) ?>" <?= $filters['kelas_industri']===$industry?'selected':'' ?>><?= e($industry) ?></option><?php endforeach;?></select></label>
<button type="submit">Terapkan</button><a href="<?= e(base_url('panel/capaian-pembelajaran/')) ?>">Reset filter</a>
</form>
<p><?= $total ?> dokumen · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Dokumen</th><th>Angkatan</th><th>Kelas industri</th><th>Ukuran</th><th>Diperbarui</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach($rows as $row):?><tr><td><strong><?= e($row['judul']) ?></strong><p><?= nl2br(e($row['deskripsi'])) ?></p><small><?= e($row['nama_asli']) ?></small></td><td><?= e((string)$row['angkatan']) ?></td><td><?= e($row['kelas_industri']) ?></td><td><?= number_format($row['ukuran']/1024,1) ?> KB</td><td><?= e($row['updated_at']) ?></td><td><div class="row-actions"><a href="<?= e(base_url('panel/capaian-pembelajaran/download.php?id='.$row['id'])) ?>">Download PDF</a><a href="<?= e(base_url('panel/capaian-pembelajaran/?'.http_build_query($filters+['id'=>$row['id']]))) ?>">Edit / ganti PDF</a><form method="post" action="<?= e(base_url($listPath)) ?>" data-confirm="Hapus dokumen capaian pembelajaran ini?"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $row['id'] ?>"><button type="submit" class="danger small-button">Hapus</button></form></div></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="6" class="empty-panel">Belum ada dokumen yang sesuai.</td></tr><?php endif;?>
</tbody></table></div>
<nav class="pagination" aria-label="Halaman capaian pembelajaran"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/capaian-pembelajaran/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
<?php panel_end($user); ?>
