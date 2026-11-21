<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/learning-reports.php';
$user=require_role('admin','staf_bkk');$ready=report_ready();$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    if(!$ready||input_text($_POST,'aksi')!=='hapus_rapor'){http_response_code(422);exit('Tindakan rapor tidak valid.');}
    $id=positive_id($_POST['id']??'');$version=positive_id($_POST['version']??'');
    try{if(!$id||!$version){throw new InvalidArgumentException('Rapor tidak valid.');}report_delete($id,$version);panel_message('Rapor dihapus.','panel/capaian-pembelajaran/');}catch(InvalidArgumentException $e){$errors[]=$e->getMessage();}
}
$q=input_text($_GET,'q');$status=input_text($_GET,'status');
if(!valid_text($q,100,false)||!in_array($status,['','draft','final'],true)){http_response_code(400);exit('Filter rapor tidak valid.');}
$where='1=1';$params=[];
if($q!==''){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$where.=" AND (nama LIKE :nama ESCAPE '!' OR periode LIKE :periode ESCAPE '!')";$params=['nama'=>$pattern,'periode'=>$pattern];}
if($status!==''){$where.=' AND status=:status';$params['status']=$status;}
$total=$ready?(int)panel_query('SELECT COUNT(*) FROM student_learning_reports WHERE '.$where,$params)->fetchColumn():0;
$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=$ready?panel_query('SELECT * FROM student_learning_reports WHERE '.$where.' ORDER BY updated_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll():[];
panel_start($user,'Rapor capaian pembelajaran','capaian');
?>
<div class="learning-report-page learning-report-list">
<div class="page-actions"><h1><?= panel_icon('certificate') ?> Rapor Capaian Pembelajaran</h1><div class="report-actions"><?php if($ready): ?><a class="button" href="<?= e(base_url('panel/capaian-pembelajaran/form.php')) ?>"><?= panel_icon('add') ?> Buat rapor</a><?php endif; ?><a href="<?= e(base_url('panel/capaian-pembelajaran/dokumen.php')) ?>"><?= panel_icon('lamaran') ?> Dokumen pendukung</a><a href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('peserta') ?> Data siswa</a></div></div>

<?php if(!$ready): ?><p class="notice">Rapor belum aktif. Impor <strong>database/updates/025_student_learning_reports.sql</strong>. Dokumen pembelajaran sebelumnya tetap tersedia melalui Dokumen pendukung.</p><?php endif; ?>
<?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<form class="filter-form card" method="get"><label for="report-search">Cari siswa / periode<input id="report-search" name="q" maxlength="100" value="<?= e($q) ?>"></label><label for="report-status-filter">Status<select id="report-status-filter" name="status"><option value="">Semua status</option><?php foreach(['draft'=>'Draft','final'=>'Final'] as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><button type="submit">Cari</button><a href="<?= e(base_url('panel/capaian-pembelajaran/')) ?>">Reset</a></form>
<p><?= $total ?> rapor - Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll panel-sheet" data-vertical-list><table><thead><tr><th><span><?= panel_icon('peserta') ?> Siswa</span></th><th><span><?= panel_icon('program') ?> Kelas / Jurusan</span></th><th><span><?= panel_icon('calendar') ?> Periode</span></th><th><span><?= panel_icon('akun') ?> Pembimbing</span></th><th><span><?= panel_icon('check-circle') ?> Status</span></th><th><span><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead><tbody>
<?php foreach($rows as $row): ?><tr><td><strong><?= e($row['nama']) ?></strong><small>Angkatan <?= (int)$row['angkatan'] ?></small></td><td><?= e($row['kelas']) ?><small><?= e($row['jurusan']) ?></small></td><td><?= e($row['periode']) ?><small><?= e($row['tanggal']) ?></small></td><td><?= e($row['pembimbing']) ?></td><td><span class="badge"><?= $row['status']==='final'?'Final':'Draft' ?></span></td><td><div class="row-actions"><a href="<?= e(base_url('panel/capaian-pembelajaran/detail.php?id='.$row['id'])) ?>" class="report-list-view"><?= panel_icon('view') ?> Lihat</a><a href="<?= e(base_url('panel/capaian-pembelajaran/form.php?id='.$row['id'])) ?>" class="report-list-edit"><?= panel_icon('edit') ?> Edit</a><form method="post" data-confirm="Hapus rapor ini?"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus_rapor"><input type="hidden" name="id" value="<?= $row['id'] ?>"><input type="hidden" name="version" value="<?= $row['version'] ?>"><button type="submit" class="danger small-button">Hapus</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6">Belum ada rapor yang sesuai. Pilih Buat rapor untuk memulai penilaian siswa.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if($pages>1): ?><nav class="pagination" aria-label="Halaman rapor"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target): if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/capaian-pembelajaran/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav><?php endif; ?>
</div>
<?php panel_end($user); ?>
