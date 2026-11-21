<?php
if(!isset($user,$data,$errors,$editing)){http_response_code(403);exit;}
$maxYear=min(2155,(int)(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y'));
panel_start($user,$editing?'Edit Data Alumni':'Tambah Data Alumni','tracer');
?>
<div class="alumni-form-workspace">
<div class="page-actions alumni-page-heading"><h1><?= panel_icon('pengguna') ?> <?= $editing?'Edit Data Alumni':'Tambah Data Alumni' ?></h1><a href="<?= e(base_url('panel/tracer-study/')) ?>"><?= panel_icon('back') ?> Kembali ke Data Alumni</a></div>
<?php if($errors):?><div class="alert" role="alert"><strong>Data belum disimpan.</strong><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?>
<form class="editor-form card admin-alumni-form" method="post" action="<?= e(base_url($editing?'panel/tracer-study/edit.php?id='.$id:'panel/tracer-study/tambah.php')) ?>">
<?= csrf_field() ?><?php if($editing): ?><input type="hidden" name="revision" value="<?= e($revision) ?>"><?php else: ?><input type="hidden" name="submission_token" value="<?= e($nonce) ?>"><?php endif; ?>
<?php foreach(['nama_lengkap'=>['Nama lengkap',150,true],'nisn'=>['NISN (opsional)',20,false]] as $field=>[$label,$max,$required]):?><label for="<?= $field ?>"><span class="alumni-field-label"><?= panel_icon(['nama_lengkap'=>'akun','nisn'=>'identity','nama_perusahaan'=>'mitra','posisi_pekerjaan'=>'lowongan','lokasi_pekerjaan'=>'location','nama_kampus'=>'program','program_studi'=>'certificate','nama_usaha'=>'mitra','bidang_usaha'=>'lowongan'][$field] ?? 'message') ?> <?= e($label) ?></span><input id="<?= $field ?>" name="<?= $field ?>" maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" <?= $required?'required':'' ?> <?= $field==='nama_lengkap'?'autocomplete="name"':'' ?>></label><?php endforeach;?>
<label for="jurusan"><span class="alumni-field-label"><?= panel_icon('program') ?> Jurusan saat sekolah</span><select id="jurusan" name="jurusan" required><option value="">Pilih jurusan</option><?php if($data['jurusan']!==''&&!in_array($data['jurusan'],['Teknik Komputer dan Jaringan','Teknik Sepeda Motor','Desain Produksi Busana','Teknik Bodi Kendaraan Ringan','Teknik Pengelasan','Teknik Instalasi Tenaga Listrik','Teknik Pendingin Tata Udara dan Pemanas','Teknik Pemesinan','Desain Teknik Furniture','Teknik Kimia Industri'],true)): ?><option value="<?= e($data['jurusan']) ?>" selected><?= e($data['jurusan']) ?></option><?php endif; ?>
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
<label for="tahun_lulus"><span class="alumni-field-label"><?= panel_icon('calendar') ?> Tahun lulus</span><select id="tahun_lulus" name="tahun_lulus" required><option value="">Pilih tahun lulus</option><?php for($year=$maxYear;$year>=1900;$year--): ?><option value="<?= $year ?>" <?= $data['tahun_lulus']===(string)$year?'selected':'' ?>><?= $year ?></option><?php endfor; ?></select></label>
<label for="nomor_telepon"><span class="alumni-field-label"><?= panel_icon('whatsapp') ?> Nomor WhatsApp</span><input type="tel" id="nomor_telepon" name="nomor_telepon" maxlength="30" autocomplete="tel" value="<?= e($data['nomor_telepon']) ?>" placeholder="08… atau +62…" required></label>
<label for="email"><span class="alumni-field-label"><?= panel_icon('mail') ?> Email (opsional)</span><input type="email" id="email" name="email" maxlength="191" autocomplete="email" value="<?= e($data['email']) ?>"></label>
<label for="status_kegiatan"><span class="alumni-field-label"><?= panel_icon('lowongan') ?> Status kegiatan</span><select id="status_kegiatan" name="status_kegiatan" required><option value="">Pilih status kegiatan</option><?php foreach(tracer_statuses() as $value=>$label):?><option value="<?= $value ?>" <?= $data['status_kegiatan']===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></label>
<noscript><p>Isi detail pada kelompok yang sesuai dengan status Anda. Kelompok lain boleh dikosongkan.</p></noscript>
<?php foreach(['bekerja'=>['nama_perusahaan'=>['Nama perusahaan',200,true],'posisi_pekerjaan'=>['Posisi pekerjaan',150,true],'lokasi_pekerjaan'=>['Lokasi pekerjaan (opsional)',255,false]],'kuliah'=>['nama_kampus'=>['Nama kampus',200,true],'program_studi'=>['Program studi',150,true]],'wirausaha'=>['nama_usaha'=>['Nama usaha',200,true],'bidang_usaha'=>['Bidang usaha',150,true]]] as $status=>$fields):?>
<fieldset data-tracer-status="<?= $status ?>"><legend>Detail <?= e(tracer_statuses()[$status]) ?></legend><?php foreach($fields as $field=>[$label,$max,$required]):?><label for="<?= $field ?>"><span class="alumni-field-label"><?= panel_icon(['nama_lengkap'=>'akun','nisn'=>'identity','nama_perusahaan'=>'mitra','posisi_pekerjaan'=>'lowongan','lokasi_pekerjaan'=>'location','nama_kampus'=>'program','program_studi'=>'certificate','nama_usaha'=>'mitra','bidang_usaha'=>'lowongan'][$field] ?? 'message') ?> <?= e($label) ?></span><input id="<?= $field ?>" name="<?= $field ?>" maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" <?= $required?'data-status-required="1"':'' ?>></label><?php endforeach;?></fieldset>
<?php endforeach;?>
<label for="keterangan"><span class="alumni-field-label"><?= panel_icon('message') ?> Keterangan tambahan (opsional)</span><textarea id="keterangan" name="keterangan" maxlength="5000" rows="2"><?= e($data['keterangan']) ?></textarea></label>

<?php if($editing): ?><p class="admin-alumni-consent">Persetujuan data dan WhatsApp yang sudah tercatat tetap dipertahankan.</p><?php else: ?><div class="admin-alumni-consent"><label class="check-label"><input type="checkbox" name="persetujuan_data" value="1" <?= $data['persetujuan_data']?'checked':'' ?> required> Saya telah memperoleh persetujuan alumni untuk pendataan dan pemrosesan data ini.</label><small>Data hanya tersedia bagi pengelola. Pencatatan ini masuk sebagai respons baru, tanpa mengganti respons sebelumnya.</small></div>
<?php endif; ?>
<div class="form-actions"><button type="submit"><?= panel_icon('save') ?> Simpan data alumni</button><a href="<?= e(base_url('panel/tracer-study/')) ?>"><?= panel_icon('close') ?> Batal</a></div>
</form>
<script src="<?= e(asset_url('js/tracer-study.js')) ?>" defer></script>
</div>
<?php panel_end($user); ?>
