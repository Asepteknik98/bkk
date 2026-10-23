CREATE TABLE IF NOT EXISTS program_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(50) NOT NULL,
    program_id BIGINT UNSIGNED NULL,
    program_judul VARCHAR(255) NULL,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    telepon VARCHAR(30) NOT NULL,
    pesan TEXT NOT NULL,
    status ENUM('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
    balasan TEXT NULL,
    replied_by BIGINT UNSIGNED NULL,
    updated_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    submission_key CHAR(64) NOT NULL UNIQUE,
    tracking_hash CHAR(64) NOT NULL UNIQUE,
    KEY idx_program_inquiries_category_status(kategori,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
