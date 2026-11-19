-- Tambahan rapor per siswa. Impor pada database aplikasi yang sudah ada.
-- Tidak menghapus dokumen pembelajaran, siswa, atau riwayat sebelumnya.
CREATE TABLE IF NOT EXISTS student_learning_reports (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(150) NOT NULL,
    kelas VARCHAR(80) NOT NULL,
    jurusan VARCHAR(150) NOT NULL DEFAULT '',
    angkatan SMALLINT UNSIGNED NOT NULL,
    periode VARCHAR(80) NOT NULL,
    tanggal DATE NOT NULL,
    pembimbing VARCHAR(150) NOT NULL,
    catatan TEXT NOT NULL,
    status ENUM('draft','final') NOT NULL DEFAULT 'draft',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_learning_report_student FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
    CONSTRAINT fk_learning_report_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_learning_report_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_learning_report_period (siswa_id, periode),
    KEY idx_learning_report_student (siswa_id, tanggal),
    KEY idx_learning_report_filter (angkatan, status, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_learning_report_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    urutan SMALLINT UNSIGNED NOT NULL,
    kompetensi VARCHAR(190) NOT NULL,
    nilai DECIMAL(5,2) NULL,
    predikat ENUM('kompeten','belum_kompeten') NULL,
    deskripsi TEXT NOT NULL,
    CONSTRAINT fk_learning_report_item FOREIGN KEY (report_id) REFERENCES student_learning_reports(id) ON DELETE CASCADE,
    UNIQUE KEY uq_learning_report_order (report_id, urutan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
