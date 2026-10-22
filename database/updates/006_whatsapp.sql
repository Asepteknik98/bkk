-- Jalankan satu kali setelah migrasi 005.
ALTER TABLE lamaran ADD COLUMN wa_consent TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN wa_consent_at DATETIME NULL;
ALTER TABLE tracer_responses ADD COLUMN wa_consent TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN wa_consent_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS wa_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    tautan VARCHAR(2048) NOT NULL DEFAULT '',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    submission_key CHAR(64) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS wa_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    source_type VARCHAR(20) NOT NULL,
    source_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(150) NOT NULL,
    nomor VARCHAR(20) NOT NULL,
    pesan TEXT NOT NULL,
    opened_at DATETIME NULL,
    marked_at DATETIME NULL,
    opened_by BIGINT UNSIGNED NULL,
    marked_by BIGINT UNSIGNED NULL,
    UNIQUE KEY uq_wa_campaign_phone(campaign_id,nomor),
    CONSTRAINT fk_wa_campaign FOREIGN KEY(campaign_id) REFERENCES wa_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
