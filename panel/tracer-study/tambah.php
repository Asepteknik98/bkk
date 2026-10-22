<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');
$data=tracer_input([]);$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_post_csrf();
    $data=tracer_input($_POST);
    $errors=tracer_errors($data);
    $nonce=is_string($_POST['submission_token']??null)?$_POST['submission_token']:'';
    $issued=$_SESSION['admin_tracer_forms'][$nonce]??0;
    if (!$issued || $issued<time()-7200) {$errors[]='Formulir kedaluwarsa. Muat ulang halaman.';}
    if (!$errors) {
        try {
            $key=hash('sha256','admin-tracer:'.$user['id'].':'.$nonce);
            $existing=panel_query('SELECT * FROM tracer_responses WHERE submission_key=:key LIMIT 1',['key'=>$key])->fetch();
            if ($existing && !tracer_same_response($existing,$data)) {throw new InvalidArgumentException('Formulir sudah digunakan. Buka formulir baru untuk data lain.');}
            if (!$existing) {
                $params=[];
                foreach(tracer_fields() as $field=>$label){$params[$field]=$data[$field]===''?null:$data[$field];}
                $params['tahun_lulus']=(int)$data['tahun_lulus'];
                $params['submission_key']=$key;
                $params['versi_persetujuan']='admin-v1:'.$user['id'];
                $columns=array_keys($params);
                try {
                    panel_query('INSERT INTO tracer_responses ('.implode(',',$columns).',persetujuan_data,persetujuan_at) VALUES (:'.implode(',:',$columns).',1,NOW())',$params);
                } catch (PDOException $exception) {
                    if ((int)($exception->errorInfo[1]??0)!==1062) {throw $exception;}
                    $existing=panel_query('SELECT * FROM tracer_responses WHERE submission_key=:key LIMIT 1',['key'=>$key])->fetch();
                    if (!$existing || !tracer_same_response($existing,$data)) {throw $exception;}
                }
            }
            panel_message('Data alumni berhasil disimpan dan masuk ke rekap Tracer Study.','panel/tracer-study/');
        } catch (InvalidArgumentException $exception) {$errors[]=$exception->getMessage();}
        catch (Throwable $exception) {error_log('Admin tracer gagal: '.get_class($exception).' '.$exception->getCode());$errors[]='Data belum dapat disimpan. Silakan coba kembali.';}
    }
} else {
    $nonce=bin2hex(random_bytes(32));
    foreach(($_SESSION['admin_tracer_forms']??[]) as $token=>$issued){if($issued<time()-7200){unset($_SESSION['admin_tracer_forms'][$token]);}}
    $_SESSION['admin_tracer_forms'][$nonce]=time();
}
$maxYear=min(2155,(int)(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y'));
panel_start($user,'Tambah Data Alumni','tracer');
?>
<div class="page-actions alumni-page-heading"><h1><?= panel_icon('pengguna') ?> Tambah Data Alumni</h1><a href="<?= e(base_url('panel/tracer-study/')) ?>">&larr; Daftar respons</a></div>
<?php if($errors):?><div class="alert" role="alert"><strong>Data belum disimpan.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?>
<form class="editor-form card admin-alumni-form" method="post" action="<?= e(base_url('panel/tracer-study/tambah.php')) ?>">
<?= csrf_field() ?><input type="hidden" name="submission_token" value="<?= e($nonce) ?>">
<?php foreach(['nama_lengkap'=>['Nama lengkap',150,true],'nisn'=>['NISN (opsional)',20,false]] as $field=>[$label,$max,$required]):?><label for="<?= $field ?>"><?= e($label) ?><input id="<?= $field ?>" name="<?= $field ?>" maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" <?= $required?'required':'' ?> <?= $field==='nama_lengkap'?'autocomplete="name"':'' ?>></label><?php endforeach;?>
<label for="jurusan">Jurusan saat sekolah<select id="jurusan" name="jurusan" required><option value="">Pilih jurusan</option>
<option value="Teknik Komputer dan Jaringan" <?= $data['jurusan']==='Teknik Komputer dan Jaringan'?'selected':'' ?>>Teknik Komputer dan Jaringan</option>
<option value="Teknik Sepeda Motor" <?= $data['jurusan']==='Teknik Sepeda Motor'?'selected':'' ?>>Teknik Sepeda Motor</option>
<option value="Desain Produksi Busana" <?= $data['jurusan']==='Desain Produksi Busana'?'selected':'' ?>>Desain Produksi Busana</option>
<option value="Teknik Bodi Kendaraan Ringan" <?= $data['jurusan']==='Teknik Bodi Kendaraan Ringan'?'selected':'' ?>>Teknik Bodi Kendaraan Ringan</option>
<option value="Teknik Pengelasan" <?= $data['jurusan']==='Teknik Pengelasan'?'selected':'' ?>>Teknik Pengelasan</option>
<option value="Teknik Instalasi Tenaga Listrik" <?= $data['jurusan']==='Teknik Instalasi Tenaga Listrik'?'selected':'' ?>>Teknik Instalasi Tenaga Listrik</option>
<option value="Teknik Pendingin Tata Udara dan Pemanas" <?= $data['jurusan']==='Teknik Pendingin Tata Udara dan Pemanas'?'selected':'' ?>>Teknik Pendingin Tata Udara dan Pemanas</option>
<option value="Teknik Pemesinan" <?= $data['jurusan']==='Teknik Pemesinan'?'selected':'' ?>>Teknik Pemesinan</option>
<option value="Desain Teknik Furniture" <?= $data['jurusan']==='Desain Teknik Furniture'?'selected':'' ?>>Desain Teknik Furniture</option>
<option value="Teknik Kimia Industri" <?= $data['jurusan']==='Teknik Kimia Industri'?'selected':'' ?>>Teknik Kimia Industri</option>
</select></label>
<label for="tahun_lulus">Tahun lulus<input type="number" id="tahun_lulus" name="tahun_lulus" min="1900" max="<?= $maxYear ?>" step="1" value="<?= e($data['tahun_lulus']) ?>" required></label>
<label for="nomor_telepon">Nomor WhatsApp<input type="tel" id="nomor_telepon" name="nomor_telepon" maxlength="30" autocomplete="tel" value="<?= e($data['nomor_telepon']) ?>" placeholder="08… atau +62…" required></label>
<label for="email">Email (opsional)<input type="email" id="email" name="email" maxlength="191" autocomplete="email" value="<?= e($data['email']) ?>"></label>
<label for="status_kegiatan">Status kegiatan<select id="status_kegiatan" name="status_kegiatan" required><option value="">Pilih status kegiatan</option><?php foreach(tracer_statuses() as $value=>$label):?><option value="<?= $value ?>" <?= $data['status_kegiatan']===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></label>
<noscript><p>Isi detail pada kelompok yang sesuai dengan status Anda. Kelompok lain boleh dikosongkan.</p></noscript>
<?php foreach(['bekerja'=>['nama_perusahaan'=>['Nama perusahaan',200,true],'posisi_pekerjaan'=>['Posisi pekerjaan',150,true],'lokasi_pekerjaan'=>['Lokasi pekerjaan (opsional)',255,false]],'kuliah'=>['nama_kampus'=>['Nama kampus',200,true],'program_studi'=>['Program studi',150,true]],'wirausaha'=>['nama_usaha'=>['Nama usaha',200,true],'bidang_usaha'=>['Bidang usaha',150,true]]] as $status=>$fields):?>
<fieldset data-tracer-status="<?= $status ?>"><legend>Detail <?= e(tracer_statuses()[$status]) ?></legend><?php foreach($fields as $field=>[$label,$max,$required]):?><label for="<?= $field ?>"><?= e($label) ?><input id="<?= $field ?>" name="<?= $field ?>" maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" <?= $required?'data-status-required="1"':'' ?>></label><?php endforeach;?></fieldset>
<?php endforeach;?>
<label for="keterangan">Keterangan tambahan (opsional)<textarea id="keterangan" name="keterangan" maxlength="5000" rows="2"><?= e($data['keterangan']) ?></textarea></label>

<div class="admin-alumni-consent"><label class="check-label"><input type="checkbox" name="persetujuan_data" value="1" <?= $data['persetujuan_data']?'checked':'' ?> required> Saya telah memperoleh persetujuan alumni untuk pendataan dan pemrosesan data ini.</label><small>Data hanya tersedia bagi pengelola. Pencatatan ini masuk sebagai respons baru, tanpa mengganti respons sebelumnya.</small></div>
<div class="form-actions"><button type="submit">Simpan data alumni</button><a href="<?= e(base_url('panel/tracer-study/')) ?>">Batal</a></div>
</form>
<script src="<?= e(asset_url('js/tracer-study.js')) ?>" defer></script>
<?php panel_end($user); ?>
