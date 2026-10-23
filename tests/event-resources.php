<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/app/helpers/event-resources.php';
ob_start();set_exception_handler(static function(Throwable $e){fwrite(STDERR,'FAIL '.get_class($e).' line '.$e->getLine().': '.$e->getMessage().PHP_EOL);exit(1);});
function check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$base=base_url();if(str_starts_with($base,'/')){$base='http://localhost'.$base;}if(!in_array(parse_url($base,PHP_URL_HOST),['localhost','127.0.0.1'],true)){throw new RuntimeException('Hanya server lokal');}
$marker='resource-test-'.bin2hex(random_bytes(5));$userId=null;$sessionFile=null;$eventId=null;$pngPath=sys_get_temp_dir().'/'.$marker.'.png';$badPath=sys_get_temp_dir().'/'.$marker.'.svg';
$im=imagecreatetruecolor(4,4);imagepng($im,$pngPath);imagedestroy($im);file_put_contents($badPath,'<svg xmlns="http://www.w3.org/2000/svg"></svg>');
try{
start_app_session();panel_query('INSERT INTO users(nama,username,email,password_hash,role,aktif) VALUES(:nama,:username,:email,:hash,:role,1)',['nama'=>'Uji publikasi','username'=>$marker,'email'=>$marker.'@example.test','hash'=>password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT),'role'=>'admin']);$userId=(int)db()->lastInsertId();
$_SESSION=['user_id'=>$userId,'auth_version'=>0,'csrf_token'=>bin2hex(random_bytes(32))];$csrf=$_SESSION['csrf_token'];$cookie=session_name().'='.session_id();$sessionFile=session_save_path().'/sess_'.session_id();session_write_close();
$request=static function(string $path,?array $post=null,bool $auth=true)use($base,$cookie):array{$headers=[];$ch=curl_init(rtrim($base,'/').'/'.$path);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>20,CURLOPT_HEADERFUNCTION=>static function($ch,$line)use(&$headers){$parts=explode(':',$line,2);if(count($parts)===2){$headers[strtolower(trim($parts[0]))]=trim($parts[1]);}return strlen($line);}]);if($auth){curl_setopt($ch,CURLOPT_COOKIE,$cookie);}if($post!==null){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,$post);}$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$error=curl_error($ch);curl_close($ch);if($body===false){throw new RuntimeException($error);}return [$code,$body,$headers];};
participant_save(participant_input(['nis'=>$marker,'nama'=>'RAHASIA-NAMA-'.$marker,'kelas'=>'XI A','angkatan'=>'2025','kelas_industri'=>$marker,'email'=>'private-'.$marker.'@example.test']));
$eventId=attendance_create(attendance_input(['judul'=>$marker,'tanggal'=>'2026-10-24','angkatan'=>'2025','kelas_industri'=>$marker,'deskripsi'=>'Catatan internal RAHASIA']),$userId);
$admin='panel/absensi/publikasi.php?id='.$eventId;$public='kegiatan.php?id='.$eventId;
[$code,$html]=$request($admin);check($code===200&&str_contains($html,'Aktifkan pretest untuk pengguna'),'Form pengaturan admin tersedia');
[$code]=$request($public,null,false);check($code===404,'Kegiatan dengan default nonaktif tidak dipublikasikan');
[$code,$html]=$request($admin,['csrf_token'=>$csrf,'version'=>'0','pretest_enabled'=>'1']);check($code===200&&str_contains($html,'Isi link pretest'),'Aktifkan pretest tanpa URL ditolak');
foreach(['javascript:alert(1)','https://user:password@example.test/quiz',"https://example.test/quiz\r\nX-Test:bad"] as $url){[$code,$html]=$request($admin,['csrf_token'=>$csrf,'version'=>'0','pretest_url'=>$url]);check($code===200&&str_contains($html,'Link pretest harus'),'URL tidak valid ditolak');}
[$code,$html]=$request($admin,['csrf_token'=>$csrf,'version'=>'0','poster_enabled'=>'1']);check($code===200&&str_contains($html,'Upload poster sebelum'),'Aktifkan poster tanpa berkas ditolak');
$url='https://example.test/quiz?class=A&batch=2025';$post=['csrf_token'=>$csrf,'version'=>'0','pretest_url'=>$url];
[$code]=$request($admin,$post+['poster'=>new CURLFile($pngPath,'image/png','poster.png')]);$row=panel_query('SELECT * FROM event_resources WHERE event_id=:id',['id'=>$eventId])->fetch();check($code===303&&$row['pretest_enabled']===0&&$row['poster_enabled']===0,'Link dan poster dapat disimpan nonaktif');$old=$row['poster_file'];
[$code]=$request('panel/absensi/poster.php?id='.$eventId);check($code===200,'Pengelola dapat preview poster nonaktif');
[$code]=$request('poster-kegiatan.php?id='.$eventId,null,false);check($code===404,'Poster nonaktif ditolak melalui tautan publik langsung');
[$code]=$request('storage/event-posters/'.$old,null,false);check($code===403,'Berkas poster tidak bisa dibuka langsung dari storage');
[$code]=$request($admin,array_replace($post,['version'=>'1','pretest_enabled'=>'1']));check($code===303,'Aktifkan pretest saja');
[$code,$html]=$request($public,null,false);check($code===200&&str_contains($html,'Buka pretest')&&!str_contains($html,'poster-kegiatan.php?id='),'Pretest saja terlihat ketika poster nonaktif');
[$code,$body,$headers]=$request('pretest.php?id='.$eventId,null,false);check($code===302&&$headers['location']===$url&&str_contains($headers['cache-control'],'no-store'),'Pretest aktif mengarah ke URL tersimpan tanpa cache');
[$code]=$request($admin,array_replace($post,['version'=>'2','poster_enabled'=>'1']));check($code===303,'Aktifkan poster saja');
[$code,$html]=$request($public,null,false);check($code===200&&!str_contains($html,'Buka pretest')&&str_contains($html,'poster-kegiatan.php?id='),'Poster aktif independen dari pretest');
check(!str_contains($html,'RAHASIA-NAMA')&&!str_contains($html,'Catatan internal RAHASIA')&&!str_contains($html,'private-'.$marker),'Halaman publik tidak memuat data peserta atau catatan internal');
[$code]=$request('pretest.php?id='.$eventId,null,false);check($code===404,'Pretest yang dimatikan ditolak melalui tautan lama');
[$code,$body,$headers]=$request('poster-kegiatan.php?id='.$eventId,null,false);check($code===200&&getimagesizefromstring($body)['mime']==='image/png'&&str_contains($headers['cache-control'],'no-store'),'Poster aktif dapat dibuka publik tanpa cache');
[$code]=$request($admin,array_replace($post,['version'=>'3','pretest_enabled'=>'1','poster_enabled'=>'1']));check($code===303,'Pretest dan poster dapat aktif bersamaan');
[$code,$html]=$request('kegiatan.php?'.http_build_query(['q'=>$marker,'angkatan'=>'2025','kelas_industri'=>$marker]),null,false);check($code===200&&str_contains($html,'Buka pretest')&&str_contains($html,$marker),'Daftar publik mengikuti filter kelompok dan kegiatan');
[$code]=$request($admin,array_replace($post,['version'=>'4','pretest_enabled'=>'1','poster_enabled'=>'1','poster'=>new CURLFile($pngPath,'image/png','poster-baru.png')]));$row=panel_query('SELECT * FROM event_resources WHERE event_id=:id',['id'=>$eventId])->fetch();check($code===303&&$row['poster_file']!==$old&&event_poster_path($old)===null,'Ganti poster membersihkan berkas lama');
$before=count(glob(APP_ROOT.'/storage/event-posters/*'));
[$code,$html]=$request($admin,array_replace($post,['version'=>'4','poster'=>new CURLFile($pngPath,'image/png','stale.png')]));check($code===200&&str_contains($html,'Pengaturan sudah diubah')&&count(glob(APP_ROOT.'/storage/event-posters/*'))===$before,'Versi lama ditolak dan poster baru dibersihkan');
[$code,$html]=$request($admin,array_replace($post,['version'=>'5','poster'=>new CURLFile($badPath,'image/png','palsu.png')]));check($code===200&&str_contains($html,'Poster harus JPG'),'Gambar palsu ditolak');
[$code]=$request($admin,array_replace($post,['version'=>'5','pretest_enabled'=>'1','poster_enabled'=>'1','hapus_poster'=>'1']));$row=panel_query('SELECT * FROM event_resources WHERE event_id=:id',['id'=>$eventId])->fetch();check($code===303&&$row['poster_file']===null&&$row['poster_enabled']===0&&$row['pretest_enabled']===1,'Hapus poster otomatis off dan mempertahankan pretest');
[$code]=$request($admin,array_replace($post,['version'=>'6']));$row=panel_query('SELECT * FROM event_resources WHERE event_id=:id',['id'=>$eventId])->fetch();check($code===303&&$row['pretest_enabled']===0&&$row['pretest_url']===$url,'Nonaktifkan pretest mempertahankan URL');
[$code]=$request($public,null,false);check($code===404,'Kedua item off menyembunyikan halaman kegiatan');
panel_query("UPDATE users SET role='staf_bkk' WHERE id=:id",['id'=>$userId]);
[$code]=$request($admin,array_replace($post,['version'=>'7','pretest_enabled'=>'1']));check($code===303,'Staf dapat mengelola pretest dan poster');
[$code]=$request($admin,array_replace($post,['version'=>'8','csrf_token'=>'salah']));check($code===403,'Pengaturan dengan CSRF salah ditolak');
[$code]=$request($admin,null,false);check($code===302,'Halaman pengelola tidak dapat dibuka anonim');
check((int)panel_query('SELECT version FROM attendance_events WHERE id=:id',['id'=>$eventId])->fetchColumn()===1,'Versi pengaturan tidak mengganggu versi absensi');
}finally{
if($eventId){$r=panel_query('SELECT poster_file FROM event_resources WHERE event_id=:id',['id'=>$eventId])->fetch();if($r){event_poster_remove($r['poster_file']);}panel_query('DELETE FROM attendance_events WHERE id=:id',['id'=>$eventId]);}
if($userId){panel_query('DELETE FROM siswa WHERE nis=:nis',['nis'=>$marker]);panel_query('DELETE FROM users WHERE id=:id',['id'=>$userId]);}
if($sessionFile&&is_file($sessionFile)){unlink($sessionFile);}foreach([$pngPath,$badPath] as $path){if(is_file($path)){unlink($path);}}
}