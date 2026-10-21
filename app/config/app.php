<?php
declare(strict_types=1);

$config = require __DIR__ . '/app.example.php';
$localFile = __DIR__ . '/app.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (!is_array($local)) {
        throw new RuntimeException('Konfigurasi lokal harus mengembalikan array.');
    }
    $config = array_replace_recursive($config, $local);
}
return $config;
