<?php
declare(strict_types=1);

// Salin sebagai app.local.php. File lokal tidak disimpan di Git.
return [
    'name' => 'BKK SMK JAYA BUANA',
    'base_url' => '/bkk', // Gunakan '' untuk root domain, atau URL lengkap HTTPS.
    'timezone' => 'Asia/Jakarta',
    // Layanan peta dapat diganti lewat app.local.php tanpa mengubah kode.
    'maps' => [
        'geocoder_url' => 'https://nominatim.openstreetmap.org',
        'tile_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'bkk_jaya_buana',
        'username' => '',
        'password' => '',
    ],
];
