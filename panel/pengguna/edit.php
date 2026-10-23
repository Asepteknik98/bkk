<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin'); $id = positive_id($_GET['id'] ?? null);
$data = $id ? panel_query('SELECT id,nama,username,email,foto_profil,role,aktif FROM users WHERE id=:id', ['id'=>$id])->fetch() : false;
if (!$data) { panel_not_found(); }
if($data['role']==='siswa'){
    $studentId=panel_query('SELECT siswa_id FROM student_accounts WHERE user_id=:id',['id'=>$id])->fetchColumn();
    if(!$studentId){http_response_code(403);exit('Akun siswa belum terhubung. Hubungi pengelola database.');}
    redirect('panel/siswa/akun.php?id='.$studentId);
}
require __DIR__ . '/form.php';
