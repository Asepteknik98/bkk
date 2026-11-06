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
<div class="page-actions"><h1><?= $id?'Edit':'Tambah' ?> Sarasehan Industri</h1><a href="<?= e(base_url('panel/sarasehan/')) ?>">Daftar jadwal</a></div>
<form class="filter-form card" method="get"><?php if($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?><label>Cari mitra perusahaan<input name="q_mitra" maxlength="100" value="<?= e($search) ?>"></label><button>Cari mitra</button></form><p>Cari mitra sebelum mengisi formulir. Maksimal 50 hasil ditambah undangan yang sudah tersimpan. Simpan pilihan sebelum melakukan pencarian berikutnya.</p>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<?php foreach(['nama'=>['Nama kegiatan',190],'tanggal'=>['Tanggal',10],'jam'=>['Jam WIB',5],'lokasi'=>['Lokasi (atau isi tautan pertemuan)',500],'tautan'=>['Tautan pertemuan (atau isi lokasi)',2048],'pic_sekolah'=>['PIC sekolah: nama / kontak',250]] as $key=>[$label,$max]): ?><label for="<?= $key ?>"><?= e($label) ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= ['tanggal'=>'date','jam'=>'time','tautan'=>'url'][$key]??'text' ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= in_array($key,['nama','tanggal','jam','pic_sekolah'],true)?'required':'' ?>></label><?php endforeach; ?>
<h2>Mitra undangan dan PIC industri</h2><p>Pilih minimal satu mitra dan isi nama PIC untuk setiap mitra terpilih. Kontak opsional. Data PIC terisi awal dari profil mitra jika tersedia; perubahan di sini hanya berlaku pada kegiatan ini.</p>
<div class="table-scroll"><table><thead><tr><th>Undang</th><th>Mitra industri</th><th>Nama PIC</th><th>Kontak PIC (opsional)</th></tr></thead><tbody><?php foreach($partners as $partner):$pid=(int)$partner['id'];$pic=$invites[$pid]??$defaults[$pid]??[]; ?><tr><td><input type="checkbox" name="mitra_ids[]" value="<?= $pid ?>" aria-label="Undang <?= e($partner['nama']) ?>" <?= isset($invites[$pid])?'checked':'' ?>></td><td><?= e($partner['nama']) ?></td><td><input name="pic_nama[<?= $pid ?>]" maxlength="150" aria-label="Nama PIC <?= e($partner['nama']) ?>" value="<?= e($pic['pic_nama']??'') ?>"></td><td><input name="pic_kontak[<?= $pid ?>]" maxlength="250" aria-label="Kontak PIC <?= e($partner['nama']) ?>" value="<?= e($pic['pic_kontak']??'') ?>"></td></tr><?php endforeach; ?><?php if(!$partners): ?><tr><td colspan="4">Mitra perusahaan belum tersedia. Lengkapi data pada modul Mitra.</td></tr><?php endif; ?></tbody></table></div><input type="hidden" name="form_complete" value="1"><button>Simpan jadwal</button></form>
<?php panel_end($user); ?>
