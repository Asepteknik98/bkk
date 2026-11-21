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
<div class="alumni-applications-page"><div class="page-actions"><h1><?= panel_icon('lamaran') ?> Lamaran dan hasil alumni</h1><a href="<?= e(base_url('panel/hasil-alumni/')) ?>"><?= panel_icon('chart') ?> Rekap</a></div>
<?php if(!$owner):$q=input_text($_GET,'q');if(!valid_text($q,100,false)){http_response_code(400);exit('Pencarian tidak valid.');}$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$peopleTotal=(int)panel_query("SELECT COUNT(*) FROM tracer_responses WHERE nama_lengkap LIKE :nama ESCAPE '!'",['nama'=>$pattern])->fetchColumn();$peoplePages=max(1,(int)ceil($peopleTotal/10));$peoplePage=min($peoplePages,positive_id($_GET['page']??'1')??1);$people=panel_query("SELECT id,nama_lengkap,jurusan,tahun_lulus FROM tracer_responses WHERE nama_lengkap LIKE :nama ESCAPE '!' ORDER BY nama_lengkap,id LIMIT :limit OFFSET :offset",['nama'=>$pattern,'limit'=>10,'offset'=>($peoplePage-1)*10])->fetchAll(); ?>
<form class="filter-form card alumni-search" method="get"><label for="alumni-q"><span><?= panel_icon('search') ?> Cari nama alumni</span><input id="alumni-q" type="search" name="q" maxlength="100" placeholder="Ketik nama alumni" value="<?= e($q) ?>"></label><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/hasil-alumni/lamaran.php')) ?>"><?= panel_icon('reset') ?> Reset</a></form>
<p class="alumni-list-note">Pilih alumni untuk mengelola lamaran. Catatan dengan NISN sama tetap terpisah pada formulir pribadi.</p>
<div class="alumni-list-heading"><h2><?= panel_icon('peserta') ?> Daftar alumni</h2><span><?= $peopleTotal ?> alumni &middot; Halaman <?= $peoplePage ?> dari <?= $peoplePages ?></span></div>
<div class="table-scroll alumni-picker-sheet" data-vertical-list tabindex="0" role="region" aria-label="Daftar alumni, gulir untuk melihat data"><table><thead><tr><th scope="col">Nama alumni</th><th scope="col">Jurusan</th><th scope="col">Tahun lulus</th><th scope="col">Tindakan</th></tr></thead><tbody>
<?php foreach($people as $person): ?><tr><td><strong><?= e($person['nama_lengkap']) ?></strong></td><td><?= e($person['jurusan']) ?></td><td><?= (int)$person['tahun_lulus'] ?></td><td><a class="alumni-manage" href="<?= e(base_url('panel/hasil-alumni/lamaran.php?tracer_id='.$person['id'])) ?>"><?= panel_icon('view') ?> Kelola lamaran</a></td></tr><?php endforeach; ?>
<?php if(!$people): ?><tr><td colspan="4" class="empty-panel">Belum ada alumni yang sesuai. Coba nama lain atau periksa data tracer study.</td></tr><?php endif; ?></tbody></table></div>
<?php $pageStart=max(1,min($peoplePage-4,$peoplePages-9));$pageEnd=min($peoplePages,$pageStart+9); ?>
<nav class="pagination news-page-slider" aria-label="Halaman daftar alumni">
<span class="news-page-summary"><?= $peopleTotal?($peoplePage-1)*10+1:0 ?>&ndash;<?= min($peopleTotal,$peoplePage*10) ?> dari <?= $peopleTotal ?> alumni</span>
<?php if($peoplePage>1): ?><a class="news-page-arrow" rel="prev" href="<?= e(base_url('panel/hasil-alumni/lamaran.php?'.http_build_query(['q'=>$q,'page'=>$peoplePage-1]))) ?>"><?= panel_icon('back') ?><span>Sebelumnya</span></a><?php else: ?><span class="news-page-arrow is-disabled" aria-disabled="true"><?= panel_icon('back') ?><span>Sebelumnya</span></span><?php endif; ?>
<div class="news-page-numbers">
<?php for($number=$pageStart;$number<=$pageEnd;$number++): ?><a class="news-page-number" href="<?= e(base_url('panel/hasil-alumni/lamaran.php?'.http_build_query(['q'=>$q,'page'=>$number]))) ?>" aria-label="Halaman <?= $number ?>" <?= $number===$peoplePage?'aria-current="page"':'' ?>><?= $number ?></a><?php endfor; ?>
</div>
<?php if($peoplePage<$peoplePages): ?><a class="news-page-arrow" rel="next" href="<?= e(base_url('panel/hasil-alumni/lamaran.php?'.http_build_query(['q'=>$q,'page'=>$peoplePage+1]))) ?>"><span>Berikutnya</span><?= panel_icon('next') ?></a><?php else: ?><span class="news-page-arrow is-disabled" aria-disabled="true"><span>Berikutnya</span><?= panel_icon('next') ?></span><?php endif; ?>
</nav>

<?php else:$applications=panel_query('SELECT r.*,l.perusahaan,l.posisi FROM alumni_application_results r JOIN lamaran l ON l.id=r.lamaran_id WHERE r.tracer_id=:id ORDER BY r.updated_at DESC',['id'=>$tracerId])->fetchAll();$jobs=panel_query("SELECT id,perusahaan,posisi FROM lowongan WHERE status='published' ORDER BY created_at DESC,id DESC")->fetchAll(); ?>
<h2><?= e($owner['nama_lengkap']) ?></h2><p><a href="<?= e(base_url('panel/tracer-study/tautan.php?id='.$tracerId)) ?>">Buat / cabut tautan alumni Tahap 1</a></p><p>Pengelola mencatat lamaran awal atau menandai proses seleksi selesai. Alumni mengisi hasil akhirnya sendiri; tanggal batas pendaftaran tidak dianggap tanggal selesai seleksi.</p>
<?php foreach($errors as $error): ?><p class="notice" role="alert"><?= e($error) ?></p><?php endforeach; ?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="aksi" value="lamaran"><label>Lowongan yang dilamar<select name="lowongan_id" required><option value="">Pilih lowongan</option><?php foreach($jobs as $job): ?><option value="<?= $job['id'] ?>"><?= e($job['posisi'].' — '.$job['perusahaan']) ?></option><?php endforeach; ?></select></label><button>Catat lamaran — Belum Mengisi</button></form>
<div class="table-scroll"><table><thead><tr><th>Lamaran</th><th>Hasil alumni</th><th>Proses</th><th>Tindakan</th></tr></thead><tbody><?php foreach($applications as $application): ?><tr><td><?= e($application['posisi'].' — '.$application['perusahaan']) ?></td><td><?= e(career_result_label($application)) ?></td><td><?= $application['seleksi_selesai']?'Seleksi selesai':'Belum ditandai selesai' ?></td><td><?php if(!$application['seleksi_selesai']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="aksi" value="selesai"><input type="hidden" name="lamaran_id" value="<?= $application['lamaran_id'] ?>"><input type="hidden" name="version" value="<?= $application['version'] ?>"><button>Tandai seleksi selesai</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$applications): ?><tr><td colspan="4">Belum ada lamaran terikat alumni ini.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?>
</div>
<?php panel_end($user); ?>
