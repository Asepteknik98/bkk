<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/careers.php';
$user=require_role('admin','staf_bkk');employment_require_ready();$tracerId=positive_id($_GET['tracer_id']??'');if(isset($_GET['tracer_id'])&&!$tracerId){panel_not_found();}
$owner=$tracerId?panel_query('SELECT * FROM tracer_responses WHERE id=:id',['id'=>$tracerId])->fetch():null;if($tracerId&&!$owner){panel_not_found();}$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();if(!$owner){http_response_code(400);exit('Pilih alumni.');}
    try{
        $action=input_text($_POST,'aksi');
        if($action==='lamaran'){
            $job=positive_id($_POST['lowongan_id']??'');if(!$job){throw new InvalidArgumentException('Pilih lowongan.');}career_assign($tracerId,$job);
        }elseif($action==='selesai'){
            $applicationId=positive_id($_POST['lamaran_id']??'');$version=(int)(filter_var($_POST['version']??null,FILTER_VALIDATE_INT)?:0);
            $update=panel_query('UPDATE alumni_application_results SET seleksi_selesai=1,version=version+1,updated_at=UTC_TIMESTAMP() WHERE lamaran_id=:id AND tracer_id=:tracer AND version=:version',['id'=>$applicationId??0,'tracer'=>$tracerId,'version'=>$version]);
            if($update->rowCount()!==1){throw new InvalidArgumentException('Lamaran tidak ditemukan atau sudah berubah.');}
        }else{throw new InvalidArgumentException('Tindakan tidak valid.');}
        panel_message('Pencatatan diperbarui. Hasil akhir tetap diisi alumni melalui tautan pribadi.','panel/hasil-alumni/lamaran.php?tracer_id='.$tracerId);
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
}
panel_start($user,'Kelola lamaran alumni','hasil-alumni');
?>
<div class="page-actions"><h1>Lamaran dan hasil alumni</h1><a href="<?= e(base_url('panel/hasil-alumni/')) ?>">Rekap</a></div>
<?php if(!$owner):$q=input_text($_GET,'q');if(!valid_text($q,100,false)){http_response_code(400);exit('Pencarian tidak valid.');}$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$people=panel_query("SELECT id,nama_lengkap,tahun_lulus FROM tracer_responses WHERE nama_lengkap LIKE :nama ESCAPE '!' ORDER BY nama_lengkap,id LIMIT 50",['nama'=>$pattern])->fetchAll(); ?>
<form class="filter-form card" method="get"><label>Cari nama alumni<input name="q" maxlength="100" value="<?= e($q) ?>"></label><button>Cari</button></form><p>Maksimal 50 hasil. Data dengan NISN sama tidak digabung atau dialihkan otomatis pada formulir pribadi.</p><ul><?php foreach($people as $person): ?><li><a href="<?= e(base_url('panel/hasil-alumni/lamaran.php?tracer_id='.$person['id'])) ?>"><?= e($person['nama_lengkap'].' — '.$person['tahun_lulus']) ?></a></li><?php endforeach; ?></ul>
<?php else:$applications=panel_query('SELECT r.*,l.perusahaan,l.posisi FROM alumni_application_results r JOIN lamaran l ON l.id=r.lamaran_id WHERE r.tracer_id=:id ORDER BY r.updated_at DESC',['id'=>$tracerId])->fetchAll();$jobs=panel_query("SELECT id,perusahaan,posisi FROM lowongan WHERE status='published' ORDER BY created_at DESC,id DESC")->fetchAll(); ?>
<h2><?= e($owner['nama_lengkap']) ?></h2><p><a href="<?= e(base_url('panel/tracer-study/tautan.php?id='.$tracerId)) ?>">Buat / cabut tautan alumni Tahap 1</a></p><p>Pengelola mencatat lamaran awal atau menandai proses seleksi selesai. Alumni mengisi hasil akhirnya sendiri; tanggal batas pendaftaran tidak dianggap tanggal selesai seleksi.</p>
<?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="aksi" value="lamaran"><label>Lowongan yang dilamar<select name="lowongan_id" required><option value="">Pilih lowongan</option><?php foreach($jobs as $job): ?><option value="<?= $job['id'] ?>"><?= e($job['posisi'].' — '.$job['perusahaan']) ?></option><?php endforeach; ?></select></label><button>Catat lamaran — Belum Mengisi</button></form>
<div class="table-scroll"><table><thead><tr><th>Lamaran</th><th>Hasil alumni</th><th>Proses</th><th>Tindakan</th></tr></thead><tbody><?php foreach($applications as $application): ?><tr><td><?= e($application['posisi'].' — '.$application['perusahaan']) ?></td><td><?= e(career_result_label($application)) ?></td><td><?= $application['seleksi_selesai']?'Seleksi selesai':'Belum ditandai selesai' ?></td><td><?php if(!$application['seleksi_selesai']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="aksi" value="selesai"><input type="hidden" name="lamaran_id" value="<?= $application['lamaran_id'] ?>"><input type="hidden" name="version" value="<?= $application['version'] ?>"><button>Tandai seleksi selesai</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$applications): ?><tr><td colspan="4">Belum ada lamaran terikat alumni ini.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?>
<?php panel_end($user); ?>
