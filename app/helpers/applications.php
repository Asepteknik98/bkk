<?php
declare(strict_types=1);
require_once __DIR__ . '/jobs.php';

function application_input(array $input): array
{
    $data = [];
    foreach (['nama','email','telepon','pendidikan','alamat','pesan'] as $field) {
        $data[$field] = input_text($input, $field);
    }
    return $data;
}

function application_errors(array $data): array
{
    $errors = [];
    foreach (['nama'=>[150,'Nama lengkap'],'email'=>[191,'Email'],'telepon'=>[30,'Nomor WhatsApp/telepon'],'pendidikan'=>[150,'Pendidikan terakhir'],'alamat'=>[2000,'Alamat domisili'],'pesan'=>[5000,'Pesan lamaran']] as $field=>[$max,$label]) {
        if (!valid_text($data[$field], $max, $field !== 'pesan')) {
            $errors[] = "$label harus berupa teks valid, maksimal $max karakter.";
        }
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'Masukkan alamat email yang valid.'; }
    if (!preg_match('/^\+?[0-9][0-9 ()-]{7,28}$/D', $data['telepon'])) { $errors[] = 'Masukkan nomor WhatsApp/telepon yang valid.'; }
    return $errors;
}
