<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/whatsapp.php';
require_once APP_ROOT.'/app/helpers/careers.php';
$user=require_role('admin','staf_bkk');
$id=positive_id($_GET['id']??'');
$application=$id?panel_query('SELECT id,nama,telepon,posisi,perusahaan,status,lowongan_id FROM lamaran WHERE id=:id',['id'=>$id])->fetch():null;
if(!$application){panel_not_found();}
career_application_guard($user,(int)$application['id']);
$filters=wa_filters(['group'=>'pelamar','lowongan'=>(string)$application['lowongan_id']]);
$recipient=null;
foreach(wa_contacts($filters) as $contact){
    if($contact['source_id']===(int)$application['id']){$recipient=$contact;break;}
}
if(!empty($_SESSION['wa_create_key'])&&panel_query('SELECT id FROM wa_campaigns WHERE submission_key=:key',['key'=>$_SESSION['wa_create_key']])->fetchColumn()){unset($_SESSION['wa_create_key']);}
if(empty($_SESSION['wa_create_key'])){$_SESSION['wa_create_key']=bin2hex(random_bytes(32));}
$message=wa_application_notice($application);
panel_start($user,'Pemberitahuan WhatsApp pelamar','lamaran');
?>
<div class="content-management applicant-notification-page">
    <div class="page-actions"><h1><?= panel_icon('whatsapp') ?> Pesan WhatsApp</h1><a href="<?= e(base_url('panel/lamaran/')) ?>"><?= panel_icon('back') ?> Kembali</a></div>
    <section class="card">
        <div class="notification-recipient"><h2><?= panel_icon('akun') ?> <?= e($application['nama']) ?></h2><?php if($recipient): ?><span><?= panel_icon('phone') ?> <?= e($recipient['nomor']) ?></span><?php endif; ?></div>
        <p class="notification-context"><?= e($application['posisi'].' - '.$application['perusahaan']) ?></p>
        <?php if(!$recipient): ?>
        <p class="notice" role="status">Penerima belum dapat dipilih: nomor tidak valid, telah meminta berhenti, atau nomor yang sama diwakili lamaran yang lebih baru. Periksa data penerima melalui menu Pesan WhatsApp.</p>
        <a class="button" href="<?= e(base_url('panel/whatsapp/?'.http_build_query($filters))) ?>">Periksa penerima</a>
        <?php else: ?>
        <form method="post" action="<?= e(base_url('panel/whatsapp/buat.php')) ?>" class="application-notification-form">
            <?= csrf_field() ?>
            <input type="hidden" name="submission_key" value="<?= e($_SESSION['wa_create_key']) ?>">
            <input type="hidden" name="mode" value="manual">
            <input type="hidden" name="selected[]" value="<?= e($recipient['key']) ?>">
            <?php foreach($filters as $key=>$value): ?><input type="hidden" name="<?= e($key) ?>" value="<?= e((string)$value) ?>"><?php endforeach; ?>
            <input type="hidden" name="judul" value="Pemberitahuan status lamaran">
            <input type="hidden" name="tautan" value="">
            <label for="applicant-message">Pesan</label>
            <textarea id="applicant-message" name="pesan" rows="8" maxlength="1500" required aria-describedby="notification-help"><?= e(wa_message($message,$recipient['nama'])) ?></textarea>
            <div class="notification-footer">
                <p class="muted" id="notification-help">Periksa pesan sebelum melanjutkan. Kirim secara manual melalui WhatsApp.</p>
                <button type="submit"><?= panel_icon('whatsapp') ?> Siapkan pesan</button>
            </div>
        </form>
        <?php endif; ?>
    </section>
</div>
<?php panel_end($user); ?>
