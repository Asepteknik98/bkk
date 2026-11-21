<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-forums.php';
$user=require_role('admin','staf_bkk');industry_forums_require_ready();$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=null;if($id){try{$old=industry_forum_find($id);}catch(InvalidArgumentException){panel_not_found();}}
$data=$old?industry_forum_input(array_map(static fn($v)=>(string)($v??''),$old)):industry_forum_input(['jam'=>'08:00']);$version=(int)($old['version']??0);$errors=[];$invites=[];
if($id){foreach(panel_query('SELECT mitra_id,pic_nama,pic_kontak FROM industry_forum_invitees WHERE forum_id=:id',['id'=>$id])->fetchAll() as $invite){$invites[(int)$invite['mitra_id']]=$invite;}}
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=industry_forum_input($_POST);$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{
        if(($_POST['form_complete']??'')!=='1'){throw new InvalidArgumentException('Formulir tidak lengkap. Muat ulang sebelum menyimpan.');}
        if(is_array($_POST['mitra_ids']??null)){
            $invites=[];foreach(array_slice($_POST['mitra_ids'],0,200) as $raw){$pid=positive_id($raw);if($pid){$invites[$pid]=['mitra_id'=>$pid,'pic_nama'=>input_text(is_array($_POST['pic_nama']??null)?$_POST['pic_nama']:[],(string)$pid),'pic_kontak'=>input_text(is_array($_POST['pic_kontak']??null)?$_POST['pic_kontak']:[],(string)$pid)];}}
        }
        $invites=industry_forum_invitees($_POST);$saved=industry_forum_save($data,$invites,$id,$version,(int)$user['id']);panel_message('Jadwal Sarasehan Industri disimpan.','panel/sarasehan/detail.php?id='.$saved);
    }catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
$search=input_text($_GET,'q_mitra');try{$partners=industry_forum_partner_options($search,array_keys($invites));}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$defaults=[];if(partner_profiles_ready()){foreach($partners as $partner){$defaults[$partner['id']]=panel_query('SELECT pic_nama,pic_kontak FROM mitra_profiles WHERE mitra_id=:id',['id'=>$partner['id']])->fetch()?:[];}}
panel_start($user,$id?'Edit Sarasehan Industri':'Tambah Sarasehan Industri','sarasehan');
?>
<div class="forum-list-page forum-form-page">
<header class="partner-detail-heading"><h1><?= panel_icon($id?'edit':'add') ?> <?= $id?'Edit':'Tambah' ?> Sarasehan Industri</h1><nav aria-label="Navigasi sarasehan"><a href="<?= e(base_url('panel/sarasehan/')) ?>"><?= panel_icon('back') ?> Daftar jadwal</a></nav></header>
<form class="filter-form card forum-partner-search" method="get"><?php if($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?><label><span><?= panel_icon('search') ?> Cari mitra perusahaan</span><input name="q_mitra" maxlength="100" value="<?= e($search) ?>" placeholder="Nama perusahaan"></label><button type="submit"><?= panel_icon('search') ?> Cari mitra</button></form>
<p class="forum-form-note">Pencarian memuat ulang formulir. Cari mitra sebelum mengisi jadwal.</p>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="forum-editor" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<div class="forum-form-columns">
<section class="partner-form-section"><h2><?= panel_icon('calendar') ?> Kegiatan &amp; waktu</h2><div class="partner-field-grid">
<?php foreach(['nama'=>['Nama kegiatan',190,'berita'],'tanggal'=>['Tanggal',10,'calendar'],'jam'=>['Jam (WIB)',5,'calendar'],'pic_sekolah'=>['PIC sekolah: nama / kontak',250,'akun']] as $key=>[$label,$max,$icon]): ?>
<div class="partner-field <?= in_array($key,['nama','pic_sekolah'],true)?'partner-field-wide':'' ?>"><label for="<?= $key ?>"><?= panel_icon($icon) ?> <?= e($label) ?> *</label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= ['tanggal'=>'date','jam'=>'time'][$key]??'text' ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" required></div><?php endforeach; ?>
</div></section>
<section class="partner-form-section"><h2><?= panel_icon('location') ?> Tempat pertemuan</h2><div class="partner-field-grid">
<div class="partner-field partner-field-wide"><label for="lokasi"><?= panel_icon('location') ?> Lokasi</label><input id="lokasi" name="lokasi" maxlength="500" value="<?= e($data['lokasi']) ?>" placeholder="Nama tempat / alamat pertemuan"></div>
<div class="partner-field partner-field-wide"><label for="tautan"><?= panel_icon('link') ?> Tautan pertemuan daring</label><input id="tautan" name="tautan" type="url" maxlength="2048" value="<?= e($data['tautan']) ?>" placeholder="https://"></div>
</div><p class="forum-form-note">Isi lokasi atau tautan pertemuan; keduanya boleh diisi.</p></section>
</div>
<div class="forum-table-heading"><h2><?= panel_icon('mitra') ?> Mitra undangan &amp; PIC</h2><span>Nama PIC wajib untuk mitra yang dicentang</span></div>
<div class="table-scroll forum-list-sheet forum-invite-sheet" data-vertical-list tabindex="0" role="region" aria-label="Pilihan mitra dan PIC, gulir untuk melihat data"><table><thead><tr><th><span><?= panel_icon('check-circle') ?> Undang</span></th><th><span><?= panel_icon('mitra') ?> Mitra industri</span></th><th><span><?= panel_icon('akun') ?> Nama PIC</span></th><th><span><?= panel_icon('phone') ?> Kontak PIC (opsional)</span></th></tr></thead><tbody><?php foreach($partners as $partner):$pid=(int)$partner['id'];$pic=$invites[$pid]??$defaults[$pid]??[]; ?><tr><td><input type="checkbox" name="mitra_ids[]" value="<?= $pid ?>" aria-label="Undang <?= e($partner['nama']) ?>" <?= isset($invites[$pid])?'checked':'' ?>></td><td><?= e($partner['nama']) ?></td><td><input name="pic_nama[<?= $pid ?>]" maxlength="150" aria-label="Nama PIC <?= e($partner['nama']) ?>" value="<?= e($pic['pic_nama']??'') ?>"></td><td><input name="pic_kontak[<?= $pid ?>]" maxlength="250" aria-label="Kontak PIC <?= e($partner['nama']) ?>" value="<?= e($pic['pic_kontak']??'') ?>"></td></tr><?php endforeach; ?><?php if(!$partners): ?><tr><td colspan="4">Mitra perusahaan belum tersedia. Lengkapi data pada modul Mitra.</td></tr><?php endif; ?></tbody></table></div><input type="hidden" name="form_complete" value="1"><div class="forum-form-actions"><span>* Wajib diisi</span><a href="<?= e(base_url('panel/sarasehan/')) ?>"><?= panel_icon('back') ?> Batal</a><button type="submit"><?= panel_icon('save') ?> Simpan jadwal</button></div></form></div>
<?php panel_end($user); ?>
