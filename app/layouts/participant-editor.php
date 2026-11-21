<?php
if(!defined('APP_ROOT')){http_response_code(403);exit;}
$title=$participantPage==='import'?'Impor siswa dari Excel':($id?'Edit siswa':'Tambah siswa');
panel_start($user,$title,'peserta');
?>
<div class="pkl-page participants-workspace participants-separated">
<div class="page-actions participants-header"><div><h1><?= panel_icon($participantPage==='import'?'upload':'peserta') ?> <?= e($title) ?></h1><p><?= $participantPage==='import'?'Tambahkan banyak siswa menggunakan template Excel.':'Isi dan simpan identitas satu siswa pada halaman ini.' ?></p></div></div>
<?php require APP_ROOT.'/app/layouts/participant-navigation.php'; ?>
<?php if(!$ready):?><p class="alert">Fitur belum diaktifkan. Jalankan database/updates/009_participants.sql pada database aplikasi.</p><?php endif;?>
<?php if($errors):?><div class="alert" role="alert"><strong>Data belum disimpan.</strong><ul><?php foreach(array_slice($errors,0,30) as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><?php if(count($errors)>30):?><p>Masih ada kesalahan lain. Perbaiki data dan unggah kembali.</p><?php endif;?></div><?php endif;?>
<?php if($continuePkl): ?><p class="notice"><?= panel_icon('peserta') ?> Tambahkan identitas siswa. Setelah disimpan, Anda langsung menuju formulir penempatan PKL. <a href="<?= e(base_url('panel/pkl/form.php'.($continueTrip?'?lanjut=perjalanan':''))) ?>">Kembali ke pemilihan siswa</a></p><?php endif; ?>
<?php if($participantPage==='form'): ?>
<form id="peserta-form" class="editor-form card participant-single-form" method="post" action="<?= e(base_url('panel/peserta/form.php?'.http_build_query($filters+($id?['id'=>$id]:[])+($continuePkl?['lanjut'=>'pkl']:[])+($continueTrip?['kembali'=>'perjalanan']:[])))) ?>"><?= csrf_field() ?><?php if($continuePkl): ?><input type="hidden" name="lanjut" value="pkl"><?php if($continueTrip): ?><input type="hidden" name="kembali" value="perjalanan"><?php endif; ?><?php endif; ?><p class="participants-form-help">Isi identitas siswa. Kolom bertanda * wajib diisi. Gunakan Impor Excel untuk menambahkan banyak siswa sekaligus.</p>
<?php if(!$participantChoices['kelas']||!$participantChoices['jurusan']||!$participantChoices['angkatan']): ?><p class="full-width notice"><?php if($user['role']==='admin'): ?>Pilihan kelas, jurusan, atau angkatan belum lengkap. Isi daftar sekali melalui <a href="<?= e(base_url('panel/pengaturan/?bagian=kelas#settings-student-options')) ?>">Pengaturan - Pilihan Kelas / Jurusan / Angkatan</a>.<?php else: ?>Pilihan kelas, jurusan, atau angkatan belum lengkap. Hubungi Admin untuk mengisi daftar pilihan.<?php endif; ?></p><?php endif; ?>
<?php foreach(participant_columns() as $key=>$label): ?><label for="<?= $key ?>"><?= e($label) ?><?= in_array($key,['nama','kelas','angkatan'],true)?' *':'' ?>
<?php if(in_array($key,['kelas','jurusan','angkatan'],true)): ?>
<select id="<?= $key ?>" name="<?= $key ?>" <?= in_array($key,['kelas','angkatan'],true)?'required':'' ?>>
    <option value=""><?= $participantChoices[$key]?'Pilih '.e($label):'Pilihan '.e($label).' belum diatur' ?></option>
    <?php foreach($participantChoices[$key] as $option): ?><option value="<?= e($option) ?>" <?= $data[$key]===$option?'selected':'' ?>><?= e($option) ?></option><?php endforeach; ?>
</select>
<?php else: ?>
<input id="<?= $key ?>" name="<?= $key ?>" value="<?= e($data[$key]) ?>" <?= in_array($key,['nama','angkatan'],true)?'required':'' ?> <?= $key==='email'?'type="email"':'type="text"' ?> maxlength="<?= ['nama'=>150,'angkatan'=>4,'kelas_industri'=>150,'telepon'=>25,'email'=>190,'tempat_pkl'=>190,'tahun_pkl'=>9][$key] ?>">
<?php endif; ?></label><?php endforeach; ?>
<div class="form-actions"><button type="submit" <?= !$ready?'disabled':'' ?>><?= panel_icon('save') ?> <?= $continuePkl?'Simpan & lanjutkan ke PKL':'Simpan siswa' ?></button><a href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('back') ?> Kembali ke daftar</a></div></form>
<?php else: ?>
<form class="editor-form card participants-import" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/peserta/impor.php')) ?>"><?= csrf_field() ?><input type="hidden" name="aksi" value="impor">
<div class="participants-import-guide"><strong>1. Unduh dan isi template</strong><p>Gunakan kolom dari template terbaru. Format telepon sebagai teks agar nol di awal tidak hilang.</p><a href="<?= e(base_url('panel/peserta/template.php')) ?>"><?= panel_icon('download') ?> Unduh template Excel</a></div>
<div class="participants-import-upload"><label for="excel"><span><?= panel_icon('upload') ?> 2. Unggah file yang sudah diisi</span><input type="file" id="excel" name="excel" accept=".xlsx" required></label><small>Format .xlsx, maksimal 5 MB dan 1.000 siswa.</small><button type="submit" <?= !$ready?'disabled':'' ?>><?= panel_icon('upload') ?> Impor siswa</button></div>
<p class="participants-import-note"><?= panel_icon('bell') ?> Impor menambah siswa baru. Hindari mengunggah berkas yang sama berulang kali.</p></form>
<?php endif; ?>
</div>
<?php panel_end($user); ?>
