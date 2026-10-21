<?php if (!defined('APP_ROOT')) { http_response_code(403); exit; } ?>
<div class="latest-strip container portal-container">
    <span class="latest-label"><span aria-hidden="true">●</span> Informasi terbaru</span>
    <?php if ($latestNews): ?><a href="<?= e(news_url($latestNews[0])) ?>"><?= e($latestNews[0]['judul']) ?></a><?php demo_label($latestNews[0]); ?>
    <?php else: ?><span>Belum ada informasi terbaru yang diterbitkan.</span><?php endif; ?>
</div>
