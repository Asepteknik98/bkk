<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin'); require_post_csrf();
$raw = $_POST['id'] ?? ''; $id = $raw === '' ? null : positive_id($raw);
if ($raw !== '' && !$id) { panel_not_found(); }
try {
    account_change((int)$user['id'], true, static function () use ($id): void {
        $data = account_identity($_POST, $id); $role = input_text($_POST, 'role');
        if (!in_array($role, ['admin','staf_bkk'], true)) { throw new InvalidArgumentException('Role tidak valid.'); }
        if ($id) {
            $old = panel_query('SELECT * FROM users WHERE id=:id FOR UPDATE', ['id'=>$id])->fetch();
            if (!$old) { throw new InvalidArgumentException('Akun tidak ditemukan.'); }
            protect_last_admin($old, $role, (int)$old['aktif']);
            panel_query('UPDATE users SET nama=:nama,username=:username,email=:email,role=:role WHERE id=:id', $data+['role'=>$role,'id'=>$id]);
        } else {
            $hash = account_password(is_string($_POST['password'] ?? null) ? $_POST['password'] : '', is_string($_POST['confirmation'] ?? null) ? $_POST['confirmation'] : '');
            panel_query('INSERT INTO users(nama,username,email,role,password_hash,aktif) VALUES(:nama,:username,:email,:role,:hash,1)', $data+['role'=>$role,'hash'=>$hash]);
        }
    });
    panel_message('Akun berhasil disimpan.', 'panel/pengguna/');
} catch (Throwable $exception) { account_failure($exception, $id ? 'panel/pengguna/edit.php?id='.$id : 'panel/pengguna/tambah.php'); }
