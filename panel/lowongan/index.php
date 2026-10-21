<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/vacancies.php';
$user=require_role('admin','staf_bkk');
$q=input_text($_GET,'q');if(!valid_text($q,100,false)){$q='';}
$filter=input_text($_GET,'status');$params=[];$where='1=1';
if($q!==''){$where.=" AND (perusahaan LIKE :company ESCAPE '!' OR posisi LIKE :position ESCAPE '!')";$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$params=['company'=>$pattern,'position'=>$pattern];}
if(in_array($filter,['draft','published'],true)){$where.=' AND status=:status';$params['status']=$filter;}
$total=(int)panel_query('SELECT COUNT(*) FROM lowongan WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT * FROM lowongan WHERE '.$where.' ORDER BY updated_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>10,'offset'=>($page-1)*10])->fetchAll();
panel_start($user,'Kelola Lowongan','lowongan');
?>
<div class="page-actions"><h1>Lowongan Kerja</h1><a class="button" href="<?= e(base_url('panel/lowongan/tambah.php')) ?>">+ Tambah lowongan</a></div>
<form class="filter-form card" method="get"><label for="q">Cari posisi/perusahaan<input id="q" name="q" maxlength="100" value="<?= e($q) ?>"></label><label for="status">Status<select id="status" name="status"><option value="">Semua status</option><?php foreach(['draft','published'] as $status):?><option value="<?= $status ?>" <?= $filter===$status?'selected':'' ?>><?= $status ?></option><?php endforeach;?></select></label><button type="submit">Cari</button><a href="<?= e(base_url('panel/lowongan/')) ?>">Reset</a></form>
<p><?= $total ?> lowongan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Posisi / Perusahaan</th><th>Lokasi / Jenis</th><th>Status</th><th>Batas lamaran (WIB)</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach($rows as $row):?><tr><td><strong><?= e($row['posisi']) ?></strong><small><?= e($row['perusahaan']) ?></small></td><td><?= e($row['lokasi']) ?><small><?= e($row['jenis_pekerjaan']) ?></small></td><td><span class="badge"><?= e($row['status']) ?></span><small><?= e(job_status_label($row)) ?></small></td><td><?= e($row['batas_lamaran']??'Belum ditentukan') ?><?= $row['batas_lamaran']?' · 23:59:59 WIB':'' ?></td><td><div class="row-actions"><a href="<?= e(base_url('panel/lowongan/edit.php?id='.$row['id'])) ?>">Edit</a><?php if($row['status']==='published'):?><a href="<?= e(job_url($row)) ?>">Lihat publik</a><?php endif;?><form method="post" action="<?= e(base_url('panel/lowongan/hapus.php')) ?>" data-confirm="Hapus lowongan ini? Poster tetap disimpan di katalog media."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $row['id'] ?>"><button type="submit" class="danger small-button">Hapus</button></form></div></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="5" class="empty-panel">Belum ada lowongan yang sesuai.</td></tr><?php endif;?></tbody></table></div>
<?php if($pages>1):?><nav class="pagination" aria-label="Halaman lowongan"><?php foreach(['← Sebelumnya'=>$page-1,'Berikutnya →'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/lowongan/?'.http_build_query(['q'=>$q,'status'=>$filter,'page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav><?php endif;?>
<?php panel_end($user); ?>
