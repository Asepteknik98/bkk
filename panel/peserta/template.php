<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/participants.php';
require_once APP_ROOT.'/app/helpers/excel.php';
$user=require_role('admin','staf_bkk');
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET'){header('Allow: GET');http_response_code(405);exit('Metode tidak diizinkan.');}
$ready=(bool)panel_query("SHOW TABLES LIKE 'siswa'")->fetchColumn();
$choices=participant_choice_lists($ready);
$fields=['kelas'=>'PilihanKelas','jurusan'=>'PilihanJurusan','angkatan'=>'PilihanAngkatan','kelas_industri'=>'PilihanIndustri','tahun_pkl'=>'PilihanTahunPKL'];
$escape=static fn($v)=>htmlspecialchars(excel_text((string)$v),ENT_XML1|ENT_QUOTES,'UTF-8');
$cell=static fn($ref,$value,$style=0)=>'<c r="'.$ref.'" t="inlineStr" s="'.$style.'"><is><t xml:space="preserve">'.$escape($value).'</t></is></c>';
$ns='http://schemas.openxmlformats.org/spreadsheetml/2006/main';
$view='<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
$headers='';foreach(array_keys(participant_columns()) as $i=>$key){$headers.=$cell(excel_column($i+1).'1',$key,2);}
$columns='<cols>';foreach(array_keys(participant_columns()) as $i=>$key){$columns.='<col min="'.($i+1).'" max="'.($i+1).'" width="'.($key==='nama'?28:23).'" style="1" customWidth="1"/>';}$columns.='</cols>';
$definitions='';$validations='';$choiceHeaders='';$choiceKeys=array_keys($fields);
foreach($fields as $key=>$range){
    $col=excel_column(array_search($key,$choiceKeys,true)+1);$choiceHeaders.=$cell($col.'1',participant_columns()[$key],2);
    if(!$choices[$key]){continue;}
    $definitions.='<definedName name="'.$range.'">'."'Pilihan'!$".$col.'$2:$'.$col.'$'.(count($choices[$key])+1).'</definedName>';
    $inputCol=excel_column(array_search($key,array_keys(participant_columns()),true)+1);
    $validations.='<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" errorStyle="stop" errorTitle="Pilihan tidak sesuai" error="Pilih dari daftar pada template terbaru." promptTitle="Pilih dari daftar" prompt="Daftar mengikuti Pengaturan saat template diunduh." sqref="'.$inputCol.'2:'.$inputCol.'1001"><formula1>'.$range.'</formula1></dataValidation>';
}
$choiceRows='<row r="1">'.$choiceHeaders.'</row>';$maxChoices=max(array_map('count',$choices));
for($r=0;$r<$maxChoices;$r++){$cells='';foreach($choiceKeys as $i=>$key){if(isset($choices[$key][$r])){$cells.=$cell(excel_column($i+1).($r+2),$choices[$key][$r]);}}$choiceRows.='<row r="'.($r+2).'">'.$cells.'</row>';}
$guide=[
    ['Bagian','Petunjuk'],['Peserta','Isi data siswa di sheet Peserta mulai baris 2. Jangan ubah judul/urutan kolom.'],
    ['Wajib','nama, kelas, angkatan wajib diisi. Tidak ada siswa contoh yang perlu dihapus.'],
    ['Dropdown','Kelas, jurusan, angkatan, kelas industri dan tahun PKL memakai daftar pada sheet Pilihan. Jika daftar kosong, minta Admin melengkapi Pengaturan lalu unduh template baru.'],
    ['Kelas industri','Pilih nama kelas industri dari Pengaturan, bukan Ya/Tidak. Boleh kosong.'],
    ['Tahun PKL','Pilih tahun/tahun ajaran dari Pengaturan. Boleh kosong.'],
    ['Pilihan terbaru','Daftar adalah salinan saat unduh. Jangan mengubah sheet Pilihan untuk menambah master; atur di website kemudian unduh ulang.'],
    ['Telepon','Sel data diformat sebagai teks untuk mempertahankan nol di awal. Jangan memakai rumus.'],
    ['Batas','Maksimal 1.000 siswa dan 5 MB per impor. Hanya sheet Peserta yang dibaca.'],
    ['Penting','Impor menambah siswa baru. Jangan mengunggah berkas yang sama berulang kali. Jika ada kesalahan, perbaiki semua baris lalu unggah kembali.'],
];$guideRows='';foreach($guide as $i=>$values){$guideRows.='<row r="'.($i+1).'">'.$cell('A'.($i+1),$values[0],$i===0?2:0).$cell('B'.($i+1),$values[1],$i===0?2:0).'</row>';}
$path=sys_get_temp_dir().'/bkk-template-'.bin2hex(random_bytes(12)).'.zip';
try{
    $zip=new PharData($path,0,null,Phar::ZIP);
    $types='<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
    for($i=1;$i<=3;$i++){$types.='<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';}$zip['[Content_Types].xml']=$types.'</Types>';
    $zip['_rels/.rels']='<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    $zip['xl/workbook.xml']='<?xml version="1.0"?><workbook xmlns="'.$ns.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Peserta" sheetId="1" r:id="rId1"/><sheet name="Pilihan" sheetId="2" r:id="rId2"/><sheet name="Panduan" sheetId="3" r:id="rId3"/></sheets>'.($definitions!==''?'<definedNames>'.$definitions.'</definedNames>':'').'</workbook>';
    $relations='<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';for($i=1;$i<=3;$i++){$relations.='<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';}$zip['xl/_rels/workbook.xml.rels']=$relations.'<Relationship Id="styles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    $zip['xl/worksheets/sheet1.xml']='<?xml version="1.0"?><worksheet xmlns="'.$ns.'">'.$view.$columns.'<sheetData><row r="1">'.$headers.'</row></sheetData><autoFilter ref="A1:'.excel_column(count(participant_columns())).'1001"/>'.($validations!==''?'<dataValidations count="'.substr_count($validations,'<dataValidation ').'">'.$validations.'</dataValidations>':'').'</worksheet>';
    $zip['xl/worksheets/sheet2.xml']='<?xml version="1.0"?><worksheet xmlns="'.$ns.'">'.$view.'<cols><col min="1" max="5" width="28" customWidth="1"/></cols><sheetData>'.$choiceRows.'</sheetData></worksheet>';
    $zip['xl/worksheets/sheet3.xml']='<?xml version="1.0"?><worksheet xmlns="'.$ns.'">'.$view.'<cols><col min="1" max="1" width="24" customWidth="1"/><col min="2" max="2" width="110" customWidth="1"/></cols><sheetData>'.$guideRows.'</sheetData></worksheet>';
    $zip['xl/styles.xml']='<?xml version="1.0"?><styleSheet xmlns="'.$ns.'"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF24548B"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/><xf numFmtId="49" fontId="0" fillId="0" borderId="0" applyNumberFormat="1"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    unset($zip);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="template-peserta.xlsx"');header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');header('Content-Length: '.filesize($path));readfile($path);
}finally{unset($zip);if(is_file($path)){unlink($path);}}
