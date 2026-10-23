<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin'); require_post_csrf();
$raw = $_POST['id'] ?? ''; $id = $raw === '' ? null : positive_id($raw);
if ($raw !== '' && !$id) { panel_not_found(); }
$upload=null;
try {
    account_change((int)$user['id'], true, static function (array $actor) use ($id, &$upload): void {
        $data = account_identity($_POST, $id); $role = input_text($_POST, 'role');
        if (!in_array($role, ['admin','staf_bkk'], true)) { throw new InvalidArgumentException('Role tidak valid.'); }
        if ($id) {
            $old = panel_query('SELECT * FROM users WHERE id=:id FOR UPDATE', ['id'=>$id])->fetch();
            if (!$old) { throw new InvalidArgumentException('Akun tidak ditemukan.'); }
            if($old['role']==='siswa'){throw new InvalidArgumentException('Kelola akun siswa melalui menu Data Siswa → Akun siswa.');}
            protect_last_admin($old, $role, (int)$old['aktif']);
            $photo=account_photo($old,(int)$actor['id'],$upload);
            panel_query('UPDATE users SET nama=:nama,username=:username,email=:email,role=:role,foto_profil=:foto WHERE id=:id', $data+['role'=>$role,'foto'=>$photo,'id'=>$id]);
        } else {
            $hash = account_password(is_string($_POST['password'] ?? null) ? $_POST['password'] : '', is_string($_POST['confirmation'] ?? null) ? $_POST['confirmation'] : '');
            $photo=account_photo([],(int)$actor['id'],$upload);
            panel_query('INSERT INTO users(nama,username,email,role,password_hash,aktif,foto_profil) VALUES(:nama,:username,:email,:role,:hash,1,:foto)', $data+['role'=>$role,'hash'=>$hash,'foto'=>$photo]);
        }
    });
    panel_message('Akun berhasil disimpan.', 'panel/pengguna/');
} catch (Throwable $exception) { discard_upload($upload); account_failure($exception, $id ? 'panel/pengguna/edit.php?id='.$id : 'panel/pengguna/tambah.php'); }
