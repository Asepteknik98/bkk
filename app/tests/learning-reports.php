<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/helpers/learning-reports.php';
function check_report(bool $ok,string $message): void { if(!$ok){fwrite(STDERR,'FAIL '.$message.PHP_EOL);exit(1);}echo 'PASS '.$message.PHP_EOL; }
$base=['kompetensi'=>'DATA UJI','nilai'=>'85,50','predikat'=>'kompeten','deskripsi'=>'DATA UJI'];
$rows=report_items(['items'=>[$base,['kompetensi'=>'','nilai'=>'','predikat'=>'','deskripsi'=>'']]]);
check_report(count($rows)===1&&$rows[0]['nilai']==='85.50','Nilai desimal dinormalisasi dan baris kosong diabaikan');
foreach(['0','100','99.99','100.00',''] as $score){check_report(count(report_items(['items'=>[array_replace($base,['nilai'=>$score])]]))===1,'Nilai valid: '.($score===''?'opsional':$score));}
foreach(['-1','101','100.01','1e2','NaN','1.234'] as $score){try{report_items(['items'=>[array_replace($base,['nilai'=>$score])]]);check_report(false,'Nilai harus ditolak');}catch(InvalidArgumentException){check_report(true,'Nilai tidak sah ditolak: '.$score);}}
foreach([['items'=>[]],['items'=>array_fill(0,51,$base)],['items'=>['invalid']],['items'=>[array_replace($base,['predikat'=>'admin'])]],['items'=>[array_replace($base,['nilai'=>[]])]]] as $input){try{report_items($input);check_report(false,'Input harus ditolak');}catch(InvalidArgumentException){check_report(true,'Baris/tipe/predikat tidak sah ditolak');}}
check_report(!isset(participant_columns()['nis']),'Nomor peserta tidak menjadi kolom formulir/template/ekspor');
$student=participant_input(['nama'=>'DATA UJI','kelas'=>'XII','angkatan'=>'2026']);
check_report(participant_errors($student)===[],'Identitas peserta valid tanpa nomor peserta');
