<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/programs.php';
$user=require_role('admin','staf_bkk');$categories=program_categories();
$q=input_text($_GET,'q');if(!valid_text($q,100,false)){$q='';}
$categoryFilter=input_text($_GET,'kategori');$statusFilter=input_text($_GET,'status');$params=[];$where='1=1';
if($categoryFilter!==''&&!isset($categories[$categoryFilter])){http_response_code(400);exit('Kategori program tidak diizinkan.');}
if($q!==''){$where.=" AND (judul LIKE :judul ESCAPE '!' OR slug LIKE :slug ESCAPE '!')";$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$params=['judul'=>$pattern,'slug'=>$pattern];}
if($categoryFilter!==''){$where.=' AND kategori=:kategori';$params['kategori']=$categoryFilter;}
if(in_array($statusFilter,['draft','published'],true)){$where.=' AND status=:status';$params['status']=$statusFilter;}
$total=(int)panel_query('SELECT COUNT(*) FROM program WHERE '.$where,$params)->fetchColumn();$pages=max(1,(int)ceil($total/10));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT * FROM program WHERE '.$where.' ORDER BY updated_at DESC,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>10,'offset'=>($page-1)*10])->fetchAll();
panel_start($user,'Kelola Program BKK','program');
?>
<div class="content-management program-management">
<div class="page-actions"><h1><?= e($categoryFilter!==''?$categories[$categoryFilter]['title']:'Program BKK') ?></h1><a href="<?= e(base_url('panel/program-minat/?kategori='.$categoryFilter)) ?>">Minat &amp; pertanyaan</a><a class="button" href="<?= e(base_url('panel/program/tambah.php?'.http_build_query(['kategori'=>$categoryFilter]))) ?>">+ Tambah program</a></div>
<form method="get" class="filter-form card program-filters"><label for="q">Cari program<input type="search" placeholder="Judul atau slug program" id="q" name="q" maxlength="100" value="<?= e($q) ?>"></label><label for="kategori">Kategori<select id="kategori" name="kategori"><option value="">Semua kategori</option><?php foreach($categories as $value=>$info):?><option value="<?= e($value) ?>" <?= $categoryFilter===$value?'selected':'' ?>><?= e($info['title']) ?></option><?php endforeach;?></select></label><label for="status">Status<select id="status" name="status"><option value="">Semua status</option><?php foreach(['draft','published'] as $status):?><option value="<?= $status ?>" <?= $statusFilter===$status?'selected':'' ?>><?= $status ?></option><?php endforeach;?></select></label><button type="submit">Cari</button><a class="filter-reset" href="<?= e(base_url('panel/program/')) ?>">Reset</a></form>
<p><?= $total ?> program · Halaman <?= $page ?> dari <?= $pages ?></p><div class="table-scroll"><table><thead><tr><th>Judul</th><th>Kategori</th><th>Status</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><strong><?= e($row['judul']) ?></strong><small><?= e($row['slug']) ?></small></td><td><?= e($categories[$row['kategori']]['title']) ?></td><td><span class="badge"><?= e($row['status']) ?></span></td><td><div class="row-actions"><a href="<?= e(base_url('panel/program/edit.php?id='.$row['id'])) ?>">Edit</a><?php if($row['status']==='published'):?><a href="<?= e(program_url($row)) ?>">Lihat publik</a><?php endif;?><form method="post" action="<?= e(base_url('panel/program/hapus.php')) ?>" data-confirm="Hapus program ini? Gambar tetap tersedia di katalog media."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $row['id'] ?>"><button type="submit" class="danger small-button">Hapus</button></form></div></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="4" class="empty-panel">Belum ada program yang sesuai.</td></tr><?php endif;?></tbody></table></div>
<?php if($pages>1):?><nav class="pagination" aria-label="Halaman program"><?php foreach(['← Sebelumnya'=>$page-1,'Berikutnya →'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/program/?'.http_build_query(['q'=>$q,'kategori'=>$categoryFilter,'status'=>$statusFilter,'page'=>$target]))) ?>"><?= e($label) ?></a><?php endforeach;?></nav><?php endif;?>
</div>
<?php panel_end($user);?>
