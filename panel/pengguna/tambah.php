<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/accounts.php';
$user = require_role('admin'); $id = null;
$data = ['nama'=>'','username'=>'','email'=>'','role'=>'staf_bkk'];
require __DIR__ . '/form.php';
