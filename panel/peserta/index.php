<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/participants.php';
$user=require_role('admin','staf_bkk');
try{[$filters,$where,$params,$order]=participant_filters($_GET);}
catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$listPath='panel/peserta/?'.http_build_query($filters);
$ready=(bool)panel_query("SHOW TABLES LIKE 'siswa'")->fetchColumn();
$errors=[];$data=participant_input([]);$id=positive_id($_GET['id']??'');
if($ready&&$id){$row=panel_query('SELECT * FROM siswa WHERE id=:id',['id'=>$id])->fetch();if(!$row){panel_not_found();}$data=participant_input($row);}
if($_SERVER['REQUEST_METHOD']==='POST'){
    require_post_csrf();
    if(!$ready){$errors[]='Jalankan pembaruan database 009_participants.sql terlebih dahulu.';}
    elseif(($_POST['aksi']??'')==='impor'){
        $file=$_FILES['excel']??[];$temp=null;
        try{
            if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_string($file['name']??null)||strtolower(pathinfo($file['name'],PATHINFO_EXTENSION))!=='xlsx'||($file['size']??0)>5*1024*1024||!is_uploaded_file($file['tmp_name']??'')){throw new InvalidArgumentException('Pilih file .xlsx maksimal 5 MB.');}
            $temp=sys_get_temp_dir().'/bkk-peserta-'.bin2hex(random_bytes(12)).'.xlsx';
            if(!move_uploaded_file($file['tmp_name'],$temp)){throw new RuntimeException('Unggahan gagal diproses.');}
            $rows=participant_xlsx_rows($temp);$participants=[];$seen=[];
            foreach($rows as $number=>$values){
                $participant=participant_input(array_combine(array_keys(participant_columns()),$values));
                $validation=participant_errors($participant);
                if(isset($seen[$participant['nis']])){$validation[]='Nomor peserta berulang dalam file.';}
                $seen[$participant['nis']]=true;
                if($validation){$errors[]='Baris data '.($number+1).': '.implode(' ',$validation);}
                $participants[]=$participant;
            }
            if(!$errors){
                db()->beginTransaction();
                try{
                    foreach($participants as $participant){participant_save($participant);}
                    db()->commit();
                }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
                unlink($temp);$temp=null;
                panel_message(count($participants).' peserta berhasil diimpor.','panel/peserta/');
            }
        }catch(PDOException $exception){$errors[]=$exception->getCode()==='23000'?'Impor dibatalkan: Nomor peserta sudah terdaftar. Gunakan edit manual untuk mengubah data.':'Impor gagal disimpan. Tidak ada data yang diimpor.';}
        catch(Throwable $exception){$errors[]=$exception instanceof InvalidArgumentException?$exception->getMessage():'File Excel tidak dapat dibaca. Gunakan template .xlsx yang tersedia.';}
        finally{if($temp&&is_file($temp)){unlink($temp);}}
    }else{
        $data=participant_input($_POST);$errors=participant_errors($data);
        if(!$errors){
            try{participant_save($data,$id);panel_message('Data peserta berhasil disimpan.',$listPath);}
            catch(PDOException $exception){if($exception->getCode()!=='23000'){throw $exception;}$errors[]='Nomor peserta sudah terdaftar. Gunakan Nomor peserta yang berbeda.';}
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
$listColumns=array_intersect_key(participant_columns(),array_flip(['nis','nama','kelas','angkatan','kelas_industri']));
panel_start($user,'Data peserta','peserta');
?>
<div class="page-actions"><h1>Data peserta</h1><a href="<?= e(base_url('panel/absensi/?'.http_build_query(array_intersect_key($filters,array_flip(['angkatan','kelas_industri']))))) ?>">Absensi kegiatan</a><a href="<?= e(base_url('panel/capaian-pembelajaran/?'.http_build_query(array_intersect_key($filters,array_flip(['angkatan','kelas_industri']))))) ?>">Capaian pembelajaran</a><a href="#peserta-form" data-open-details="peserta-form">Tambah peserta</a><a href="<?= e(base_url('panel/peserta/template.php')) ?>">Template Excel</a><a class="button" href="<?= e(base_url('panel/ekspor.php?'.http_build_query(['jenis'=>'peserta']+$filters))) ?>" title="Download Excel data peserta"><?= panel_icon('download') ?> Excel</a></div>

<?php if(!$ready):?><p class="alert">Fitur belum diaktifkan. Jalankan database/updates/009_participants.sql pada database aplikasi.</p><?php endif;?>
<?php if($errors):?><div class="alert" role="alert"><strong>Data belum disimpan.</strong><ul><?php foreach(array_slice($errors,0,30) as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><?php if(count($errors)>30):?><p>Masih ada kesalahan lain. Perbaiki data dan unggah kembali.</p><?php endif;?></div><?php endif;?>
<details class="bmw-disclosure" id="peserta-form" <?= $id||($errors&&($_POST['aksi']??'')!=='impor')?'open':'' ?>><summary><?= panel_icon('peserta') ?> <?= $id?'Edit peserta':'Tambah peserta' ?></summary>
<form class="editor-form card" method="post"><?= csrf_field() ?><p>Angkatan mengikuti kelompok peserta. Nomor peserta menjadi identitas unik. Riwayat PKL dapat diisi jika tersedia.</p>
<?php foreach(participant_columns() as $key=>$label):?><label for="<?= $key ?>"><?= e($label) ?><input id="<?= $key ?>" name="<?= $key ?>" value="<?= e($data[$key]) ?>" <?= in_array($key,['nis','nama','kelas','angkatan'],true)?'required':'' ?> <?= $key==='email'?'type="email"':'type="text"' ?> maxlength="<?= ['nis'=>30,'nama'=>150,'kelas'=>80,'jurusan'=>150,'angkatan'=>4,'kelas_industri'=>150,'telepon'=>25,'email'=>190,'tempat_pkl'=>190,'tahun_pkl'=>9][$key] ?>"></label><?php endforeach;?>
<div class="form-actions"><button type="submit" <?= !$ready?'disabled':'' ?>>Simpan peserta</button><?php if($id):?><a href="<?= e(base_url('panel/peserta/')) ?>">Batal edit</a><?php endif;?></div></form>
</details>
<details class="bmw-disclosure" id="impor-form" <?= $errors&&($_POST['aksi']??'')==='impor'?'open':'' ?>><summary><?= panel_icon('upload') ?> Impor Excel</summary>
<form class="editor-form card" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="aksi" value="impor"><p>Gunakan template .xlsx, maksimal 5 MB dan 1.000 peserta. Format Nomor peserta, dan telepon sebagai teks agar angka nol di awal tetap tersimpan. Nomor peserta yang sudah terdaftar akan menolak seluruh impor; data lama tetap tersimpan.</p><label for="excel">File Excel<input type="file" id="excel" name="excel" accept=".xlsx" required></label><button type="submit" <?= !$ready?'disabled':'' ?>>Impor peserta</button></form>
</details>
<details class="bmw-filter" open><summary><?= panel_icon('filter') ?> Cari &amp; filter</summary><form class="filter-form card" method="get">
<label>Cari nama / Nomor peserta<input name="q" maxlength="100" value="<?= e($filters['q']) ?>"></label>
<label>Angkatan<select name="angkatan"><option value="">Semua angkatan</option><?php foreach($angkatanOptions as $year):?><option value="<?= e((string)$year) ?>" <?= $filters['angkatan']===(string)$year?'selected':'' ?>><?= e((string)$year) ?></option><?php endforeach;?></select></label>
<label>Kelas industri<select name="kelas_industri"><option value="">Semua kelas industri</option><?php foreach($industryOptions as $industry):?><option value="<?= e($industry) ?>" <?= $filters['kelas_industri']===$industry?'selected':'' ?>><?= e($industry) ?></option><?php endforeach;?></select></label>
<label>Urutkan<select name="urut"><?php foreach(participant_sort_options() as $key=>$label):?><option value="<?= e($key) ?>" <?= $filters['urut']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></label>
<button type="submit">Terapkan</button><a href="<?= e(base_url('panel/peserta/')) ?>">Reset filter</a></form></details>
<p><?= $total ?> peserta · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><?php foreach($listColumns as $label):?><th><?= e($label) ?></th><?php endforeach;?><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><?php foreach($listColumns as $key=>$label):?><td><?= e((string)$row[$key]) ?></td><?php endforeach;?><td><a href="<?= e(base_url('panel/peserta/?'.http_build_query($filters+['id'=>$row['id'],'page'=>$page]))) ?>">Edit</a></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="6">Belum ada peserta yang sesuai.</td></tr><?php endif;?></tbody></table></div>
<nav class="pagination" aria-label="Halaman peserta"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/peserta/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav>
<?php panel_end($user); ?>
