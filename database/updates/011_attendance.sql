CREATE TABLE IF NOT EXISTS attendance_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(190) NOT NULL,
    tanggal DATE NOT NULL,
    angkatan SMALLINT UNSIGNED NOT NULL,
    kelas_industri VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_attendance_group (angkatan,kelas_industri,tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    siswa_id BIGINT UNSIGNED NOT NULL,
    nis VARCHAR(30) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    kelas VARCHAR(80) NOT NULL,
    status ENUM('hadir','izin','sakit','alpa') NULL,
    keterangan VARCHAR(500) NOT NULL DEFAULT '',
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_attendance_student (event_id,siswa_id),
    KEY idx_attendance_student (siswa_id),
    CONSTRAINT fk_attendance_event FOREIGN KEY (event_id) REFERENCES attendance_events(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_student FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
