<?php
declare(strict_types=1);
require_once __DIR__.'/accounts.php';
require_once __DIR__.'/public.php';
require_once __DIR__.'/students.php';

function require_student(): array
{
    $user=require_role('siswa');
    $student=panel_query('SELECT s.* FROM siswa s JOIN student_accounts sa ON sa.siswa_id=s.id WHERE sa.user_id=:id',['id'=>(int)$user['id']])->fetch();
    if(!$student){http_response_code(403);exit('Akun belum terhubung ke Data Siswa. Hubungi pengelola.');}
    return [$user,$student];
}

/** A missing class must never match all unassigned records. */
function student_group(array $student): array
{
    return ['angkatan'=>(int)$student['angkatan'],'industri'=>$student['kelas_industri']];
}

function student_event(array $student,int $id): array
{
    $row=panel_query("SELECT * FROM attendance_events WHERE id=:id AND angkatan=:angkatan AND kelas_industri=:industri AND kelas_industri<>''",['id'=>$id]+student_group($student))->fetch();
    if(!$row){panel_not_found();}return $row;
}

function student_document(array $student,int $id): array
{
    $row=panel_query("SELECT * FROM learning_documents WHERE id=:id AND angkatan=:angkatan AND kelas_industri=:industri AND kelas_industri<>''",['id'=>$id]+student_group($student))->fetch();
    if(!$row){panel_not_found();}return $row;
}

function student_speaker_scope(): string
{
    return "(:utama<>'' AND s.kelas_industri=:industri_utama) OR EXISTS(SELECT 1 FROM speaker_events se JOIN attendance_events e ON e.id=se.event_id WHERE se.speaker_id=s.id AND e.angkatan=:angkatan AND e.kelas_industri=:industri AND e.kelas_industri<>'')";
}

function student_speaker_params(array $student): array
{
    return ['utama'=>$student['kelas_industri'],'industri_utama'=>$student['kelas_industri']]+student_group($student);
}

function student_speaker(array $student,int $id): array
{
    $row=panel_query('SELECT s.* FROM speakers s WHERE s.id=:id AND ('.student_speaker_scope().')',['id'=>$id]+student_speaker_params($student))->fetch();
    if(!$row){panel_not_found();}return $row;
}

function student_start(array $user,array $student,string $title,string $active): void
{
    $pageTitle=$title;$activePage='siswa/';require APP_ROOT.'/app/layouts/header.php';
    echo '<main id="konten" class="container portal-container page-main"><div class="page-heading"><p class="eyebrow">Ruang Siswa</p><h1>'.e($title).'</h1><p>'.e($student['nama']).' · '.e($student['kelas']).' · Angkatan '.e((string)$student['angkatan']).' · '.e($student['kelas_industri']?:'Kelas industri belum ditentukan').'</p></div><nav class="surface d-flex flex-wrap gap-3 mb-4" aria-label="Menu siswa">';
    foreach([''=>'Beranda','kegiatan.php'=>'Kegiatan','capaian.php'=>'Capaian pembelajaran','pemateri.php'=>'Pemateri','absensi.php'=>'Absensi saya','akun.php'=>'Akun saya'] as $path=>$label){echo '<a href="'.e(base_url('siswa/'.$path)).'" '.($active===$path?'aria-current="page"':'').'>'.e($label).'</a>';}
    echo '<form method="post" action="'.e(base_url('auth/logout.php')).'">'.csrf_field().'<button type="submit" class="button">Keluar</button></form></nav>';
    if(isset($_SESSION['panel_message'])){echo '<p class="surface" role="status">'.e($_SESSION['panel_message']).'</p>';unset($_SESSION['panel_message']);}
}

function student_end(): void
{
    echo '</main>';require APP_ROOT.'/app/layouts/footer.php';
}

function student_pagination(string $path,int $page,int $pages,array $params=[]): void
{
    echo '<nav class="pagination mt-4" aria-label="Halaman">';foreach(['Sebelumnya'=>$page-1,'Berikutnya'=>$page+1] as $label=>$target){if($target<1||$target>$pages){continue;}echo '<a class="button" href="'.e(base_url('siswa/'.$path.'?'.http_build_query($params+['page'=>$target]))).'">'.e($label).'</a>';}echo '</nav>';
}
