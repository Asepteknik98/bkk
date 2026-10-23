<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/students.php';
$user=require_role('admin','staf_bkk');
$path=sys_get_temp_dir().'/bkk-template-'.bin2hex(random_bytes(12)).'.zip';
try{
    $zip=new PharData($path,0,null,Phar::ZIP);
    $zip['[Content_Types].xml']='<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
    $zip['_rels/.rels']='<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    $zip['xl/workbook.xml']='<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Siswa" sheetId="1" r:id="rId1"/></sheets></workbook>';
    $zip['xl/_rels/workbook.xml.rels']='<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';
    $cells='';foreach(array_keys(student_columns()) as $index=>$key){$cells.='<c r="'.chr(65+$index).'1" t="inlineStr"><is><t>'.$key.'</t></is></c>';}
    $zip['xl/worksheets/sheet1.xml']='<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1">'.$cells.'</row></sheetData></worksheet>';
    unset($zip);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template-siswa.xlsx"');
    header('Cache-Control: no-store');
    header('Content-Length: '.filesize($path));readfile($path);
}finally{if(is_file($path)){unlink($path);}}
