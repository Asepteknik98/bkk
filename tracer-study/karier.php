<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/careers.php';
employment_require_ready();$owner=require_alumni_link();$applicationId=positive_id($_GET['lamaran_id']??'');if(isset($_GET['lamaran_id'])&&!$applicationId){http_response_code(404);exit('Lamaran tidak tersedia.');}
$application=$applicationId?panel_query('SELECT r.*,l.perusahaan,l.posisi FROM alumni_application_results r JOIN lamaran l ON l.id=r.lamaran_id WHERE r.lamaran_id=:id AND r.tracer_id=:tracer',['id'=>$applicationId,'tracer'=>$owner['tracer_id']])->fetch():null;
if($applicationId&&!$application){http_response_code(404);exit('Lamaran tidak tersedia untuk pemilik tautan.');}
$workData=career_work_form_data((int)$owner['tracer_id'],$owner);$result=$application['hasil']??'';$finished=!empty($application['seleksi_selesai']);$version=(int)($application['version']??0);$errors=[];$selectedJob=input_text($_GET,'lowongan_id');
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();career_check_form(input_text($_POST,'form_key'),$owner['token_hash']);$workData=career_work_input($_POST);$result=input_text($_POST,'hasil');$version=(int)(filter_var($_POST['selection_version']??null,FILTER_VALIDATE_INT)?:0);$selectedJob=input_text($_POST,'lowongan_id');
    try{
        if(($_POST['persetujuan']??'')!=='1'){throw new InvalidArgumentException('Persetujuan pemrosesan data diperlukan.');}
        career_save_link($owner['token_hash'],$applicationId,positive_id($selectedJob),$result,($_POST['seleksi_selesai']??'')==='1',$version,$workData);
        unset($_SESSION['alumni_link_hash'],$_SESSION['career_form'],$_SESSION['alumni_form']);header('Content-Type: text/html; charset=UTF-8');echo '<h1>Data karier alumni berhasil disimpan</h1><p>Data dan rekap internal telah diperbarui. Tautan telah digunakan. Hubungi BKK untuk tautan baru pada pembaruan berikutnya.</p><a href="'.e(base_url()).'">Kembali ke beranda</a>';exit;
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
}
$formKey=career_form_key($owner['token_hash']);$jobs=panel_query("SELECT id,perusahaan,posisi,batas_lamaran FROM lowongan WHERE status='published' ORDER BY created_at DESC,id DESC")->fetchAll();
$applications=panel_query('SELECT r.*,l.perusahaan,l.posisi FROM alumni_application_results r JOIN lamaran l ON l.id=r.lamaran_id WHERE r.tracer_id=:id ORDER BY r.updated_at DESC',['id'=>$owner['tracer_id']])->fetchAll();
require_once APP_ROOT.'/app/helpers/public.php';$pageTitle='Lamaran dan hasil seleksi alumni';$activePage='';require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><section class="surface"><h1>Lamaran, hasil seleksi, dan status kerja</h1><p>Alumni: <?= e($owner['nama_lengkap']) ?>. Pencatatan ini untuk BKK; pendaftaran ke perusahaan dilakukan melalui halaman rekrutmen resmi pada lowongan. Tautan hanya menyimpan satu pembaruan dan tidak membuka dashboard.</p><a href="<?= e(base_url('tracer-study/alumni.php')) ?>">Kembali ke formulir tracer</a>
<h2>Lamaran milik Anda</h2><ul><?php foreach($applications as $item): ?><li><a href="<?= e(base_url('tracer-study/karier.php?lamaran_id='.$item['lamaran_id'])) ?>"><?= e($item['posisi'].' — '.$item['perusahaan']) ?></a>: <?= e(career_result_label($item)) ?><?= $item['seleksi_selesai']?' (seleksi selesai)':'' ?></li><?php endforeach; ?></ul><a href="<?= e(base_url('tracer-study/karier.php')) ?>">Catat lamaran lain</a>
<?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<form class="tracer-form" method="post"><?= csrf_field() ?><input type="hidden" name="form_key" value="<?= e($formKey) ?>"><input type="hidden" name="selection_version" value="<?= $version ?>">
<?php if($application): ?><p>Lamaran: <strong><?= e($application['posisi'].' — '.$application['perusahaan']) ?></strong></p><?php else: ?><label for="lowongan_id">Lowongan yang dilamar<select id="lowongan_id" name="lowongan_id" required><option value="">Pilih lowongan</option><?php foreach($jobs as $job): ?><option value="<?= $job['id'] ?>" <?= (string)$job['id']===$selectedJob?'selected':'' ?>><?= e($job['posisi'].' — '.$job['perusahaan'].' (batas '.$job['batas_lamaran'].')') ?></option><?php endforeach; ?></select></label><?php endif; ?>
<label class="tracer-check"><input type="checkbox" name="seleksi_selesai" value="1" <?= $finished?'checked disabled':'' ?>> Proses seleksi sudah selesai</label><?php if($finished): ?><p>Pengelola telah menandai proses selesai. Wajib isi hasil akhir.</p><?php endif; ?>
<label for="hasil">Hasil seleksi<select id="hasil" name="hasil" required><option value="">Pilih hasil</option><?php foreach(['dalam_proses'=>'Dalam Proses','lolos'=>'Lolos','tidak_lolos'=>'Tidak Lolos'] as $key=>$label): ?><option value="<?= $key ?>" <?= $result===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<?php require APP_ROOT.'/app/layouts/career-work-fields.php'; ?>
<label class="tracer-check"><input type="checkbox" name="persetujuan" value="1" required> Saya menyetujui pemrosesan data lamaran, hasil seleksi, dan pekerjaan oleh pengelola BKK untuk pendataan serta evaluasi layanan karier. Data tidak ditampilkan ke publik.</label><button class="button button-navy">Simpan pembaruan karier</button></form></section></main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
