<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
[$user,$student]=require_student();$id=positive_id($_GET['id']??'');if(isset($_GET['id'])&&!$id){panel_not_found();}
$params=student_speaker_params($student);$where='('.student_speaker_scope().')';if($id){student_speaker($student,$id);$where.=' AND s.id=:id';$params['id']=$id;}
$total=(int)panel_query('SELECT COUNT(*) FROM speakers s WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/12));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT s.id,s.nama,s.instansi,s.keahlian,s.bio,(s.cv_file IS NOT NULL) AS has_cv,(s.foto_file IS NOT NULL) AS has_foto FROM speakers s WHERE '.$where.' ORDER BY s.nama,s.id LIMIT :limit OFFSET :offset',$params+['limit'=>12,'offset'=>($page-1)*12])->fetchAll();
student_start($user,$student,'Pemateri kelas','pemateri.php');
?>
<p><?= $total ?> pemateri · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="row g-4"><?php foreach($rows as $row):?><div class="col-12 col-lg-6"><article class="surface h-100"><?php if($row['has_foto']):?><img src="<?= e(base_url('siswa/berkas.php?jenis=foto&id='.$row['id'])) ?>" alt="Foto <?= e($row['nama']) ?>" width="128" height="128" style="object-fit:cover" loading="lazy"><?php endif;?><h2><a href="<?= e(base_url('siswa/pemateri.php?id='.$row['id'])) ?>"><?= e($row['nama']) ?></a></h2><p><?= e($row['instansi']) ?></p><p><?= e($row['keahlian']) ?></p><p><?= nl2br(e($row['bio'])) ?></p><?php if($row['has_cv']):?><a class="button button-navy" href="<?= e(base_url('siswa/berkas.php?jenis=cv&id='.$row['id'])) ?>">Download CV</a><?php endif;?></article></div><?php endforeach;?></div>
<?php if(!$rows):?><p class="surface">Belum ada pemateri untuk kelas Anda.</p><?php endif;?>
<?php if($id):?><p class="mt-4"><a href="<?= e(base_url('siswa/pemateri.php')) ?>">Semua pemateri kelas</a></p><?php else:student_pagination('pemateri.php',$page,$pages);endif;?>
<?php student_end(); ?>
