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

/** Ringkasan UTF-8 dengan batas karakter tanpa ekstensi tambahan. */
function meta_description(string $text, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    preg_match_all('/./us', $text, $characters);
    return count($characters[0]) > $length ? implode('', array_slice($characters[0], 0, $length - 1)) . '…' : $text;
}

/** Ubah paragraf teks biasa menjadi HTML ter-escape; bukan renderer HTML dari input. */
function plain_text_paragraphs(string $value): string
{
    $text = trim(str_replace(["\r\n", "\r"], "\n", $value));
    if ($text === '') { return ''; }
    $paragraphs = preg_split('/\n[\t ]*\n+/', $text) ?: [$text];
    return implode("\n", array_map(static fn(string $paragraph): string => '<p>' . nl2br(e($paragraph), false) . '</p>', $paragraphs));
}
