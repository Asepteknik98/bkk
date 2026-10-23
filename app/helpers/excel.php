<?php
declare(strict_types=1);

function excel_column(int $number): string
{
    $name='';while($number>0){$number--;$name=chr(65+$number%26).$name;$number=intdiv($number,26);}return $name;
}

function excel_text(string $value): string
{
    $value=mb_convert_encoding($value,'UTF-8','UTF-8');
    return mb_substr(preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u','',$value)??'',0,32767);
}

/** Explicit text cells preserve identifiers and prevent formula execution. */
function excel_row(XMLWriter $xml,int $number,array $values,bool $header=false): void
{
    $xml->startElement('row');$xml->writeAttribute('r',(string)$number);
    foreach(array_values($values) as $index=>$value){
        $xml->startElement('c');$xml->writeAttribute('r',excel_column($index+1).$number);
        if($header){$xml->writeAttribute('s','1');}
        if(!$header&&(is_int($value)||is_float($value))&&is_finite((float)$value)){$xml->writeElement('v',(string)$value);}
        else{$xml->writeAttribute('t','inlineStr');$xml->startElement('is');$xml->startElement('t');$xml->writeAttribute('xml:space','preserve');$xml->text(excel_text((string)($value??'')));$xml->endElement();$xml->endElement();}
        $xml->endElement();
    }
    $xml->endElement();
}

/** Each sheet supplies fixed column keys/labels and an iterable of associative rows. */
function excel_download(string $filename,array $sheets): void
{
    $directory=sys_get_temp_dir().'/bkk-excel-'.bin2hex(random_bytes(16));
    if(!mkdir($directory,0700)){throw new RuntimeException('File Excel gagal dibuat.');}
    $paths=[];$archive=null;
    try{
        $archivePath=$directory.'/export.zip';$paths[]=$archivePath;$archive=new PharData($archivePath,0,null,Phar::ZIP);
        $types='<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $workbook='<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        $relations='<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach(array_values($sheets) as $index=>$sheet){
            $number=$index+1;$path=$directory.'/sheet'.$number.'.xml';$paths[]=$path;
            $xml=new XMLWriter();if(!$xml->openUri($path)){throw new RuntimeException('Lembar Excel gagal dibuat.');}
            $xml->startDocument('1.0','UTF-8');$xml->startElementNS(null,'worksheet','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $xml->startElement('sheetViews');$xml->startElement('sheetView');$xml->writeAttribute('workbookViewId','0');$xml->startElement('pane');foreach(['ySplit'=>'1','topLeftCell'=>'A2','activePane'=>'bottomLeft','state'=>'frozen'] as $key=>$value){$xml->writeAttribute($key,$value);}$xml->endElement();$xml->endElement();$xml->endElement();
            $xml->startElement('cols');foreach(array_values($sheet['columns']) as $i=>$label){$xml->startElement('col');$xml->writeAttribute('min',(string)($i+1));$xml->writeAttribute('max',(string)($i+1));$xml->writeAttribute('width',(string)min(45,max(16,mb_strlen($label)+4)));$xml->writeAttribute('customWidth','1');$xml->endElement();}$xml->endElement();
            $xml->startElement('sheetData');excel_row($xml,1,array_values($sheet['columns']),true);$rowNumber=1;
            foreach($sheet['rows'] as $row){if($rowNumber>=1048576){throw new InvalidArgumentException('Data melebihi batas Excel. Persempit filter lalu unduh kembali.');}$values=[];foreach($sheet['columns'] as $key=>$label){$values[]=$row[$key]??null;}excel_row($xml,++$rowNumber,$values);if($rowNumber%250===0){$xml->flush();}}
            $xml->endElement();$xml->startElement('autoFilter');$xml->writeAttribute('ref','A1:'.excel_column(count($sheet['columns'])).$rowNumber);$xml->endElement();$xml->endElement();$xml->endDocument();$xml->flush();unset($xml);
            $archive->addFile($path,'xl/worksheets/sheet'.$number.'.xml');
            $types.='<Override PartName="/xl/worksheets/sheet'.$number.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $workbook.='<sheet name="'.htmlspecialchars($sheet['name'],ENT_XML1|ENT_QUOTES,'UTF-8').'" sheetId="'.$number.'" r:id="rId'.$number.'"/>';
            $relations.='<Relationship Id="rId'.$number.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$number.'.xml"/>';
        }
        $archive['[Content_Types].xml']=$types.'</Types>';
        $archive['xl/workbook.xml']=$workbook.'</sheets></workbook>';
        $archive['xl/_rels/workbook.xml.rels']=$relations.'<Relationship Id="styles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        $archive['_rels/.rels']='<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
        $archive['xl/styles.xml']='<?xml version="1.0"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
        unset($archive);$archive=null;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="'.$filename.'"');header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');header('Content-Length: '.filesize($archivePath));readfile($archivePath);
    }finally{unset($xml,$archive);foreach(array_reverse($paths) as $path){if(is_file($path)){unlink($path);}}rmdir($directory);}
}
