<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user=require_role('admin'); require_post_csrf(); $id=positive_id($_POST['id']??null);
if (!$id) { panel_not_found(); }
try {
    account_change((int)$user['id'],true,static function () use ($id): void {
        if (!panel_query('SELECT id FROM users WHERE id=:id FOR UPDATE',['id'=>$id])->fetch()) { throw new InvalidArgumentException('Akun tidak ditemukan.'); }
        $hash=account_password(is_string($_POST['password']??null)?$_POST['password']:'',is_string($_POST['confirmation']??null)?$_POST['confirmation']:'');
        panel_query('UPDATE users SET password_hash=:hash,session_version=session_version+1 WHERE id=:id',['hash'=>$hash,'id'=>$id]);
    });
    panel_message('Password direset; seluruh sesi akun dibatalkan.','panel/pengguna/');
} catch (Throwable $exception) { account_failure($exception,'panel/pengguna/edit.php?id='.$id); }
