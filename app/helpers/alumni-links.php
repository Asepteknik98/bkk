<?php
declare(strict_types=1);
require_once __DIR__.'/tracer.php';

function alumni_link_hash(string $token): ?string
{
    return preg_match('/^[a-f0-9]{64}$/D',$token) ? hash('sha256',$token) : null;
}

function alumni_link_find(string $hash, bool $lock=false): array|false
{
    $sql='SELECT l.id AS link_id,l.tracer_id,l.token_hash,t.* FROM alumni_form_links l JOIN tracer_responses t ON t.id=l.tracer_id WHERE l.token_hash=:hash AND l.expires_at>UTC_TIMESTAMP() AND l.used_at IS NULL AND l.revoked_at IS NULL'.($lock?' FOR UPDATE':'');
    $query=db()->prepare($sql);$query->execute(['hash'=>$hash]);return $query->fetch();
}

function require_alumni_link(): array
{
    header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
    $hash=$_SESSION['alumni_link_hash']??'';
    $link=is_string($hash)&&preg_match('/^[a-f0-9]{64}$/D',$hash)?alumni_link_find($hash):false;
    if(!$link){unset($_SESSION['alumni_link_hash']);http_response_code(410);exit('Tautan alumni tidak tersedia, sudah digunakan, dicabut, atau kedaluwarsa. Hubungi BKK untuk tautan baru.');}
    return $link;
}

function alumni_bound_input(array $input,array $owner): array
{
    // Identity and target always come from the database, never POST or an id in the URL.
    foreach(['nama_lengkap','nisn','jurusan','tahun_lulus'] as $key){$input[$key]=(string)($owner[$key]??'');}
    return tracer_input($input);
}

function alumni_link_update(string $hash,array $input): array
{
    db()->beginTransaction();
    try{
        $owner=alumni_link_find($hash,true);
        if(!$owner){throw new InvalidArgumentException('Tautan tidak berlaku lagi.');}
        $data=alumni_bound_input($input,$owner);$errors=tracer_errors($data);
        if($errors){db()->rollBack();return $errors;}
        $values=[];$set=[];
        foreach(tracer_fields() as $field=>$label){
            if(in_array($field,['nama_lengkap','nisn','jurusan','tahun_lulus'],true)){continue;}
            $set[]=$field.'=:'.$field;$values[$field]=$data[$field]===''?null:$data[$field];
        }
        $values+=['id'=>$owner['tracer_id'],'consent'=>(int)$data['wa_consent'],'version'=>TRACER_CONSENT_VERSION];
        $query=db()->prepare('UPDATE tracer_responses SET '.implode(',',$set).',wa_consent=:consent,wa_consent_at=IF(:consent_time=1,UTC_TIMESTAMP(),NULL),persetujuan_data=1,persetujuan_at=UTC_TIMESTAMP(),versi_persetujuan=:version WHERE id=:id');
        $values['consent_time']=(int)$data['wa_consent'];$query->execute($values);
        // Preserve prior WhatsApp opt-out; a form submission never silently removes it.
        $query=db()->prepare('UPDATE alumni_form_links SET used_at=UTC_TIMESTAMP() WHERE id=:id');$query->execute(['id'=>$owner['link_id']]);
        db()->commit();return [];
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}
