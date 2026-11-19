<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/learning-reports.php';
$user=require_role('admin','staf_bkk');report_require_ready();
$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$old=null;if($id){try{$old=report_find($id);}catch(InvalidArgumentException){panel_not_found();}}
$data=$old?report_input(array_map('strval',$old)):report_input(['siswa_id'=>(string)(positive_id($_GET['siswa_id']??'')??''),'tanggal'=>(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d'),'status'=>'draft']);
$version=(int)($old['version']??0);$errors=[];
$items=$old?panel_query('SELECT kompetensi,nilai,predikat,deskripsi FROM student_learning_report_items WHERE report_id=:id ORDER BY urutan',['id'=>$id])->fetchAll():[['kompetensi'=>'','nilai'=>'','predikat'=>'','deskripsi'=>'']];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data=report_input($_POST);$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
    try{$items=report_items($_POST);$saved=report_save($data,$items,$id,$version,(int)$user['id']);panel_message('Rapor berhasil disimpan.','panel/capaian-pembelajaran/detail.php?id='.$saved);}
    catch(InvalidArgumentException $e){
        http_response_code(422);
        $errors[]=$e->getMessage();
        if(isset($_POST['items'])&&is_array($_POST['items'])&&count($_POST['items'])<=50){
            $items=[];foreach($_POST['items'] as $row){if(!is_array($row)){continue;}$item=[];foreach(['kompetensi'=>190,'nilai'=>6,'predikat'=>30,'deskripsi'=>2000] as $key=>$max){$item[$key]=mb_substr(input_text($row,$key),0,$max);}$items[]=$item;}
            if(!$items){$items=[['kompetensi'=>'','nilai'=>'','predikat'=>'','deskripsi'=>'']];}
        }
    }
}
$search=input_text($_GET,'q');if(!valid_text($search,100,false)){http_response_code(400);exit('Pencarian siswa tidak valid.');}
$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';
$students=panel_query("SELECT id,nama,kelas,jurusan,angkatan FROM siswa WHERE nama LIKE :q ESCAPE '!' ORDER BY nama,id LIMIT 500",['q'=>$pattern])->fetchAll();
$selected=positive_id($data['siswa_id']);
if($selected&&!in_array($selected,array_map('intval',array_column($students,'id')),true)){
    $student=panel_query('SELECT id,nama,kelas,jurusan,angkatan FROM siswa WHERE id=:id',['id'=>$selected])->fetch();if($student){array_unshift($students,$student);}
}
panel_start($user,$id?'Edit rapor siswa':'Buat rapor siswa','capaian');
?>
<div class="learning-report-page report-form-page">
<div class="page-actions"><h1><?= panel_icon('certificate') ?> <?= $id?'Edit':'Buat' ?> rapor siswa</h1><a class="report-back" href="<?= e(base_url('panel/capaian-pembelajaran/')) ?>"><?= panel_icon('back') ?> Daftar rapor</a></div>
<p class="report-intro">Satu siswa, satu periode. Lengkapi identitas dan penilaian kompetensi di bawah.</p>
<?php if(!$id): ?><form class="filter-form card report-student-search" method="get"><label for="report-student-search"><span class="report-label"><?= panel_icon('search') ?> Cari siswa</span><input id="report-student-search" name="q" placeholder="Ketik nama siswa" maxlength="100" value="<?= e($search) ?>"></label><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('add') ?> Data siswa</a></form><?php endif; ?>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?><a href="<?= e(base_url('panel/capaian-pembelajaran/form.php'.($id?'?id='.$id:''))) ?>">Muat ulang formulir</a></div><?php endif; ?>
<form class="card report-editor" method="post" data-report-editor>
<?= csrf_field() ?><input type="hidden" name="version" value="<?= $version ?>">
<section class="report-section"><h2 class="report-section-title"><?= panel_icon('identity') ?> Identitas rapor</h2><div class="report-form-grid">
<label for="report-student"><span class="report-label"><?= panel_icon('peserta') ?> Siswa</span><?php if($old): ?><input type="hidden" name="siswa_id" value="<?= (int)$old['siswa_id'] ?>"><input id="report-student" readonly value="<?= e($old['nama'].' / '.$old['kelas'].' / '.$old['jurusan']) ?>"><?php else: ?><select id="report-student" name="siswa_id" required><option value="">Pilih siswa</option><?php foreach($students as $student): ?><option value="<?= $student['id'] ?>" <?= (int)$data['siswa_id']===(int)$student['id']?'selected':'' ?>><?= e($student['nama'].' / '.$student['kelas'].' / '.$student['jurusan'].' / '.$student['angkatan']) ?></option><?php endforeach; ?></select><?php endif; ?></label>
<label for="report-period"><span class="report-label"><?= panel_icon('calendar') ?> Periode / semester</span><input id="report-period" name="periode" value="<?= e($data['periode']) ?>" maxlength="80" placeholder="Contoh: 2026/2027 - Ganjil" required></label>
<label for="report-date"><span class="report-label"><?= panel_icon('calendar') ?> Tanggal penilaian</span><input id="report-date" name="tanggal" type="date" value="<?= e($data['tanggal']) ?>" required></label>
<label for="report-teacher"><span class="report-label"><?= panel_icon('akun') ?> Pembimbing / penilai</span><input id="report-teacher" name="pembimbing" value="<?= e($data['pembimbing']) ?>" maxlength="150" required></label>
<label for="report-state"><span class="report-label"><?= panel_icon('check-circle') ?> Status rapor</span><select id="report-state" name="status"><option value="draft" <?= $data['status']==='draft'?'selected':'' ?>>Draft</option><option value="final" <?= $data['status']==='final'?'selected':'' ?>>Final</option></select></label>
</div></section>
<section class="report-section"><div class="report-section-heading"><h2 class="report-section-title"><?= panel_icon('capaian') ?> Penilaian kompetensi</h2><span class="report-hint">Nilai opsional: 0-100</span></div>
<div class="report-item-list" data-report-items>
<?php foreach($items as $index=>$item): ?>
<fieldset class="report-item" data-report-item><legend>Kompetensi <span data-report-number><?= $index+1 ?></span></legend>
<label><span class="report-label"><?= panel_icon('program') ?> Kompetensi</span><input name="items[<?= $index ?>][kompetensi]" maxlength="190" value="<?= e($item['kompetensi']) ?>" required></label>
<label><span class="report-label"><?= panel_icon('tracer') ?> Nilai (opsional)</span><input name="items[<?= $index ?>][nilai]" inputmode="decimal" maxlength="6" value="<?= e($item['nilai']??'') ?>" placeholder="0-100"></label>
<label><span class="report-label"><?= panel_icon('certificate') ?> Predikat</span><select name="items[<?= $index ?>][predikat]"><option value="">Belum dinilai</option><?php foreach(report_predicates() as $key=>$label): ?><option value="<?= $key ?>" <?= ($item['predikat']??'')===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<label class="report-item-description"><span class="report-label"><?= panel_icon('edit') ?> Deskripsi capaian</span><textarea name="items[<?= $index ?>][deskripsi]" rows="2" maxlength="2000"><?= e($item['deskripsi']) ?></textarea></label>
<button type="button" class="button secondary" data-report-remove hidden><?= panel_icon('trash') ?> Hapus baris</button></fieldset>
<?php endforeach; ?>
</div>
<button type="button" class="button secondary" data-report-add hidden><?= panel_icon('add') ?> Tambah kompetensi</button></section>
<section class="report-section report-notes-section">
<label for="report-notes"><span class="report-label"><?= panel_icon('message') ?> Catatan pembimbing</span><textarea id="report-notes" name="catatan" rows="3" maxlength="5000"><?= e($data['catatan']) ?></textarea></label>
</section><div class="report-save-bar"><p class="muted"><?= panel_icon('lock') ?> Final memerlukan predikat pada setiap kompetensi.</p>
<button type="submit"><?= panel_icon('save') ?> Simpan rapor</button></div>
</form></div>
<script src="<?= e(asset_url('js/learning-reports.js')) ?>" defer></script>
<?php panel_end($user); ?>
