<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/industry-forums.php';
$user=require_role('admin','staf_bkk');$ready=industry_forums_ready();$q=input_text($_GET,'q');$fromDate=input_text($_GET,'dari');$toDate=input_text($_GET,'sampai');
if(!valid_text($q,100,false)||($fromDate!==''&&!pkl_date($fromDate))||($toDate!==''&&!pkl_date($toDate))||($fromDate!==''&&$toDate!==''&&$fromDate>$toDate)){http_response_code(400);exit('Filter sarasehan tidak valid.');}
$where='1=1';$params=[];
if($q!==''){$where.=" AND nama LIKE :nama ESCAPE '!'";$params['nama']='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';}
if($fromDate!==''){$where.=' AND tanggal>=:dari';$params['dari']=$fromDate;}if($toDate!==''){$where.=' AND tanggal<=:sampai';$params['sampai']=$toDate;}
$total=$ready?(int)panel_query('SELECT COUNT(*) FROM industry_forums WHERE '.$where,$params)->fetchColumn():0;$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=$ready?panel_query('SELECT * FROM industry_forums WHERE '.$where.' ORDER BY tanggal,jam,id LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll():[];
panel_start($user,'Sarasehan Industri','sarasehan');
?>
<div class="page-actions"><h1>Jadwal Sarasehan Industri</h1><a class="button" href="<?= e(base_url('panel/sarasehan/form.php')) ?>">Tambah jadwal</a></div>
<?php if(!$ready): ?><p class="notice">Impor database/updates/023_industry_forums_ideas.sql untuk mengaktifkan modul.</p><?php endif; ?>
<form class="filter-form card" method="get"><label>Cari kegiatan<input name="q" maxlength="100" value="<?= e($q) ?>"></label><label>Dari tanggal<input name="dari" type="date" value="<?= e($fromDate) ?>"></label><label>Sampai tanggal<input name="sampai" type="date" value="<?= e($toDate) ?>"></label><button>Tampilkan</button><a href="<?= e(base_url('panel/sarasehan/')) ?>">Reset</a></form>
<p><?= $total ?> kegiatan · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Nama kegiatan</th><th>Jadwal WIB</th><th>Lokasi / pertemuan</th><th>PIC sekolah</th><th>Tindakan</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?= e($row['nama']) ?></td><td><?= e($row['tanggal'].' '.substr($row['jam'],0,5)) ?></td><td><?= e($row['lokasi']?:'Pertemuan daring') ?><?php if($row['tautan']&&http_url($row['tautan'])): ?><br><a href="<?= e($row['tautan']) ?>" target="_blank" rel="noopener noreferrer">Tautan pertemuan</a><?php endif; ?></td><td><?= e($row['pic_sekolah']) ?></td><td><a href="<?= e(base_url('panel/sarasehan/detail.php?id='.$row['id'])) ?>">Detail undangan / PIC</a> · <a href="<?= e(base_url('panel/sarasehan/form.php?id='.$row['id'])) ?>">Edit</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5">Belum ada jadwal yang sesuai.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman sarasehan"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url('panel/sarasehan/?'.http_build_query(['q'=>$q,'dari'=>$fromDate,'sampai'=>$toDate,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
<?php panel_end($user); ?>
