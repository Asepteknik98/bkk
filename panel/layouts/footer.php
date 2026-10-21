<?php if (!isset($user)) { http_response_code(403); exit('Akses ditolak.'); } ?>
<footer class="panel-footer">Panel Pengelola <?= e(app_config('name')) ?></footer>
</body>
</html>
