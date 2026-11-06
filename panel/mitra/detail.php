<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/partners.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
try{$partner=partner_find($id);}catch(InvalidArgumentException){panel_not_found();}
panel_start($user,'Detail mitra','mitra');
?>
<div class="page-actions"><h1><?= e($partner['nama']) ?></h1><a href="<?= e(base_url('panel/mitra/')) ?>">Daftar mitra</a><a href="<?= e(base_url('panel/mitra/edit.php?id='.$id)) ?>">Edit profil / PIC</a><?php if(user_can($user,'mou')): ?><a href="<?= e(base_url('panel/mou/?mitra_id='.$id)) ?>">Riwayat MoU</a><?php endif; ?></div>
<section class="card"><?php if($partner['logo']&&managed_upload_path($partner['logo'])): ?><img src="<?= e(base_url($partner['logo'])) ?>" alt="Logo <?= e($partner['nama']) ?>" width="96"><?php endif; ?>
<dl class="response-details"><dt>Jenis</dt><dd><?= $partner['jenis']==='kampus'?'Kampus':'Perusahaan' ?></dd><dt>Klaster</dt><dd><?= e($partner['jenis']==='kampus'?($partner['negara']==='Indonesia'?'Kampus Dalam Negeri':'Negara belum dilengkapi'):(partner_clusters()[$partner['klaster']]??'Belum diklasifikasikan')) ?></dd>
<?php foreach(partner_profile_fields() as $key=>[$label,$max]): ?><dt><?= e($label) ?></dt><dd><?= nl2br(e($partner[$key]?:'Belum diisi')) ?></dd><?php endforeach; ?>
<dt>Website resmi</dt><dd><?php if($partner['website']&&http_url($partner['website'])): ?><a href="<?= e($partner['website']) ?>" target="_blank" rel="noopener noreferrer"><?= e($partner['website']) ?></a><?php else: ?>Belum diisi<?php endif; ?></dd><dt>Deskripsi publik</dt><dd><?= nl2br(e($partner['deskripsi']??'')) ?></dd><dt>Status mitra</dt><dd><?= $partner['aktif']?'Aktif':'Nonaktif' ?></dd></dl><p>Data PIC hanya ditampilkan di panel. Logo, website, dan deskripsi mitra aktif dapat tampil di beranda publik.</p></section>
<?php panel_end($user); ?>
