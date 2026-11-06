<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require_once dirname(__DIR__).'/helpers/industry-forums.php';
require_once dirname(__DIR__).'/helpers/industry-ideas.php';
function forum_check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label.PHP_EOL;}
$forum=industry_forum_input(['nama'=>'Sarasehan','tanggal'=>'2026-11-06','jam'=>'08:30','lokasi'=>'Aula','pic_sekolah'=>'Guru PIC']);
forum_check(industry_forum_errors($forum)===[],'Jadwal luring valid');
forum_check(industry_forum_errors(array_replace($forum,['lokasi'=>'','tautan'=>'https://example.test/rapat']))===[],'Jadwal daring tanpa lokasi diterima');
forum_check(industry_forum_errors(array_replace($forum,['lokasi'=>'']))!==[],'Lokasi atau tautan wajib diisi');
forum_check(industry_forum_errors(array_replace($forum,['tautan'=>'javascript:alert(1)']))!==[],'Skema tautan berbahaya ditolak');
forum_check(industry_forum_errors(array_replace($forum,['tanggal'=>'2026-02-29']))!==[],'Tanggal tidak valid ditolak');
forum_check(industry_forum_errors(array_replace($forum,['jam'=>'24:00']))!==[],'Jam tidak valid ditolak');
$invitees=industry_forum_invitees(['mitra_ids'=>['1','1'],'pic_nama'=>['1'=>'PIC'],'pic_kontak'=>['1'=>'pic@example.test']]);
forum_check(count($invitees)===1&&$invitees[1]['pic_nama']==='PIC','Undangan dideduplikasi dan PIC terikat mitra');
foreach([[],['mitra_ids'=>['1']],['mitra_ids'=>['../1']],['mitra_ids'=>['1'],'pic_nama'=>'PIC']] as $input){$rejected=false;try{industry_forum_invitees($input);}catch(InvalidArgumentException){$rejected=true;}forum_check($rejected,'Undangan/PIC tidak valid ditolak');}
$data=industry_idea_input(['nama_pengirim'=>'Pengirim','email'=>'user@example.test','judul'=>'Usulan','isi'=>'Isi usulan','perusahaan'=>'Palsu','mitra_id'=>'999']);
forum_check(industry_idea_errors($data,true)===[],'Identitas dan usulan valid diterima');
forum_check(!isset($data['perusahaan'],$data['mitra_id']),'Perusahaan/ID kiriman tidak masuk data usulan');
forum_check(industry_idea_errors($data,false)!==[],'Persetujuan diperlukan');
forum_check(industry_idea_errors(array_replace($data,['email'=>'']),true)!==[],'Minimal satu kontak diperlukan');
forum_check(industry_idea_errors(array_replace($data,['email'=>'bad']),true)!==[],'Email salah ditolak');
$phone=industry_idea_input(['nama_pengirim'=>'Pengirim','telepon'=>'0812 3456-7890','judul'=>'Judul','isi'=>'Isi']);
forum_check($phone['telepon']==='081234567890'&&industry_idea_errors($phone,true)===[],'Telepon dinormalisasi dan dapat menggantikan email');
forum_check(industry_idea_errors(array_replace($data,['isi'=>str_repeat('x',10001)]),true)!==[],'Isi melebihi batas ditolak');
$token=bin2hex(random_bytes(32));$hash=industry_idea_hash($token);forum_check($hash===hash('sha256',$token),'Token diubah menjadi hash');
foreach(['',str_repeat('a',63),$token."\n",str_repeat('g',64)] as $bad){forum_check(industry_idea_hash($bad)===null,'Token tidak sah ditolak');}
$key=str_repeat('b',48);$forms=[$key=>['hash'=>$hash,'expires'=>time()+60]];
forum_check(industry_idea_form_matches($key,$hash,$forms),'Form terikat tautan yang sama');
forum_check(!industry_idea_form_matches($key,str_repeat('a',64),$forms),'Form lama tidak boleh berpindah perusahaan');
$forms[$key]['expires']=time()-1;forum_check(!industry_idea_form_matches($key,$hash,$forms),'Form kedaluwarsa ditolak');
forum_check(panel_route_permission(APP_ROOT.'/panel/sarasehan/unduh.php',[])==='sarasehan','Unduhan jadwal memakai izin Sarasehan');
forum_check(panel_route_permission(APP_ROOT.'/panel/ide-industri/detail.php',[])==='ide-industri','Isi/kontak pengirim memakai izin Kotak Ide');
