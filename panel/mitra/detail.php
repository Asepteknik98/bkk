<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/partners.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$partner=partner_find($id);}catch(InvalidArgumentException){panel_not_found();}
panel_start($user,'Detail mitra','mitra');
?>
<div class="partner-detail-page">
<header class="partner-detail-heading">
    <div><h1><?= panel_icon('mitra') ?> <?= e($partner['nama']) ?></h1><p>Profil mitra dan kontak penanggung jawab.</p></div>
    <nav aria-label="Tindakan mitra">
        <a href="<?= e(base_url('panel/mitra/')) ?>"><?= panel_icon('back') ?> Daftar mitra</a>
        <a class="detail-edit" href="<?= e(base_url('panel/mitra/edit.php?id='.$id)) ?>"><?= panel_icon('edit') ?> Edit profil / PIC</a>
        <?php if(user_can($user,'mou')): ?><a class="detail-mou" href="<?= e(base_url('panel/mou/?mitra_id='.$id)) ?>"><?= panel_icon('history') ?> Riwayat MoU</a><?php endif; ?>
    </nav>
</header>
<div class="partner-detail-identity">
    <?php if($partner['logo']&&managed_upload_path($partner['logo'])): ?><img src="<?= e(base_url($partner['logo'])) ?>" alt="Logo <?= e($partner['nama']) ?>" width="56" height="56"><?php else: ?><span class="detail-logo-empty"><?= panel_icon('mitra') ?></span><?php endif; ?>
    <div><strong><?= e($partner['nama']) ?></strong><span><?= $partner['jenis']==='kampus'?'Kampus':'Perusahaan' ?></span></div>
    <span class="partner-state <?= $partner['aktif']?'is-active':'is-inactive' ?>"><?= panel_icon('check-circle') ?> <?= $partner['aktif']?'Aktif':'Nonaktif' ?></span>
</div>
<div class="partner-detail-columns">
<section class="partner-detail-sheet" aria-labelledby="partner-profile-title">
<h2 id="partner-profile-title"><?= panel_icon('identity') ?> Profil mitra</h2>
<dl>
<div><dt>Jenis</dt><dd><?= $partner['jenis']==='kampus'?'Kampus':'Perusahaan' ?></dd></div>
<?php if($partner['jenis']==='perusahaan'): ?><div><dt>Klaster</dt><dd><?= e(partner_clusters()[$partner['klaster']]??'Belum diklasifikasikan') ?></dd></div><div><dt>Bidang usaha</dt><dd><?= e($partner['bidang_usaha']?:'Belum diisi') ?></dd></div><?php endif; ?>
<div><dt>Alamat lengkap</dt><dd><?= nl2br(e($partner['alamat_lengkap']?:'Belum diisi')) ?></dd></div>
<div><dt>Negara</dt><dd><?= e($partner['negara']?:'Belum diisi') ?></dd></div>
<div><dt>Website resmi</dt><dd><?php if($partner['website']&&http_url($partner['website'])): ?><a href="<?= e($partner['website']) ?>" target="_blank" rel="noopener noreferrer"><?= e($partner['website']) ?> <?= panel_icon('link') ?></a><?php else: ?>Belum diisi<?php endif; ?></dd></div>
</dl>
</section>
<section class="partner-detail-sheet" aria-labelledby="partner-pic-title">
<h2 id="partner-pic-title"><?= panel_icon('akun') ?> Penanggung jawab (PIC) <small>Internal</small></h2>
<dl>
<div><dt>Nama PIC</dt><dd><?= e($partner['pic_nama']?:'Belum diisi') ?></dd></div>
<div><dt>Jabatan</dt><dd><?= e($partner['pic_jabatan']?:'Belum diisi') ?></dd></div>
<div><dt>Telepon / email</dt><dd><?= e($partner['pic_kontak']?:'Belum diisi') ?></dd></div>
</dl>
<p class="partner-detail-note"><?= panel_icon('lock') ?> Data PIC hanya tersedia bagi pengelola berizin.</p>
<?php if($partner['jenis']==='perusahaan'&&user_can($user,'ide-industri')): ?><a class="partner-idea-link" href="<?= e(base_url('panel/ide-industri/tautan.php?mitra_id='.$id)) ?>"><?= panel_icon('message') ?> Kelola tautan Kotak Ide <?= panel_icon('next') ?></a><?php endif; ?>
</section>
</div>
<section class="partner-detail-sheet partner-detail-description" aria-labelledby="partner-description-title"><h2 id="partner-description-title"><?= panel_icon('berita') ?> Deskripsi publik</h2><p><?= nl2br(e($partner['deskripsi']?:'Belum ada deskripsi.')) ?></p></section>
<p class="partner-detail-note"><?= panel_icon('view') ?> Logo, website, dan deskripsi mitra aktif dapat tampil di beranda publik.</p>
</div>
<?php panel_end($user); ?>
