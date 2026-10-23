<?php
declare(strict_types=1);
require_once __DIR__.'/panel.php';
require_once __DIR__.'/jobs.php';

function wa_phone(string $raw): ?string
{
    if (!preg_match('/^\+?[0-9\s().-]+$/D',trim($raw))) { return null; }
    $phone = preg_replace('/[^0-9]/','',$raw);
    if (str_starts_with($phone,'0')) { $phone='62'.substr($phone,1); }
    elseif (str_starts_with($phone,'8')) { $phone='62'.$phone; }
    return preg_match('/^[1-9][0-9]{7,14}$/D',$phone) ? $phone : null;
}

function wa_filters(array $input): array
{
    $group=input_text($input,'group');
    $q=input_text($input,'q');$year=input_text($input,'tahun_lulus');
    return ['group'=>in_array($group,['pelamar','tracer','semua'],true)?$group:'pelamar',
        'lowongan'=>$group==='tracer'?0:(positive_id($input['lowongan']??'')??0), 'q'=>valid_text($q,100,false)?$q:'','tahun_lulus'=>$group!=='pelamar'&&preg_match('/^[0-9]{4}$/D',$year)&&(int)$year>=1900&&(int)$year<=2155?(int)$year:0];
}

/** Satu nomor per daftar; respons tracer terbaru menentukan status dan persetujuan. */
function wa_contacts(array $filters, bool $includeUnavailable=false): array
{
    $contacts=[];$blocked=[];
    foreach(['lamaran'=>'telepon','tracer_responses'=>'nomor_telepon'] as $table=>$column){
        foreach(panel_query('SELECT '.$column.' FROM '.$table.' WHERE wa_opted_out_at IS NOT NULL') as $optout){$p=wa_phone((string)$optout[$column]);if($p){$blocked[$p]=true;}}
    }
    if ($filters['group']!=='tracer') {
        $params=[];$where='1=1';
        if ($filters['lowongan']) {$where='lowongan_id=:job';$params['job']=$filters['lowongan'];}
        $seen=[];
        foreach (panel_query('SELECT id,nama,email,telepon,wa_consent FROM lamaran WHERE '.$where.' ORDER BY created_at DESC,id DESC',$params) as $row) {
            $phone=wa_phone($row['telepon']);
            if (!$phone || isset($seen[$phone])) {continue;} $seen[$phone]=true;
            $contacts[$phone]=['key'=>'pelamar:'.$row['id'],'source_type'=>'pelamar','source_id'=>(int)$row['id'],'nama'=>$row['nama'],'nomor'=>$phone,'sumber'=>'Pelamar','email'=>$row['email'],'consent'=>(bool)$row['wa_consent'],'selectable'=>!isset($blocked[$phone])];
        }
    }
    if ($filters['group']!=='pelamar') {
        $seen=[];$identities=[];
        foreach (panel_query('SELECT id,nama_lengkap,nomor_telepon,email,nisn,tahun_lulus,status_kegiatan,wa_consent FROM tracer_responses ORDER BY created_at DESC,id DESC') as $row) {
            $phone=wa_phone($row['nomor_telepon']);$identity=trim((string)$row['nisn']);
            if (!$phone || isset($seen[$phone]) || ($identity!==''&&isset($identities[$identity]))) {continue;}
            $seen[$phone]=true;if($identity!==''){$identities[$identity]=true;}
            if ($row['status_kegiatan']!=='mencari_kerja') {continue;}
            if(!empty($filters['tahun_lulus'])&&(int)$row['tahun_lulus']!==$filters['tahun_lulus']){continue;}
            $contacts[$phone]??=['key'=>'tracer:'.$row['id'],'source_type'=>'tracer','source_id'=>(int)$row['id'],'nama'=>$row['nama_lengkap'],'nomor'=>$phone,'sumber'=>'Alumni mencari kerja - '.$row['tahun_lulus'],'email'=>$row['email']??'','consent'=>(bool)$row['wa_consent'],'selectable'=>!isset($blocked[$phone])];
        }
    }
    if(!$includeUnavailable){$contacts=array_filter($contacts,static fn($r)=>$r['selectable']);}
    $q=mb_strtolower($filters['q'],'UTF-8');
    if($q!=='') {$contacts=array_filter($contacts,static fn($r)=>str_contains(mb_strtolower($r['nama'].' '.$r['nomor'].' '.($r['email']??''),'UTF-8'),$q)||(($p=wa_phone($q))!==null&&str_contains($r['nomor'],$p)));}
    uasort($contacts,static fn($a,$b)=>strnatcasecmp($a['nama'],$b['nama']));
    return array_values($contacts);
}

function wa_message(string $template, string $name, string $link=''): string
{
    return str_replace('{nama}',$name,$template).($link!==''?"\n\n".$link:'');
}

function wa_eligible(array $recipient): bool
{
    // Cek ulang: perubahan status/persetujuan setelah daftar dibuat harus dihormati.
    $contacts=wa_contacts(['group'=>$recipient['source_type']==='tracer'?'tracer':'pelamar','lowongan'=>0,'q'=>'']);
    foreach($contacts as $contact){if($contact['nomor']===$recipient['nomor']){return true;}}
    return false;
}

/** Pilihan disimpan per nomor, terlepas dari pencarian/halaman yang sedang dibuka. */
function wa_selected_contacts(): array
{
    $selected=$_SESSION['wa_selected']??[];$result=[];
    foreach(['pelamar','tracer'] as $group){
        foreach(wa_contacts(['group'=>$group,'lowongan'=>0,'q'=>'']) as $contact){
            $source=$selected[$contact['nomor']]??null;
            if($source!==null&&(!is_string($source)||str_starts_with($source,$group.':'))){$result[$contact['nomor']]??=$contact;}
        }
    }
    return array_values($result);
}
