<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/mou.php';
$user=require_role('admin','staf_bkk');mou_require_ready();
$partnerId=positive_id($_GET['mitra_id']??'');if(isset($_GET['mitra_id'])&&$_GET['mitra_id']!==''&&!$partnerId){panel_not_found();}
$partner=null;if($partnerId){try{$partner=partner_find($partnerId);}catch(InvalidArgumentException){panel_not_found();}}
$previous=null;
if(isset($_GET['previous_id'])){
    $previousId=positive_id($_GET['previous_id']);$previous=$previousId&&$partnerId?panel_query('SELECT * FROM mitra_mous WHERE id=:id AND mitra_id=:partner',['id'=>$previousId,'partner'=>$partnerId])->fetch():false;
    if(!$previous){panel_not_found();}
}
$data=mou_input(['previous_id'=>$previous?(string)$previous['id']:'']);$errors=[];
if($previous){$candidate=(new DateTimeImmutable($previous['tanggal_berakhir']))->modify('+1 day')->format('Y-m-d');$data['tanggal_mulai']=$candidate<='2199-12-31'?$candidate:'';}
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();if(!$partner){http_response_code(400);exit('Pilih mitra terlebih dahulu.');}
    $data=mou_input($_POST);$file=null;$saved=false;
    try{
        if($errors=mou_errors($data)){throw new InvalidArgumentException(implode(' ',$errors));}
        if(isset($_FILES['dokumen'])&&!is_array($_FILES['dokumen'])){throw new InvalidArgumentException('Unggahan dokumen tidak valid.');}
        $file=mou_upload($_FILES['dokumen']??null);mou_save($partnerId,$data,$file,(int)$user['id']);$saved=true;
        panel_message('MoU baru berhasil disimpan. Riwayat sebelumnya tetap tersedia.','panel/mou/?mitra_id='.$partnerId);
    }catch(Throwable $exception){$errors=[$exception instanceof PDOException&&($exception->errorInfo[1]??0)===1062?'MoU dengan nomor dan periode yang sama sudah tersimpan.':pkl_error($exception)];}
    finally{if($file&&!$saved){mou_discard($file);}}
}
$search='';$partners=[];$total=0;$page=1;$pages=1;
if(!$partner){
    $search=input_text($_GET,'q');
    if(!valid_text($search,100,false)){http_response_code(400);exit('Pencarian terlalu panjang atau tidak valid.');}
    $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';
    $total=(int)panel_query("SELECT COUNT(*) FROM mitra WHERE nama LIKE :nama ESCAPE '!'",['nama'=>$pattern])->fetchColumn();
    $pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
    $partners=panel_query("SELECT id,nama,jenis,aktif FROM mitra WHERE nama LIKE :nama ESCAPE '!' ORDER BY nama,id LIMIT :limit OFFSET :offset",['nama'=>$pattern,'limit'=>10,'offset'=>($page-1)*10])->fetchAll();
}
panel_start($user,$previous?'Perpanjangan MoU':'Tambah MoU','mou');
?>

