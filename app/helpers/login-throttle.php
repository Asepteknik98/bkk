<?php
declare(strict_types=1);

/** Reservasi percobaan atomik lintas session. Tidak menyimpan email/IP mentah. */
function login_attempt(string $key, int $limit): int
{
    $directory = APP_ROOT . '/storage/login-attempts';
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Penyimpanan pembatas login tidak tersedia.');
    }
    $handle = fopen($directory . '/' . hash('sha256', $key) . '.json', 'c+');
    if ($handle === false) {
        throw new RuntimeException('Penyimpanan pembatas login tidak tersedia.');
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Pembatas login tidak dapat dikunci.');
        }
        $data = json_decode(stream_get_contents($handle), true);
        $now = time();
        if (!is_array($data) || ($data['until'] ?? 0) <= $now) {
            $data = ['count' => 0, 'until' => $now + 900];
        }
        if ($data['count'] >= $limit) {
            return max(1, $data['until'] - $now);
        }
        $data['count']++;
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        rewind($handle);
        if (!ftruncate($handle, 0) || fwrite($handle, $json) !== strlen($json) || !fflush($handle)) {
            throw new RuntimeException('Pembatas login gagal disimpan.');
        }
        return 0;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
