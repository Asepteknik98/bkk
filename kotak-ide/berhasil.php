<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/public.php';
header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
if(!is_int($_SESSION['industry_idea_receipt']??null)||$_SESSION['industry_idea_receipt']<time()){http_response_code(404);exit('Halaman tidak tersedia.');}
$pageTitle='Usulan diterima';$activePage='';require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><section class="surface"><h1>Terima kasih, usulan telah diterima</h1><p>Pengelola BKK akan meninjau masukan Anda. Tautan telah digunakan dan tidak dapat dikirim ulang. Hubungi BKK untuk tautan baru jika ingin menyampaikan masukan lain.</p><a class="button button-navy" href="<?= e(base_url()) ?>">Kembali ke beranda</a></section></main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
