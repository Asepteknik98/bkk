<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/industry-ideas.php';
industry_ideas_require_ready();header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
if(array_key_exists('token',$_GET)){
    $hash=industry_idea_hash(is_string($_GET['token'])?$_GET['token']:'');unset($_SESSION['industry_idea_hash'],$_SESSION['industry_idea_form'],$_SESSION['industry_idea_receipt'],$_SESSION['industry_idea_bindings']);
    if(!$hash||!industry_idea_link_find($hash)){http_response_code(410);exit('Tautan mitra tidak berlaku. Hubungi BKK untuk tautan baru.');}
    $_SESSION['industry_idea_hash']=$hash;redirect('kotak-ide/',303);
}
$link=industry_idea_require_link();$formKey=industry_idea_form_key($link['token_hash']);$data=industry_idea_input([]);$errors=[];
if(isset($_SESSION['industry_idea_form'])){$data=$_SESSION['industry_idea_form']['data'];$errors=$_SESSION['industry_idea_form']['errors'];unset($_SESSION['industry_idea_form']);}
require_once APP_ROOT.'/app/helpers/public.php';$pageTitle='Kotak Ide Industri';$activePage='';require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><section class="surface"><p class="eyebrow">Saluran khusus mitra</p><h1>Kotak Ide Industri</h1><p>Sampaikan masukan kepada SMK Jaya Buana. Usulan dan kontak Anda hanya tersedia bagi pengelola yang memiliki izin Kotak Ide Industri. Formulir ini tidak memberikan akses ke dashboard atau usulan yang telah tersimpan.</p>
<?php if($errors): ?><div class="tracer-error" role="alert"><ul><?php foreach($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="tracer-form" method="post" action="<?= e(base_url('kotak-ide/simpan.php')) ?>"><?= csrf_field() ?><input type="hidden" name="form_key" value="<?= e($formKey) ?>">
<label for="perusahaan">Perusahaan<input id="perusahaan" name="perusahaan" readonly value="<?= e($link['perusahaan']) ?>"></label>
<?php foreach(['nama_pengirim'=>['Nama pengirim',150,'text',true],'jabatan'=>['Jabatan (opsional)',150,'text',false],'email'=>['Email',191,'email',false],'telepon'=>['Telepon / WhatsApp',30,'tel',false],'judul'=>['Judul usulan',190,'text',true]] as $key=>[$label,$max,$type,$required]): ?><label for="<?= $key ?>"><?= e($label) ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $type ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= $required?'required':'' ?>></label><?php endforeach; ?>
<p>Isi minimal email atau nomor telepon agar pengelola dapat menindaklanjuti.</p><label for="isi">Isi usulan<textarea id="isi" name="isi" rows="7" maxlength="10000" required><?= e($data['isi']) ?></textarea></label>
<div style="display:none" aria-hidden="true"><label for="company_website">Biarkan kosong<input id="company_website" name="company_website" tabindex="-1" autocomplete="off"></label></div>
<label class="tracer-check"><input type="checkbox" name="persetujuan" value="1" required> Saya setuju identitas, kontak, dan usulan ini diproses pengelola BKK untuk evaluasi dan tindak lanjut masukan. <a href="<?= e(base_url('privasi.php')) ?>">Informasi privasi</a></label><button class="button button-navy">Kirim usulan</button></form></section></main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
