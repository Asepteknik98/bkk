<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-classes.php';
$user=require_role('admin','staf_bkk');industry_require_ready();
$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=null;if($id){try{$old=industry_find($id);}catch(InvalidArgumentException){panel_not_found();}}
$data=$old?industry_input(array_map(static fn($value)=>(string)($value??''),$old)):industry_input(['rencana_pertemuan'=>'1','status'=>'aktif']);
$selectedSpeakers=$id?industry_refs($id,'speakers'):[];$selectedParticipants=$id?industry_refs($id,'participants'):[];$version=(int)($old['version']??0);$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=industry_input($_POST);$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{
        if(($_POST['form_complete']??'')!=='1'){throw new InvalidArgumentException('Formulir tidak lengkap. Muat ulang; periksa batas input server jika memilih banyak peserta.');}
        $selectedSpeakers=industry_ids($_POST['speakers']??[]);$selectedParticipants=industry_ids($_POST['participants']??[]);
        $saved=industry_save($data,$selectedSpeakers,$selectedParticipants,$id,$version,(int)$user['id']);panel_message('Kelas industri berhasil disimpan.','panel/kelas-industri/detail.php?id='.$saved);
    }catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
$search=input_text($_GET,'q_siswa');try{$participants=industry_participant_options($search,$selectedParticipants);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$partners=panel_query("SELECT id,nama,aktif FROM mitra WHERE jenis='perusahaan' ORDER BY nama,id")->fetchAll();$speakers=panel_query('SELECT id,nama,instansi FROM speakers ORDER BY nama,id')->fetchAll();
panel_start($user,$id?'Edit kelas industri':'Tambah kelas industri','kelas-industri');
?>
<div class="page-actions"><h1><?= $id?'Edit':'Tambah' ?> kelas industri</h1><a href="<?= e(base_url('panel/kelas-industri/')) ?>">Daftar kelas</a></div>
<p>Gunakan data mitra perusahaan dan pemateri yang terdaftar. Bila belum tersedia, minta pengelola modul Mitra/Pemateri menambahkannya.</p>
<form class="filter-form card" method="get"><?php if($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?><label>Cari pilihan peserta: nama, NIS, atau kelas<input name="q_siswa" maxlength="100" value="<?= e($search) ?>"></label><button>Cari peserta</button></form>
<p>Cari peserta sebelum mengisi formulir. Maksimal 50 hasil ditambah seluruh peserta terpilih pada kelas tersimpan.</p>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<label for="nama">Nama kelas<input id="nama" name="nama" maxlength="190" value="<?= e($data['nama']) ?>" required></label>
<label for="mitra_id">Mitra industri<select id="mitra_id" name="mitra_id" required><option value="">Pilih mitra perusahaan</option><?php foreach($partners as $partner): ?><option value="<?= $partner['id'] ?>" <?= (string)$partner['id']===$data['mitra_id']?'selected':'' ?>><?= e($partner['nama'].(!$partner['aktif']?' (nonaktif)':'')) ?></option><?php endforeach; ?></select></label>
<label for="speakers">Pemateri kelas (pilih satu atau beberapa)<select id="speakers" name="speakers[]" multiple size="6" style="min-height:10rem" required><?php foreach($speakers as $speaker): ?><option value="<?= $speaker['id'] ?>" <?= in_array((int)$speaker['id'],$selectedSpeakers,true)?'selected':'' ?>><?= e($speaker['nama'].($speaker['instansi']?' — '.$speaker['instansi']:'')) ?></option><?php endforeach; ?></select></label>
<label for="kelas_sasaran">Kelas / kelompok sasaran<input id="kelas_sasaran" name="kelas_sasaran" maxlength="200" value="<?= e($data['kelas_sasaran']) ?>" placeholder="Contoh: XI TKJ 1"></label>
<label for="participants">Peserta dari data siswa (opsional bila sasaran sudah diisi)<select id="participants" name="participants[]" multiple size="8" style="min-height:12rem"><?php foreach($participants as $participant): ?><option value="<?= $participant['id'] ?>" <?= in_array((int)$participant['id'],$selectedParticipants,true)?'selected':'' ?>><?= e($participant['nama'].' — '.$participant['nis'].' — '.$participant['kelas']) ?></option><?php endforeach; ?></select></label>
<p>Minimal isi kelas sasaran atau pilih peserta. Pilihan beberapa nama dapat memakai Ctrl/Cmd pada komputer. Pilihan yang tidak dicentang dilepas saat disimpan; identitas siswa tetap ada.</p>
<label for="rencana_pertemuan">Jumlah pertemuan yang direncanakan<input id="rencana_pertemuan" name="rencana_pertemuan" type="number" min="1" max="500" value="<?= e($data['rencana_pertemuan']) ?>" required></label><p>Slot bernomor dibuat sesuai rencana. Pengurangan hanya dapat membuang slot terakhir yang belum pernah diisi.</p>
<label for="catatan">Catatan kelas (opsional)<textarea id="catatan" name="catatan" rows="3" maxlength="10000"><?= e($data['catatan']) ?></textarea></label>
<label for="status">Status kelas<select id="status" name="status"><?php foreach(['aktif'=>'Aktif','arsip'=>'Arsip'] as $key=>$label): ?><option value="<?= $key ?>" <?= $data['status']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><input type="hidden" name="form_complete" value="1"><button>Simpan kelas</button></form>
<?php panel_end($user); ?>
