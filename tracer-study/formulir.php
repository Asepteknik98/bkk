<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/public.php';
require_once APP_ROOT.'/app/helpers/tracer.php';
$pageTitle='Formulir Tracer Study';$activePage='tracer-study/';$errors=[];$data=tracer_input([]);
if(($_GET['baru']??'')==='1'){unset($_SESSION['tracer_form']);}
if(isset($_SESSION['tracer_form'])){$old=$_SESSION['tracer_form'];$data=$old['data'];$errors=$old['errors'];$nonce=$old['nonce'];unset($_SESSION['tracer_form']);}
else{$nonce=tracer_nonce();}
$maxYear=min(2155,(int)(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y'));
require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Ruang alumni</p><h1>Formulir Tracer Study</h1><p>Isi kegiatan Anda setelah lulus. Data dipakai oleh pengelola BKK untuk pendataan alumni, evaluasi layanan karir, dan tindak lanjut terkait kegiatan alumni. Respons serta data pribadi tidak ditampilkan di portal publik.</p></div>
<?php if($errors):?><div class="tracer-error" role="alert"><h2>Formulir belum dikirim</h2><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><a href="<?= e(base_url('tracer-study/formulir.php?baru=1')) ?>">Buka formulir baru</a></div><?php endif;?>
<form class="tracer-form surface" method="post" action="<?= e(base_url('tracer-study/simpan.php')) ?>" id="tracer-form" autocomplete="on"><?= csrf_field() ?><input type="hidden" name="submission_token" value="<?= e($nonce) ?>">
<div class="tracer-honeypot" aria-hidden="true"><label for="company_website">Biarkan kosong<input id="company_website" name="company_website" tabindex="-1" autocomplete="off"></label></div>
<?php foreach(['nama_lengkap'=>['Nama lengkap',150,true],'nisn'=>['NISN (opsional)',20,false],'jurusan'=>['Jurusan saat sekolah',150,true]] as $field=>[$label,$max,$required]):?><label for="<?= $field ?>"><?= e($label) ?><input id="<?= $field ?>" name="<?= $field ?>" maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" <?= $required?'required':'' ?> <?= $field==='nama_lengkap'?'autocomplete="name"':'' ?>></label><?php endforeach;?>
<label for="tahun_lulus">Tahun lulus<input type="number" id="tahun_lulus" name="tahun_lulus" min="1900" max="<?= $maxYear ?>" step="1" value="<?= e($data['tahun_lulus']) ?>" required></label>
<label for="nomor_telepon">Nomor WhatsApp<input type="tel" id="nomor_telepon" name="nomor_telepon" maxlength="30" autocomplete="tel" value="<?= e($data['nomor_telepon']) ?>" placeholder="08… atau +62…" required></label>
<label for="email">Email (opsional)<input type="email" id="email" name="email" maxlength="191" autocomplete="email" value="<?= e($data['email']) ?>"></label>
<label for="status_kegiatan">Status kegiatan<select id="status_kegiatan" name="status_kegiatan" required><option value="">Pilih status kegiatan</option><?php foreach(tracer_statuses() as $value=>$label):?><option value="<?= $value ?>" <?= $data['status_kegiatan']===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></label>
<noscript><p>Isi detail pada kelompok yang sesuai dengan status Anda. Kelompok lain boleh dikosongkan.</p></noscript>
<?php foreach(['bekerja'=>['nama_perusahaan'=>['Nama perusahaan',200,true],'posisi_pekerjaan'=>['Posisi pekerjaan',150,true],'lokasi_pekerjaan'=>['Lokasi pekerjaan (opsional)',255,false]],'kuliah'=>['nama_kampus'=>['Nama kampus',200,true],'program_studi'=>['Program studi',150,true]],'wirausaha'=>['nama_usaha'=>['Nama usaha',200,true],'bidang_usaha'=>['Bidang usaha',150,true]]] as $status=>$fields):?>
<fieldset data-tracer-status="<?= $status ?>"><legend>Detail <?= e(tracer_statuses()[$status]) ?></legend><?php foreach($fields as $field=>[$label,$max,$required]):?><label for="<?= $field ?>"><?= e($label) ?><input id="<?= $field ?>" name="<?= $field ?>" maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" <?= $required?'data-status-required="1"':'' ?>></label><?php endforeach;?></fieldset>
<?php endforeach;?>
<label for="keterangan">Keterangan tambahan (opsional)<textarea id="keterangan" name="keterangan" maxlength="5000" rows="4"><?= e($data['keterangan']) ?></textarea></label>
<div class="consent-box"><p><?= e(TRACER_CONSENT) ?></p><label class="tracer-check"><input type="checkbox" name="persetujuan_data" value="1" <?= $data['persetujuan_data']?'checked':'' ?> required> Saya setuju dengan pemrosesan data untuk tujuan tersebut.</label></div>
<p>Untuk koreksi data, hubungi kontak BKK pada <a href="<?= e(base_url('profil.php')) ?>">halaman profil dan kontak</a>. Mengirim ulang formulir yang sama tidak membuat catatan ganda. Jika ingin mengirim perubahan, gunakan formulir baru atau hubungi pengelola.</p>
<button class="button button-navy" type="submit">Kirim data alumni</button></form></main>
<script src="<?= e(asset_url('js/tracer-study.js')) ?>" defer></script>
<?php require APP_ROOT.'/app/layouts/footer.php';?>
