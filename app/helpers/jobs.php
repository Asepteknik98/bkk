<?php
declare(strict_types=1);
require_once __DIR__ . '/validation.php';

function http_url(string $value): bool
{
    if ($value === '' || strlen($value) > 2048 || preg_match('/[\x00-\x20\x7f]/', $value)
        || !filter_var($value, FILTER_VALIDATE_URL)) { return false; }
    $parts = parse_url($value);
    return is_array($parts) && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
        && !empty($parts['host']) && !isset($parts['user']) && !isset($parts['pass']);
}

function job_deadline(?string $value): ?DateTimeImmutable
{
    if (!$value || !preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/D', $value)) { return null; }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Jakarta'));
    return $date && $date->format('Y-m-d') === $value ? $date : null;
}

/** Hari batas lamaran masih dibuka; tepat pukul 00:00 WIB hari berikutnya ditutup. */
function job_is_open(array $job, ?DateTimeImmutable $now = null): bool
{
    $deadline = job_deadline($job['batas_lamaran'] ?? null);
    $now ??= new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
    return ($job['status'] ?? 'published') === 'published' && $deadline !== null && $now < $deadline->modify('+1 day');
}

function job_status_label(array $job): string
{
    if (($job['status'] ?? 'published') !== 'published') { return 'Tidak dibuka'; }
    if (!job_deadline($job['batas_lamaran'] ?? null)) { return 'Batas belum ditentukan'; }
    return job_is_open($job) ? 'Masih dibuka' : 'Sudah ditutup';
}

function job_url(array $job): string
{
    return base_url('lowongan-kerja/detail.php?slug=' . rawurlencode($job['slug']));
}
