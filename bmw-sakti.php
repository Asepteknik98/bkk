<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
$title=setting('bmw_title','Program BMW Sakti');$description=setting('bmw_description');$published=setting('bmw_status','draft')==='published'&&trim($description)!=='';
$pageTitle=$published?$title:'Program BMW Sakti';$activePage='bmw-sakti.php';
if(!empty($GLOBALS['public_database_unavailable'])){http_response_code(503);}
require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><article class="surface"><p class="eyebrow">Program sekolah</p><h1><?= e($pageTitle) ?></h1>
<?php if($published): ?><?php foreach(preg_split('/\n\s*\n/',trim($description)) as $paragraph): ?><p><?= nl2br(e($paragraph)) ?></p><?php endforeach; ?>
<?php elseif(!empty($GLOBALS['public_database_unavailable'])): ?><p>Deskripsi sementara belum dapat dimuat. Silakan coba kembali.</p><?php else: ?><p>Deskripsi Program BMW Sakti belum dipublikasikan oleh pengelola sekolah.</p><?php endif; ?>
<h2>Jelajahi program</h2><ul><?php foreach(program_categories() as $category): ?><li><a href="<?= e(base_url($category['path'])) ?>"><?= e($category['title']) ?></a></li><?php endforeach; ?></ul></article></main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
