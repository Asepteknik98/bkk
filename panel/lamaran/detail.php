<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/panel.php';
$user = require_role('admin','staf_bkk');
$id = positive_id($_GET['id'] ?? '');
$row = $id ? panel_query('SELECT * FROM lamaran WHERE id=:id',['id'=>$id])->fetch() : null;
if (!$row) { panel_not_found(); }
$files = panel_query('SELECT id,jenis,nama_asli,ukuran FROM lamaran_berkas WHERE lamaran_id=:id ORDER BY id',['id'=>$id])->fetchAll();
$documentLabels = ['cv'=>'CV','ijazah'=>'Ijazah / SKL','sertifikat'=>'Sertifikat','pendukung'=>'Berkas pendukung'];
$statuses = ['baru'=>'Baru','diproses'=>'Diproses','diterima'=>'Diterima','ditolak'=>'Ditolak'];
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_post_csrf();
    $status = $_POST['status'] ?? '';
    if (!is_string($status) || !isset($statuses[$status])) { http_response_code(422); exit('Status tidak valid.'); }
    panel_query('UPDATE lamaran SET status=:status WHERE id=:id',['status'=>$status,'id'=>$id]);
    panel_message('Status lamaran berhasil diperbarui.','panel/lamaran/detail.php?id='.$id);
}
panel_query('UPDATE lamaran SET viewed_at=CURRENT_TIMESTAMP WHERE id=:id AND viewed_at IS NULL',['id'=>$id]);
panel_start($user,'Detail Lamaran','lamaran');
?>
<div class="application-review">
<div class="page-actions"><h1><?= panel_icon('view') ?> Detail Lamaran</h1><a href="<?= e(base_url('panel/lamaran/')) ?>"><?= panel_icon('back') ?> Lamaran masuk</a></div>
<section class="card application-review-card">
<header class="application-review-heading"><div><h2><?= panel_icon('akun') ?> <?= e($row['nama']) ?></h2><p><?= e($row['posisi']) ?> &middot; <?= e($row['perusahaan']) ?></p></div><span class="badge"><?= e($statuses[$row['status']]) ?></span></header>
<div class="application-review-layout"><div class="application-review-info"><dl class="application-review-data">
<?php foreach (['email'=>'Email','telepon'=>'WhatsApp / telepon','pendidikan'=>'Pendidikan','alamat'=>'Alamat domisili','created_at'=>'Dikirim (WIB)'] as $field=>$label): ?><dt><?= panel_icon(['email'=>'mail','telepon'=>'phone','pendidikan'=>'program','alamat'=>'location','created_at'=>'calendar'][$field]) ?> <?= $label ?></dt><dd><?= e($row[$field]) ?></dd><?php endforeach; ?></dl>
<section class="application-review-message"><h2><?= panel_icon('message') ?> Pesan lamaran</h2><p><?= e($row['pesan'] ?: 'Tidak ada pesan tambahan.') ?></p></section>
</div><aside class="application-review-side" aria-label="Berkas dan status lamaran"><section class="application-review-documents"><h2><?= panel_icon('lamaran') ?> Berkas lamaran</h2>
<?php if ($files): ?><ul><?php foreach ($files as $file): ?><li><div class="application-file-meta"><strong><?= panel_icon($file['jenis']==='sertifikat' ? 'certificate' : 'lamaran') ?> <?= e($documentLabels[$file['jenis']] ?? 'Berkas') ?></strong><small><?= e($file['nama_asli']) ?> &middot; <?= (int)ceil($file['ukuran']/1024) ?> KB</small></div><div class="application-file-actions"><a class="application-file-view" href="<?= e(base_url('panel/lamaran/berkas.php?id='.$file['id'].'&lihat=1')) ?>" target="_blank" rel="noopener" aria-label="<?= e('Lihat '.($documentLabels[$file['jenis']] ?? 'berkas').' di tab baru') ?>"><?= panel_icon('view') ?> Lihat</a><a href="<?= e(base_url('panel/lamaran/berkas.php?id='.$file['id'])) ?>" aria-label="<?= e('Unduh '.($documentLabels[$file['jenis']] ?? 'berkas')) ?>"><?= panel_icon('download') ?> Unduh</a></div></li><?php endforeach; ?></ul><?php else: ?><p>Lamaran ini belum memiliki berkas.</p><?php endif; ?></section>
<form method="post" class="application-review-status"><?= csrf_field() ?><div><label for="application-status">Status lamaran</label><select name="status" id="application-status"><?php foreach ($statuses as $value=>$label): ?><option value="<?= $value ?>" <?= $row['status']===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div><button type="submit">Simpan status</button></form></aside></div>
<div class="wa-consent-note"><p>Informasi WhatsApp: <strong><?= !empty($row['wa_consent'])?'Bersedia menerima':'Tidak berlangganan' ?></strong></p><?php if(!empty($row['wa_consent'])):?><form method="post" action="<?= e(base_url('panel/whatsapp/optout.php')) ?>"><?= csrf_field() ?><input type="hidden" name="source" value="pelamar"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" class="button secondary">Nonaktifkan atas permintaan penerima</button></form><?php endif;?></div></section></div>
<?php panel_end($user); ?>
