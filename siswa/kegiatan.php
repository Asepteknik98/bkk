<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
[$user,$student]=require_student();$id=positive_id($_GET['id']??'');
if(isset($_GET['id'])&&!$id){panel_not_found();}
$params=student_group($student);$where="e.angkatan=:angkatan AND e.kelas_industri=:industri AND e.kelas_industri<>''";
if($id){student_event($student,$id);$where.=' AND e.id=:id';$params['id']=$id;}
$total=(int)panel_query('SELECT COUNT(*) FROM attendance_events e WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/12));$page=min($pages,positive_id($_GET['page']??'1')??1);
$events=panel_query('SELECT e.id,e.judul,e.tanggal,e.angkatan,e.kelas_industri,COALESCE(r.pretest_enabled,0) AS pretest_enabled,COALESCE(r.poster_enabled,0) AS poster_enabled FROM attendance_events e LEFT JOIN event_resources r ON r.event_id=e.id WHERE '.$where.' ORDER BY e.tanggal DESC,e.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>12,'offset'=>($page-1)*12])->fetchAll();
student_start($user,$student,'Kegiatan kelas','kegiatan.php');
?>
<p><?= $total ?> kegiatan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="row g-4"><?php foreach($events as $event):?><div class="col-12 col-lg-6"><article class="surface h-100"><p class="eyebrow"><?= e($event['tanggal']) ?></p><h2><a href="<?= e(base_url('siswa/kegiatan.php?id='.$event['id'])) ?>"><?= e($event['judul']) ?></a></h2><p><?= e($event['kelas_industri']) ?> · Angkatan <?= e((string)$event['angkatan']) ?></p><?php if($event['poster_enabled']):?><a href="<?= e(base_url('siswa/berkas.php?jenis=poster&id='.$event['id'])) ?>" target="_blank" rel="noopener"><img src="<?= e(base_url('siswa/berkas.php?jenis=poster&id='.$event['id'])) ?>" alt="Poster <?= e($event['judul']) ?>" style="display:block;max-width:100%;max-height:500px;object-fit:contain" loading="lazy"></a><?php endif;?><?php if($event['pretest_enabled']):?><p><a class="button button-navy" href="<?= e(base_url('siswa/pretest.php?id='.$event['id'])) ?>" target="_blank" rel="noopener noreferrer">Buka pretest</a></p><?php endif;?><?php if(!$event['pretest_enabled']&&!$event['poster_enabled']):?><p>Pretest dan poster belum dibuka.</p><?php endif;?></article></div><?php endforeach;?></div>
<?php if(!$events):?><p class="surface">Belum ada kegiatan untuk kelompok Anda.</p><?php endif;?>
<?php if($id):?><p class="mt-4"><a href="<?= e(base_url('siswa/kegiatan.php')) ?>">Semua kegiatan kelas</a></p><?php else:student_pagination('kegiatan.php',$page,$pages);endif;?>
<?php student_end(); ?>
