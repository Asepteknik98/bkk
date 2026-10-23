<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/whatsapp.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');
$campaign=$id?panel_query('SELECT c.*,u.nama AS pembuat FROM wa_campaigns c LEFT JOIN users u ON u.id=c.created_by WHERE c.id=:id',['id'=>$id])->fetch():null;
if(!$campaign){panel_not_found();}
$total=(int)panel_query('SELECT COUNT(*) FROM wa_recipients WHERE campaign_id=:id',['id'=>$id])->fetchColumn();$pages=max(1,(int)ceil($total/20));$page=min($pages,positive_id($_GET['page']??'1')??1);
$rows=panel_query('SELECT r.*,o.nama AS opened_name,m.nama AS marked_name FROM wa_recipients r LEFT JOIN users o ON o.id=r.opened_by LEFT JOIN users m ON m.id=r.marked_by WHERE campaign_id=:id ORDER BY r.id LIMIT 20 OFFSET :offset',['id'=>$id,'offset'=>($page-1)*20])->fetchAll();
panel_start($user,'Daftar Pesan WhatsApp','whatsapp');
?>
<div class="content-management wa-page"><div class="page-actions"><h1><?= panel_icon('whatsapp') ?> <?= e($campaign['judul']) ?></h1><a href="<?= e(base_url('panel/whatsapp/')) ?>">Kembali</a></div><p><?= $total ?> penerima &middot; Dibuat <?= e($campaign['created_at']) ?> WIB oleh <?= e($campaign['pembuat']??'Pengelola') ?></p>
<ol class="wa-steps"><li><span>1</span> Klik Buka WhatsApp</li><li><span>2</span> Periksa pesan lalu Kirim di WhatsApp</li><li><span>3</span> Kembali dan tandai sudah dikirim</li></ol><p class="wa-intro">Setiap tombol membuka satu percakapan. Tanda dikirim adalah catatan Anda, bukan konfirmasi dari WhatsApp.</p>
<details class="card wa-template"><summary>Template pesan</summary><p><?= e($campaign['pesan']) ?></p><?php if($campaign['tautan']):?><p><?= e($campaign['tautan']) ?></p><?php endif;?></details>
<div class="table-scroll" tabindex="0" aria-label="Daftar pesan penerima"><table><thead><tr><th>Penerima</th><th>Pesan siap kirim</th><th>Riwayat (WIB)</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach($rows as $row):?><tr><td><strong><?= e($row['nama']) ?></strong><small><?= e($row['nomor']) ?></small></td><td><details><summary>Lihat pesan</summary><p class="wa-message-preview"><?= e($row['pesan']) ?></p></details></td><td><?php if($row['opened_at']):?>Dibuka: <?= e($row['opened_at']) ?><small><?= e($row['opened_name']??'Pengelola') ?></small><?php else:?>Belum dibuka<?php endif;?><?php if($row['marked_at']):?><small>Ditandai dikirim: <?= e($row['marked_at']) ?> oleh <?= e($row['marked_name']??'Pengelola') ?></small><?php endif;?></td><td><div class="wa-row-actions">
<form method="post" action="<?= e(base_url('panel/whatsapp/tindakan.php')) ?>" target="_blank"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="action" value="open"><button type="submit"><?= panel_icon('whatsapp') ?> Buka WhatsApp</button></form>
<form method="post" action="<?= e(base_url('panel/whatsapp/tindakan.php')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="action" value="<?= $row['marked_at']?'unmark':'mark' ?>"><input type="hidden" name="page" value="<?= $page ?>"><button type="submit" class="button secondary"><?= $row['marked_at']?'Batalkan tanda':'Tandai sudah dikirim' ?></button></form></div></td></tr><?php endforeach;?></tbody></table></div>
<?php if($pages>1):?><nav class="pagination" aria-label="Halaman pesan"><?php foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target):if($target<1||$target>$pages){continue;}?><a href="<?= e(base_url('panel/whatsapp/detail.php?'.http_build_query(['id'=>$id,'page'=>$target]))) ?>"><?= $label ?></a><?php endforeach;?></nav><?php endif;?><p>Halaman <?= $page ?>/<?= $pages ?>. Muat ulang halaman untuk memperbarui catatan dibuka.</p></div>
<?php panel_end($user); ?>
