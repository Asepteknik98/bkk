<?php
if(!isset($user)){http_response_code(403);exit;}
require_once APP_ROOT.'/app/helpers/careers.php';if(!career_can($user)){return;}
if(!employment_ready()){echo '<p class="notice">Rekap hasil alumni tersedia setelah pembaruan database 024 diterapkan.</p>';return;}
$careerYear=input_text($_GET,'career_year');if($careerYear!==''&&!preg_match('/^[0-9]{4}$/D',$careerYear)){http_response_code(400);exit('Tahun alumni tidak valid.');}
$careerData=career_dashboard($careerYear);$careerPages=max(1,(int)ceil(count($careerData['rows'])/20));$careerPage=min($careerPages,positive_id($_GET['career_page']??'1')??1);$careerRows=array_slice($careerData['rows'],($careerPage-1)*20,20);
$careerYears=panel_query('SELECT DISTINCT tahun_lulus FROM tracer_responses ORDER BY tahun_lulus DESC')->fetchAll(PDO::FETCH_COLUMN);
?>
<section id="career-results" class="career-results" aria-labelledby="career-title">
    <header class="career-heading">
        <div><p class="career-eyebrow">Pemantauan alumni</p><h2 id="career-title"><?= panel_icon('tracer') ?> Hasil seleksi dan keterserapan alumni</h2><p>Ringkasan hasil seleksi dan pekerjaan yang dilaporkan alumni.</p></div>
        <a class="career-primary" href="<?= e(base_url('panel/hasil-alumni/lamaran.php')) ?>"><?= panel_icon('lamaran') ?> Kelola lamaran alumni</a>
    </header>
    <form class="career-filter" method="get">
        <label for="career-year"><span><?= panel_icon('calendar') ?> Tahun lulus</span><select id="career-year" name="career_year"><option value="">Semua tahun</option><?php foreach($careerYears as $year): ?><option value="<?= (int)$year ?>" <?= (string)$year===$careerYear?'selected':'' ?>><?= (int)$year ?></option><?php endforeach; ?></select></label>
        <button type="submit" class="career-primary"><?= panel_icon('search') ?> Tampilkan rekap</button>
    </form>
    <div class="career-stats">
        <section class="career-stat career-stat-blue"><span class="career-stat-icon"><?= panel_icon('peserta') ?></span><h3>Alumni tercatat</h3><strong><?= (int)$careerData['total'] ?></strong><p>Alumni pada data tracer yang dipilih.</p></section>
        <section class="career-stat career-stat-teal"><span class="career-stat-icon"><?= panel_icon('lowongan') ?></span><h3>Sudah mulai bekerja</h3><strong><?= (int)$careerData['working'] ?></strong><p><?= e((string)$careerData['percentage']) ?>% dari alumni tercatat</p></section>
        <section class="career-stat career-stat-violet"><span class="career-stat-icon"><?= panel_icon('certificate') ?></span><h3>Lolos seleksi</h3><strong><?= (int)$careerData['passed'] ?></strong><p>Alumni yang pernah melaporkan Lolos; belum tentu sudah bekerja.</p></section>
    </div>
    <details class="career-note"><summary><?= panel_icon('view') ?> Cara membaca rekap</summary><p>Alumni dihitung unik berdasarkan NISN yang terisi; tanpa NISN dihitung per catatan tracer. Keterserapan memerlukan laporan sudah mulai bekerja, tanggal mulai tidak mendatang, serta perusahaan, posisi, dan jenis hubungan kerja lengkap. Data tracer lama tanpa rincian ini belum dihitung terserap.</p></details>
    <div class="career-roster"><h3><?= panel_icon('peserta') ?> Rincian alumni <span><?= count($careerData['rows']) ?> alumni</span></h3>
    <div class="career-table-wrap"><table class="career-table"><caption class="sr-only">Hasil seleksi dan status kerja alumni sesuai filter tahun lulus</caption><thead><tr><th scope="col"><?= panel_icon('akun') ?> Alumni</th><th scope="col"><?= panel_icon('lamaran') ?> Hasil seleksi</th><th scope="col"><?= panel_icon('lowongan') ?> Status kerja</th><th scope="col"><?= panel_icon('settings') ?> Tindakan</th></tr></thead><tbody>
    <?php foreach($careerRows as $family): $person=$family['person']; ?>
        <tr><td data-label="Alumni"><strong class="career-person"><?= e($person['nama_lengkap']) ?></strong><span class="career-muted"><?= e($person['jurusan']) ?></span><span class="career-year"><?= (int)$person['tahun_lulus'] ?></span></td>
        <td data-label="Hasil seleksi"><?php if(!$family['applications']): ?><span class="career-status"><?= panel_icon('lamaran') ?> Belum Mengisi</span><p class="career-muted">Belum ada catatan lamaran.</p><?php else: ?><ul class="career-applications"><?php foreach($family['applications'] as $application): ?><li><span><?= e($application['posisi'].' - '.$application['perusahaan']) ?></span><strong class="career-result-label"><?= e(career_result_label($application)) ?></strong><?php if($application['seleksi_selesai']): ?><small>Seleksi selesai</small><?php endif; ?><a class="career-inline-link" href="<?= e(base_url('panel/hasil-alumni/lamaran.php?tracer_id='.$application['tracer_id'])) ?>"><?= panel_icon('view') ?> Kelola</a></li><?php endforeach; ?></ul><?php endif; ?></td>
        <td data-label="Status kerja"><?php if($family['started']): ?><span class="career-status career-status-working"><?= panel_icon('lowongan') ?> Sudah mulai bekerja</span><p><?= e($family['work']['perusahaan'].' - '.$family['work']['posisi']) ?></p><p class="career-muted"><?= e($family['work']['tanggal_mulai'].' - '.$family['work']['jenis']) ?></p><?php else: ?><span class="career-muted">Belum tercatat sudah mulai bekerja.</span><?php endif; ?></td>
        <td data-label="Tindakan"><a class="career-row-action" href="<?= e(base_url('panel/hasil-alumni/lamaran.php?tracer_id='.$person['id'])) ?>"><?= panel_icon('next') ?> Lamaran / tautan alumni</a></td></tr>
    <?php endforeach; ?>
    <?php if(!$careerRows): ?><tr><td colspan="4" class="career-empty"><?= panel_icon('peserta') ?><strong>Belum ada alumni untuk pilihan ini.</strong><span>Pilih tahun lulus lain untuk melihat rekap.</span></td></tr><?php endif; ?>
    </tbody></table></div></div>
    <?php if($careerPages>1): ?><nav class="career-pagination" aria-label="Halaman hasil alumni"><span>Halaman <?= $careerPage ?> dari <?= $careerPages ?></span><?php foreach(['Sebelumnya'=>$careerPage-1,'Berikutnya'=>$careerPage+1] as $label=>$target): if($target<1||$target>$careerPages){continue;} ?><a class="career-row-action" href="<?= e('?'.http_build_query(['career_year'=>$careerYear,'career_page'=>$target]).'#career-results') ?>"><?= e($label) ?></a><?php endforeach; ?></nav><?php endif; ?>
</section>
