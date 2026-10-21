<?php
declare(strict_types=1);
require_once __DIR__.'/public.php';

/** Sumber, kolom, dan urutan SQL selalu ditentukan server. */
function portal_search(array $input): array
{
    $filters=[]; $error='';
    foreach(['q','type','kategori'] as $key){$filters[$key]=input_text($input,$key);if(isset($input[$key])&&!is_string($input[$key])){$error='Parameter pencarian harus berupa teks.';}}
    ['q'=>$q,'type'=>$type,'kategori'=>$category]=$filters;
    if(!valid_text($q,100,false)){$error='Kata kunci maksimal 100 karakter.';}
    if(!in_array($type,['','berita','lowongan','program'],true)){$error='Jenis informasi tidak valid.';}
    if($category!==''&&!isset(program_categories()[$category])){$error='Kategori tidak valid.';}
    if($error){http_response_code(400);return [$filters,[],0,1,1,$error];}
    $sources=[
        'berita'=>["judul,slug,ringkasan,gambar,'' AS kategori,COALESCE(published_at,created_at) AS tanggal","status='published' AND (published_at IS NULL OR published_at<=NOW())",['judul','ringkasan','isi']],
        'lowongan'=>["posisi AS judul,slug,CONCAT(perusahaan,' · ',lokasi,' · ',deskripsi) AS ringkasan,gambar,'' AS kategori,created_at AS tanggal","status='published'",['posisi','perusahaan','lokasi','deskripsi']],
        'program'=>['judul,slug,ringkasan,gambar,kategori,created_at AS tanggal',"status='published'",['judul','ringkasan','isi']],
    ];
    $parts=[];$params=[];$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';
    foreach($sources as $table=>[$columns,$where,$fields]){
        if(($type!==''&&$type!==$table)||($category!==''&&$table!=='program')){continue;}
        if($table==='program'){
            $allowed=[];foreach(array_keys(program_categories()) as $i=>$value){$key='allowed_'.$i;$allowed[]=':'.$key;$params[$key]=$value;}
            $where.=' AND kategori IN ('.implode(',',$allowed).')';
            if($category!==''){$where.=' AND kategori=:kategori';$params['kategori']=$category;}
        }
        if($q!==''){$terms=[];foreach($fields as $field){$key=$table.'_'.$field;$terms[]="$field LIKE :$key ESCAPE '!'";$params[$key]=$pattern;}$where.=' AND ('.implode(' OR ',$terms).')';}
        $deadlineColumn=$table==='lowongan'?'batas_lamaran':'NULL AS batas_lamaran';
        $parts[]="SELECT '$table' AS jenis,id,$columns,$deadlineColumn FROM $table WHERE $where";
    }
    if(!$parts){return [$filters,[],0,1,1,''];}
    $union=implode(' UNION ALL ',$parts);
    $total=(int)(public_query('SELECT COUNT(*) AS total FROM ('.$union.') AS hasil',$params)[0]['total']??0);
    $pages=max(1,(int)ceil($total/9));$raw=$input['page']??'1';$page=is_string($raw)&&ctype_digit($raw)?max(1,min($pages,(int)$raw)):1;
    $rows=public_query('SELECT * FROM ('.$union.') AS hasil ORDER BY tanggal DESC,jenis,id DESC LIMIT :limit OFFSET :offset',$params+['limit'=>9,'offset'=>($page-1)*9]);
    return [$filters,$rows,$total,$page,$pages,''];
}
