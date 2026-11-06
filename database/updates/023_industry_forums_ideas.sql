-- Tahap 6. Requires existing users/mitra; 018 for staff permissions.
-- Additive and safe to re-import. No existing data is removed or published.
CREATE TABLE IF NOT EXISTS industry_forums (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(190) NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    lokasi VARCHAR(500) NOT NULL DEFAULT '',
    tautan VARCHAR(2048) NULL,
    pic_sekolah VARCHAR(250) NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_industry_forum_date (tanggal,jam),
    CONSTRAINT fk_industry_forum_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS industry_forum_invitees (
    forum_id BIGINT UNSIGNED NOT NULL,
    mitra_id BIGINT UNSIGNED NOT NULL,
    pic_nama VARCHAR(150) NOT NULL,
    pic_kontak VARCHAR(250) NOT NULL DEFAULT '',
    PRIMARY KEY (forum_id,mitra_id),
    CONSTRAINT fk_industry_invitee_forum FOREIGN KEY (forum_id) REFERENCES industry_forums(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_invitee_partner FOREIGN KEY (mitra_id) REFERENCES mitra(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS industry_idea_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mitra_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    revoked_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_industry_idea_token (token_hash),
    UNIQUE KEY uq_industry_idea_link_partner (id,mitra_id),
    KEY idx_industry_idea_link_partner (mitra_id),
    CONSTRAINT fk_industry_idea_link_partner FOREIGN KEY (mitra_id) REFERENCES mitra(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_idea_link_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS industry_ideas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    link_id BIGINT UNSIGNED NOT NULL,
    mitra_id BIGINT UNSIGNED NOT NULL,
    perusahaan VARCHAR(200) NOT NULL,
    nama_pengirim VARCHAR(150) NOT NULL,
    jabatan VARCHAR(150) NOT NULL DEFAULT '',
    email VARCHAR(191) NOT NULL DEFAULT '',
    telepon VARCHAR(30) NOT NULL DEFAULT '',
    judul VARCHAR(190) NOT NULL,
    isi TEXT NOT NULL,
    persetujuan_at DATETIME NOT NULL,
    versi_persetujuan VARCHAR(30) NOT NULL,
    status ENUM('baru','ditinjau','ditindaklanjuti','arsip') NOT NULL DEFAULT 'baru',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_industry_idea_submission (link_id),
    KEY idx_industry_idea_status (status,created_at),
    CONSTRAINT fk_industry_idea_link FOREIGN KEY (link_id,mitra_id) REFERENCES industry_idea_links(id,mitra_id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_idea_partner FOREIGN KEY (mitra_id) REFERENCES mitra(id) ON DELETE RESTRICT,
    CONSTRAINT fk_industry_idea_editor FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
