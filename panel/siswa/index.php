<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/students.php';
$user=require_role('admin','staf_bkk');
try{[$filters,$where,$params,$order]=student_filters($_GET);}
catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$listPath='panel/siswa/?'.http_build_query($filters);
$ready=(bool)panel_query("SHOW TABLES LIKE 'siswa'")->fetchColumn();
$errors=[];$data=student_input([]);$id=positive_id($_GET['id']??'');
if($ready&&$id){$row=panel_query('SELECT * FROM siswa WHERE id=:id',['id'=>$id])->fetch();if(!$row){panel_not_found();}$data=student_input($row);}
if($_SERVER['REQUEST_METHOD']==='POST'){
    require_post_csrf();
    if(!$ready){$errors[]='Jalankan pembaruan database 009_siswa.sql terlebih dahulu.';}
    elseif(($_POST['aksi']??'')==='impor'){
        $file=$_FILES['excel']??[];$temp=null;
        try{
            if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_string($file['name']??null)||strtolower(pathinfo($file['name'],PATHINFO_EXTENSION))!=='xlsx'||($file['size']??0)>5*1024*1024||!is_uploaded_file($file['tmp_name']??'')){throw new InvalidArgumentException('Pilih file .xlsx maksimal 5 MB.');}
            $temp=sys_get_temp_dir().'/bkk-siswa-'.bin2hex(random_bytes(12)).'.xlsx';
            if(!move_uploaded_file($file['tmp_name'],$temp)){throw new RuntimeException('Unggahan gagal diproses.');}
            $rows=student_xlsx_rows($temp);$students=[];$seen=[];
            foreach($rows as $number=>$values){
                $student=student_input(array_combine(array_keys(student_columns()),$values));
                $validation=student_errors($student);
                if(isset($seen[$student['nis']])){$validation[]='NIS berulang dalam file.';}
                $seen[$student['nis']]=true;
                if($validation){$errors[]='Baris data '.($number+1).': '.implode(' ',$validation);}
                $students[]=$student;
            }
            if(!$errors){
                db()->beginTransaction();
                try{
                    foreach($students as $student){student_save($student);}
                    db()->commit();
                }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
                unlink($temp);$temp=null;
                panel_message(count($students).' siswa berhasil diimpor.','panel/siswa/');
            }
        }catch(PDOException $exception){$errors[]=$exception->getCode()==='23000'?'Impor dibatalkan: NIS sudah terdaftar. Gunakan edit manual untuk mengubah data.':'Impor gagal disimpan. Tidak ada data yang diimpor.';}
        catch(Throwable $exception){$errors[]=$exception instanceof InvalidArgumentException?$exception->getMessage():'File Excel tidak dapat dibaca. Gunakan template .xlsx yang tersedia.';}
        finally{if($temp&&is_file($temp)){unlink($temp);}}
    }else{
        $data=student_input($_POST);$errors=student_errors($data);
        if(!$errors){
            try{student_save($data,$id);panel_message('Data siswa berhasil disimpan.',$listPath);}
            catch(PDOException $exception){if($exception->getCode()!=='23000'){throw $exception;}$errors[]='NIS sudah terdaftar. Gunakan NIS yang berbeda.';}
        }
    }
}
$angkatanOptions=$ready?panel_query('SELECT DISTINCT angkatan FROM siswa ORDER BY angkatan DESC')->fetchAll(PDO::FETCH_COLUMN):[];
$industryOptions=$ready?panel_query("SELECT DISTINCT kelas_industri FROM siswa WHERE kelas_industri<>'' ORDER BY kelas_industri")->fetchAll(PDO::FETCH_COLUMN):[];
if($filters['angkatan']!==''&&!in_array($filters['angkatan'],array_map('strval',$angkatanOptions),true)){$angkatanOptions[]=$filters['angkatan'];}
if($filters['kelas_industri']!==''&&!in_array($filters['kelas_industri'],$industryOptions,true)){$industryOptions[]=$filters['kelas_industri'];}
$total=$ready?(int)panel_query('SELECT COUNT(*) FROM siswa WHERE '.$where,$params)->fetchColumn():0;
$pages=max(1,(int)ceil($total/25));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=$ready?panel_query('SELECT * FROM siswa WHERE '.$where.' ORDER BY '.$order.' LIMIT :limit OFFSET :offset',$params+['limit'=>25,'offset'=>($page-1)*25])->fetchAll():[];
panel_start($user,'Data siswa','siswa');
?>
<div class="page-actions"><h1>Data siswa</h1><a href="<?= e(base_url('panel/absensi/?'.http_build_query(array_intersect_key($filters,array_flip(['angkatan','kelas_industri']))))) ?>">Absensi kegiatan</a><a href="<?= e(base_url('panel/capaian-pembelajaran/?'.http_build_query(array_intersect_key($filters,array_flip(['angkatan','kelas_industri']))))) ?>">Capaian pembelajaran</a><a href="<?= e(base_url('panel/siswa/')) ?>">Tambah siswa</a><a href="<?= e(base_url('panel/siswa/template.php')) ?>">Download template Excel</a></div>
<?php if(!$ready):?><p class="alert">Fitur belum diaktifkan. Jalankan database/updates/009_siswa.sql pada database aplikasi.</p><?php endif;?>
<?php if($errors):?><div class="alert" role="alert"><strong>Data belum disimpan.</strong><ul><?php foreach(array_slice($errors,0,30) as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><?php if(count($errors)>30):?><p>Masih ada kesalahan lain. Perbaiki data dan unggah kembali.</p><?php endif;?></div><?php endif;?>
<form class="editor-form card" method="post"><?= csrf_field() ?><h2><?= $id?'Edit siswa':'Tambah siswa' ?></h2><p>Angkatan adalah tahun masuk siswa. NIS menjadi identitas unik. Data PKL kelas 11 dapat diisi jika tersedia.</p>
<?php foreach(student_columns() as $key=>$label):?><label for="<?= $key ?>"><?= e($label) ?><input id="<?= $key ?>" name="<?= $key ?>" value="<?= e($data[$key]) ?>" <?= in_array($key,['nis','nama','kelas','angkatan'],true)?'required':'' ?> <?= $key==='email'?'type="email"':'type="text"' ?> maxlength="<?= ['nis'=>30,'nisn'=>10,'nama'=>150,'kelas'=>80,'jurusan'=>150,'angkatan'=>4,'kelas_industri'=>150,'telepon'=>25,'email'=>190,'tempat_pkl'=>190,'tahun_pkl'=>9][$key] ?>"></label><?php endforeach;?>
<div class="form-actions"><button type="submit" <?= !$ready?'disabled':'' ?>>Simpan siswa</button><?php if($id):?><a href="<?= e(base_url('panel/siswa/')) ?>">Batal edit</a><?php endif;?></div></form>
<form class="editor-form card" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="aksi" value="impor"><h2>Impor siswa dari Excel</h2><p>Gunakan template .xlsx, maksimal 5 MB dan 1.000 siswa. Format NIS, NISN, dan telepon sebagai teks agar angka nol di awal tetap tersimpan. NIS yang sudah terdaftar akan menolak seluruh impor; data lama tetap tersimpan.</p><label for="excel">File Excel<input type="file" id="excel" name="excel" accept=".xlsx" required></label><button type="submit" <?= !$ready?'disabled':'' ?>>Impor siswa</button></form>
<form class="filter-form card" method="get">
<label>Cari nama / NIS<input name="q" maxlength="100" value="<?= e($filters['q']) ?>"></label>
<label>Angkatan (tahun masuk)<select name="angkatan"><option value="">Semua angkatan</option><?php foreach($angkatanOptions as $year):?><option value="<?= e((string)$year) ?>" <?= $filters['angkatan']===(string)$year?'selected':'' ?>><?= e((string)$year) ?></option><?php endforeach;?></select></label>
<label>Kelas industri<select name="kelas_industri"><option value="">Semua kelas industri</option><?php foreach($industryOptions as $industry):?><option value="<?= e($industry) ?>" <?= $filters['kelas_industri']===$industry?'selected':'' ?>><?= e($industry) ?></option><?php endforeach;?></select></label>
<label>Urutkan<select name="urut"><?php foreach(student_sort_options() as $key=>$label):?><option value="<?= e($key) ?>" <?= $filters['urut']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></label>
<button type="submit">Terapkan</button><a href="<?= e(base_url('panel/siswa/')) ?>">Reset filter</a></form>
<p><?= $total ?> siswa · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><?php foreach(student_columns() as $label):?><th><?= e($label) ?></th><?php endforeach;?><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><?php foreach(student_columns() as $key=>$label):?><td><?= e((string)$row[$key]) ?></td><?php endforeach;?><td><a href="<?= e(base_url('panel/siswa/?'.http_build_query($filters+['id'=>$row['id'],'page'=>$page]))) ?>">Edit</a></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="12">Belum ada siswa yang sesuai.</td></tr><?php endif;?></tbody></table></div>
<nav class="pagination" aria-label="Halaman siswa"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/siswa/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
<?php panel_end($user); ?>
