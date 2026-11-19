<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/helpers/employment.php';
$user = require_role('admin', 'staf_bkk');
employment_require_ready();
$id = positive_id($_GET['id'] ?? null);
$type = $id ? panel_query('SELECT * FROM employment_types WHERE id=:id', ['id' => $id])->fetch() : false;
if (!$type) { panel_not_found(); }
panel_start($user, 'Detail jenis hubungan kerja', 'hubungan-kerja');
?>
<div class="employment-type-page">
<div class="page-actions"><h1><?= panel_icon('view') ?> Detail jenis hubungan kerja</h1><a href="<?= e(base_url('panel/hubungan-kerja/')) ?>"><?= panel_icon('back') ?> Daftar jenis</a></div>
<dl class="employment-detail">
<div><dt>Nama jenis</dt><dd><?= e($type['nama']) ?></dd></div>
<div><dt>Status</dt><dd><span class="badge <?= $type['aktif'] ? 'is-active' : '' ?>"><?= $type['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></dd></div>
<div><dt>Penjelasan</dt><dd><?= $type['penjelasan'] !== '' ? nl2br(e($type['penjelasan'])) : 'Belum diisi.' ?></dd></div>
<div><dt>Rujukan aturan resmi</dt><dd><?php if ($type['rujukan_url'] && http_url($type['rujukan_url'])): ?><a href="<?= e($type['rujukan_url']) ?>" target="_blank" rel="noopener noreferrer"><?= panel_icon('link') ?> <?= e($type['rujukan_url']) ?></a><?php else: ?>Belum diisi.<?php endif; ?></dd></div>
</dl>
<div class="employment-type-actions"><a class="employment-edit" href="<?= e(base_url('panel/hubungan-kerja/?id=' . $id)) ?>"><?= panel_icon('edit') ?> Edit jenis</a></div>
</div>
<?php panel_end($user); ?>
