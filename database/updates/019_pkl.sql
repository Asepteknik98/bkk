-- Tahap 2. Prerequisites: siswa, users, and migration 018 for staff permissions.
-- Additive; safe to re-import. No existing participants or PKL history modified.
CREATE TABLE IF NOT EXISTS pkl_placements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    kelas VARCHAR(80) NOT NULL,
    jurusan VARCHAR(150) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    tempat_pkl VARCHAR(190) NOT NULL,
    alamat_lengkap TEXT NOT NULL,
    guru_pembimbing VARCHAR(150) NOT NULL,
    pembimbing_industri VARCHAR(150) NOT NULL,
    status ENUM('aktif','selesai','arsip') NOT NULL DEFAULT 'aktif',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pkl_placement (siswa_id,tanggal_mulai,tanggal_selesai,tempat_pkl),
    KEY idx_pkl_period (tanggal_mulai,tanggal_selesai),
    CONSTRAINT fk_pkl_student FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pkl_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_pkl_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_pkl_period CHECK (tanggal_selesai >= tanggal_mulai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pkl_journals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    placement_id BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,
    kegiatan TEXT NOT NULL,
    catatan TEXT NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pkl_journal_date (placement_id,tanggal),
    CONSTRAINT fk_pkl_journal_placement FOREIGN KEY (placement_id) REFERENCES pkl_placements(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pkl_journal_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pkl_final_reports (
    placement_id BIGINT UNSIGNED PRIMARY KEY,
    tanggal_laporan DATE NOT NULL,
    ringkasan TEXT NOT NULL,
    hasil TEXT NOT NULL,
    kendala TEXT NOT NULL,
    saran TEXT NOT NULL,
    kesimpulan TEXT NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pkl_report_placement FOREIGN KEY (placement_id) REFERENCES pkl_placements(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pkl_report_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
