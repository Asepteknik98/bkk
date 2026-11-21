-- Impor satu kali pada database aplikasi yang sudah memiliki tabel pkl_placements.
-- Tidak mengubah atau menghapus data penempatan yang sudah ada.
CREATE TABLE IF NOT EXISTS pkl_location_points (
    placement_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    CONSTRAINT fk_pkl_location_placement FOREIGN KEY (placement_id)
        REFERENCES pkl_placements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
