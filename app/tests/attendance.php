<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/attendance.php';
$groups=[['angkatan'=>2026,'kelas_industri'=>'DATA UJI Industri A','jumlah'=>2],['angkatan'=>2025,'kelas_industri'=>'DATA UJI Industri B','jumlah'=>1]];
$selected=attendance_select_group(['kelompok'=>attendance_group_key($groups[0]),'angkatan'=>'2025','kelas_industri'=>'palsu'],$groups);
if($selected!==['angkatan'=>'2026','kelas_industri'=>'DATA UJI Industri A']){throw new RuntimeException('Kelompok harus mengikuti pilihan server, bukan field yang disisipkan.');}
echo "PASS pilihan kelompok mengikat angkatan dan kelas industri yang tersedia\n";
foreach([[],['kelompok'=>'palsu'],['kelompok'=>[]],['kelompok'=>attendance_group_key($groups[1])]] as $input){
    try{attendance_select_group($input,[$groups[0]]);throw new RuntimeException('Kelompok tidak tersedia seharusnya ditolak.');}
    catch(InvalidArgumentException){echo "PASS kelompok kosong, palsu, atau tidak tersedia ditolak\n";}
}
try{attendance_select_group(['kelompok'=>attendance_group_key($groups[0])],[]);throw new RuntimeException('Kelompok kosong seharusnya ditolak.');}
catch(InvalidArgumentException){echo "PASS data siswa kosong ditangani dengan penjelasan\n";}
