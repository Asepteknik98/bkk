<?php
declare(strict_types=1);

function input_text(array $input, string $key): string
{
    return is_string($input[$key] ?? null) ? trim(str_replace(["\r\n", "\r"], "\n", $input[$key])) : '';
}

function valid_text(string $value, int $max, bool $required = true): bool
{
    return (!$required || $value !== '') && !str_contains($value, "\0")
        && preg_match('//u', $value) === 1 && preg_match_all('/./us', $value) <= $max;
}

function slugify(string $value): string
{
    $ascii = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) : $value;
    $slug = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii ?: $value)), '-');
    return rtrim(substr($slug, 0, 170), '-') ?: 'berita';
}
