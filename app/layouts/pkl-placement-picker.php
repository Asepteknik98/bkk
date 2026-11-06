<?php
if(!isset($user,$pickerAction,$pickerParams)){http_response_code(403);exit;}
$query=input_text($_GET,'q');if(!valid_text($query,100,false)){http_response_code(400);exit('Pencarian terlalu panjang.');}
$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$query).'%';
$placements=panel_query("SELECT p.id,p.tanggal_mulai,p.tanggal_selesai,p.tempat_pkl,s.nama,s.nis FROM pkl_placements p JOIN siswa s ON s.id=p.siswa_id WHERE s.nama LIKE :nama ESCAPE '!' OR s.nis LIKE :nis ESCAPE '!' OR p.tempat_pkl LIKE :tempat ESCAPE '!' ORDER BY p.tanggal_mulai DESC,p.id DESC LIMIT 50",['nama'=>$pattern,'nis'=>$pattern,'tempat'=>$pattern])->fetchAll();
?>
<p>Pilih penempatan siswa dan periode PKL. Maksimal 50 hasil; gunakan pencarian nama, NIS, atau tempat PKL.</p>
<form class="filter-form card" method="get" action="<?= e(base_url($pickerAction)) ?>"><?php foreach($pickerParams as $key=>$value): ?><input type="hidden" name="<?= e($key) ?>" value="<?= e($value) ?>"><?php endforeach; ?><label>Cari penempatan<input name="q" maxlength="100" value="<?= e($query) ?>"></label><button>Cari</button></form>
<div class="table-scroll"><table><thead><tr><th>Siswa</th><th>Periode PKL</th><th>Tempat</th><th>Pilih</th></tr></thead><tbody><?php foreach($placements as $item): ?><tr><td><?= e($item['nama'].' / '.$item['nis']) ?></td><td><?= e($item['tanggal_mulai'].' – '.$item['tanggal_selesai']) ?></td><td><?= e($item['tempat_pkl']) ?></td><td><a href="<?= e(base_url($pickerAction.'?'.http_build_query($pickerParams+['placement_id'=>$item['id']]))) ?>">Pilih penempatan</a></td></tr><?php endforeach; ?><?php if(!$placements): ?><tr><td colspan="4">Penempatan tidak ditemukan. Buat melalui modul PKL terlebih dahulu.</td></tr><?php endif; ?></tbody></table></div>
