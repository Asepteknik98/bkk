-- Impor pada database aplikasi setelah 019_pkl.sql. Cadangkan database dahulu.
-- Tidak menghapus penempatan, jurnal, laporan atau data siswa.
-- 'aktif' dipertahankan untuk kompatibilitas; labelnya Sedang PKL.
-- 'arsip' adalah data lama, BUKAN otomatis Gagal PKL.
SET @pkl_previous_time_zone = @@session.time_zone;
SET time_zone = '+07:00';

ALTER TABLE pkl_placements MODIFY status
    ENUM('aktif','belum','gagal','selesai','arsip') NOT NULL DEFAULT 'belum';

CREATE TABLE IF NOT EXISTS pkl_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    placement_id BIGINT UNSIGNED NOT NULL,
    placement_version INT UNSIGNED NOT NULL,
    status_sebelum VARCHAR(10) NULL,
    status_sesudah VARCHAR(10) NOT NULL,
    alasan TEXT NOT NULL,
    sumber ENUM('baseline','baru','perubahan') NOT NULL,
    changed_by BIGINT UNSIGNED NULL,
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pkl_status_version (placement_id,placement_version),
    KEY idx_pkl_status_history (placement_id,recorded_at,id),
    CONSTRAINT fk_pkl_status_placement FOREIGN KEY (placement_id)
        REFERENCES pkl_placements(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pkl_status_actor FOREIGN KEY (changed_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Snapshot saat migrasi, bukan rekonstruksi waktu perubahan masa lalu.
-- Aman diimpor ulang: tidak menimpa riwayat atau membuat baseline kedua.
INSERT INTO pkl_status_history
    (placement_id,placement_version,status_sebelum,status_sesudah,alasan,sumber,changed_by)
SELECT p.id,p.version,NULL,p.status,
    'Status awal saat pembaruan fitur. Waktu perubahan sebelum pembaruan tidak diketahui.',
    'baseline',NULL
FROM pkl_placements p
WHERE NOT EXISTS (SELECT 1 FROM pkl_status_history h WHERE h.placement_id=p.id);

SET time_zone = @pkl_previous_time_zone;
