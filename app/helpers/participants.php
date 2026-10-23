<?php
declare(strict_types=1);
require_once __DIR__.'/panel.php';
require_once __DIR__.'/validation.php';

function participant_columns(): array
{
    return ['nis'=>'Nomor peserta','nama'=>'Nama peserta','kelas'=>'Kelas','jurusan'=>'Jurusan','angkatan'=>'Angkatan','kelas_industri'=>'Kelas industri','telepon'=>'Telepon','email'=>'Email','tempat_pkl'=>'Tempat PKL','tahun_pkl'=>'Tahun PKL'];
}

function participant_sort_options(): array
{
    return ['nama'=>'Nama A–Z','angkatan_baru'=>'Angkatan terbaru','angkatan_lama'=>'Angkatan terlama','kelas_industri'=>'Kelas industri A–Z'];
}

/** Shared filters keep the count, list and pagination consistent. */
function participant_filters(array $input): array
{
    $filters=[];
    foreach(['q'=>100,'angkatan'=>4,'kelas_industri'=>150,'urut'=>30] as $key=>$max){
        if(isset($input[$key])&&!is_string($input[$key])){throw new InvalidArgumentException('Filter peserta tidak valid.');}
        $value=input_text($input,$key);
        if(!valid_text($value,$max,false)){throw new InvalidArgumentException('Filter peserta terlalu panjang atau tidak valid.');}
        $filters[$key]=$value;
    }
    if($filters['angkatan']!==''&&!preg_match('/^(19|20|21)[0-9]{2}$/D',$filters['angkatan'])){throw new InvalidArgumentException('Angkatan harus berupa tahun (1900–2199).');}
    if($filters['urut']===''){$filters['urut']='nama';}
    $orders=['nama'=>'nama ASC,id ASC','angkatan_baru'=>'angkatan DESC,nama ASC,id ASC','angkatan_lama'=>'angkatan ASC,nama ASC,id ASC','kelas_industri'=>'kelas_industri ASC,angkatan DESC,nama ASC,id ASC'];
    if(!isset($orders[$filters['urut']])){throw new InvalidArgumentException('Urutan peserta tidak valid.');}
    $where='1=1';$params=[];
    if($filters['q']!==''){
        $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$filters['q']).'%';
        $where.=" AND (nama LIKE :nama ESCAPE '!' OR nis LIKE :nis ESCAPE '!')";
        $params+=['nama'=>$pattern,'nis'=>$pattern];
    }
    foreach(['angkatan','kelas_industri'] as $key){if($filters[$key]!==''){$where.=" AND $key=:$key";$params[$key]=$filters[$key];}}
    return [$filters,$where,$params,$orders[$filters['urut']]];
}

function participant_input(array $input): array
{
    $data=[];
    foreach(participant_columns() as $key=>$label){$data[$key]=isset($input[$key])&&is_string($input[$key])?trim($input[$key]):'';}
    return $data;
}

function participant_errors(array $data): array
{
    $errors=[];
    foreach(['nis'=>30,'nama'=>150,'kelas'=>80,'jurusan'=>150,'kelas_industri'=>150,'telepon'=>25,'email'=>190,'tempat_pkl'=>190,'tahun_pkl'=>9] as $key=>$max){
        if(!mb_check_encoding($data[$key],'UTF-8')||mb_strlen($data[$key])>$max||preg_match('/[\x00-\x1F\x7F]/',$data[$key])){$errors[]=participant_columns()[$key].' tidak valid atau terlalu panjang.';}
    }
    foreach(['nis','nama','kelas'] as $key){if($data[$key]===''){$errors[]=participant_columns()[$key].' wajib diisi.';}}
    if(!preg_match('/^(19|20|21)[0-9]{2}$/D',$data['angkatan'])){$errors[]='Angkatan harus berupa tahun (1900–2199).';}
    if($data['email']!==''&&!filter_var($data['email'],FILTER_VALIDATE_EMAIL)){$errors[]='Email tidak valid.';}
    if($data['tahun_pkl']!==''&&!preg_match('~^(?:19|20|21)[0-9]{2}(?:/(?:19|20|21)[0-9]{2})?$~D',$data['tahun_pkl'])){$errors[]='Tahun PKL harus seperti 2025 atau 2025/2026.';}
    return $errors;
}

