<?php
declare(strict_types=1);

function app_config(?string $key = null): mixed
{
    return $key === null ? $GLOBALS['app_config'] : ($GLOBALS['app_config'][$key] ?? null);
}

function base_url(string $path = ''): string
{
    $base = rtrim((string) app_config('base_url'), '/');
    return $base . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

/** Redirect hanya ke path aplikasi, bukan URL eksternal dari input pengguna. */
function redirect(string $path = '', int $status = 302): never
{
    if (preg_match('/[\r\n]/', $path) || preg_match('~^(?:[a-z][a-z0-9+.-]*:|//)~i', $path)) {
        throw new InvalidArgumentException('Tujuan redirect harus berupa path aplikasi.');
    }
    header('Location: ' . base_url($path), true, $status);
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
