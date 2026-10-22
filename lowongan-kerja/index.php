<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/helpers/public.php';
$pageTitle = 'Lowongan Kerja'; $activePage = 'lowongan-kerja/';
$q=input_text($_GET,'q');$location=input_text($_GET,'lokasi');$type=input_text($_GET,'jenis_pekerjaan');
foreach(['q'=>100,'location'=>255,'type'=>100] as $field=>$max){if(!valid_text($$field,$max,false)){$$field='';}}
$params=[];$where="status='published'";
if($q!==''){$where.=" AND (posisi LIKE :position ESCAPE '!' OR perusahaan LIKE :company ESCAPE '!')";$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$params=['position'=>$pattern,'company'=>$pattern];}
if($location!==''){$where.=' AND lokasi=:lokasi';$params['lokasi']=$location;}
if($type!==''){$where.=' AND jenis_pekerjaan=:jenis';$params['jenis']=$type;}
$total=(int)(public_query('SELECT COUNT(*) AS total FROM lowongan WHERE '.$where,$params)[0]['total']??0);
$pages=max(1,(int)ceil($total/9));$rawPage=$_GET['page']??'1';$page=is_string($rawPage)&&ctype_digit($rawPage)?max(1,min($pages,(int)$rawPage)):1;
$jobs=public_query('SELECT * FROM lowongan WHERE '.$where.' ORDER BY created_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>9,'offset'=>($page-1)*9]);
$locations=public_query("SELECT DISTINCT lokasi FROM lowongan WHERE status='published' ORDER BY lokasi");
$types=public_query("SELECT DISTINCT jenis_pekerjaan FROM lowongan WHERE status='published' ORDER BY jenis_pekerjaan");
$pageDescription='Informasi lowongan kerja BKK'.($q!==''?' untuk '.$q:'').($location!==''?' di '.$location:'').($type!==''?' — '.$type:'').'. Periksa persyaratan dan batas lamaran menurut WIB.';
require APP_ROOT . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main jobs-page"><div class="page-heading"><p class="eyebrow">Langkah menuju dunia kerja</p><h1>Lowongan Kerja</h1><p>Temukan informasi posisi dan perusahaan. Batas lamaran berlaku sampai akhir tanggal yang tercantum, menurut waktu Asia/Jakarta (WIB).</p></div>
<form class="job-filters surface" method="get" action="<?= e(base_url('lowongan-kerja/')) ?>"><label for="job-q">Posisi/perusahaan<input type="search" id="job-q" name="q" maxlength="100" value="<?= e($q) ?>" placeholder="Cari lowongan…"></label><label for="lokasi">Lokasi<select id="lokasi" name="lokasi"><option value="">Semua lokasi</option><?php foreach($locations as $option):?><option value="<?= e($option['lokasi']) ?>" <?= $location===$option['lokasi']?'selected':'' ?>><?= e($option['lokasi']) ?></option><?php endforeach;?></select></label><label for="jenis">Jenis pekerjaan<select id="jenis" name="jenis_pekerjaan"><option value="">Semua jenis</option><?php foreach($types as $option):?><option value="<?= e($option['jenis_pekerjaan']) ?>" <?= $type===$option['jenis_pekerjaan']?'selected':'' ?>><?= e($option['jenis_pekerjaan']) ?></option><?php endforeach;?></select></label><button class="button button-navy" type="submit">Cari lowongan</button><a href="<?= e(base_url('lowongan-kerja/')) ?>">Reset</a></form>
<div><div class="content-column"><p class="jobs-result"><?= $total ?> lowongan ditemukan · Halaman <?= $page ?> dari <?= $pages ?></p><div class="news-slider-controls" hidden><button type="button" class="news-slide-prev" aria-label="Lowongan sebelumnya" aria-controls="jobs-slider">&larr;</button><button type="button" class="news-slide-next" aria-label="Lowongan berikutnya" aria-controls="jobs-slider">&rarr;</button></div><div class="job-grid job-slider news-slider" id="jobs-slider" tabindex="0" role="region" aria-label="Geser daftar lowongan"><?php if($jobs):foreach($jobs as $job):job_card($job);endforeach;else:empty_state('Belum ada lowongan yang sesuai','Ubah kata kunci/filter atau periksa kembali setelah pengelola menerbitkan lowongan.');endif;?></div>
<?php if($pages>1):?><nav class="pagination-nav" aria-label="Halaman lowongan"><?php foreach(['← Sebelumnya'=>$page-1,'Berikutnya →'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a class="button button-outline" href="<?= e(base_url('lowongan-kerja/?'.http_build_query(['q'=>$q,'lokasi'=>$location,'jenis_pekerjaan'=>$type,'page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav><?php endif;?></div></div></main>
<?php require APP_ROOT . '/app/layouts/footer.php'; ?>
