<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/pkl-schedules.php';
$user=require_role('admin','staf_bkk');pkl_schedules_require_ready();
$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=null;if($id){try{$old=pkl_schedule_find($id);}catch(InvalidArgumentException){panel_not_found();}}
$kind=$old['jenis']??input_text($_GET,'jenis');if(!isset(pkl_schedule_types()[$kind])){http_response_code(400);exit('Pilih jenis jadwal yang tersedia.');}
$isParentMeeting=$kind==='orang_tua';$schoolMeetingDefaults=[];
if($isParentMeeting){require_once APP_ROOT.'/app/helpers/public.php';$schoolMeetingDefaults=pkl_parent_meeting_defaults(setting('school_name'),setting('contact_address'));}
$placementId=$old?(int)$old['placement_id']:(positive_id($_GET['placement_id']??'')??0);
$placement=null;if($placementId){try{$placement=pkl_find($placementId);}catch(InvalidArgumentException){panel_not_found();}}
$data=$old?pkl_schedule_input($old):pkl_schedule_input(['jenis'=>$kind,'status'=>'dijadwalkan']);$version=(int)($old['version']??0);$errors=[];
if(!$old&&$placement){
    $defaults=['petugas'=>$placement['guru_pembimbing'],'agenda'=>pkl_schedule_types()[$kind]];
    if($kind==='monitoring'){$defaults+=['tujuan'=>$placement['tempat_pkl'],'alamat_lengkap'=>$placement['alamat_lengkap']];}
    if($isParentMeeting){$defaults=$schoolMeetingDefaults;}
    $data=array_replace($data,$defaults);
}
$isBatch=false;$batchPlacements=[];$batchIds=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$action=input_text($_POST,'aksi');
    try{
        if(in_array($action,['pilih_banyak','simpan_banyak'],true)){
            if($id){throw new InvalidArgumentException('Edit jadwal hanya untuk satu siswa.');}
            $batchPlacements=pkl_schedule_placements($_POST['placement_ids']??null);$isBatch=true;
            $batchIds=array_map(static fn($row)=>(string)$row['id'],$batchPlacements);
            if($action==='simpan_banyak'){
                $data=pkl_schedule_input($_POST);if($data['jenis']!==$kind){throw new InvalidArgumentException('Jenis jadwal tidak sesuai halaman.');}
                $savedIds=pkl_schedule_save_batch($batchIds,$data,(int)$user['id']);
                $_SESSION['schedule_selection_clear'][$kind]=true;
                panel_message(count($savedIds).' jadwal berhasil dibuat, masing-masing terpisah per siswa.','panel/pkl/jadwal.php?'.http_build_query(['jenis'=>$kind]));
            }
        }else{
            if(!$placement){throw new InvalidArgumentException('Pilih penempatan terlebih dahulu.');}
            $data=pkl_schedule_input($_POST);$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
            if($data['jenis']!==$kind){throw new InvalidArgumentException('Jenis jadwal tidak sesuai halaman.');}
            pkl_schedule_save($placementId,$id,$version,$data,(int)$user['id']);panel_message('Jadwal PKL berhasil disimpan.','panel/pkl/jadwal.php?placement_id='.$placementId);
        }
    }catch(Throwable $exception){$errors[]=pkl_error($exception);}
}
if($isBatch){
    $placement=['nama'=>count($batchPlacements).' siswa dipilih','tempat_pkl'=>'Jadwal bersama','tanggal_mulai'=>max(array_column($batchPlacements,'tanggal_mulai')),'tanggal_selesai'=>min(array_column($batchPlacements,'tanggal_selesai'))];
    if(input_text($_POST,'aksi')==='pilih_banyak'){
        $data=pkl_schedule_input(['jenis'=>$kind,'status'=>'dijadwalkan','agenda'=>pkl_schedule_types()[$kind]]);
        if($isParentMeeting){$data=array_replace($data,$schoolMeetingDefaults);}
        foreach(($isParentMeeting?[]:['petugas'=>'guru_pembimbing'])+($kind==='monitoring'?['tujuan'=>'tempat_pkl','alamat_lengkap'=>'alamat_lengkap']:[]) as $field=>$source){$values=array_unique(array_column($batchPlacements,$source));if(count($values)===1){$data[$field]=(string)reset($values);}}
    }
}
$scheduleNoOverlap=$isBatch&&$kind==='monitoring'&&$placement['tanggal_mulai']>$placement['tanggal_selesai'];
panel_start($user,($id?'Edit ':'Tambah ').pkl_schedule_types()[$kind],'pkl');
?>
<div class="schedule-form-workspace">
<header class="page-actions"><div><h1><?= panel_icon($kind==='orang_tua'?'pengguna':($kind==='sidang'?'certificate':'calendar')) ?> <?= $id?'Edit':'Tambah' ?> <?= e(pkl_schedule_types()[$kind]) ?></h1><p><?= $isParentMeeting?($placement?'Atur rapat orang tua/wali di sekolah.':'Pilih siswa yang orang tua/walinya akan diundang.'):($placement?'Lengkapi waktu, lokasi, dan agenda jadwal.':'Pilih siswa yang terkait jadwal terlebih dahulu.') ?></p></div><a href="<?= e(base_url('panel/pkl/jadwal.php')) ?>"><?= panel_icon('back') ?> Daftar jadwal</a></header>
<?php if(!$id): ?><ol class="schedule-form-steps" aria-label="Langkah membuat jadwal"><li <?= !$placement?'aria-current="step"':'' ?>>1. <?= $isParentMeeting?'Pilih siswa undangan':'Pilih siswa / penempatan' ?></li><li <?= $placement?'aria-current="step"':'' ?>>2. <?= $isParentMeeting?'Isi rapat &amp; simpan':'Isi jadwal &amp; simpan' ?></li></ol><?php endif; ?>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if(!$placement):$pickerAction='panel/pkl/jadwal-form.php';$pickerParams=['jenis'=>$kind];require APP_ROOT.'/app/layouts/pkl-placement-picker.php';else: ?>
<div class="schedule-placement-context"><div><strong><?= panel_icon('peserta') ?> <?= e($placement['nama']) ?></strong><?php if(!$isBatch&&$isParentMeeting): ?><small>Orang tua/wali siswa diundang rapat di sekolah.</small><?php elseif(!$isBatch): ?><small><?= e($placement['tempat_pkl']) ?> &middot; <?= e($placement['tanggal_mulai'].' - '.$placement['tanggal_selesai']) ?></small><?php else: ?><small>Waktu, lokasi, agenda, dan status berlaku sama; jadwal tersimpan terpisah untuk tiap siswa.</small><?php endif; ?></div><?php if(!$id): ?><a href="<?= e(base_url('panel/pkl/jadwal-form.php?'.http_build_query(['jenis'=>$kind]))) ?>"><?= panel_icon('reset') ?> Ganti siswa</a><?php endif; ?></div>

