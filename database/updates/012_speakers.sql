CREATE TABLE IF NOT EXISTS speakers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    instansi VARCHAR(190) NOT NULL DEFAULT '',
    keahlian VARCHAR(190) NOT NULL DEFAULT '',
    kelas_industri VARCHAR(150) NOT NULL DEFAULT '',
    bio TEXT NOT NULL,
    cv_nama VARCHAR(200) NULL,
    cv_file VARCHAR(69) NULL,
    cv_size INT UNSIGNED NULL,
    foto_file VARCHAR(69) NULL,
    foto_mime VARCHAR(30) NULL,
    foto_size INT UNSIGNED NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_speaker_industry (kelas_industri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS speaker_events (
    speaker_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (speaker_id,event_id),
    KEY idx_speaker_event (event_id),
    CONSTRAINT fk_speaker_event_speaker FOREIGN KEY (speaker_id) REFERENCES speakers(id) ON DELETE CASCADE,
    CONSTRAINT fk_speaker_event_event FOREIGN KEY (event_id) REFERENCES attendance_events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
