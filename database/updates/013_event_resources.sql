CREATE TABLE IF NOT EXISTS event_resources (
    event_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    pretest_url VARCHAR(2048) NOT NULL DEFAULT '',
    pretest_enabled TINYINT(1) NOT NULL DEFAULT 0,
    poster_file VARCHAR(69) NULL,
    poster_name VARCHAR(200) NULL,
    poster_mime VARCHAR(30) NULL,
    poster_size INT UNSIGNED NULL,
    poster_enabled TINYINT(1) NOT NULL DEFAULT 0,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_resources FOREIGN KEY (event_id) REFERENCES attendance_events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
