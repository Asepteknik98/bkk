<?php
if(!defined('APP_ROOT')){http_response_code(403);exit;}
if(!isset($programCategory)||!is_string($programCategory)||!array_key_exists($programCategory,program_categories())){http_response_code(404);exit('Kategori tidak ditemukan.');}
$categoryInfo=program_categories()[$programCategory];
$slug=is_string($_GET['slug']??null)?$_GET['slug']:'';
$program=strlen($slug)<=191?(public_query("SELECT p.*,u.nama AS penulis FROM program p LEFT JOIN users u ON u.id=p.penulis_id WHERE p.slug=:slug AND p.kategori=:kategori AND p.status='published' LIMIT 1",['slug'=>$slug,'kategori'=>$programCategory])[0]??null):null;
setting('site_name');if(!$program){http_response_code(!empty($GLOBALS['public_database_unavailable'])?503:404);}
$pageTitle=$program['judul']??'Program tidak tersedia';$activePage=$categoryInfo['path'];$pageDescription=$program['ringkasan']??$categoryInfo['description'];
if (!$program && empty($GLOBALS['public_database_unavailable'])) { require APP_ROOT.'/404.php'; exit; }
require __DIR__.'/header.php';
?>
<main id="konten" class="container portal-container page-main detail-page"><nav class="breadcrumb-trail" aria-label="Jejak navigasi"><a href="<?= e(base_url()) ?>">Beranda</a><span>/</span><a href="<?= e(base_url($categoryInfo['path'])) ?>"><?= e($categoryInfo['title']) ?></a><span>/</span><span>Detail program</span></nav><div><div class="content-column">
<?php if($program):?><article class="article-detail surface"><p class="eyebrow"><?= e($categoryInfo['title']) ?></p><?php demo_label($program);?><h1><?= e($program['judul']) ?></h1><p class="meta">Diperbarui <?= e(indo_date($program['updated_at'])) ?> · <?= e($program['penulis']??'Pengelola BKK') ?></p><p class="article-summary"><?= e($program['ringkasan']) ?></p><img class="article-image" src="<?= e(content_image($program['gambar'])) ?>" alt="<?= e($program['judul']) ?>" width="960" height="600"><div class="article-body"><?= plain_text_paragraphs($program['isi']) ?></div>
<?php if(http_url($program['tautan_pendaftaran']??'')):?><section class="application-box"><h2>Pendaftaran / Informasi lanjutan</h2><p>Tautan dari pengelola menuju <strong><?= e(parse_url($program['tautan_pendaftaran'],PHP_URL_HOST)) ?></strong>.</p><a class="button button-navy" href="<?= e($program['tautan_pendaftaran']) ?>" target="_blank" rel="noopener noreferrer">Buka tautan pendaftaran ↗</a><p class="muted">Tautan dibuka di tab baru.</p></section><?php endif;?></article>
<?php else:empty_state(!empty($GLOBALS['public_database_unavailable'])?'Program belum dapat dimuat':'Program tidak ditemukan','Program mungkin belum diterbitkan atau tidak tersedia dalam kategori ini.');endif;?><p class="section-space"><a class="text-link" href="<?= e(base_url($categoryInfo['path'])) ?>">← Kembali ke <?= e($categoryInfo['title']) ?></a></p></div></div></main>
<?php require __DIR__.'/footer.php';?>
