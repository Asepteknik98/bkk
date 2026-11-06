<?php
declare(strict_types=1);
require_once __DIR__.'/partners.php';

const INDUSTRY_IDEA_CONSENT_VERSION='ide-industri-v1';

function industry_ideas_ready(): bool
{
    return (int)panel_query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('industry_idea_links','industry_ideas')")->fetchColumn()===2;
}

function industry_ideas_require_ready(): void
{
    if(!industry_ideas_ready()){http_response_code(503);exit('Kotak Ide Industri belum tersedia. Hubungi pengelola BKK.');}
}

function industry_idea_hash(string $token): ?string
{
    return preg_match('/^[a-f0-9]{64}$/D',$token)?hash('sha256',$token):null;
}

function industry_idea_link_find(string $hash): array|false
{
    return panel_query("SELECT l.id AS link_id,l.mitra_id,l.token_hash,m.nama AS perusahaan FROM industry_idea_links l JOIN mitra m ON m.id=l.mitra_id WHERE l.token_hash=:hash AND l.expires_at>UTC_TIMESTAMP() AND l.used_at IS NULL AND l.revoked_at IS NULL AND m.jenis='perusahaan' AND m.aktif=1",['hash'=>$hash])->fetch();
}

function industry_idea_require_link(): array
{
    header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
    $hash=$_SESSION['industry_idea_hash']??'';
    $link=is_string($hash)&&preg_match('/^[a-f0-9]{64}$/D',$hash)?industry_idea_link_find($hash):false;
    if(!$link){unset($_SESSION['industry_idea_hash'],$_SESSION['industry_idea_form']);http_response_code(410);exit('Tautan mitra tidak tersedia, sudah digunakan, dicabut, atau kedaluwarsa. Hubungi BKK untuk tautan baru.');}
    return $link;
}

function industry_idea_form_key(string $hash): string
{
    $forms=$_SESSION['industry_idea_bindings']??[];
    foreach($forms as $key=>$form){if(($form['expires']??0)<time()){unset($forms[$key]);}}
    while(count($forms)>=8){array_shift($forms);}
    $key=bin2hex(random_bytes(24));$forms[$key]=['hash'=>$hash,'expires'=>time()+7200];$_SESSION['industry_idea_bindings']=$forms;return $key;
}

function industry_idea_form_matches(string $key,string $hash,array $forms): bool
{
    $form=$forms[$key]??null;
    return preg_match('/^[a-f0-9]{48}$/D',$key)===1&&is_array($form)&&is_string($form['hash']??null)&&($form['expires']??0)>=time()&&hash_equals($hash,$form['hash']);
}

function industry_idea_link_change(int $partner,int $hours,int $actor,bool $revoke=false): ?string
{
    if(!$revoke&&($hours<1||$hours>720)){throw new InvalidArgumentException('Masa berlaku harus 1–720 jam.');}
    db()->beginTransaction();
    try{
        $company=panel_query('SELECT jenis,aktif FROM mitra WHERE id=:id FOR UPDATE',['id'=>$partner])->fetch();
        if(!$company||$company['jenis']!=='perusahaan'||(!$revoke&&!(int)$company['aktif'])){throw new InvalidArgumentException('Tautan baru hanya dapat dibuat untuk mitra perusahaan aktif.');}
        panel_query('UPDATE industry_idea_links SET revoked_at=UTC_TIMESTAMP() WHERE mitra_id=:id AND used_at IS NULL AND revoked_at IS NULL',['id'=>$partner]);
        $token=null;if(!$revoke){
            $token=bin2hex(random_bytes(32));panel_query('INSERT INTO industry_idea_links(mitra_id,token_hash,expires_at,created_by,created_at) VALUES(:partner,:hash,TIMESTAMPADD(HOUR,:hours,UTC_TIMESTAMP()),:actor,UTC_TIMESTAMP())',['partner'=>$partner,'hash'=>hash('sha256',$token),'hours'=>$hours,'actor'=>$actor]);
        }
        db()->commit();return $token;
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function industry_idea_input(array $input): array
{
    $data=[];foreach(['nama_pengirim','jabatan','email','telepon','judul','isi'] as $key){$data[$key]=input_text($input,$key);}
    $data['telepon']=preg_replace('/[\s().-]+/','',$data['telepon']);return $data;
}

function industry_idea_errors(array $data,bool $consent): array
{
    $errors=[];foreach(['nama_pengirim'=>150,'jabatan'=>150,'email'=>191,'telepon'=>30,'judul'=>190,'isi'=>10000] as $key=>$max){if(!valid_text($data[$key],$max,in_array($key,['nama_pengirim','judul','isi'],true))){$errors[]=ucfirst(str_replace('_',' ',$key)).' tidak valid atau belum diisi.';}}
    if($data['email']!==''&&!filter_var($data['email'],FILTER_VALIDATE_EMAIL)){$errors[]='Email tidak valid.';}
    if($data['telepon']!==''&&!preg_match('/^\+?[0-9]{8,15}$/D',$data['telepon'])){$errors[]='Nomor telepon harus 8–15 digit, boleh diawali +.';}
    if($data['email']===''&&$data['telepon']===''){$errors[]='Isi email atau nomor telepon untuk tindak lanjut.';}
    if(!$consent){$errors[]='Persetujuan pemrosesan data diperlukan untuk mengirim usulan.';}return $errors;
}

function industry_idea_submit(string $hash,array $data,bool $consent): void
{
    if($errors=industry_idea_errors($data,$consent)){throw new InvalidArgumentException(implode(' ',$errors));}
    $link=industry_idea_link_find($hash);if(!$link){throw new InvalidArgumentException('Tautan tidak berlaku lagi.');}
    db()->beginTransaction();
    try{
        // Match the issuer/revoker lock order so revocation and submission serialize.
        $company=panel_query("SELECT nama FROM mitra WHERE id=:id AND jenis='perusahaan' AND aktif=1 FOR UPDATE",['id'=>$link['mitra_id']])->fetch();
        $current=panel_query('SELECT id FROM industry_idea_links WHERE token_hash=:hash AND mitra_id=:partner AND expires_at>UTC_TIMESTAMP() AND used_at IS NULL AND revoked_at IS NULL FOR UPDATE',['hash'=>$hash,'partner'=>$link['mitra_id']])->fetchColumn();
        if(!$company||!$current){throw new InvalidArgumentException('Tautan tidak berlaku lagi.');}
        panel_query('INSERT INTO industry_ideas(link_id,mitra_id,perusahaan,nama_pengirim,jabatan,email,telepon,judul,isi,persetujuan_at,versi_persetujuan,created_at,updated_at) VALUES(:link,:partner,:company,:nama_pengirim,:jabatan,:email,:telepon,:judul,:isi,UTC_TIMESTAMP(),:consent,UTC_TIMESTAMP(),UTC_TIMESTAMP())',$data+['link'=>(int)$current,'partner'=>$link['mitra_id'],'company'=>$company['nama'],'consent'=>INDUSTRY_IDEA_CONSENT_VERSION]);
        panel_query('UPDATE industry_idea_links SET used_at=UTC_TIMESTAMP() WHERE id=:id',['id'=>(int)$current]);db()->commit();
    }catch(Throwable $exception){if(db()->inTransaction()){db()->rollBack();}throw $exception;}
}

function industry_idea_statuses(): array
{
    return ['baru'=>'Baru','ditinjau'=>'Ditinjau','ditindaklanjuti'=>'Ditindaklanjuti','arsip'=>'Arsip'];
}

function industry_idea_display_time(?string $utc): string
{
    return $utc?(new DateTimeImmutable($utc,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i').' WIB':'—';
}
