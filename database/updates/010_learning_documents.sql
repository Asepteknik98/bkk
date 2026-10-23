CREATE TABLE IF NOT EXISTS learning_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(190) NOT NULL,
    deskripsi TEXT NOT NULL,
    angkatan SMALLINT UNSIGNED NOT NULL,
    kelas_industri VARCHAR(150) NOT NULL,
    nama_asli VARCHAR(200) NOT NULL,
    nama_simpan VARCHAR(68) NOT NULL,
    ukuran INT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_learning_file (nama_simpan),
    KEY idx_learning_cohort_industry (angkatan,kelas_industri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
