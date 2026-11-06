-- Tahap 3. Requires migration 019 (and 018 for staff permissions).
-- Additive and safe to re-import; no existing rows or tables are removed.
CREATE TABLE IF NOT EXISTS pkl_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    placement_id BIGINT UNSIGNED NOT NULL,
    jenis ENUM('monitoring','orang_tua','sidang') NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    petugas VARCHAR(250) NOT NULL,
    tujuan VARCHAR(190) NOT NULL,
    alamat_lengkap TEXT NOT NULL,
    orang_tua_wali VARCHAR(200) NOT NULL DEFAULT '',
    agenda TEXT NOT NULL,
    status ENUM('dijadwalkan','selesai','ditunda','dibatalkan') NOT NULL DEFAULT 'dijadwalkan',
    hasil_monitoring TEXT NOT NULL,
    tindak_lanjut TEXT NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pkl_schedule_placement (id,placement_id),
    KEY idx_pkl_schedule_date (jenis,tanggal,jam),
    KEY idx_pkl_schedule_placement (placement_id,tanggal),
    CONSTRAINT fk_pkl_schedule_placement FOREIGN KEY (placement_id) REFERENCES pkl_placements(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pkl_schedule_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pkl_business_trips (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    placement_id BIGINT UNSIGNED NOT NULL,
    monitoring_id BIGINT UNSIGNED NULL,
    tujuan VARCHAR(190) NOT NULL,
    petugas VARCHAR(250) NOT NULL,
    tanggal DATE NOT NULL,
    nominal DECIMAL(15,2) UNSIGNED NOT NULL,
    sumber_biaya ENUM('sekolah','pribadi') NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_pkl_trip_date (tanggal,sumber_biaya),
    KEY idx_pkl_trip_monitoring (monitoring_id,placement_id),
    CONSTRAINT fk_pkl_trip_placement FOREIGN KEY (placement_id) REFERENCES pkl_placements(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pkl_trip_monitoring FOREIGN KEY (monitoring_id,placement_id) REFERENCES pkl_schedules(id,placement_id) ON DELETE RESTRICT,
    CONSTRAINT fk_pkl_trip_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_pkl_trip_nominal CHECK (nominal >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
