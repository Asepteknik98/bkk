<?php if (!defined('APP_ROOT')) { http_response_code(403); exit; } ?>
<link rel="stylesheet" href="<?= e(asset_url('css/connection.css')) ?>?v=<?= (int) filemtime(APP_ROOT.'/assets/css/connection.css') ?>">
<script src="<?= e(asset_url('js/connection.js')) ?>?v=<?= (int) filemtime(APP_ROOT.'/assets/js/connection.js') ?>" defer></script>
