<?php
declare(strict_types=1);
require_once __DIR__.'/app/helpers/public.php';
require_once __DIR__.'/app/helpers/event-resources.php';
header('Cache-Control: no-store');
try{[$filters,$where,$params]=attendance_filters($_GET);}catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$id=positive_id($_GET['id']??'');
if(isset($_GET['id'])&&!$id){http_response_code(404);require APP_ROOT.'/404.php';exit;}
$where.=" AND ((r.pretest_enabled=1 AND r.pretest_url<>'') OR (r.poster_enabled=1 AND r.poster_file IS NOT NULL))";
if($id){$where.=' AND e.id=:event_id';$params['event_id']=$id;}
$from=' FROM attendance_events e JOIN event_resources r ON r.event_id=e.id WHERE '.$where;
$total=(int)(public_query('SELECT COUNT(*) AS total'.$from,$params)[0]['total']??0);
if($id&&$total===0){http_response_code(404);require APP_ROOT.'/404.php';exit;}
$pages=max(1,(int)ceil($total/12));$page=min($pages,positive_id($_GET['page']??'1')??1);
// Only activity information is public; student/attendance data is never queried here.
$events=public_query("SELECT e.id,e.judul,e.tanggal,e.angkatan,e.kelas_industri,r.pretest_enabled,r.poster_enabled,(r.poster_file IS NOT NULL) AS has_poster".$from.' ORDER BY e.tanggal DESC,e.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>12,'offset'=>($page-1)*12]);
$years=public_query('SELECT DISTINCT e.angkatan FROM attendance_events e JOIN event_resources r ON r.event_id=e.id WHERE (r.pretest_enabled=1 OR r.poster_enabled=1) ORDER BY e.angkatan DESC');
$industries=public_query('SELECT DISTINCT e.kelas_industri FROM attendance_events e JOIN event_resources r ON r.event_id=e.id WHERE (r.pretest_enabled=1 OR r.poster_enabled=1) ORDER BY e.kelas_industri');
$pageTitle=$id?($events[0]['judul']??'Kegiatan'):'Kegiatan dan pretest';$activePage='kegiatan.php';
require APP_ROOT.'/app/layouts/header.php';
?>
<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Program BKK</p><h1><?= e($pageTitle) ?></h1><p>Informasi poster dan pretest kegiatan yang telah dibuka oleh pengelola.</p></div>
<?php if(!$id):?><form method="get" class="surface d-flex flex-wrap gap-3 align-items-end mb-4"><label>Cari kegiatan<input class="form-control" name="q" maxlength="100" value="<?= e($filters['q']) ?>"></label><label>Angkatan<select class="form-select" name="angkatan"><option value="">Semua angkatan</option><?php foreach($years as $year):?><option value="<?= e((string)$year['angkatan']) ?>" <?= $filters['angkatan']===(string)$year['angkatan']?'selected':'' ?>><?= e((string)$year['angkatan']) ?></option><?php endforeach;?></select></label><label>Kelas industri<select class="form-select" name="kelas_industri"><option value="">Semua kelas industri</option><?php foreach($industries as $industry):?><option value="<?= e($industry['kelas_industri']) ?>" <?= $filters['kelas_industri']===$industry['kelas_industri']?'selected':'' ?>><?= e($industry['kelas_industri']) ?></option><?php endforeach;?></select></label><button class="button button-navy" type="submit">Tampilkan</button><a href="<?= e(base_url('kegiatan.php')) ?>">Reset filter</a></form><p><?= $total ?> kegiatan · Halaman <?= $page ?> dari <?= $pages ?></p><?php endif;?>
<?php if(!empty($GLOBALS['public_database_unavailable'])):?><p class="surface">Informasi kegiatan sementara belum dapat dimuat. Silakan coba kembali.</p><?php elseif(!$events):?><p class="surface">Belum ada poster atau pretest yang dibuka untuk pilihan ini.</p><?php endif;?>
<div class="row g-4"><?php foreach($events as $event):?><div class="<?= $id?'col-12':'col-12 col-lg-6' ?>"><article class="surface h-100"><p class="eyebrow"><?= e($event['tanggal']) ?> · Angkatan <?= e((string)$event['angkatan']) ?></p><?php if(!$id):?><h2><a href="<?= e(base_url('kegiatan.php?id='.$event['id'])) ?>"><?= e($event['judul']) ?></a></h2><?php endif;?><p><?= e($event['kelas_industri']) ?></p><?php if($event['poster_enabled']&&$event['has_poster']):?><a href="<?= e(base_url('poster-kegiatan.php?id='.$event['id'])) ?>" target="_blank" rel="noopener"><img src="<?= e(base_url('poster-kegiatan.php?id='.$event['id'])) ?>" alt="Poster <?= e($event['judul']) ?>" style="display:block;max-width:100%;height:auto;max-height:720px;object-fit:contain" loading="lazy"></a><p><a href="<?= e(base_url('poster-kegiatan.php?id='.$event['id'])) ?>" target="_blank" rel="noopener">Lihat poster</a></p><?php endif;?><?php if($event['pretest_enabled']):?><a class="button button-navy" href="<?= e(base_url('pretest.php?id='.$event['id'])) ?>" target="_blank" rel="noopener noreferrer">Buka pretest</a><?php endif;?></article></div><?php endforeach;?></div>
<?php if($id):?><p class="mt-4"><a href="<?= e(base_url('kegiatan.php')) ?>">Semua kegiatan</a></p><?php else:?><nav class="pagination mt-4" aria-label="Halaman kegiatan"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a class="button" href="<?= e(base_url('kegiatan.php?'.http_build_query($filters+['page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav><?php endif;?>
</main>
<?php require APP_ROOT.'/app/layouts/footer.php'; ?>
