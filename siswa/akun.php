<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/student-portal.php';
[$user,$student]=require_student();$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();
    try{
        $newVersion=account_change((int)$user['id'],false,static function(array $actor):int{
            if($actor['role']!=='siswa'){throw new InvalidArgumentException('Akses siswa diperlukan.');}
            $current=is_string($_POST['current_password']??null)?$_POST['current_password']:'';
            if(strlen($current)>72||!password_verify($current,$actor['password_hash'])){throw new InvalidArgumentException('Password saat ini salah.');}
            $hash=account_password(is_string($_POST['password']??null)?$_POST['password']:'',is_string($_POST['confirmation']??null)?$_POST['confirmation']:'');
            panel_query('UPDATE users SET password_hash=:hash,session_version=session_version+1 WHERE id=:id',['hash'=>$hash,'id'=>(int)$actor['id']]);return (int)$actor['session_version']+1;
        });
        session_regenerate_id(true);$_SESSION['auth_version']=$newVersion;panel_message('Password berhasil diubah. Sesi lain dibatalkan.','siswa/akun.php');
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
}
student_start($user,$student,'Akun saya','akun.php');
?>
<section class="surface"><h2>Data saya</h2><dl><?php foreach(['nis'=>'NIS','nisn'=>'NISN','nama'=>'Nama','kelas'=>'Kelas','jurusan'=>'Jurusan','angkatan'=>'Angkatan','kelas_industri'=>'Kelas industri','telepon'=>'Telepon','email'=>'Email siswa'] as $key=>$label):?><dt><?= e($label) ?></dt><dd><?= e((string)$student[$key]) ?></dd><?php endforeach;?><dt>Username login</dt><dd><?= e($user['username']) ?></dd><dt>Email login</dt><dd><?= e($user['email']) ?></dd></dl><p>Hubungi pengelola untuk koreksi data siswa atau identitas login.</p></section>
<?php if($errors):?><div class="surface mt-4" role="alert"><?php foreach($errors as $error):?><p><?= e($error) ?></p><?php endforeach;?></div><?php endif;?>
<form class="surface mt-4" method="post"><?= csrf_field() ?><h2>Ubah password</h2><label class="d-block mb-3">Password saat ini<input class="form-control" type="password" name="current_password" maxlength="72" autocomplete="current-password" required></label><label class="d-block mb-3">Password baru (12–72 byte)<input class="form-control" type="password" name="password" maxlength="72" autocomplete="new-password" required></label><label class="d-block mb-3">Ulangi password baru<input class="form-control" type="password" name="confirmation" maxlength="72" autocomplete="new-password" required></label><button class="button button-navy" type="submit">Ubah password</button></form>
<?php student_end(); ?>
