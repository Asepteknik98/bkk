<?php
declare(strict_types=1);
require_once __DIR__.'/panel.php';
require_once __DIR__.'/tracer.php';

/** Satu builder filter untuk daftar, rekap, dan CSV. */
function tracer_filters(array $input):array
{
    $filters=[];$params=[];$where='1=1';
    foreach(['jurusan','tahun_lulus','status_kegiatan'] as $key){
        if(isset($input[$key])&&!is_string($input[$key])){http_response_code(400);exit('Filter tidak valid.');}
        $value=input_text($input,$key);$filters[$key]=$value;if($value===''){continue;}
        if(($key==='jurusan'&&!valid_text($value,150))||($key==='tahun_lulus'&&(!preg_match('/^[0-9]{4}$/D',$value)||(int)$value<1900||(int)$value>2155))||($key==='status_kegiatan'&&!array_key_exists($value,tracer_statuses()))){http_response_code(400);exit('Filter tidak valid.');}
        $where.=" AND $key=:$key";$params[$key]=$value;
    }
    return [$filters,$where,$params];
}

function tracer_filter_form(array $filters):void
{
    $departments=panel_query('SELECT DISTINCT jurusan FROM tracer_responses ORDER BY jurusan')->fetchAll();
    $years=panel_query('SELECT DISTINCT tahun_lulus FROM tracer_responses ORDER BY tahun_lulus DESC')->fetchAll();
    ?>
    <nav class="alumni-cohorts" aria-label="Pilih angkatan alumni"><span><?= panel_icon('calendar') ?> Angkatan</span><a href="<?= e(base_url('panel/tracer-study/'.(basename($_SERVER['SCRIPT_NAME']??'index.php')==='rekap.php'?'rekap.php':'').'?' . http_build_query(array_replace($filters,['tahun_lulus'=>''])))) ?>" <?= $filters['tahun_lulus']===''?'aria-current="page"':'' ?>>Semua tahun</a><?php $visibleYears=array_slice($years,0,8); if($filters['tahun_lulus']!==''&&!in_array($filters['tahun_lulus'],array_map('strval',array_column($visibleYears,'tahun_lulus')),true)){$visibleYears[]=['tahun_lulus'=>$filters['tahun_lulus']];} foreach($visibleYears as $year): ?><a href="<?= e(base_url('panel/tracer-study/'.(basename($_SERVER['SCRIPT_NAME']??'index.php')==='rekap.php'?'rekap.php':'').'?' . http_build_query(array_replace($filters,['tahun_lulus'=>(string)$year['tahun_lulus']])))) ?>" <?= $filters['tahun_lulus']===(string)$year['tahun_lulus']?'aria-current="page"':'' ?>><?= (int)$year['tahun_lulus'] ?></a><?php endforeach; ?></nav>
    <form class="filter-form card" method="get"><label for="jurusan">Jurusan<select id="jurusan" name="jurusan"><option value="">Semua jurusan</option><?php foreach($departments as $row):?><option value="<?= e($row['jurusan']) ?>" <?= $filters['jurusan']===$row['jurusan']?'selected':'' ?>><?= e($row['jurusan']) ?></option><?php endforeach;?></select></label><label for="tahun_lulus">Tahun lulus<select id="tahun_lulus" name="tahun_lulus"><option value="">Semua tahun</option><?php foreach($years as $row):?><option value="<?= $row['tahun_lulus'] ?>" <?= $filters['tahun_lulus']===(string)$row['tahun_lulus']?'selected':'' ?>><?= $row['tahun_lulus'] ?></option><?php endforeach;?></select></label><label for="status_kegiatan">Status<select id="status_kegiatan" name="status_kegiatan"><option value="">Semua status</option><?php foreach(tracer_statuses() as $value=>$label):?><option value="<?= $value ?>" <?= $filters['status_kegiatan']===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach;?></select></label><button type="submit">Terapkan</button><a href="<?= e(base_url('panel/tracer-study/')) ?>">Reset</a></form>
    <?php
}
