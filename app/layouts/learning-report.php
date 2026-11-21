<?php
if(!defined('APP_ROOT')||!isset($report,$reportRows)){http_response_code(403);exit;}
$letterhead=null;$letterheadSize=null;
foreach(['png','webp','jpg','jpeg'] as $extension){
    $relative='assets/images/kop-rapor.'.$extension;
    if(is_file(APP_ROOT.'/'.$relative)){
        $size=@getimagesize(APP_ROOT.'/'.$relative);
        if($size!==false){$letterhead=base_url($relative).'?v='.(int)filemtime(APP_ROOT.'/'.$relative);$letterheadSize=$size;break;}
    }
}
$schoolLogos=site_logos();
$schoolAddress=trim(setting('contact_address'));
if(preg_match('/^(belum|isi alamat|alamat belum)/iu',$schoolAddress)){$schoolAddress='';}
?>
<article class="student-report school-report" style="--report-letterhead-width:<?= (int)($letterheadSize[0]??892) ?>px" aria-label="Rapor capaian pembelajaran siswa">
    <header class="report-letterhead <?= $letterhead?'report-letterhead-custom':'' ?>">
        <?php if($letterhead): ?><img class="report-letterhead-image" src="<?= e($letterhead) ?>" width="<?= (int)$letterheadSize[0] ?>" height="<?= (int)$letterheadSize[1] ?>" alt="Kop surat SMK Jaya Buana">
        <?php else: ?><div class="report-letterhead-fallback"><img src="<?= e($schoolLogos['school_logo']) ?>" alt="Logo sekolah"><div><h2><?= e(setting('school_name','SMK JAYA BUANA')) ?></h2><p><?= e(setting('site_name','BKK SMK JAYA BUANA')) ?></p><?php if($schoolAddress!==''): ?><p class="report-school-address"><?= e($schoolAddress) ?></p><?php endif; ?></div></div><img class="report-letterhead-band" src="<?= e(asset_url('images/batik-sekolah.webp')) ?>" alt=""><?php endif; ?>
    </header>
    <div class="report-document-heading"><h1 class="report-document-title">Rapor Capaian Pembelajaran</h1><p>Laporan perkembangan kompetensi siswa</p><?php if($report['status']!=='final'): ?><p class="report-draft-label">DRAFT &mdash; penilaian belum final</p><?php endif; ?></div>
    <div class="report-identity-columns"><dl class="report-identity"><dt>Nama siswa</dt><dd><?= e($report['nama']) ?></dd><dt>Kelas / jurusan</dt><dd><?= e($report['kelas'].' / '.$report['jurusan']) ?></dd><dt>Angkatan</dt><dd><?= (int)$report['angkatan'] ?></dd></dl><dl class="report-identity"><dt>Periode / semester</dt><dd><?= e($report['periode']) ?></dd><dt>Tanggal penilaian</dt><dd><?= e(indo_date($report['tanggal'])) ?></dd><dt>Status penilaian</dt><dd><?= $report['status']==='final'?'Final':'Draft' ?></dd></dl></div>
    <section class="report-results" aria-labelledby="report-results-title"><h2 id="report-results-title" class="report-document-section-title">A. Penilaian kompetensi</h2>
    <div class="report-table-viewport" tabindex="0" role="region" aria-label="Tabel penilaian kompetensi"><table class="report-card-table"><colgroup><col style="width:5%"><col style="width:25%"><col style="width:9%"><col style="width:17%"><col style="width:44%"></colgroup><thead><tr><th scope="col">No.</th><th scope="col">Kompetensi</th><th scope="col">Nilai</th><th scope="col">Predikat</th><th scope="col">Deskripsi capaian</th></tr></thead><tbody><?php foreach($reportRows as $index=>$item): ?><tr><td><?= $index+1 ?></td><td><?= e($item['kompetensi']) ?></td><td class="report-score"><?= $item['nilai']!==null?e(number_format((float)$item['nilai'],2,',','.')):'&mdash;' ?></td><td><?= e(report_predicates()[$item['predikat']??'']??'Belum dinilai') ?></td><td><?= $item['deskripsi']!==''?nl2br(e($item['deskripsi'])):'&mdash;' ?></td></tr><?php endforeach; ?><?php if(!$reportRows): ?><tr><td colspan="5">Belum ada penilaian kompetensi.</td></tr><?php endif; ?></tbody></table></div><p class="report-document-help">Nilai menggunakan skala 0&ndash;100 dan bersifat opsional. Predikat ditetapkan oleh pembimbing.</p></section>
    <section class="report-teacher-note"><h2 class="report-document-section-title">B. Catatan pembimbing</h2><p><?= nl2br(e($report['catatan']!==''?$report['catatan']:'Belum ada catatan.')) ?></p></section>
    <footer class="report-signoff"><div><p><?= e(indo_date($report['tanggal'])) ?></p><p>Pembimbing / penilai</p><div class="report-signature-space" aria-hidden="true"></div><strong><?= e($report['pembimbing']) ?></strong></div></footer>
</article>
