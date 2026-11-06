<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-classes.php';
$user=require_role('admin','staf_bkk');$ready=industry_ready();$q=input_text($_GET,'q');$status=input_text($_GET,'status');
if(!valid_text($q,100,false)||!in_array($status,['','aktif','arsip'],true)){http_response_code(400);exit('Filter kelas tidak valid.');}
$where='1=1';$params=[];
if($q!==''){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$where.=" AND (c.nama LIKE :nama ESCAPE '!' OR m.nama LIKE :mitra ESCAPE '!' OR c.kelas_sasaran LIKE :sasaran ESCAPE '!')";$params=['nama'=>$pattern,'mitra'=>$pattern,'sasaran'=>$pattern];}
if($status!==''){$where.=' AND c.status=:status';$params['status']=$status;}
$from=' FROM industry_classes c JOIN mitra m ON m.id=c.mitra_id WHERE '.$where;
$total=$ready?(int)panel_query('SELECT COUNT(*)'.$from,$params)->fetchColumn():0;$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=$ready?panel_query("SELECT c.*,m.nama AS mitra_nama,(SELECT COUNT(*) FROM industry_class_meetings a WHERE a.class_id=c.id AND a.status='terlaksana') AS terlaksana".$from.' ORDER BY c.updated_at DESC,c.id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll():[];
panel_start($user,'Kelas Industri','kelas-industri');
?>
<div class="page-actions"><h1>Kelas Industri</h1><a class="button" href="<?= e(base_url('panel/kelas-industri/form.php')) ?>">Tambah kelas industri</a><?php if(user_can($user,'program')): ?><a href="<?= e(base_url('panel/program/deskripsi.php')) ?>">Deskripsi BMW Sakti</a><?php endif; ?></div>
<p>Kelola mitra, pemateri, sasaran, rencana pertemuan, dan pelaksanaannya. Data peserta dan jadwal operasional hanya tersedia bagi pengelola berizin.</p>
<?php if(!$ready): ?><p class="notice">Modul belum aktif. Impor database/updates/021_industry_classes.sql.</p><?php endif; ?>
<form class="filter-form card" method="get"><label>Cari nama kelas, mitra, atau sasaran<input name="q" maxlength="100" value="<?= e($q) ?>"></label><label>Status<select name="status"><?php foreach([''=>'Semua','aktif'=>'Aktif','arsip'=>'Arsip'] as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><button>Tampilkan</button><a href="<?= e(base_url('panel/kelas-industri/')) ?>">Reset</a></form>
<p><?= $total ?> kelas · Halaman <?= $page ?> dari <?= $pages ?></p><div class="table-scroll"><table><thead><tr><th>Nama kelas</th><th>Mitra</th><th>Sasaran</th><th>Terlaksana / rencana</th><th>Status</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?= e($row['nama']) ?></td><td><?= e($row['mitra_nama']) ?></td><td><?= e($row['kelas_sasaran']?:'Peserta terdaftar pada kelas') ?></td><td><strong><?= (int)$row['terlaksana'] ?> / <?= (int)$row['rencana_pertemuan'] ?></strong><br><progress value="<?= (int)$row['terlaksana'] ?>" max="<?= (int)$row['rencana_pertemuan'] ?>" aria-label="Pertemuan terlaksana dibandingkan rencana"></progress></td><td><?= e(ucfirst($row['status'])) ?></td><td><a href="<?= e(base_url('panel/kelas-industri/detail.php?id='.$row['id'])) ?>">Jadwal &amp; pertemuan</a> · <a href="<?= e(base_url('panel/kelas-industri/form.php?id='.$row['id'])) ?>">Edit kelas</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6">Belum ada kelas industri yang sesuai.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman kelas industri"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/kelas-industri/?'.http_build_query(['q'=>$q,'status'=>$status,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
<?php panel_end($user); ?>
