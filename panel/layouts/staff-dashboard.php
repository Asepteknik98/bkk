<?php
if (!isset($user) || $user['role']!=='staf_bkk') { http_response_code(403); exit; }
panel_start($user,'Dashboard','dashboard');
?>
<h1>Dashboard Staf BKK</h1><p>Selamat datang, <?= e($user['nama']) ?>. Modul berikut tersedia sesuai izin Admin.</p>
<div class="summary-grid">
<?php $available=0; foreach(permission_modules() as $module=>$label):
    if(!user_can($user,$module) || $module==='mou'){continue;}
    if($module==='absensi'&&!user_can($user,'peserta')){continue;}
    if($module==='pkl'&&!user_can($user,'peserta')){continue;}
    if($module==='kelas-industri'&&!user_can($user,'peserta')){continue;}
    if($module==='biaya-perjalanan'&&(!user_can($user,'pkl')||!user_can($user,'peserta'))){continue;}
    if($module==='whatsapp'&&(!user_can($user,'lamaran')||!user_can($user,'tracer-study'))){continue;}
    $available++;
?><section class="card"><h2><a href="<?= e(base_url('panel/'.$module.'/')) ?>"><?= e($label) ?></a></h2></section><?php endforeach; ?>
</div><?php if(!$available): ?><p>Belum ada izin operasional. Hubungi Admin BKK.</p><?php endif; ?>
<?php panel_end($user); ?>
