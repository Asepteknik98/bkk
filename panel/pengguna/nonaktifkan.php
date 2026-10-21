<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user=require_role('admin'); require_post_csrf(); $id=positive_id($_POST['id']??null);
if (!$id) { panel_not_found(); }
try {
    account_change((int)$user['id'], true, static function () use ($id): void {
        $active = input_text($_POST,'aktif');
        if (!in_array($active,['0','1'],true)) { throw new InvalidArgumentException('Status tidak valid.'); }
        $old=panel_query('SELECT * FROM users WHERE id=:id FOR UPDATE',['id'=>$id])->fetch();
        if (!$old) { throw new InvalidArgumentException('Akun tidak ditemukan.'); }
        protect_last_admin($old,$old['role'],(int)$active);
        panel_query('UPDATE users SET aktif=:aktif,session_version=session_version+1 WHERE id=:id',['aktif'=>(int)$active,'id'=>$id]);
    });
    panel_message('Status akun diperbarui; sesi lama dibatalkan.','panel/pengguna/');
} catch (Throwable $exception) { account_failure($exception,'panel/pengguna/'); }
