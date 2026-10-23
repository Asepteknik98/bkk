<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers/participants.php';
function check(bool $condition,string $message):void{if(!$condition){throw new RuntimeException($message);}echo 'PASS '.$message.PHP_EOL;}
check(input_text(['q'=>' peserta '],'q')==='peserta'&&valid_text('',100,false),'Helper pencarian halaman tersedia');
$data=participant_input(['nis'=>'00123','nama'=>'Peserta Uji','kelas'=>'XI A','angkatan'=>'2025']);
check(participant_errors($data)===[],'Data valid dan identitas nol awal diterima');
check(count(participant_errors(array_replace($data,['email'=>'invalid','angkatan'=>'abc'])))===2,'Email dan angkatan tidak valid ditolak');
$path=sys_get_temp_dir().'/bkk-test-'.bin2hex(random_bytes(8)).'.xlsx';
try{
    $zip=new PharData($path,0,null,Phar::ZIP);
    $zip['xl/workbook.xml']='<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Peserta" r:id="rId1"/></sheets></workbook>';
    $zip['xl/_rels/workbook.xml.rels']='<Relationships><Relationship Id="rId1" Target="worksheets/sheet1.xml"/></Relationships>';
    $zip['xl/sharedStrings.xml']='<sst><si><t>Peserta Uji</t></si></sst>';
    $header='';foreach(array_keys(participant_columns()) as $i=>$key){$header.='<c r="'.chr(65+$i).'1" t="inlineStr"><is><t>'.$key.'</t></is></c>';}
    $body='<row r="2"><c r="A2" t="inlineStr"><is><t>00123</t></is></c><c r="B2" t="s"><v>0</v></c><c r="C2" t="inlineStr"><is><t>XI A</t></is></c><c r="E2"><v>2025</v></c></row>';
    $sheet='<worksheet><sheetData><row r="1">'.$header.'</row>'.$body.'</sheetData></worksheet>';
    $zip['xl/worksheets/sheet1.xml']=$sheet;
    $rows=participant_xlsx_rows($path);
    check(count($rows)===1&&$rows[0][0]==='00123'&&$rows[0][1]==='Peserta Uji'&&$rows[0][3]===''&&$rows[0][4]==='2025','XLSX inline/shared strings, angka, kolom kosong, dan nol awal');
    $zip['xl/worksheets/sheet1.xml']=str_replace('<v>2025</v>','<f>2000+25</f><v>2025</v>',$sheet);
    $rejected=false;try{participant_xlsx_rows($path);}catch(InvalidArgumentException $e){$rejected=true;}
    check($rejected,'Rumus Excel ditolak');
    $zip['xl/worksheets/sheet1.xml']=str_replace('<t>nis</t>','<t>kolom_salah</t>',$sheet);
    $rejected=false;try{participant_xlsx_rows($path);}catch(InvalidArgumentException $e){$rejected=true;}
    check($rejected,'Header salah ditolak');
    $zip['xl/worksheets/sheet1.xml']='<!DOCTYPE worksheet [<!ENTITY x "test">]>'.$sheet;
    $rejected=false;try{participant_xlsx_rows($path);}catch(InvalidArgumentException $e){$rejected=true;}
    check($rejected,'XML entity ditolak');
}finally{unset($zip);if(is_file($path)){unlink($path);}}