function participant_save(array $data, ?int $id=null): void
{
    $columns=array_keys(participant_columns());
    if($id!==null){
        panel_query('UPDATE siswa SET '.implode(',',array_map(static fn($key)=>"$key=:$key",$columns)).' WHERE id=:id',$data+['id'=>$id]);
    }else{
        panel_query('INSERT INTO siswa ('.implode(',',$columns).') VALUES (:'.implode(',:',$columns).')',$data);
    }
}

/** Read only text/numbers from the first worksheet; never evaluate formulas or XML entities. */
function participant_xlsx_rows(string $path): array
{
    $archive=new PharData($path);
    $xml=static function(string $name)use($archive): SimpleXMLElement {
        if(!isset($archive[$name])||$archive[$name]->getSize()>8*1024*1024){throw new InvalidArgumentException('Isi Excel tidak tersedia atau terlalu besar.');}
        $text=$archive[$name]->getContent();
        if(stripos($text,'<!DOCTYPE')!==false||stripos($text,'<!ENTITY')!==false){throw new InvalidArgumentException('XML Excel tidak diizinkan.');}
        $previous=libxml_use_internal_errors(true);
        try{$node=simplexml_load_string($text,SimpleXMLElement::class,LIBXML_NONET);if($node===false){throw new InvalidArgumentException('Struktur Excel tidak valid.');}return $node;}
        finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
    };
    $strings=[];
    if(isset($archive['xl/sharedStrings.xml'])){
        foreach($xml('xl/sharedStrings.xml')->xpath('//*[local-name()="si"]') as $item){$strings[]=implode('',array_map('strval',$item->xpath('.//*[local-name()="t"]')));}
    }
    $book=$xml('xl/workbook.xml');$sheets=$book->xpath('//*[local-name()="sheet"]');
    if(!$sheets){throw new InvalidArgumentException('Excel tidak memiliki lembar kerja.');}
    $rid=(string)$sheets[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
    $target='';foreach($xml('xl/_rels/workbook.xml.rels')->xpath('//*[local-name()="Relationship"]') as $rel){if((string)$rel['Id']===$rid&&(string)$rel['TargetMode']!=='External'){$target=(string)$rel['Target'];break;}}
    $target=str_starts_with($target,'/xl/')?substr($target,1):'xl/'.$target;
    if(!preg_match('~^xl/worksheets/[a-zA-Z0-9_.-]+\.xml$~D',$target)){throw new InvalidArgumentException('Lokasi lembar Excel tidak didukung.');}
    $rows=[];
    foreach($xml($target)->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') as $row){
        if(count($rows)>=1001){throw new InvalidArgumentException('Maksimal 1.000 peserta per impor.');}
        $values=array_fill(0,count(participant_columns()),'');
        foreach($row->xpath('./*[local-name()="c"]') as $cell){
            if(!preg_match('/^([A-Z]+)[0-9]+$/D',(string)$cell['r'],$match)){throw new InvalidArgumentException('Referensi sel tidak valid.');}
            $col=0;foreach(str_split($match[1]) as $letter){$col=$col*26+ord($letter)-64;}
            if($cell->xpath('./*[local-name()="f"]')){throw new InvalidArgumentException('Hapus rumus Excel sebelum impor; gunakan nilai teks.');}
            $v=$cell->xpath('./*[local-name()="v"]');$value=(string)($v[0]??'');
            if((string)$cell['t']==='s'){$value=$strings[(int)$value]??'';}
            elseif((string)$cell['t']==='inlineStr'){$value=implode('',array_map('strval',$cell->xpath('.//*[local-name()="t"]')));}
            if($col>count($values)){if($value!==''){throw new InvalidArgumentException('Kolom Excel harus mengikuti template.');}continue;}
            $values[$col-1]=trim($value);
        }
        if(implode('',$values)!==''){$rows[]=$values;}
    }
    if(!$rows||array_shift($rows)!==array_keys(participant_columns())){throw new InvalidArgumentException('Judul kolom harus sama dan berurutan seperti template.');}
    if(!$rows){throw new InvalidArgumentException('Tidak ada data peserta untuk diimpor.');}
    return $rows;
}
