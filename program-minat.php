<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
$categories=program_categories();$receipt=input_text($_GET,'bukti');
header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');
if($receipt!==''){
    $entry=preg_match('/^[a-f0-9]{64}$/D',$receipt)?(public_query('SELECT kategori,program_judul,status,balasan,updated_at FROM program_inquiries WHERE tracking_hash=:hash',['hash'=>hash('sha256',$receipt)])[0]??null):null;
    if(!$entry){http_response_code(404);require APP_ROOT.'/404.php';exit;}
    $pageTitle='Status minat program';$activePage=$categories[$entry['kategori']]['path'];
    require APP_ROOT.'/app/layouts/header.php';
    ?>
    <main id="konten" class="container portal-container page-main program-interest-page"><section class="surface"><p class="eyebrow"><?= e($categories[$entry['kategori']]['title']) ?></p><h1>Pesan Anda sudah diterima</h1><p><?= e($entry['program_judul']??'Pertanyaan / minat program') ?></p><p>Status: <strong><?= e(['baru'=>'Menunggu tanggapan','diproses'=>'Sedang diproses','selesai'=>'Selesai'][$entry['status']]) ?></strong></p><section class="interest-response"><h2>Tanggapan pengelola</h2><p><?= nl2br(e($entry['balasan']?:'Belum ada tanggapan. Silakan periksa kembali halaman ini nanti.')) ?></p></section><p class="muted">Simpan tautan halaman ini untuk melihat balasan. Jangan bagikan tautan kepada orang lain.</p><a class="text-link" href="<?= e(base_url($categories[$entry['kategori']]['path'])) ?>">Kembali ke program</a></section></main>
    <?php require APP_ROOT.'/app/layouts/footer.php';exit;
}
$interestCategory=input_text($_GET,'kategori');if(!isset($categories[$interestCategory])){http_response_code(404);require APP_ROOT.'/404.php';exit;}
$rawProgram=input_text($_GET,'program');$program=null;
if($rawProgram!==''){
    $program=ctype_digit($rawProgram)?(public_query("SELECT id,judul FROM program WHERE id=:id AND kategori=:category AND status='published'",['id'=>(int)$rawProgram,'category'=>$interestCategory])[0]??null):null;
    if(!$program){http_response_code(404);require APP_ROOT.'/404.php';exit;}
}
$formKey='interest_'.$interestCategory.'_'.($program['id']??0);
if(($_SERVER['REQUEST_METHOD']??'GET')==='GET'&&!empty($_SESSION[$formKey.'_receipt'])){unset($_SESSION[$formKey],$_SESSION[$formKey.'_receipt']);}
if(empty($_SESSION[$formKey])){$_SESSION[$formKey]=bin2hex(random_bytes(32));}
$data=[];foreach(['nama','email','telepon','pesan'] as $field){$data[$field]=input_text($_POST,$field);}$errors=[];
if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
    require_post_csrf();
    foreach(['nama'=>150,'email'=>191,'telepon'=>30,'pesan'=>5000] as $field=>$max){if(!valid_text($data[$field],$max)){$errors[]='Lengkapi '.$field.' dengan teks valid, maksimal '.$max.' karakter.';}}
    if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL)){$errors[]='Email tidak valid.';}
    if(!preg_match('/^\+?[0-9][0-9 ()-]{7,28}$/D',$data['telepon'])){$errors[]='Nomor telepon tidak valid.';}
    if(($_POST['consent']??'')!=='1'){$errors[]='Persetujuan penggunaan data wajib dicentang.';}
    if(!hash_equals($_SESSION[$formKey],input_text($_POST,'submission_key'))){$errors[]='Formulir kedaluwarsa. Muat ulang halaman.';}
    if(!$errors){
        try{
            $key=hash('sha256',$_SESSION[$formKey]);
            $existing=public_query('SELECT id FROM program_inquiries WHERE submission_key=:key',['key'=>$key]);
            if($existing&&!empty($_SESSION[$formKey.'_receipt'])){redirect('program-minat.php?bukti='.$_SESSION[$formKey.'_receipt'],303);}
            if((int)($_SESSION['interest_last_at']??0)>time()-60){throw new InvalidArgumentException('Tunggu satu menit sebelum mengirim pesan berikutnya.');}
            $receipt=bin2hex(random_bytes(32));$pdo=db();$pdo->beginTransaction();
            if($program){$check=$pdo->prepare("SELECT id FROM program WHERE id=:id AND kategori=:category AND status='published' FOR UPDATE");$check->execute(['id'=>$program['id'],'category'=>$interestCategory]);if(!$check->fetch()){throw new InvalidArgumentException('Program sudah tidak tersedia.');}}
            $statement=$pdo->prepare('INSERT INTO program_inquiries(kategori,program_id,program_judul,nama,email,telepon,pesan,created_at,submission_key,tracking_hash) VALUES(:kategori,:program_id,:program_judul,:nama,:email,:telepon,:pesan,:created_at,:submission_key,:tracking_hash)');
            $statement->execute($data+['kategori'=>$interestCategory,'program_id'=>$program['id']??null,'program_judul'=>$program['judul']??null,'created_at'=>date('Y-m-d H:i:s'),'submission_key'=>$key,'tracking_hash'=>hash('sha256',$receipt)]);
            $pdo->commit();$_SESSION['interest_last_at']=time();$_SESSION[$formKey.'_receipt']=$receipt;
            redirect('program-minat.php?bukti='.$receipt,303);
        }catch(Throwable $exception){if(isset($pdo)&&$pdo->inTransaction()){$pdo->rollBack();}$errors[]=$exception instanceof InvalidArgumentException?$exception->getMessage():'Pesan belum dapat dikirim. Silakan coba kembali.';if(!$exception instanceof InvalidArgumentException){error_log('Minat program gagal: '.get_class($exception).'; kode='.$exception->getCode());}}
    }
    if($errors){http_response_code(422);}
}
$pageTitle='Tanya / minat program';$activePage=$categories[$interestCategory]['path'];
require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main program-interest-page"><a class="text-link" href="<?= e(base_url($categories[$interestCategory]['path'])) ?>">Kembali ke program</a><section class="surface"><p class="eyebrow"><?= e($categories[$interestCategory]['title']) ?></p><h1>Tanya / minat program</h1><?php if($program):?><p><strong><?= e($program['judul']) ?></strong></p><?php endif;?><p>Sampaikan minat atau pertanyaan Anda. Pengelola BKK akan menanggapi melalui halaman status pesan.</p>
<?php if($errors):?><div class="alert" role="alert"><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?>
<form method="post" class="program-interest-form"><?= csrf_field() ?><input type="hidden" name="submission_key" value="<?= e($_SESSION[$formKey]) ?>">
<?php foreach(['nama'=>['Nama lengkap','text',150],'email'=>['Email','email',191],'telepon'=>['WhatsApp / telepon','tel',30]] as $field=>[$label,$type,$max]):?><label><?= $label ?><input name="<?= $field ?>" type="<?= $type ?>" maxlength="<?= $max ?>" value="<?= e($data[$field]) ?>" required></label><?php endforeach;?><label class="full-width">Minat / pertanyaan<textarea name="pesan" rows="4" maxlength="5000" required><?= e($data['pesan']) ?></textarea></label><label class="interest-consent full-width"><input type="checkbox" name="consent" value="1" <?= ($_POST['consent']??'')==='1'?'checked':'' ?> required> Saya menyetujui data ini digunakan BKK untuk menanggapi minat atau pertanyaan saya.</label><button class="button button-navy" type="submit">Kirim ke pengelola</button></form></section></main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