<?php if($isBatch): ?><details class="schedule-batch-preview"><summary><?= panel_icon('peserta') ?> Rincian siswa terpilih (<?= count($batchPlacements) ?>)</summary><div class="table-scroll" data-vertical-list><table><thead><tr><th>Siswa</th><th><?= $isParentMeeting?'Kelas / jurusan':'Tempat / periode PKL' ?></th></tr></thead><tbody><?php foreach($batchPlacements as $member): ?><tr><td><?= e($member['nama']) ?></td><td><?= e($isParentMeeting?$member['kelas'].' / '.$member['jurusan']:$member['tempat_pkl'].' / '.$member['tanggal_mulai'].' - '.$member['tanggal_selesai']) ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
<?php if($isParentMeeting&&$data['alamat_lengkap']===''): ?><p class="notice">Alamat sekolah belum tersedia. Isi alamat resmi sekolah pada kolom Alamat sekolah.</p><?php endif; ?>
<?php if($scheduleNoOverlap): ?><p class="notice" role="alert">Periode siswa yang dipilih tidak beririsan. Ganti pilihan siswa atau buat jadwal monitoring secara terpisah.</p><?php endif; ?>
<form class="editor-form card schedule-event-editor" method="post"><?= csrf_field() ?><?php if($isBatch): ?><input type="hidden" name="aksi" value="simpan_banyak"><?php foreach($batchIds as $batchId): ?><input type="hidden" name="placement_ids[]" value="<?= e($batchId) ?>"><?php endforeach; ?><?php endif; ?><input type="hidden" name="jenis" value="<?= e($kind) ?>"><input type="hidden" name="version" value="<?= $version ?>">
<?php $eventGroups=[['Waktu & peserta',['tanggal','jam','petugas','orang_tua_wali']],['Lokasi & agenda',['tujuan','alamat_lengkap','agenda']],['Status & hasil',['status','hasil_monitoring','tindak_lanjut']]];$eventIcons=['tanggal'=>'calendar','jam'=>'calendar','petugas'=>'akun','orang_tua_wali'=>'pengguna','tujuan'=>'location','alamat_lengkap'=>'location','agenda'=>'message','status'=>'check-circle','hasil_monitoring'=>'lamaran','tindak_lanjut'=>'edit'];$eventFields=pkl_schedule_fields();if($isParentMeeting){$eventGroups[0][0]='Waktu & undangan';$eventGroups[1][0]='Lokasi rapat & agenda';$eventGroups[2][0]='Status rapat';$eventFields['petugas'][0]='Penanggung jawab rapat';$eventFields['orang_tua_wali'][0]='Nama / kelompok undangan';$eventFields['tujuan'][0]='Ruang / lokasi rapat di sekolah';$eventFields['alamat_lengkap'][0]='Alamat sekolah';$eventFields['agenda'][0]='Agenda rapat';} ?>
<?php foreach($eventGroups as $number=>[$groupTitle,$groupFields]): ?><h2><?= panel_icon(['calendar','location','check-circle'][$number]) ?> <?= $number+1 ?>. <?= e($groupTitle) ?></h2>
<?php foreach($groupFields as $key):[$label,$max]=$eventFields[$key];if($key==='orang_tua_wali'&&$kind!=='orang_tua'){continue;}if(in_array($key,['hasil_monitoring','tindak_lanjut'],true)&&$kind!=='monitoring'){continue;} ?>
<div class="schedule-event-cell <?= (in_array($key,['tujuan','alamat_lengkap','agenda','status','hasil_monitoring','tindak_lanjut'],true)||($key==='petugas'&&$kind!=='orang_tua'))?'schedule-event-wide':'' ?>"><label for="<?= $key ?>"><?= panel_icon($eventIcons[$key]) ?> <?= e($label) ?><?= $key==='orang_tua_wali'?' (opsional)':(in_array($key,['hasil_monitoring','tindak_lanjut'],true)?'':' *') ?></label>
<?php if($key==='status'): ?><select id="status" name="status"><?php foreach(pkl_schedule_statuses() as $value=>$statusLabel): ?><option value="<?= $value ?>" <?= $data['status']===$value?'selected':'' ?>><?= e($statusLabel) ?></option><?php endforeach; ?></select>
<?php elseif(in_array($key,['alamat_lengkap','agenda','hasil_monitoring','tindak_lanjut'],true)): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="2" maxlength="<?= $max ?>" <?= in_array($key,['alamat_lengkap','agenda'],true)?'required':'' ?>><?= e($data[$key]) ?></textarea>
<?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= ['tanggal'=>'date','jam'=>'time'][$key]??'text' ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= $key==='orang_tua_wali'?'':'required' ?> <?= $key==='tanggal'&&$kind==='monitoring'?'min="'.e($placement['tanggal_mulai']).'" max="'.e($placement['tanggal_selesai']).'"':'' ?>><?php endif; ?></div>
<?php endforeach; ?><?php endforeach; ?>
<p class="schedule-event-note"><?php if($kind==='monitoring'): ?>Tanggal monitoring harus dalam periode PKL. Untuk beberapa siswa, tanggal harus berada dalam periode seluruh penempatan. Hasil monitoring wajib saat status Selesai; tindak lanjut boleh dikosongkan.<?php elseif($isParentMeeting): ?>Rapat dilaksanakan di sekolah; isi ruang rapat bila diperlukan. Waktu rapat tidak mengikuti periode PKL.<?php else: ?>Lokasi sidang ditentukan pengelola; tanggal boleh di luar periode praktik.<?php endif; ?> <?php if($isBatch&&$kind==='monitoring'): ?>Hasil bersama harus berlaku untuk semua siswa; hasil berbeda diubah pada jadwal masing-masing.<?php endif; ?> <?= $isParentMeeting?'Untuk beberapa penanggung jawab, pisahkan nama dengan koma.':'Untuk beberapa petugas, pisahkan nama dengan koma.' ?> Gunakan status Dibatalkan untuk menjaga riwayat.</p>
<div class="schedule-event-footer"><small>* Wajib diisi. <?= $isParentMeeting?'Undangan terkait siswa yang dipilih.':'Jadwal terkait siswa yang dipilih.' ?></small><button type="submit" <?= $scheduleNoOverlap?'disabled':'' ?>><?= panel_icon('save') ?> <?= $isBatch?'Simpan '.count($batchPlacements).($isParentMeeting?' undangan':' jadwal'):($isParentMeeting?'Simpan rapat':'Simpan jadwal') ?></button><a href="<?= e(base_url('panel/pkl/jadwal.php')) ?>">Batal</a></div></form><?php endif; ?>
</div>
<?php if(!$placement): ?><script src="<?= e(asset_url('js/schedule-selection.js')) ?>?v=<?= (int)filemtime(APP_ROOT.'/assets/js/schedule-selection.js') ?>" defer></script><?php endif; ?>
<?php panel_end($user); ?>
