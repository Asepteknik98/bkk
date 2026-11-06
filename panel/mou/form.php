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
panel_start($user,$previous?'Perpanjangan MoU':'Tambah MoU','mou');
?>
<div class="page-actions"><h1><?= $previous?'Perpanjangan':'Tambah' ?> MoU</h1><a href="<?= e(base_url('panel/mou/'.($partnerId?'?mitra_id='.$partnerId:''))) ?>">Riwayat MoU</a></div>
<?php if(!$partner):
$search=input_text($_GET,'q');if(!valid_text($search,100,false)){http_response_code(400);exit('Pencarian terlalu panjang.');}
$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$search).'%';$partners=panel_query("SELECT id,nama,jenis FROM mitra WHERE nama LIKE :nama ESCAPE '!' ORDER BY nama,id LIMIT 50",['nama'=>$pattern])->fetchAll();
?><p>Pilih mitra. Maksimal 50 hasil; gunakan pencarian untuk menemukan mitra lain.</p><form class="filter-form card" method="get"><label>Cari nama mitra<input name="q" maxlength="100" value="<?= e($search) ?>"></label><button>Cari</button></form><ul><?php foreach($partners as $item): ?><li><a href="<?= e(base_url('panel/mou/form.php?mitra_id='.$item['id'])) ?>"><?= e($item['nama']) ?></a> (<?= e($item['jenis']) ?>)</li><?php endforeach; ?></ul>
<?php else: ?>
<h2><?= e($partner['nama']) ?></h2><p>Setiap dokumen disimpan sebagai catatan tersendiri. Periksa nomor dan tanggal sebelum menyimpan; dokumen yang tersimpan tidak ditimpa melalui halaman ini.</p>
<?php if($previous): ?><p>MoU sebelumnya #<?= (int)$previous['id'] ?>: <?= e($previous['nomor_dokumen']) ?>, <?= e($previous['tanggal_mulai'].' – '.$previous['tanggal_berakhir']) ?>.</p><?php endif; ?>
<?php if($errors): ?><div class="notice" role="alert"><?php foreach($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?><p>Pilih kembali berkas PDF jika perlu mengirim ulang.</p></div><?php endif; ?>
<form class="editor-form card" method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="previous_id" value="<?= e($data['previous_id']) ?>">
<label for="nomor_dokumen">Nomor dokumen<input id="nomor_dokumen" name="nomor_dokumen" maxlength="190" value="<?= e($data['nomor_dokumen']) ?>" required></label><label for="tanggal_mulai">Tanggal mulai<input id="tanggal_mulai" name="tanggal_mulai" type="date" value="<?= e($data['tanggal_mulai']) ?>" required></label><label for="tanggal_berakhir">Tanggal berakhir<input id="tanggal_berakhir" name="tanggal_berakhir" type="date" value="<?= e($data['tanggal_berakhir']) ?>" required></label>
<label for="dokumen">Berkas MoU PDF (maksimal 5 MB)<input id="dokumen" name="dokumen" type="file" accept=".pdf,application/pdf" required></label><p>Status dihitung otomatis berdasarkan tanggal mulai dan berakhir, menggunakan WIB. Berkas hanya dapat diunduh oleh pengelola dengan izin MoU dan Mitra.</p><button>Simpan sebagai riwayat baru</button></form><?php endif; ?>
<?php panel_end($user); ?>
