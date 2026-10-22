<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/helpers/public.php';
require_once APP_ROOT . '/app/helpers/applications.php';
require_once APP_ROOT . '/app/helpers/application-files.php';
$slug = input_text($_GET, 'slug');
$job = valid_text($slug, 191) ? (public_query("SELECT * FROM lowongan WHERE slug=:slug AND status='published' LIMIT 1", ['slug'=>$slug])[0] ?? null) : null;
if (!$job) { http_response_code(empty($GLOBALS['public_database_unavailable']) ? 404 : 503); require APP_ROOT.'/404.php'; exit; }
$data = application_input([]); $errors = []; $success = false;
$keyName = 'application_key_' . $job['id'];
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0 && empty($_POST) && empty($_FILES)) {
        http_response_code(413);
        exit('Ukuran unggahan melebihi batas server. Kembali ke formulir dan pilih berkas maksimal 2 MB per berkas, total maksimal 6 MB.');
    }
    require_post_csrf();
    $data = application_input($_POST);
    $errors = application_errors($data);
    if (($_POST['consent'] ?? '') !== '1') { $errors[] = 'Persetujuan pengiriman data wajib dicentang.'; }
    if (!job_is_open($job)) { $errors[] = 'Lowongan ini sudah tidak menerima lamaran.'; }
    $key = input_text($_POST, 'submission_key');
    if (!isset($_SESSION[$keyName]) || !hash_equals($_SESSION[$keyName], $key)) { $errors[] = 'Formulir sudah kedaluwarsa. Muat ulang halaman dan coba kembali.'; }
    if (!$errors) {
        $moved = [];
        try {
            $pdo = db(); $pdo->beginTransaction();
            $check = $pdo->prepare('SELECT * FROM lowongan WHERE id=:id FOR UPDATE');
            $check->execute(['id'=>$job['id']]); $current = $check->fetch();
            if (!$current || !job_is_open($current)) { throw new InvalidArgumentException('Lowongan ini sudah tidak menerima lamaran.'); }
            $exists = $pdo->prepare('SELECT id FROM lamaran WHERE submission_key=:key');
            $exists->execute(['key'=>$key]);
            if (!$exists->fetchColumn()) {
                if ((int)($_SESSION['last_application_at'] ?? 0) > time()-60) { throw new InvalidArgumentException('Tunggu satu menit sebelum mengirim lamaran berikutnya.'); }
                $files = validate_application_files($_FILES);
                $insert = $pdo->prepare('INSERT INTO lamaran (lowongan_id,posisi,perusahaan,nama,email,telepon,pendidikan,alamat,pesan,submission_key,created_at) VALUES (:lowongan_id,:posisi,:perusahaan,:nama,:email,:telepon,:pendidikan,:alamat,:pesan,:submission_key,:created_at)');
                $insert->execute($data + ['lowongan_id'=>$current['id'],'posisi'=>$current['posisi'],'perusahaan'=>$current['perusahaan'],'submission_key'=>$key,'created_at'=>(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s')]);
                save_application_files($pdo, (int)$pdo->lastInsertId(), $files, $moved);
            }
            $pdo->commit();
            $_SESSION['last_application_at'] = time();
            $_SESSION['application_received_' . $job['id']] = true;
            redirect('lowongan-kerja/lamar.php?slug='.rawurlencode($slug).'&terkirim=1', 303);
        } catch (Throwable $exception) {
            if (db()->inTransaction()) { db()->rollBack(); }
            foreach ($moved as $path) { if (is_file($path)) { unlink($path); } }
            if ($exception instanceof InvalidArgumentException) { $errors[] = $exception->getMessage(); }
            else { error_log('Pengiriman lamaran gagal: '.get_class($exception).'; kode='.$exception->getCode()); $errors[] = 'Lamaran belum terkirim. Silakan coba kembali.'; }
        }
    }
    if ($errors) { http_response_code(422); }
}
$success = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && ($_GET['terkirim'] ?? '') === '1' && !empty($_SESSION['application_received_' . $job['id']]);
if (!isset($_SESSION[$keyName])) { $_SESSION[$keyName] = bin2hex(random_bytes(32)); }
$pageTitle = 'Lamar ' . $job['posisi']; $activePage = 'lowongan-kerja/';
require APP_ROOT . '/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main application-page">
<a class="text-link" href="<?= e(job_url($job)) ?>">Kembali ke detail lowongan</a>
<section class="surface">
<p class="eyebrow"><?= e($job['perusahaan']) ?></p><h1 data-public-icon="job">Lamar <?= e($job['posisi']) ?></h1>
<?php if ($success): ?>
<div role="status"><h2>Lamaran berhasil dikirim</h2><p>Lamaran Anda sudah masuk ke panel pengelola BKK untuk ditinjau. Pengelola dapat menghubungi Anda melalui email atau nomor telepon yang dicantumkan.</p><p>Pengiriman lamaran belum berarti diterima bekerja.</p></div>
<?php elseif (!job_is_open($job)): ?><p>Lowongan ini sudah tidak menerima lamaran.</p>
<?php else: ?>
<p>Isi data berikut. Lamaran dikirim langsung kepada pengelola BKK.</p>
<?php if ($errors): ?><div class="alert" role="alert"><strong>Lamaran belum terkirim.</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="<?= e(base_url('lowongan-kerja/lamar.php?slug='.rawurlencode($slug))) ?>" class="application-form" enctype="multipart/form-data">
<?= csrf_field() ?><input type="hidden" name="submission_key" value="<?= e($_SESSION[$keyName]) ?>">
<fieldset class="application-personal"><legend data-public-icon="user">Data pelamar</legend>
<?php foreach (['nama'=>['Nama lengkap','text',150,'name'],'email'=>['Email','email',191,'email'],'telepon'=>['Nomor WhatsApp / telepon','tel',30,'tel'],'pendidikan'=>['Pendidikan terakhir / jurusan','text',150,'off']] as $field=>[$label,$type,$max,$autocomplete]): ?>
<label for="<?= $field ?>" data-public-icon="<?= ['nama'=>'user','email'=>'mail','telepon'=>'phone','pendidikan'=>'school'][$field] ?>"><?= e($label) ?><input id="<?= $field ?>" name="<?= $field ?>" type="<?= $type ?>" maxlength="<?= $max ?>" autocomplete="<?= $autocomplete ?>" value="<?= e($data[$field]) ?>" required></label>
<?php endforeach; ?>
<label data-public-icon="location" for="alamat" class="full-width">Alamat domisili<textarea id="alamat" name="alamat" rows="2" maxlength="2000" autocomplete="street-address" required><?= e($data['alamat']) ?></textarea></label>
<label data-public-icon="message" for="pesan" class="full-width">Pesan lamaran <span class="muted">(opsional)</span><textarea id="pesan" name="pesan" rows="2" maxlength="5000" placeholder="Ceritakan pengalaman atau keterampilan yang sesuai."><?= e($data['pesan']) ?></textarea></label>
</fieldset>
<fieldset class="application-documents"><legend data-public-icon="upload">Berkas lamaran</legend><p id="document-help">Siapkan CV dan ijazah atau SKL. Format PDF, JPG, atau PNG; maksimal 2 MB per berkas dan 6 MB seluruhnya.</p>
<?php foreach (application_file_fields() as $field=>[$label,$required]): ?>
<label for="file-<?= $field ?>" data-public-icon="<?= ['cv'=>'clipboard','ijazah'=>'school','sertifikat'=>'certificate','pendukung'=>'file'][$field] ?>"><?= e($label) ?> <span class="muted"><?= $required ? '(wajib)' : '(opsional)' ?></span><input type="file" id="file-<?= $field ?>" name="<?= $field ?>" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" aria-describedby="document-help" <?= $required ? 'required' : '' ?>></label>
<?php endforeach; ?>
<?php if ($errors): ?><p class="full-width">Pilih kembali berkas sebelum mengirim ulang formulir.</p><?php endif; ?></fieldset>
<div class="application-submit full-width"><label class="application-consent" data-public-icon="shield"><input type="checkbox" name="consent" value="1" required <?= ($_POST['consent'] ?? '') === '1' ? 'checked' : '' ?>> Saya menyetujui data ini digunakan pengelola BKK untuk proses lamaran dan menghubungi saya.</label>
<button data-public-icon="send" class="button button-navy" type="submit">Kirim lamaran</button></div>
</form>
<?php endif; ?></section></main>
<?php require APP_ROOT . '/app/layouts/footer.php'; ?>
