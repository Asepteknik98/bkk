CREATE TABLE IF NOT EXISTS lamaran (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lowongan_id BIGINT UNSIGNED NOT NULL,
    posisi VARCHAR(200) NOT NULL,
    perusahaan VARCHAR(200) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    telepon VARCHAR(30) NOT NULL,
    pendidikan VARCHAR(150) NOT NULL,
    alamat TEXT NOT NULL,
    pesan TEXT NOT NULL,
    submission_key CHAR(64) NOT NULL,
    status ENUM('baru','diproses','diterima','ditolak') NOT NULL DEFAULT 'baru',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lamaran_submission (submission_key),
    KEY idx_lamaran_created (created_at, id),
    KEY idx_lamaran_lowongan (lowongan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
