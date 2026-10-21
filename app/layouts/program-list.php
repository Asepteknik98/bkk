<?php
if (!defined('APP_ROOT')) { http_response_code(403); exit; }
if(!isset($programCategory)||!is_string($programCategory)||!array_key_exists($programCategory,program_categories())){http_response_code(404);exit('Kategori tidak ditemukan.');}
$categoryInfo = program_categories()[$programCategory];
$pageTitle = $categoryInfo['title']; $activePage = $categoryInfo['path'];
$total=(int)(public_query("SELECT COUNT(*) AS total FROM program WHERE kategori=:kategori AND status='published'",['kategori'=>$programCategory])[0]['total']??0);
$pages=max(1,(int)ceil($total/9));$rawPage=$_GET['page']??'1';$page=is_string($rawPage)&&ctype_digit($rawPage)?max(1,min($pages,(int)$rawPage)):1;
$programs = public_query("SELECT kategori,judul,slug,ringkasan,gambar FROM program WHERE kategori=:kategori AND status='published' ORDER BY created_at DESC,id DESC LIMIT :limit OFFSET :offset", ['kategori'=>$programCategory,'limit'=>9,'offset'=>($page-1)*9]);
require __DIR__ . '/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Program BKK / <?= e($categoryInfo['short']) ?></p><h1><?= e($pageTitle) ?></h1><p><?= e($categoryInfo['description']) ?></p></div><div class="content-with-sidebar"><div class="content-column"><p><?= $total ?> program · Halaman <?= $page ?> dari <?= $pages ?></p><div class="news-grid"><?php if($programs):foreach($programs as $program):?><article class="news-card"><a class="image-link" tabindex="-1" aria-hidden="true" href="<?= e(program_url($program)) ?>"><img src="<?= e(content_image($program['gambar'])) ?>" alt="" width="640" height="400" loading="lazy"></a><div class="news-card-body"><?php demo_label($program);?><h2><a href="<?= e(program_url($program)) ?>"><?= e($program['judul']) ?></a></h2><p class="clamp-text"><?= e($program['ringkasan']) ?></p><a class="text-link" href="<?= e(program_url($program)) ?>">Lihat detail program ↗</a></div></article><?php endforeach;else:empty_state('Belum ada program diterbitkan');endif;?></div>
<?php if($pages>1):?><nav class="pagination-nav" aria-label="Halaman program"><?php foreach(['← Sebelumnya'=>$page-1,'Berikutnya →'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a class="button button-outline" href="<?= e(base_url($categoryInfo['path'].'?page='.$target)) ?>"><?= e($label) ?></a><?php endforeach;?></nav><?php endif;?></div><?php require __DIR__.'/sidebar.php';?></div></main>
<?php require __DIR__ . '/footer.php'; ?>