<div class="mou-list-page mou-form-page">
<header class="partner-detail-heading"><div><h1><?= panel_icon($previous?'history':'add') ?> <?= $previous?'Perpanjang':'Tambah' ?> MoU</h1></div><nav aria-label="Navigasi MoU"><a href="<?= e(base_url('panel/mou/'.($partnerId?'?mitra_id='.$partnerId:''))) ?>"><?= panel_icon('back') ?> Riwayat MoU</a></nav></header>
<?php if(!$partner): ?>
<form class="filter-form card mou-partner-search" method="get"><label for="mou-search"><span><?= panel_icon('search') ?> Cari nama mitra</span><input id="mou-search" name="q" maxlength="100" value="<?= e($search) ?>" placeholder="Nama perusahaan atau perguruan tinggi"></label><button type="submit"><?= panel_icon('search') ?> Cari</button><a href="<?= e(base_url('panel/mou/form.php')) ?>"><?= panel_icon('reset') ?> Reset</a></form>
<div class="mou-table-heading"><h2><?= panel_icon('mitra') ?> Pilih mitra</h2><span><?= $total ?> mitra &middot; Halaman <?= $page ?> dari <?= $pages ?></span></div>
<div class="table-scroll mou-list-sheet mou-partner-picker" data-vertical-list tabindex="0" role="region" aria-label="Pilihan mitra, gulir untuk melihat data"><table><thead><tr><th><span class="mou-picker-label"><?= panel_icon('mitra') ?> Nama mitra</span></th><th><span class="mou-picker-label"><?= panel_icon('master') ?> Jenis</span></th><th><span class="mou-picker-label"><?= panel_icon('check-circle') ?> Status</span></th><th><span class="mou-picker-label"><?= panel_icon('pengaturan') ?> Tindakan</span></th></tr></thead><tbody>
<?php foreach($partners as $item): ?><tr><td><strong><?= e($item['nama']) ?></strong></td><td><?= $item['jenis']==='kampus'?'Perguruan tinggi':'Perusahaan' ?></td><td><span class="partner-state <?= $item['aktif']?'is-active':'is-inactive' ?>"><?= $item['aktif']?'Aktif':'Nonaktif' ?></span></td><td><a class="mou-renew" href="<?= e(base_url('panel/mou/form.php?mitra_id='.$item['id'])) ?>"><?= panel_icon('next') ?> Pilih mitra</a></td></tr><?php endforeach; ?>
<?php if(!$partners): ?><tr><td colspan="4" class="mou-empty">Mitra tidak ditemukan.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman pilihan mitra"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target): if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/mou/form.php?'.http_build_query(['q'=>$search,'page'=>$target]))) ?>"><?= panel_icon($target<$page?'back':'next') ?> <?= $label ?></a><?php endforeach; ?></nav>
<?php else: ?>
<div class="mou-selected-partner"><div><?= panel_icon('mitra') ?> <strong><?= e($partner['nama']) ?></strong><small><?= $partner['jenis']==='kampus'?'Perguruan tinggi':'Perusahaan' ?> &middot; <?= $partner['aktif']?'Aktif':'Nonaktif' ?></small></div><?php if(!$previous): ?><a href="<?= e(base_url('panel/mou/form.php')) ?>"><?= panel_icon('reset') ?> Ganti mitra</a><?php endif; ?></div>
<?php if($previous): ?><p class="mou-form-note">Memperpanjang <strong><?= e($previous['nomor_dokumen']) ?></strong> (<?= e($previous['tanggal_mulai'].' ? '.$previous['tanggal_berakhir']) ?>). Dokumen lama tetap tersimpan.</p><?php endif; ?>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?><p>Pilih kembali berkas PDF sebelum mengirim ulang.</p></div><?php endif; ?>
<form class="mou-document-form" method="post" enctype="multipart/form-data">
<?= csrf_field() ?><input type="hidden" name="previous_id" value="<?= e($data['previous_id']) ?>">
<div class="mou-document-columns">
<section class="partner-form-section"><h2><?= panel_icon('identity') ?> Identitas &amp; periode dokumen</h2><div class="partner-field-grid">
<div class="partner-field partner-field-wide"><label for="nomor_dokumen"><?= panel_icon('berita') ?> Nomor dokumen *</label><input id="nomor_dokumen" name="nomor_dokumen" maxlength="190" value="<?= e($data['nomor_dokumen']) ?>" required></div>
<div class="partner-field"><label for="tanggal_mulai"><?= panel_icon('calendar') ?> Mulai berlaku *</label><input id="tanggal_mulai" name="tanggal_mulai" type="date" value="<?= e($data['tanggal_mulai']) ?>" required></div>
<div class="partner-field"><label for="tanggal_berakhir"><?= panel_icon('calendar') ?> Berakhir berlaku *</label><input id="tanggal_berakhir" name="tanggal_berakhir" type="date" value="<?= e($data['tanggal_berakhir']) ?>" required></div>
</div></section>
<section class="partner-form-section"><h2><?= panel_icon('upload') ?> Berkas kerja sama</h2><div class="partner-field"><label for="dokumen"><?= panel_icon('berita') ?> Dokumen MoU (PDF) *</label><input id="dokumen" name="dokumen" type="file" accept=".pdf,application/pdf" required><small>Maksimal 5 MB. Hanya pengelola berizin yang dapat mengunduh berkas.</small></div></section>
</div>
<div class="mou-save-actions"><a href="<?= e(base_url('panel/mou/?mitra_id='.$partnerId)) ?>"><?= panel_icon('back') ?> Batal</a><button type="submit"><?= panel_icon('save') ?> <?= $previous?'Simpan perpanjangan':'Simpan MoU' ?></button></div>
</form><?php endif; ?>
</div>
<?php panel_end($user); ?>
