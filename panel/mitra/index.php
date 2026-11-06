<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/partners.php';
$user=require_role('admin','staf_bkk');
$campusDirectory=$campusDirectory??false;$ready=partner_profiles_ready();
$q=input_text($_GET,'q');$kind=$campusDirectory?'kampus':input_text($_GET,'jenis');$cluster=input_text($_GET,'klaster');
if(!valid_text($q,100,false)||!in_array($kind,['','perusahaan','kampus'],true)||!in_array($cluster,['','dalam_negeri','luar_negeri','belum'],true)){http_response_code(400);exit('Filter mitra tidak valid.');}
$where='1=1';$params=[];
if($q!==''){$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';$where.=" AND m.nama LIKE :nama ESCAPE '!'";$params['nama']=$pattern;}
if($kind!==''){$where.=' AND m.jenis=:jenis';$params['jenis']=$kind;}
if($campusDirectory){$where.=$ready?" AND p.negara='Indonesia'":' AND 1=0';}
if($cluster!==''){
    $where.=" AND m.jenis='perusahaan'";
    if($cluster==='belum'){$where.=$ready?" AND (p.klaster IS NULL OR p.klaster='')":'';}
    elseif($ready){$where.=' AND p.klaster=:klaster';$params['klaster']=$cluster;}else{$where.=' AND 1=0';}
}
$from=' FROM mitra m'.($ready?' LEFT JOIN mitra_profiles p ON p.mitra_id=m.id':'').' WHERE '.$where;
$total=(int)panel_query('SELECT COUNT(*)'.$from,$params)->fetchColumn();$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$extra=$ready?'p.klaster,p.negara,p.alamat_lengkap,p.pic_nama,p.pic_kontak':"NULL AS klaster,'' AS negara,'' AS alamat_lengkap,'' AS pic_nama,'' AS pic_kontak";
$items=panel_query('SELECT m.*,'.$extra.$from.' ORDER BY m.nama,m.id LIMIT :limit OFFSET :offset',$params+['limit'=>20,'offset'=>($page-1)*20])->fetchAll();
$path=$campusDirectory?'panel/mitra/kampus.php':'panel/mitra/';$filters=['q'=>$q,'jenis'=>$kind,'klaster'=>$cluster];
panel_start($user,$campusDirectory?'Kampus Dalam Negeri':'Mitra','mitra');
?>
<div class="partners-admin"><div class="page-actions"><h1><?= $campusDirectory?'Direktori Kampus Dalam Negeri':'Mitra industri & kampus' ?></h1><a class="button" href="<?= e(base_url('panel/mitra/tambah.php'.($campusDirectory?'?jenis=kampus':''))) ?>">Tambah <?= $campusDirectory?'kampus':'mitra' ?></a><a href="<?= e(base_url($campusDirectory?'panel/mitra/':'panel/mitra/kampus.php')) ?>"><?= $campusDirectory?'Semua mitra':'Kampus Dalam Negeri' ?></a><?php if(user_can($user,'mou')): ?><a href="<?= e(base_url('panel/mou/')) ?>">Riwayat MoU</a><?php endif; ?></div>
<p class="partners-admin-note">Hanya logo, website, dan deskripsi mitra aktif ditampilkan di beranda publik. PIC dan MoU merupakan data internal.</p>
<?php if(!$ready): ?><p class="notice">Profil lengkap memerlukan pembaruan 022. Data mitra lama tetap tersedia.</p><?php endif; ?>
<?php if($campusDirectory): ?><p>Direktori ini hanya memuat kampus dengan negara Indonesia yang telah diisi. Lengkapi negara kampus lama melalui Semua mitra.</p><?php endif; ?>
<form class="filter-form card" method="get"><label>Cari nama<input name="q" maxlength="100" value="<?= e($q) ?>"></label><?php if(!$campusDirectory): ?><label>Jenis<select name="jenis"><?php foreach([''=>'Semua','perusahaan'=>'Perusahaan','kampus'=>'Kampus'] as $key=>$label): ?><option value="<?= $key ?>" <?= $kind===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><label>Klaster<select name="klaster"><option value="">Semua</option><?php foreach(partner_clusters()+['belum'=>'Belum diklasifikasikan'] as $key=>$label): ?><option value="<?= $key ?>" <?= $cluster===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><?php endif; ?><button>Tampilkan</button><a href="<?= e(base_url($path)) ?>">Reset</a></form>
<p><?= $total ?> mitra · Halaman <?= $page ?> dari <?= $pages ?></p>
<div class="table-scroll"><table><thead><tr><th>Nama / website</th><th>Jenis / klaster</th><th>Alamat / negara</th><th>PIC (internal)</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $item): ?><tr><td><div class="partner-admin-identity"><?php if($item['logo']&&managed_upload_path($item['logo'])): ?><img src="<?= e(base_url($item['logo'])) ?>" alt="" width="32" height="32"><?php endif; ?><strong><?= e($item['nama']) ?></strong></div><?php if($item['website']&&http_url($item['website'])): ?><a href="<?= e($item['website']) ?>" target="_blank" rel="noopener noreferrer">Website resmi</a><?php endif; ?></td><td><?= e($item['jenis']==='kampus'?'Kampus':(partner_clusters()[$item['klaster']]??'Perusahaan: belum diklasifikasikan')) ?></td><td><?= nl2br(e($item['alamat_lengkap']?:'Alamat belum diisi')) ?><br><?= e($item['negara']?:'Negara belum diisi') ?></td><td><?= e($item['pic_nama']?:'Belum diisi') ?><br><?= e($item['pic_kontak']) ?></td><td><?= $item['aktif']?'Aktif':'Nonaktif' ?></td><td><div class="row-actions"><a href="<?= e(base_url('panel/mitra/detail.php?id='.$item['id'])) ?>">Detail / PIC</a><a href="<?= e(base_url('panel/mitra/edit.php?id='.$item['id'])) ?>">Edit</a><?php if(user_can($user,'mou')): ?><a href="<?= e(base_url('panel/mou/?mitra_id='.$item['id'])) ?>">MoU</a><?php endif; ?><form method="post" action="<?= e(base_url('panel/mitra/hapus.php')) ?>" data-confirm="Hapus mitra ini? Logo tetap di katalog; mitra dengan riwayat MoU atau relasi kelas tidak dapat dihapus."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $item['id'] ?>"><button type="submit" class="partner-delete">Hapus</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$items): ?><tr><td colspan="6">Belum ada mitra untuk pilihan ini.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Halaman mitra"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;} ?><a href="<?= e(base_url($path.'?'.http_build_query($filters+['page'=>$target]))) ?>"><?= $label ?></a><?php endforeach; ?></nav>
</div>
<?php panel_end($user); ?>
