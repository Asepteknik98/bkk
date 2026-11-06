<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/mou.php';
$user=require_role('admin','staf_bkk');mou_require_ready();
$partnerId=positive_id($_GET['mitra_id']??'');if(isset($_GET['mitra_id'])&&$_GET['mitra_id']!==''&&!$partnerId){panel_not_found();}
$partner=null;if($partnerId){try{$partner=partner_find($partnerId);}catch(InvalidArgumentException){panel_not_found();}}
$q=input_text($_GET,'q');$status=input_text($_GET,'status');
if(!valid_text($q,100,false)||!in_array($status,['','belum','aktif','kedaluwarsa'],true)){http_response_code(400);exit('Filter MoU tidak valid.');}
$asOf=new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta'));$today=mou_today($asOf);$where='1=1';$params=[];
if($partnerId){$where.=' AND d.mitra_id=:partner';$params['partner']=$partnerId;}
if($q!==''){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$where.=" AND (d.nomor_dokumen LIKE :nomor ESCAPE '!' OR m.nama LIKE :nama ESCAPE '!')";$params+=['nomor'=>$pattern,'nama'=>$pattern];}
if($status==='belum'){$where.=' AND d.tanggal_mulai>:today';$params['today']=$today;}
elseif($status==='kedaluwarsa'){$where.=' AND d.tanggal_berakhir<:today';$params['today']=$today;}
elseif($status==='aktif'){$where.=' AND d.tanggal_mulai<=:start_day AND d.tanggal_berakhir>=:end_day';$params+=['start_day'=>$today,'end_day'=>$today];}
$from=' FROM mitra_mous d JOIN mitra m ON m.id=d.mitra_id WHERE '.$where;
$total=(int)panel_query('SELECT COUNT(*)'.$from,$params)->fetchColumn();$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT d.*,m.nama'.$from.' ORDER BY d.tanggal_berakhir DESC,d.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll();
$filters=['mitra_id'=>$partnerId?(string)$partnerId:'','q'=>$q,'status'=>$status];
panel_start($user,'Riwayat MoU','mou');
?>
<div class="page-actions"><h1>Riwayat MoU<?= $partner?': '.e($partner['nama']):'' ?></h1><a class="button" href="<?= e(base_url('panel/mou/form.php'.($partnerId?'?mitra_id='.$partnerId:''))) ?>">Tambah MoU</a><a href="<?= e(base_url('panel/mitra/')) ?>">Data mitra</a><?php if($partnerId): ?><a href="<?= e(base_url('panel/mitra/detail.php?id='.$partnerId)) ?>">Profil / PIC mitra</a><a href="<?= e(base_url('panel/mou/')) ?>">Semua MoU</a><?php endif; ?></div>
<p>Status dihitung otomatis menurut tanggal <?= e($today) ?> (WIB), termasuk seluruh hari tanggal berakhir. Perpanjangan membuat catatan dan berkas baru; dokumen lama tetap tersedia.</p>
<form class="filter-form card" method="get"><?php if($partnerId): ?><input type="hidden" name="mitra_id" value="<?= $partnerId ?>"><?php endif; ?><label>Cari nomor dokumen / nama mitra<input name="q" maxlength="100" value="<?= e($q) ?>"></label><label>Status<select name="status"><?php foreach([''=>'Semua','belum'=>'Belum Berlaku','aktif'=>'Aktif','kedaluwarsa'=>'Kedaluwarsa'] as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button>Tampilkan</button><a href="<?= e(base_url('panel/mou/'.($partnerId?'?mitra_id='.$partnerId:''))) ?>">Reset</a></form>
<p><?= $total ?> dokumen · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Mitra</th><th>Nomor dokumen</th><th>Mulai</th><th>Berakhir</th><th>Status</th><th>Riwayat</th><th>Berkas</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><a href="<?= e(base_url('panel/mou/?mitra_id='.$row['mitra_id'])) ?>"><?= e($row['nama']) ?></a></td><td><?= e($row['nomor_dokumen']) ?></td><td><?= e($row['tanggal_mulai']) ?></td><td><?= e($row['tanggal_berakhir']) ?></td><td><?= e(mou_status($row['tanggal_mulai'],$row['tanggal_berakhir'],$asOf)) ?></td><td>Catatan #<?= (int)$row['id'] ?><?php if($row['previous_id']): ?><br><a href="<?= e(base_url('panel/mou/unduh.php?id='.$row['previous_id'])) ?>">MoU sebelumnya #<?= (int)$row['previous_id'] ?></a><?php endif; ?><br><a href="<?= e(base_url('panel/mou/form.php?mitra_id='.$row['mitra_id'].'&previous_id='.$row['id'])) ?>">Perpanjang</a></td><td><a href="<?= e(base_url('panel/mou/unduh.php?id='.$row['id'])) ?>">Unduh PDF</a><br><?= e($row['nama_asli']) ?><br><?= number_format($row['ukuran']/1024,1) ?> KB</td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7">Belum ada MoU untuk pilihan ini.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman MoU"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/mou/?'.http_build_query($filters+['page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
<?php panel_end($user); ?>
