<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
[$user,$student]=require_student();$params=student_group($student);$where="angkatan=:angkatan AND kelas_industri=:industri AND kelas_industri<>''";
$total=(int)panel_query('SELECT COUNT(*) FROM learning_documents WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT id,judul,deskripsi,nama_asli,ukuran,updated_at FROM learning_documents WHERE '.$where.' ORDER BY updated_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll();
student_start($user,$student,'Capaian pembelajaran','capaian.php');
?>
<p><?= $total ?> dokumen · Halaman <?= $page ?> dari <?= $pages ?></p>
<?php foreach($rows as $row):?><article class="surface mb-4"><h2><?= e($row['judul']) ?></h2><p><?= nl2br(e($row['deskripsi'])) ?></p><p><?= e($row['nama_asli']) ?> · <?= number_format($row['ukuran']/1024,1) ?> KB · Diperbarui <?= e($row['updated_at']) ?></p><a class="button button-navy" href="<?= e(base_url('siswa/berkas.php?jenis=capaian&id='.$row['id'])) ?>">Download PDF</a></article><?php endforeach;?>
<?php if(!$rows):?><p class="surface">Belum ada dokumen untuk kelompok Anda.</p><?php endif;?>
<?php student_pagination('capaian.php',$page,$pages);student_end(); ?>
