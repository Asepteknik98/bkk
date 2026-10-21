<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/tracer-panel.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??null);$row=$id?panel_query('SELECT * FROM tracer_responses WHERE id=:id',['id'=>$id])->fetch():false;if(!$row){panel_not_found();}
panel_start($user,'Detail Respons Alumni','tracer');
?>
<div class="page-actions"><h1>Detail Respons Alumni</h1><a href="<?= e(base_url('panel/tracer-study/')) ?>">← Daftar respons</a></div><section class="card"><dl class="response-details"><?php foreach(tracer_fields() as $field=>$label):?><dt><?= e($label) ?></dt><dd><?= nl2br(e($field==='status_kegiatan'?(tracer_statuses()[$row[$field]]??$row[$field]):(string)($row[$field]??'')))?:'—' ?></dd><?php endforeach;?><dt>Persetujuan</dt><dd><?= (int)$row['persetujuan_data']===1?'Diberikan':'Tidak tersedia' ?></dd><dt>Waktu persetujuan</dt><dd><?= e($row['persetujuan_at']) ?></dd><dt>Versi persetujuan</dt><dd><?= e($row['versi_persetujuan']) ?></dd><dt>Dikirim</dt><dd><?= e($row['created_at']) ?></dd></dl><?php if($row['versi_persetujuan']===TRACER_CONSENT_VERSION):?><h2>Teks persetujuan</h2><p><?= e(TRACER_CONSENT) ?></p><?php endif;?></section>
<?php panel_end($user);?>
