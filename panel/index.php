<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/panel.php';
require_once APP_ROOT.'/app/helpers/tracer.php';
$user=require_role('admin','staf_bkk');
if($user['role']==='staf_bkk'){require __DIR__.'/layouts/staff-dashboard.php';exit;}
$counts=[];
foreach(['berita','lowongan','program'] as $table){
    $counts[$table]=panel_query("SELECT status,COUNT(*) AS jumlah FROM $table GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
}
$today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
$open=(int)panel_query("SELECT COUNT(*) FROM lowongan WHERE status='published' AND batas_lamaran>=:today",['today'=>$today])->fetchColumn();
$activePartners=(int)panel_query('SELECT COUNT(*) FROM mitra WHERE aktif=1')->fetchColumn();
$mediaCount=(int)panel_query('SELECT COUNT(*) FROM media')->fetchColumn();
$unseenApplications=(int)panel_query('SELECT COUNT(*) FROM lamaran WHERE viewed_at IS NULL')->fetchColumn();
$applications=panel_query('SELECT status,COUNT(*) AS jumlah FROM lamaran GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$tracer=panel_query('SELECT status_kegiatan,COUNT(*) AS jumlah FROM tracer_responses GROUP BY status_kegiatan')->fetchAll(PDO::FETCH_KEY_PAIR);
$users=$user['role']==='admin'?(int)panel_query('SELECT COUNT(*) FROM users WHERE aktif=1')->fetchColumn():null;
$participantStats=panel_query("SELECT COUNT(*) AS total,COALESCE(SUM(TRIM(tempat_pkl)<>'' OR TRIM(tahun_pkl)<>''),0) AS pkl FROM siswa")->fetch();
$eventCount=(int)panel_query('SELECT COUNT(*) FROM attendance_events')->fetchColumn();
$speakerCount=(int)panel_query('SELECT COUNT(*) FROM speakers')->fetchColumn();
panel_start($user,'Dashboard','dashboard');
?>
<div class="dashboard-view dashboard-sheet"><header class="dashboard-heading"><h1><?= panel_icon('dashboard') ?> Dashboard</h1><p>Selamat datang, <?= e($user['nama']) ?>. Berikut ringkasan data BKK saat ini.</p></header>
<div class="summary-grid">
<?php foreach(['berita'=>'Berita','lowongan'=>'Lowongan','program'=>'Program'] as $key=>$label): ?><section class="card"><span class="stat-icon"><?= panel_icon($key) ?></span><h2><a href="<?= e(base_url('panel/'.$key.'/')) ?>"><?= $label ?></a></h2><p><strong><?= array_sum($counts[$key]) ?></strong> total</p><p><?= (int)($counts[$key]['published']??0) ?> published · <?= (int)($counts[$key]['draft']??0) ?> draft</p></section><?php endforeach; ?>
<section class="card"><span class="stat-icon"><?= panel_icon('lowongan') ?></span><h2><a href="<?= e(base_url('panel/lowongan/?status=open')) ?>">Lowongan dibuka</a></h2><p><strong><?= $open ?></strong> aktif</p><p>Published, batas lamaran belum lewat (WIB).</p></section>
<section class="card"><span class="stat-icon"><?= panel_icon('mitra') ?></span><h2><a href="<?= e(base_url('panel/mitra/')) ?>">Mitra aktif</a></h2><p><strong><?= $activePartners ?></strong> mitra</p></section>
<section class="card"><span class="stat-icon"><?= panel_icon('media') ?></span><h2><a href="<?= e(base_url('panel/media/')) ?>">Media</a></h2><p><strong><?= $mediaCount ?></strong> berkas dalam katalog</p></section>
<?php if($users!==null): ?><section class="card"><span class="stat-icon"><?= panel_icon('pengguna') ?></span><h2><a href="<?= e(base_url('panel/pengguna/')) ?>">Pengguna aktif</a></h2><p><strong><?= $users ?></strong> akun</p></section><?php endif; ?>
<section class="card stat-applications"><span class="stat-icon"><?= panel_icon('bell') ?><?php if($unseenApplications>0): ?><span class="inbox-new-count" aria-label="<?= $unseenApplications ?> lamaran belum dilihat"><?= $unseenApplications>99?'99+':$unseenApplications ?></span><?php endif; ?></span><h2><a href="<?= e(base_url('panel/lamaran/')) ?>">Lamaran Masuk</a></h2><p><strong><?= (int)array_sum($applications) ?></strong> lamaran</p><p><?= (int)($applications['baru']??0) ?> belum diproses</p></section>
<?php foreach([
    ['participants','peserta','Data Siswa / PKL','panel/peserta/',(int)$participantStats['total'],'peserta',(int)$participantStats['pkl'].' memiliki riwayat PKL'],
    ['tracer','tracer','Tracer Study','panel/tracer-study/rekap.php',(int)array_sum($tracer),'respons','Respons kegiatan alumni'],
    ['events','absensi','Kegiatan','panel/absensi/',$eventCount,'kegiatan','Kegiatan dan absensi peserta'],
    ['speakers','pemateri','Pemateri Kelas','panel/pemateri/',$speakerCount,'pemateri','Profil, CV, dan foto pemateri'],
] as [$statClass,$icon,$label,$path,$value,$unit,$description]): ?>
<section class="card stat-<?= e($statClass) ?>"><span class="stat-icon"><?= panel_icon($icon) ?></span><h2><a href="<?= e(base_url($path)) ?>"><?= e($label) ?></a></h2><p><strong><?= $value ?></strong> <?= e($unit) ?></p><p><?= e($description) ?></p></section>
<?php endforeach; ?>
</div>
<section class="dashboard-insights" aria-label="Grafik PKL dan Tracer Study">
<div class="dashboard-charts"><section class="card section-space tracer-summary tracer-sheet-summary" aria-labelledby="tracer-chart-title">
    <div class="tracer-summary-heading"><h2 id="tracer-chart-title"><?= panel_icon('tracer') ?> Ringkasan Tracer Study</h2><span class="badge"><?= (int) array_sum($tracer) ?> respons</span></div>
    <p class="tracer-sheet-note">Jumlah respons menurut kegiatan alumni, bukan jumlah alumni unik.</p>
    <?php $labels=tracer_statuses();if(isset($tracer['lainnya'])){$labels['lainnya']='Lainnya (data lama)';} ?>
    <?php $starTotal=(int)array_sum($tracer);$starColors=['#2458eb','#6355e8','#8319b7','#05a2d4','#277c62'];$starIcons=['bekerja'=>'lowongan','kuliah'=>'program','wirausaha'=>'mitra','mencari_kerja'=>'search','lainnya'=>'berita'];$starCount=count($labels); ?>
    <div class="tracer-star-layout">
        <div class="tracer-star-art" aria-hidden="true"><svg viewBox="0 0 360 360" focusable="false">
            <defs><?php foreach(array_values($labels) as $i=>$label): ?><linearGradient id="tracer-star-<?= $i ?>" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="<?= $starColors[$i] ?>"/><stop offset="1" stop-color="<?= $starColors[$i] ?>" stop-opacity=".7"/></linearGradient><?php endforeach; ?></defs>
            <?php $i=0;foreach($labels as $status=>$label):$angle=$i*360/$starCount; ?>
            <g class="tracer-star-petal" style="--petal-delay:<?= $i*90 ?>ms"><g transform="rotate(<?= $angle ?> 180 180)"><path d="M174 18Q180 6 186 18L237 117Q242 130 229 143L180 184L131 143Q118 130 123 117Z" fill="url(#tracer-star-<?= $i ?>)"/><path d="M180 15L180 184L229 143Q242 130 237 117L186 18Q183 12 180 15Z" fill="#000" opacity=".08"/></g>
            <?php $r=105;$cx=180+sin(deg2rad($angle))*$r;$cy=180-cos(deg2rad($angle))*$r; ?><g transform="translate(<?= round($cx-13,2) ?> <?= round($cy-13,2) ?>)" class="tracer-star-symbol"><?= str_replace('<svg ', '<svg width="26" height="26" ', panel_icon($starIcons[$status]??'berita')) ?></g></g>
            <?php $i++;endforeach; ?>
            <circle cx="180" cy="180" r="61" class="tracer-star-center"/><text x="180" y="179" class="tracer-star-total" text-anchor="middle"><?= $starTotal ?></text><text x="180" y="201" class="tracer-star-caption" text-anchor="middle">Total respons</text>
        </svg></div>
        <ul class="tracer-star-legend" aria-label="Jumlah respons per kegiatan alumni">
        <?php $i=0;foreach($labels as $status=>$label):$count=(int)($tracer[$status]??0); ?><li style="--star-color:<?= $starColors[$i] ?>"><a href="<?= e(base_url('panel/tracer-study/'.($status==='lainnya'?'':'?status_kegiatan='.rawurlencode($status)))) ?>"><span class="tracer-star-icon"><?= panel_icon($starIcons[$status]??'berita') ?></span><span><?= e($label) ?><small><?= $starTotal?number_format($count/$starTotal*100,1,',','.'):'0' ?>% respons</small></span><strong><?= $count ?></strong></a></li><?php $i++;endforeach; ?>
        </ul>
    </div>
    <?php if(array_sum($tracer)===0): ?><p class="tracer-sheet-note">Belum ada respons tracer study yang masuk.</p><?php endif; ?>
    <a class="tracer-sheet-link" href="<?= e(base_url('panel/tracer-study/rekap.php')) ?>"><?= panel_icon('view') ?> Lihat rekap dan filter <?= panel_icon('next') ?></a>
</section>
<section class="card tracer-visual tracer-fan-card" aria-labelledby="tracer-visual-title">
    <h2 id="tracer-visual-title"><?= panel_icon('tracer') ?> Komposisi kegiatan alumni</h2>
    <p class="muted">Pilih kategori untuk melihat jumlah respons. Luas bidang menunjukkan proporsinya.</p>
    <?php
    $tracerTotal=(int)array_sum($tracer);$offset=0;$categoryIndex=0;
    $fanColors=['#ed8c0a','#70a51b','#246e98','#d83383','#13a0ac'];
    $fanDark=['#a85b05','#456c0b','#174e70','#a32261','#086d78'];
    $fanPoint=static fn(float $radius,float $angle):string=>number_format(60+$radius*cos(deg2rad($angle)),3,'.','').','.number_format(180+$radius*sin(deg2rad($angle)),3,'.','');
    ?>
    <div class="tracer-fan-layout">
        <div class="tracer-fan" data-total="<?= $tracerTotal ?>">
            <svg viewBox="0 0 245 360" role="img" aria-label="Grafik kipas komposisi kegiatan alumni, <?= $tracerTotal ?> respons">
                <defs><?php foreach($labels as $status=>$label):$i=array_search($status,array_keys($labels),true); ?><linearGradient id="tracer-fan-fill-<?= $i ?>" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="<?= $fanColors[$i] ?>"/><stop offset="1" stop-color="<?= $fanDark[$i] ?>"/></linearGradient><?php endforeach; ?></defs>
                <path class="fan-track" d="M60,20 A160,160 0 0 1 60,340 L60,230 A50,50 0 0 0 60,130 Z"/>
                <?php foreach($labels as $status=>$label):$value=(int)($tracer[$status]??0);$angle=$tracerTotal?$value/$tracerTotal*180:0;$start=-90+$offset;$end=$start+$angle; ?>
                <?php if($value>0): ?><path class="fan-segment fan-category-<?= $categoryIndex ?>" data-category="<?= $categoryIndex ?>" fill="url(#tracer-fan-fill-<?= $categoryIndex ?>)" d="M<?= $fanPoint(160,$start) ?> A160,160 0 0 1 <?= $fanPoint(160,$end) ?> L<?= $fanPoint(50,$end) ?> A50,50 0 0 0 <?= $fanPoint(50,$start) ?> Z"><title><?= e($label) ?>: <?= $value ?> respons (<?= number_format($angle/180*100,1,',','.') ?>%)</title></path><?php if($angle>=18): [$tx,$ty]=explode(',',$fanPoint(108,($start+$end)/2)); ?><text class="fan-sector-number" x="<?= $tx ?>" y="<?= $ty ?>" text-anchor="middle" dominant-baseline="middle" aria-hidden="true"><?= str_pad((string)($categoryIndex+1),2,'0',STR_PAD_LEFT) ?></text><?php endif; ?><?php endif; ?>
                <?php $offset+=$angle;$categoryIndex++;endforeach; ?>
                <circle class="fan-center-disc" cx="60" cy="180" r="48"/>
            </svg>
            <div class="fan-total" aria-live="polite"><strong><?= $tracerTotal ?></strong><span>Total respons</span></div>
        </div>
        <div class="fan-legend" aria-label="Kategori kegiatan alumni">
            <?php $categoryIndex=0;foreach($labels as $status=>$label):$value=(int)($tracer[$status]??0);$percent=$tracerTotal?$value/$tracerTotal*100:0; ?>
            <button type="button" class="fan-category-<?= $categoryIndex ?>" data-category="<?= $categoryIndex ?>" data-label="<?= e($label) ?>" data-value="<?= $value ?>"><span class="fan-legend-number" aria-hidden="true"><?= str_pad((string)($categoryIndex+1),2,'0',STR_PAD_LEFT) ?></span><span class="fan-legend-text"><?= e($label) ?><small><?= number_format($percent,1,',','.') ?>% dari respons</small></span><strong><?= $value ?><span class="sr-only"> respons</span></strong></button>
            <?php $categoryIndex++;endforeach; ?>
        </div>
    </div>
    <?php if(!$tracerTotal): ?><p class="muted">Belum ada respons. Bidang warna akan muncul setelah data tersedia.</p><?php endif; ?>
    <a class="tracer-composition-link" href="<?= e(base_url('panel/tracer-study/rekap.php')) ?>"><?= panel_icon('tracer') ?> Lihat rekap dan filter <?= panel_icon('next') ?></a>
</section>
<section class="card tracer-summary pkl-summary pkl-completion-card" aria-labelledby="pkl-chart-title">
    <?php $pklTotal=(int)$participantStats['total'];$pklFilled=(int)$participantStats['pkl'];$pklMissing=$pklTotal-$pklFilled;$pklPercent=$pklTotal?$pklFilled/$pklTotal*100:0;$pklMissingPercent=$pklTotal?$pklMissing/$pklTotal*100:0; ?>
    <div class="tracer-summary-heading"><h2 id="pkl-chart-title"><?= panel_icon('peserta') ?> Kelengkapan Data PKL</h2><span class="badge"><?= $pklTotal ?> siswa</span></div>
    <p class="pkl-completion-note">Riwayat dianggap terisi jika tempat atau tahun PKL sudah dicatat.</p>
    <div class="pkl-completion-layout"><div class="pkl-completion-stat"><strong class="pkl-percent-display" aria-label="<?= $pklTotal?e(number_format($pklPercent,1,',','.').' persen riwayat terisi'):'Belum ada data siswa' ?>"><span aria-hidden="true" <?= $pklTotal?'data-pkl-count="'.number_format($pklPercent,4,'.','').'"':'' ?>><?= $pklTotal?number_format($pklPercent,1,',','.'):'-' ?></span><?php if($pklTotal): ?><small aria-hidden="true">%</small><?php endif; ?></strong><span>riwayat terisi</span><p><?= $pklFilled ?> dari <?= $pklTotal ?> siswa</p></div>
    <div class="pkl-completion-graph"><div class="pkl-completion-bar" role="img" aria-label="<?= $pklFilled ?> siswa memiliki riwayat PKL; <?= $pklMissing ?> siswa belum terisi; total <?= $pklTotal ?> siswa"><span class="pkl-bar-filled" style="width:<?= number_format($pklPercent,4,'.','') ?>%" title="Riwayat terisi: <?= $pklFilled ?> siswa"><?php if($pklPercent>=15): ?><?= number_format($pklPercent,1,',','.') ?>%<?php endif; ?></span><span class="pkl-bar-missing" style="width:<?= number_format($pklMissingPercent,4,'.','') ?>%" title="Belum terisi: <?= $pklMissing ?> siswa"><?php if($pklMissingPercent>=15): ?><?= number_format($pklMissingPercent,1,',','.') ?>%<?php endif; ?></span></div>
    <div class="pkl-completion-scale" aria-hidden="true"><span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span></div>
    <dl class="pkl-completion-legend"><div><dt><span class="pkl-legend-dot is-filled" aria-hidden="true"></span>Riwayat terisi</dt><dd><strong><?= $pklFilled ?></strong> siswa <small><?= number_format($pklPercent,1,',','.') ?>%</small></dd></div><div><dt><span class="pkl-legend-dot is-missing" aria-hidden="true"></span>Belum terisi</dt><dd><strong><?= $pklMissing ?></strong> siswa <small><?= number_format($pklMissingPercent,1,',','.') ?>%</small></dd></div></dl></div></div>
    <?php if(!$pklTotal): ?><p class="pkl-completion-note">Belum ada data siswa. Grafik akan terisi setelah data ditambahkan.</p><?php endif; ?>
    <a class="pkl-completion-link" href="<?= e(base_url('panel/peserta/')) ?>"><?= panel_icon('identity') ?> Kelola data siswa <?= panel_icon('next') ?></a>
</section></div></section>

</div>
<?php panel_end($user); ?>
