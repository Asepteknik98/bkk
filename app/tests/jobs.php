<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Hanya CLI.');}
require_once dirname(__DIR__).'/helpers/jobs.php';
function check_job(bool $condition,string $label):void{if(!$condition){throw new RuntimeException('Gagal: '.$label);}}
$job=['status'=>'published','batas_lamaran'=>'2026-10-21'];
date_default_timezone_set('Pacific/Honolulu');
check_job(job_is_open($job,new DateTimeImmutable('2026-10-21T16:59:59.999999Z')),'tetap terbuka pada akhir hari WIB');
check_job(!job_is_open($job,new DateTimeImmutable('2026-10-21T17:00:00Z')),'ditutup tepat tengah malam WIB berikutnya');
check_job(job_is_open($job,new DateTimeImmutable('2026-10-21T00:00:00+07:00')),'awal hari batas lamaran');
check_job(!job_is_open(['status'=>'draft','batas_lamaran'=>'2099-01-01']),'draft tidak dibuka');
check_job(!job_is_open(['status'=>'published','batas_lamaran'=>null]),'tanggal kosong tidak dianggap terbuka');
check_job(job_deadline('2028-02-29')!==null&&job_deadline('2026-02-29')===null,'tahun kabisat');
check_job(job_deadline('2026-02-30')===null&&job_deadline('2026-13-01')===null,'tanggal tidak valid');
foreach(['https://example.org/jobs?id=1','http://example.org/daftar'] as $url){check_job(http_url($url),'URL http/https valid');}
foreach(['javascript:alert(1)','data:text/html,test','file:///etc/passwd','//example.org','https://user:pass@example.org/',"https://example.org/\r\nLocation:x",'https://',''] as $url){check_job(!http_url($url),'URL berbahaya/tidak valid ditolak');}
echo "LULUS: batas tanggal WIB, zona waktu, draft, tanggal valid, dan URL http/https.\n";
