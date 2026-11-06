-- Tahap 4. Requires users, mitra, speakers (012), siswa (009); 018 for staff permissions.
-- Additive; safe to re-import. Existing programs, speakers and participants unchanged.
CREATE TABLE IF NOT EXISTS industry_classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(190) NOT NULL,
    mitra_id BIGINT UNSIGNED NOT NULL,
    kelas_sasaran VARCHAR(200) NOT NULL DEFAULT '',
    rencana_pertemuan SMALLINT UNSIGNED NOT NULL,
    catatan TEXT NOT NULL,
    status ENUM('aktif','arsip') NOT NULL DEFAULT 'aktif',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_industry_class_status (status,nama),
    CONSTRAINT fk_industry_class_partner FOREIGN KEY (mitra_id) REFERENCES mitra(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_class_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_industry_class_plan CHECK (rencana_pertemuan BETWEEN 1 AND 500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS industry_class_speakers (
    class_id BIGINT UNSIGNED NOT NULL,
    speaker_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (class_id,speaker_id),
    CONSTRAINT fk_industry_speaker_class FOREIGN KEY (class_id) REFERENCES industry_classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_speaker_person FOREIGN KEY (speaker_id) REFERENCES speakers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS industry_class_participants (
    class_id BIGINT UNSIGNED NOT NULL,
    siswa_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (class_id,siswa_id),
    CONSTRAINT fk_industry_participant_class FOREIGN KEY (class_id) REFERENCES industry_classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_participant_person FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS industry_class_meetings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    urutan SMALLINT UNSIGNED NOT NULL,
    tanggal DATE NULL,
    jam TIME NULL,
    lokasi VARCHAR(200) NOT NULL DEFAULT '',
    materi TEXT NOT NULL,
    pemateri_id BIGINT UNSIGNED NULL,
    status ENUM('direncanakan','dijadwalkan','terlaksana','ditunda','dibatalkan') NOT NULL DEFAULT 'direncanakan',
    catatan TEXT NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_industry_meeting_number (class_id,urutan),
    KEY idx_industry_meeting_date (tanggal,jam),
    CONSTRAINT fk_industry_meeting_class FOREIGN KEY (class_id) REFERENCES industry_classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_meeting_speaker FOREIGN KEY (pemateri_id) REFERENCES speakers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_meeting_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
