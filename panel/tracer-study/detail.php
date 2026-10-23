<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??null);$row=$id?panel_query('SELECT * FROM tracer_responses WHERE id=:id',['id'=>$id])->fetch():false;if(!$row){panel_not_found();}
$identity=['nisn'=>['NISN','lamaran'],'jurusan'=>['Jurusan','program'],'tahun_lulus'=>['Tahun lulus','calendar'],'nomor_telepon'=>['WhatsApp','phone'],'email'=>['Email','mail']];
$activityFields=match($row['status_kegiatan']){
    'bekerja'=>['nama_perusahaan','posisi_pekerjaan','lokasi_pekerjaan'],
    'kuliah'=>['nama_kampus','program_studi'],
    'wirausaha'=>['nama_usaha','bidang_usaha'],default=>[]
};
$labels=tracer_fields();
panel_start($user,'Detail Respons Alumni','tracer');
?>
<div class="alumni-detail">
<div class="page-actions"><h1><?= panel_icon('akun') ?> Detail Alumni</h1><a href="<?= e(base_url('panel/tracer-study/?'.http_build_query(['tahun_lulus'=>(string)$row['tahun_lulus']]))) ?>"><?= panel_icon('back') ?> Daftar alumni</a></div>
<section class="card alumni-detail-card">
<header class="alumni-detail-heading"><div><h2><?= e($row['nama_lengkap']) ?></h2><p><?= e($row['jurusan']) ?> &middot; Angkatan <?= (int)$row['tahun_lulus'] ?></p></div><span class="badge"><?= e(tracer_statuses()[$row['status_kegiatan']]??$row['status_kegiatan']) ?></span></header>
<div class="alumni-detail-grid">
<section><h3><?= panel_icon('akun') ?> Identitas &amp; kontak</h3><dl class="alumni-detail-data"><?php foreach($identity as $field=>[$label,$icon]):?><dt><?= panel_icon($icon) ?> <?= $label ?></dt><dd><?= e((string)($row[$field]??''))?:'Belum diisi' ?></dd><?php endforeach;?></dl></section>
<section class="alumni-detail-activity"><h3><?= panel_icon('lowongan') ?> Kegiatan saat ini</h3><p class="alumni-current-status"><?= e(tracer_statuses()[$row['status_kegiatan']]??$row['status_kegiatan']) ?></p>
<?php if($row['status_kegiatan']==='mencari_kerja'):?><p class="muted">Alumni sedang mencari pekerjaan.</p><?php endif;?>
<?php if($activityFields):?><dl class="alumni-detail-data"><?php foreach($activityFields as $field):if(trim((string)($row[$field]??''))===''){continue;}?><dt><?= e($labels[$field]) ?></dt><dd><?= e($row[$field]) ?></dd><?php endforeach;?></dl><?php endif;?>
<h3><?= panel_icon('message') ?> Keterangan</h3><p class="alumni-detail-note"><?= e($row['keterangan']?:'Tidak ada keterangan tambahan.') ?></p>
<p class="alumni-submitted"><?= panel_icon('calendar') ?> Dikirim <?= e($row['created_at']) ?> WIB</p>
</section></div>
<details class="alumni-consent"><summary>Persetujuan data &amp; WhatsApp</summary><dl class="alumni-detail-data"><dt>Persetujuan data</dt><dd><?= (int)$row['persetujuan_data']===1?'Diberikan':'Tidak tersedia' ?></dd><dt>Waktu</dt><dd><?= e($row['persetujuan_at']) ?></dd><dt>Versi</dt><dd><?= e($row['versi_persetujuan']) ?></dd><dt>WhatsApp</dt><dd><?= !empty($row['wa_opted_out_at'])?'Meminta berhenti':(!empty($row['wa_consent'])?'Bersedia menerima informasi':'Persetujuan belum tercatat') ?></dd></dl>
<?php if($row['versi_persetujuan']===TRACER_CONSENT_VERSION):?><p><?= e(TRACER_CONSENT) ?></p><?php endif;?>
<?php if(empty($row['wa_opted_out_at'])):?><form method="post" action="<?= e(base_url('panel/whatsapp/optout.php')) ?>"><?= csrf_field() ?><input type="hidden" name="source" value="tracer"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" class="button secondary">Nonaktifkan WhatsApp atas permintaan alumni</button></form><?php endif;?>
</details></section></div>
<?php panel_end($user);?>
