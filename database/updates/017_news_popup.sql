ALTER TABLE berita ADD COLUMN popup TINYINT(1) NOT NULL DEFAULT 0 AFTER unggulan,
    ADD KEY idx_berita_popup (popup,status,published_at);
