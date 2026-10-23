<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/accounts.php';
$user=require_role('admin','staf_bkk');$id=positive_id($_GET['id']??'');if(!$id){panel_not_found();}
$student=panel_query('SELECT * FROM siswa WHERE id=:id',['id'=>$id])->fetch();if(!$student){panel_not_found();}
if(!panel_query("SHOW TABLES LIKE 'student_accounts'")->fetchColumn()){panel_start($user,'Akun siswa','siswa');echo '<p class="alert">Jalankan pembaruan database 014_student_accounts.sql terlebih dahulu.</p>';panel_end($user);exit;}
$account=panel_query('SELECT u.id,u.username,u.email,u.aktif,u.session_version FROM users u JOIN student_accounts sa ON sa.user_id=u.id WHERE sa.siswa_id=:id',['id'=>$id])->fetch();
$data=$account?:['id'=>0,'username'=>'siswa-'.$id,'email'=>$student['email'],'aktif'=>1,'session_version'=>0];$errors=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    require_post_csrf();$data['username']=input_text($_POST,'username');$data['email']=input_text($_POST,'email');$data['aktif']=input_text($_POST,'aktif');
    try{
        account_change((int)$user['id'],false,static function(array $actor)use($id):void{
            if(!in_array($actor['role'],['admin','staf_bkk'],true)){throw new InvalidArgumentException('Akses pengelola diperlukan.');}
            $student=panel_query('SELECT * FROM siswa WHERE id=:id FOR UPDATE',['id'=>$id])->fetch();if(!$student){throw new InvalidArgumentException('Data siswa tidak tersedia.');}
            $old=panel_query('SELECT u.* FROM users u JOIN student_accounts sa ON sa.user_id=u.id WHERE sa.siswa_id=:id FOR UPDATE',['id'=>$id])->fetch();
            $expected=input_text($_POST,'account_id');$expectedVersion=input_text($_POST,'version');
            if(!preg_match('/^(0|[1-9][0-9]{0,17})$/D',$expected)||!preg_match('/^(0|[1-9][0-9]{0,9})$/D',$expectedVersion)||(int)$expected!==(int)($old['id']??0)||(int)$expectedVersion!==(int)($old['session_version']??0)){
                throw new InvalidArgumentException('Akun sudah diperbarui pengelola lain. Muat ulang halaman.');
            }
            if($old&&$old['role']!=='siswa'){throw new InvalidArgumentException('Pemetaan akun tidak valid. Hubungi admin.');}
            $identity=account_identity(['nama'=>$student['nama'],'username'=>input_text($_POST,'username'),'email'=>input_text($_POST,'email')],$old?(int)$old['id']:null);
            $active=input_text($_POST,'aktif');if(!in_array($active,['0','1'],true)){throw new InvalidArgumentException('Status akun tidak valid.');}
            $password=is_string($_POST['password']??null)?$_POST['password']:'';$confirmation=is_string($_POST['confirmation']??null)?$_POST['confirmation']:'';
            $hash=(!$old||$password!==''||$confirmation!=='')?account_password($password,$confirmation):$old['password_hash'];
            if($old){panel_query('UPDATE users SET nama=:nama,username=:username,email=:email,aktif=:aktif,password_hash=:hash,session_version=session_version+1 WHERE id=:id',$identity+['aktif'=>(int)$active,'hash'=>$hash,'id'=>(int)$old['id']]);}
            else{panel_query("INSERT INTO users(nama,username,email,role,aktif,password_hash) VALUES(:nama,:username,:email,'siswa',:aktif,:hash)",$identity+['aktif'=>(int)$active,'hash'=>$hash]);$userId=(int)db()->lastInsertId();panel_query('INSERT INTO student_accounts(user_id,siswa_id) VALUES(:user,:student)',['user'=>$userId,'student'=>$id]);}
        });
        panel_message('Akun siswa berhasil disimpan. Sampaikan username dan password kepada siswa. Perubahan akun membatalkan sesi lamanya.','panel/siswa/akun.php?id='.$id);
    }catch(InvalidArgumentException $exception){$errors[]=$exception->getMessage();}
    catch(PDOException $exception){if($exception->getCode()!=='23000'){throw $exception;}$errors[]='Username atau email sudah digunakan akun lain.';}
}
panel_start($user,'Akun siswa: '.$student['nama'],'siswa');
$formAccountId=$errors?input_text($_POST,'account_id'):(string)($account['id']??0);
$formVersion=$errors?input_text($_POST,'version'):(string)($account['session_version']??0);
?>
<div class="page-actions"><h1>Akun siswa</h1><a href="<?= e(base_url('panel/siswa/')) ?>">Data siswa</a><a href="<?= e(base_url('auth/login.php')) ?>">Halaman login</a></div>
<p><strong><?= e($student['nama']) ?></strong> · NIS <?= e($student['nis']) ?> · <?= e($student['kelas']) ?> · Angkatan <?= e((string)$student['angkatan']) ?></p>
<?php if($errors):?><div class="alert" role="alert"><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul><a href="<?= e(base_url('panel/siswa/akun.php?id='.$id)) ?>">Muat ulang akun terbaru</a></div><?php endif;?>
<form class="editor-form card" method="post"><?= csrf_field() ?><input type="hidden" name="account_id" value="<?= e($formAccountId) ?>"><input type="hidden" name="version" value="<?= e($formVersion) ?>"><h2><?= $account?'Kelola akun siswa':'Buat akun siswa' ?></h2><p>Akun ini hanya terhubung ke siswa di atas. Hak akses siswa ditentukan otomatis.</p>
<label>Username<input name="username" maxlength="50" autocomplete="off" value="<?= e($data['username']) ?>" required></label><label>Email login<input name="email" type="email" maxlength="191" value="<?= e($data['email']) ?>" required></label><p>Email login harus unik. Siswa juga dapat masuk menggunakan username.</p>
<label>Status<select name="aktif"><option value="1" <?= (string)$data['aktif']==='1'?'selected':'' ?>>Aktif</option><option value="0" <?= (string)$data['aktif']==='0'?'selected':'' ?>>Nonaktif</option></select></label>
<label><?= $account?'Password baru (kosongkan jika tidak diubah)':'Password' ?><input type="password" name="password" maxlength="72" autocomplete="new-password" <?= !$account?'required':'' ?>></label><label>Ulangi password<input type="password" name="confirmation" maxlength="72" autocomplete="new-password" <?= !$account?'required':'' ?>></label><p>Password 12–72 byte. Password tidak ditampilkan kembali setelah disimpan.</p><button type="submit">Simpan akun siswa</button></form>
<?php panel_end($user); ?>
