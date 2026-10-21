<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin'); $id = positive_id($_GET['id'] ?? null);
$data = $id ? panel_query('SELECT id,nama,username,email,role,aktif FROM users WHERE id=:id', ['id'=>$id])->fetch() : false;
if (!$data) { panel_not_found(); }
require __DIR__ . '/form.php';
