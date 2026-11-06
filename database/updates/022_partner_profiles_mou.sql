-- Tahap 5. Requires existing mitra/users; 018 for staff permissions.
-- Additive and safe to re-import. No existing partners are classified automatically.
CREATE TABLE IF NOT EXISTS mitra_profiles (
    mitra_id BIGINT UNSIGNED PRIMARY KEY,
    klaster ENUM('dalam_negeri','luar_negeri') NULL,
    bidang_usaha VARCHAR(200) NOT NULL DEFAULT '',
    alamat_lengkap TEXT NOT NULL,
    negara VARCHAR(100) NOT NULL DEFAULT '',
    pic_nama VARCHAR(150) NOT NULL DEFAULT '',
    pic_jabatan VARCHAR(150) NOT NULL DEFAULT '',
    pic_kontak VARCHAR(250) NOT NULL DEFAULT '',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_partner_profile FOREIGN KEY (mitra_id) REFERENCES mitra(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mitra_mous (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mitra_id BIGINT UNSIGNED NOT NULL,
    previous_id BIGINT UNSIGNED NULL,
    nomor_dokumen VARCHAR(190) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_berakhir DATE NOT NULL,
    nama_asli VARCHAR(200) NOT NULL,
    nama_simpan VARCHAR(68) NOT NULL,
    ukuran INT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mou_partner (id,mitra_id),
    UNIQUE KEY uq_mou_document (mitra_id,nomor_dokumen,tanggal_mulai,tanggal_berakhir),
    UNIQUE KEY uq_mou_file (nama_simpan),
    KEY idx_mou_dates (tanggal_mulai,tanggal_berakhir),
    KEY idx_mou_previous (previous_id,mitra_id),
    CONSTRAINT fk_mou_partner FOREIGN KEY (mitra_id) REFERENCES mitra(id) ON DELETE RESTRICT,
    CONSTRAINT fk_mou_previous FOREIGN KEY (previous_id,mitra_id) REFERENCES mitra_mous(id,mitra_id) ON DELETE RESTRICT,
    CONSTRAINT fk_mou_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_mou_dates CHECK (tanggal_berakhir >= tanggal_mulai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
