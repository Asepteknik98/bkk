CREATE TABLE IF NOT EXISTS lamaran_berkas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lamaran_id BIGINT UNSIGNED NOT NULL,
    jenis VARCHAR(30) NOT NULL,
    nama_asli VARCHAR(200) NOT NULL,
    nama_simpan VARCHAR(80) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    ukuran INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_lamaran_berkas_jenis (lamaran_id, jenis),
    CONSTRAINT fk_lamaran_berkas FOREIGN KEY (lamaran_id) REFERENCES lamaran(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
