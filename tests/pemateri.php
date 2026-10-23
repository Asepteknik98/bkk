<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/app/helpers/speakers.php';
ob_start();
set_exception_handler(static function(Throwable $e){fwrite(STDERR,'FAIL '.get_class($e).' line '.$e->getLine().': '.$e->getMessage().PHP_EOL);exit(1);});
function check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$base=base_url();if(str_starts_with($base,'/')){$base='http://localhost'.$base;}if(!in_array(parse_url($base,PHP_URL_HOST),['localhost','127.0.0.1'],true)){throw new RuntimeException('Hanya server lokal');}
$marker='speaker-test-'.bin2hex(random_bytes(5));$userId=null;$sessionFile=null;
$pdfPath=sys_get_temp_dir().'/'.$marker.'.pdf';$pngPath=sys_get_temp_dir().'/'.$marker.'.png';
$pdf="%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";
file_put_contents($pdfPath,$pdf);$image=imagecreatetruecolor(4,4);imagepng($image,$pngPath);imagedestroy($image);
try{
start_app_session();panel_query('INSERT INTO users(nama,username,email,password_hash,role,aktif) VALUES(:nama,:username,:email,:hash,:role,1)',['nama'=>'Uji pemateri','username'=>$marker,'email'=>$marker.'@example.test','hash'=>password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT),'role'=>'admin']);$userId=(int)db()->lastInsertId();
$_SESSION=['user_id'=>$userId,'auth_version'=>0,'csrf_token'=>bin2hex(random_bytes(32))];$csrf=$_SESSION['csrf_token'];$cookie=session_name().'='.session_id();$sessionFile=session_save_path().'/sess_'.session_id();session_write_close();
$request=static function(string $path,?array $post=null,bool $auth=true)use($base,$cookie):array{$ch=curl_init(rtrim($base,'/').'/'.$path);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>20]);if($auth){curl_setopt($ch,CURLOPT_COOKIE,$cookie);}if($post!==null){$flat=[];foreach($post as $key=>$value){if(is_array($value)){foreach($value as $i=>$item){$flat[$key.'['.$i.']']=$item;}}else{$flat[$key]=$value;}}curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,$flat);}$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$err=curl_error($ch);curl_close($ch);if($body===false){throw new RuntimeException($err);}return [$code,$body];};
$events=[];foreach(['A','B'] as $suffix){panel_query('INSERT INTO attendance_events(judul,tanggal,angkatan,kelas_industri,deskripsi,created_by) VALUES(:judul,:tanggal,2025,:industri,:deskripsi,:user)',['judul'=>$marker.$suffix,'tanggal'=>'2026-10-24','industri'=>$marker.$suffix,'deskripsi'=>'Uji','user'=>$userId]);$events[]=(string)db()->lastInsertId();}
[$code,$html]=$request('panel/pemateri/form.php');check($code===200&&str_contains($html,'CV pemateri'),'Form pemateri dapat dibuka admin');
$post=['nama'=>$marker,'instansi'=>'Instansi uji','keahlian'=>'Keahlian uji','kelas_industri'=>'Kelas utama','bio'=>'Profil uji','csrf_token'=>$csrf,'events'=>$events];
[$code]=$request('panel/pemateri/form.php',$post+['cv'=>new CURLFile($pdfPath,'application/pdf','cv.pdf'),'foto'=>new CURLFile($pngPath,'image/png','foto.png')]);check($code===303,'Upload CV dan foto melalui HTTP');
$row=panel_query('SELECT * FROM speakers WHERE created_by=:user',['user'=>$userId])->fetch();check((bool)$row&&speaker_path($row['cv_file'])!==null&&speaker_path($row['foto_file'])!==null,'Berkas tersimpan di penyimpanan internal');$id=(int)$row['id'];$oldCv=$row['cv_file'];$oldPhoto=$row['foto_file'];
check((int)panel_query('SELECT COUNT(*) FROM speaker_events WHERE speaker_id=:id',['id'=>$id])->fetchColumn()===2,'Satu pemateri terhubung ke dua kegiatan');
[$code,$html]=$request('panel/pemateri/detail.php?id='.$id);check($code===200&&str_contains($html,$marker.'A')&&str_contains($html,$marker.'B'),'Profil menampilkan kegiatan yang diampu');
[$code,$html]=$request('panel/absensi/detail.php?id='.$events[0]);check($code===200&&str_contains($html,$marker)&&str_contains($html,'Pemateri kegiatan'),'Halaman kegiatan menampilkan pemateri');
[$code,$body]=$request('panel/pemateri/berkas.php?id='.$id.'&jenis=cv');check($code===200&&$body===$pdf,'Download CV sesuai unggahan');
[$code,$body]=$request('panel/pemateri/berkas.php?id='.$id.'&jenis=foto');check($code===200&&getimagesizefromstring($body)['mime']==='image/png','Foto dapat ditampilkan');
[$code]=$request('panel/pemateri/berkas.php?id='.$id.'&jenis=cv',null,false);check($code===302,'Unduh anonim diarahkan ke login');
[$code]=$request('storage/speakers/'.$oldCv,null,false);check($code===403,'Akses langsung berkas ditolak');
[$code]=$request('panel/pemateri/berkas.php?id='.$id.'&jenis=../cv');check($code===404,'Jenis berkas tidak valid ditolak');
[$code]=$request('panel/pemateri/form.php?id='.$id,array_replace($post,['version'=>'1','events'=>[$events[0]]]));$row=panel_query('SELECT * FROM speakers WHERE id=:id',['id'=>$id])->fetch();check($code===303&&$row['cv_file']===$oldCv&&$row['foto_file']===$oldPhoto,'Edit metadata mempertahankan CV dan foto');
$before=count(glob(APP_ROOT.'/storage/speakers/*'));
[$code,$html]=$request('panel/pemateri/form.php?id='.$id,$post+['version'=>'1','cv'=>new CURLFile($pdfPath,'application/pdf','stale.pdf')]);check($code===200&&str_contains($html,'Profil sudah diubah')&&count(glob(APP_ROOT.'/storage/speakers/*'))===$before,'Versi lama ditolak dan unggahan baru dibersihkan');
[$code,$html]=$request('panel/pemateri/form.php?id='.$id,$post+['version'=>'2','cv'=>new CURLFile($pdfPath,'application/pdf','cv.pdf'),'foto'=>new CURLFile($pdfPath,'image/jpeg','palsu.jpg')]);check($code===200&&str_contains($html,'Foto harus JPG')&&count(glob(APP_ROOT.'/storage/speakers/*'))===$before,'Foto palsu ditolak tanpa meninggalkan CV baru');
[$code,$html]=$request('panel/pemateri/form.php?id='.$id,array_replace($post,['version'=>'2','events'=>['999999999999'],'cv'=>new CURLFile($pdfPath,'application/pdf','cv.pdf')]));check($code===200&&str_contains($html,'Kegiatan yang dipilih sudah tidak tersedia')&&count(glob(APP_ROOT.'/storage/speakers/*'))===$before,'Kegiatan tidak valid membatalkan perubahan dan membersihkan berkas');
[$code]=$request('panel/pemateri/form.php?id='.$id,$post+['version'=>'2','cv'=>new CURLFile($pdfPath,'application/pdf','pengganti.pdf'),'foto'=>new CURLFile($pngPath,'image/png','baru.png')]);$row=panel_query('SELECT * FROM speakers WHERE id=:id',['id'=>$id])->fetch();check($code===303&&$row['cv_file']!==$oldCv&&$row['foto_file']!==$oldPhoto&&speaker_path($oldCv)===null&&speaker_path($oldPhoto)===null,'Ganti CV dan foto membersihkan berkas lama');
panel_query("UPDATE users SET role='staf_bkk' WHERE id=:id",['id'=>$userId]);
[$code,$html]=$request('panel/pemateri/?'.http_build_query(['kelas_industri'=>$marker.'B','q'=>$marker]));check($code===200&&str_contains($html,$marker),'Filter kelas kegiatan dan pencarian dapat digunakan staf');
[$code,$html]=$request('panel/pemateri/berkas.php?id='.$id.'&jenis=cv');check($code===200&&$html===$pdf,'Staf dapat mengunduh CV');
[$code]=$request('panel/pemateri/form.php?id='.$id,$post+['version'=>'3','hapus_foto'=>'1']);$row=panel_query('SELECT * FROM speakers WHERE id=:id',['id'=>$id])->fetch();check($code===303&&$row['foto_file']===null&&$row['cv_file']!==null,'Hapus foto mempertahankan CV');
[$code]=$request('panel/pemateri/detail.php?id='.$id,['aksi'=>'hapus','version'=>'4','csrf_token'=>'wrong']);check($code===403,'Penghapusan dengan CSRF salah ditolak');
$cv=$row['cv_file'];[$code]=$request('panel/pemateri/detail.php?id='.$id,['aksi'=>'hapus','version'=>'4','csrf_token'=>$csrf]);check($code===303&&speaker_path($cv)===null&&(int)panel_query('SELECT COUNT(*) FROM speaker_events WHERE speaker_id=:id',['id'=>$id])->fetchColumn()===0,'Staf menghapus profil, CV, dan relasi');
check((int)panel_query('SELECT COUNT(*) FROM attendance_events WHERE created_by=:user',['user'=>$userId])->fetchColumn()===2,'Kegiatan tetap tersedia setelah pemateri dihapus');
[$code]=$request('panel/pemateri/detail.php?id='.$id);check($code===404,'Profil yang dihapus mengembalikan 404');
}finally{
if($userId){foreach(panel_query('SELECT id,version FROM speakers WHERE created_by=:user',['user'=>$userId])->fetchAll() as $s){speaker_delete((int)$s['id'],(int)$s['version']);}panel_query('DELETE FROM attendance_events WHERE created_by=:user',['user'=>$userId]);panel_query('DELETE FROM users WHERE id=:user',['user'=>$userId]);}
if($sessionFile&&is_file($sessionFile)){unlink($sessionFile);}foreach([$pdfPath,$pngPath] as $path){if(is_file($path)){unlink($path);}}
}
