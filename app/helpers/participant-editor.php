<?php
declare(strict_types=1);
require_once __DIR__.'/participants.php';
if(!in_array($participantPage??'', ['form','import'],true)){http_response_code(404);exit;}
$user=require_role('admin','staf_bkk');
try{[$filters,$where,$params,$order]=participant_filters($_GET);}
catch(InvalidArgumentException $exception){http_response_code(400);exit(e($exception->getMessage()));}
$listPath='panel/peserta/?'.http_build_query($filters);
$ready=(bool)panel_query("SHOW TABLES LIKE 'siswa'")->fetchColumn();
$participantChoices=participant_choice_lists($ready);
$errors=[];$data=participant_input([]);$id=$participantPage==='form'?positive_id($_GET['id']??''):null;
if($participantPage==='form'&&isset($_GET['id'])&&!$id){panel_not_found();}
$continuePkl=$participantPage==='form'&&$id===null&&(input_text($_GET,'lanjut')==='pkl'||input_text($_POST,'lanjut')==='pkl');
$continueTrip=$continuePkl&&(input_text($_GET,'kembali')==='perjalanan'||input_text($_POST,'kembali')==='perjalanan');
if($continueTrip){require_permission($user,'biaya-perjalanan');}
if($continuePkl){require_permission($user,'pkl');}
if($ready&&$id){$row=panel_query('SELECT * FROM siswa WHERE id=:id',['id'=>$id])->fetch();if(!$row){panel_not_found();}$data=participant_input($row);}
// Preserve the current legacy name for this edit only; new students use Settings choices.
if($id&&$data['kelas_industri']!==''&&!participant_industry_boolean($data['kelas_industri'])&&!in_array($data['kelas_industri'],$participantChoices['kelas_industri'],true)){$participantChoices['kelas_industri'][]=$data['kelas_industri'];}
if($_SERVER['REQUEST_METHOD']==='POST'){
    require_post_csrf();
    $action=input_text($_POST,'aksi');
    if(($participantPage==='import'&&$action!=='impor')||($participantPage==='form'&&!in_array($action,['','simpan'],true))){http_response_code(400);exit('Tindakan tidak sesuai halaman.');}
    if(!$ready){$errors[]='Jalankan pembaruan database 009_participants.sql terlebih dahulu.';}
    elseif(($_POST['aksi']??'')==='impor'){
        $file=$_FILES['excel']??[];$temp=null;
        try{
            if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_string($file['name']??null)||strtolower(pathinfo($file['name'],PATHINFO_EXTENSION))!=='xlsx'||($file['size']??0)>5*1024*1024||!is_uploaded_file($file['tmp_name']??'')){throw new InvalidArgumentException('Pilih file .xlsx maksimal 5 MB.');}
            $temp=sys_get_temp_dir().'/bkk-peserta-'.bin2hex(random_bytes(12)).'.xlsx';
            if(!move_uploaded_file($file['tmp_name'],$temp)){throw new RuntimeException('Unggahan gagal diproses.');}
            $rows=participant_xlsx_rows($temp);$participants=[];
            foreach($rows as $number=>$values){
                $participant=participant_input(array_combine(array_keys(participant_columns()),$values));
                $validation=participant_errors($participant);
                if($participant['kelas_industri']!==''&&!in_array($participant['kelas_industri'],$participantChoices['kelas_industri'],true)){$validation[]='Pilih nama kelas industri yang sudah diatur di Pengaturan - Pilihan Kelas Industri.';}
                if($validation){$errors[]='Baris data '.($number+1).': '.implode(' ',$validation);}
                $participants[]=$participant;
            }
            if(!$errors){
                db()->beginTransaction();
                try{
                    foreach($participants as $participant){participant_save($participant);}
                    db()->commit();
                }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
                unlink($temp);$temp=null;
                panel_message(count($participants).' peserta berhasil diimpor.','panel/peserta/');
            }
        }catch(PDOException $exception){$errors[]=$exception->getCode()==='23000'?'Impor dibatalkan karena konflik data. Periksa berkas dan coba kembali.':'Impor gagal disimpan. Tidak ada data yang diimpor.';}
        catch(Throwable $exception){$errors[]=$exception instanceof InvalidArgumentException?$exception->getMessage():'File Excel tidak dapat dibaca. Gunakan template .xlsx yang tersedia.';}
        finally{if($temp&&is_file($temp)){unlink($temp);}}
    }else{
        $data=participant_input($_POST);$errors=participant_errors($data);
        foreach(['kelas','jurusan','angkatan','kelas_industri'] as $choiceField){
            if(isset($_POST[$choiceField])&&!is_string($_POST[$choiceField])){$errors[]='Pilihan '.$choiceField.' tidak valid.';}
            if($data[$choiceField]!==''&&!in_array($data[$choiceField],$participantChoices[$choiceField],true)){$errors[]='Pilih '.$choiceField.' dari daftar yang tersedia.';}
        }
        if(!$errors){
            try{
                participant_save($data,$id);
                if($continuePkl){panel_message('Siswa berhasil ditambahkan. Lengkapi penempatan PKL berikut.','panel/pkl/form.php?siswa_id='.(int)db()->lastInsertId().($continueTrip?'&lanjut=perjalanan':''));}
                panel_message('Data peserta berhasil disimpan.',$listPath);
            }
            catch(PDOException $exception){if($exception->getCode()!=='23000'){throw $exception;}$errors[]='Data peserta belum tersimpan karena konflik data. Muat ulang dan coba kembali.';}
        }
    }
}

if($errors){http_response_code(422);}
