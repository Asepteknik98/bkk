-- Tahap 7. Additive; safe to re-import. Requires users, lowongan, lamaran,
-- tracer_responses and alumni_form_links (018). No old records are inferred/removed.
CREATE TABLE IF NOT EXISTS employment_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(80) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    penjelasan TEXT NOT NULL,
    rujukan_url VARCHAR(2048) NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_employment_code (kode),
    UNIQUE KEY uq_employment_name (nama),
    CONSTRAINT fk_employment_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO employment_types(kode,nama,penjelasan) VALUES
('harian_lepas','Harian Lepas',''),('pkwt','PKWT',''),('pkwtt','PKWTT','');

CREATE TABLE IF NOT EXISTS vacancy_details (
    lowongan_id BIGINT UNSIGNED PRIMARY KEY,
    employment_type_id BIGINT UNSIGNED NOT NULL,
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NOT NULL,
    CONSTRAINT fk_vacancy_detail_job FOREIGN KEY (lowongan_id) REFERENCES lowongan(id) ON DELETE CASCADE,
    CONSTRAINT fk_vacancy_employment FOREIGN KEY (employment_type_id) REFERENCES employment_types(id) ON DELETE RESTRICT,
    CONSTRAINT fk_vacancy_verifier FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alumni_application_results (
    lamaran_id BIGINT UNSIGNED PRIMARY KEY,
    tracer_id BIGINT UNSIGNED NOT NULL,
    lowongan_id BIGINT UNSIGNED NOT NULL,
    hasil ENUM('belum_mengisi','dalam_proses','lolos','tidak_lolos') NOT NULL DEFAULT 'belum_mengisi',
    seleksi_selesai TINYINT(1) NOT NULL DEFAULT 0,
    reported_at DATETIME NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_alumni_job (tracer_id,lowongan_id),
    CONSTRAINT fk_alumni_result_application FOREIGN KEY (lamaran_id) REFERENCES lamaran(id) ON DELETE RESTRICT,
    CONSTRAINT fk_alumni_result_person FOREIGN KEY (tracer_id) REFERENCES tracer_responses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_alumni_result_job FOREIGN KEY (lowongan_id) REFERENCES lowongan(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alumni_work_profiles (
    tracer_id BIGINT UNSIGNED PRIMARY KEY,
    status_kerja ENUM('belum_bekerja','sudah_bekerja') NOT NULL,
    perusahaan VARCHAR(200) NOT NULL DEFAULT '',
    posisi VARCHAR(200) NOT NULL DEFAULT '',
    tanggal_mulai DATE NULL,
    employment_type_id BIGINT UNSIGNED NULL,
    updated_at DATETIME(6) NOT NULL,
    CONSTRAINT fk_alumni_work_person FOREIGN KEY (tracer_id) REFERENCES tracer_responses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_alumni_work_type FOREIGN KEY (employment_type_id) REFERENCES employment_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
