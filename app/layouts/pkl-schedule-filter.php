<?php if(!isset($user,$filters,$periods,$filterAction,$travel)){http_response_code(403);exit;} ?>
<form class="filter-form card" method="get" action="<?= e(base_url($filterAction)) ?>">
<?php if($filters['placement_id']!==''): ?><input type="hidden" name="placement_id" value="<?= e($filters['placement_id']) ?>"><p>Penempatan #<?= e($filters['placement_id']) ?></p><?php endif; ?>
<label>Periode PKL<select name="periode"><option value="">Semua periode</option><?php foreach($periods as $period):$value=$period['tanggal_mulai'].'_'.$period['tanggal_selesai']; ?><option value="<?= e($value) ?>" <?= $filters['periode']===$value?'selected':'' ?>><?= e($period['tanggal_mulai'].' – '.$period['tanggal_selesai']) ?></option><?php endforeach; ?></select></label>
<label>Tanggal pada minggu yang ditampilkan<input type="date" name="minggu" value="<?= e($filters['minggu']) ?>"></label>
<?php if(!$travel): ?><label>Jenis jadwal<select name="jenis"><option value="">Semua jenis</option><?php foreach(pkl_schedule_types() as $key=>$label): ?><option value="<?= $key ?>" <?= $filters['jenis']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><?php endif; ?>
<button>Tampilkan</button><a href="<?= e(base_url($filterAction)) ?>">Reset filter</a></form>
