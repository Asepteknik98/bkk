<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/search.php';
[$filters,$results,$total,$page,$pages,$error]=portal_search($_GET);
$searchTerm=$filters['q'];$activePage='pencarian.php';
$pageTitle=$searchTerm!==''?'Pencarian: '.$searchTerm:'Pencarian Informasi';
$pageDescription='Cari berita, lowongan kerja, dan program BKK yang telah diterbitkan'.($searchTerm!==''?' untuk '.$searchTerm:'').'.';
require __DIR__.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main jobs-page search-page"><div class="page-heading"><h1>Pencarian Informasi</h1><p>Temukan berita, lowongan kerja, dan program BKK yang telah diterbitkan.</p></div>
<form class="job-filters surface" method="get" action="<?= e(base_url('pencarian.php')) ?>" role="search">
<label for="search-q">Kata kunci<input id="search-q" name="q" type="search" maxlength="100" value="<?= e($searchTerm) ?>"></label>
<label for="search-type">Jenis informasi<select id="search-type" name="type"><?php foreach([''=>'Semua informasi','berita'=>'Berita','lowongan'=>'Lowongan kerja','program'=>'Program BKK'] as $value=>$label): ?><option value="<?= $value ?>" <?= $filters['type']===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<label for="search-category">Kategori program<select id="search-category" name="kategori"><option value="">Semua kategori</option><?php foreach(program_categories() as $value=>$info): ?><option value="<?= $value ?>" <?= $filters['kategori']===$value?'selected':'' ?>><?= e($info['title']) ?></option><?php endforeach; ?></select></label>
<button class="button button-navy" type="submit">Cari informasi</button><a href="<?= e(base_url('pencarian.php')) ?>">Reset</a></form><p class="search-hint muted">Memilih kategori membatasi hasil ke program dalam kategori tersebut.</p>
<?php if($error): ?><p class="service-notice" role="alert"><?= e($error) ?></p><?php else: ?>
<p class="jobs-result" role="status"><?= $total ?> hasil<?= $searchTerm!==''?' untuk “'.e($searchTerm).'”':'' ?> · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="news-grid"><?php foreach($results as $result):
$url=match($result['jenis']){'berita'=>news_url($result),'lowongan'=>job_url($result),'program'=>program_url($result)};
$label=match($result['jenis']){'berita'=>'Berita','lowongan'=>'Lowongan kerja','program'=>program_categories()[$result['kategori']]['title']};
?><article class="news-card search-result-card"><img src="<?= e(content_image($result['gambar'])) ?>" alt="" width="640" height="400" loading="lazy"><div class="news-card-body"><p class="eyebrow"><?= e($label) ?></p><?php demo_label($result); ?><h2><a href="<?= e($url) ?>"><?= e($result['judul']) ?></a></h2><p class="clamp-text"><?= e(meta_description($result['ringkasan'],250)) ?></p><?php if($result['jenis']==='lowongan'): ?><p class="deadline"><strong><?= e(job_status_label($result)) ?></strong><br>Batas lamaran: <?= e(indo_date($result['batas_lamaran'])) ?><?= $result['batas_lamaran']?' - 23:59:59 WIB':'' ?></p><?php endif; ?><a class="text-link" href="<?= e($url) ?>">Lihat detail<span class="visually-hidden">: <?= e($result['judul']) ?></span></a></div></article><?php endforeach; ?></div>
<?php if(!$results): empty_state('Informasi tidak ditemukan','Coba kata kunci, jenis informasi, atau kategori lain.'); endif; ?>
<?php if($pages>1): ?><nav class="pagination-nav" aria-label="Halaman hasil pencarian"><?php foreach(['← Sebelumnya'=>$page-1,'Berikutnya →'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a class="button button-outline" href="<?= e(base_url('pencarian.php?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach; ?></nav><?php endif; endif; ?>
</main><?php require __DIR__.'/app/layouts/footer.php'; ?>
