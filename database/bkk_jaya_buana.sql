-- Instalasi baru: pilih database KOSONG terlebih dahulu di phpMyAdmin.
-- Tidak membuat/menghapus database, tidak menyediakan akun atau password bawaan.
-- Jangan impor ulang pada database produksi. DDL MySQL melakukan implicit commit.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staf_bkk') NOT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_aktif (role, aktif),
    CONSTRAINT chk_users_aktif CHECK (aktif IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE berita (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    ringkasan TEXT NOT NULL,
    isi LONGTEXT NOT NULL,
    gambar VARCHAR(255) NULL,
    penulis_id BIGINT UNSIGNED NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    unggulan TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_berita_slug (slug),
    KEY idx_berita_publikasi (status, published_at),
    KEY idx_berita_unggulan (status, unggulan, published_at),
    CONSTRAINT fk_berita_penulis FOREIGN KEY (penulis_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_berita_unggulan CHECK (unggulan IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lowongan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perusahaan VARCHAR(200) NOT NULL,
    posisi VARCHAR(200) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    jenis_pekerjaan VARCHAR(100) NOT NULL,
    pendidikan VARCHAR(150) NOT NULL,
    persyaratan TEXT NOT NULL,
    deskripsi LONGTEXT NOT NULL,
    batas_lamaran DATE NULL,
    tautan_pendaftaran VARCHAR(2048) NULL,
    gambar VARCHAR(255) NULL,
    status ENUM('draft', 'published', 'closed') NOT NULL DEFAULT 'draft',
    penulis_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lowongan_slug (slug),
    KEY idx_lowongan_status_batas (status, batas_lamaran),
    CONSTRAINT fk_lowongan_penulis FOREIGN KEY (penulis_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE program (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kategori ENUM('kelas_industri', 'ppks', 'kelas_melanjutkan', 'entrepreneur', 'kelas_internasional') NOT NULL,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    ringkasan TEXT NOT NULL,
    isi LONGTEXT NOT NULL,
    gambar VARCHAR(255) NULL,
    tautan_pendaftaran VARCHAR(2048) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    penulis_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_program_slug (slug),
    KEY idx_program_kategori_status (kategori, status, created_at),
    CONSTRAINT fk_program_penulis FOREIGN KEY (penulis_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mitra (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(200) NOT NULL,
    jenis ENUM('perusahaan', 'kampus') NOT NULL,
    logo VARCHAR(255) NULL,
    website VARCHAR(2048) NULL,
    deskripsi TEXT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_mitra_aktif_jenis (aktif, jenis),
    CONSTRAINT chk_mitra_aktif CHECK (aktif IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data pribadi: hanya untuk pengelola berwenang; tidak untuk query halaman publik.
-- Tidak menyimpan NIK atau identitas sensitif yang tidak diperlukan.
CREATE TABLE tracer_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(150) NOT NULL,
    nisn VARCHAR(20) NULL,
    jurusan VARCHAR(150) NOT NULL,
    tahun_lulus SMALLINT UNSIGNED NOT NULL,
    email VARCHAR(191) NULL,
    nomor_telepon VARCHAR(30) NULL,
    status_kegiatan ENUM('bekerja', 'kuliah', 'wirausaha', 'mencari_kerja', 'lainnya') NOT NULL,
    nama_perusahaan VARCHAR(200) NULL,
    posisi_pekerjaan VARCHAR(150) NULL,
    lokasi_pekerjaan VARCHAR(255) NULL,
    nama_kampus VARCHAR(200) NULL,
    program_studi VARCHAR(150) NULL,
    nama_usaha VARCHAR(200) NULL,
    bidang_usaha VARCHAR(150) NULL,
    keterangan TEXT NULL,
    persetujuan_data TINYINT(1) NOT NULL,
    persetujuan_at DATETIME NOT NULL,
    versi_persetujuan VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_tracer_tahun_status (tahun_lulus, status_kegiatan),
    KEY idx_tracer_jurusan (jurusan),
    CONSTRAINT chk_tracer_tahun CHECK (tahun_lulus BETWEEN 1900 AND 2155),
    CONSTRAINT chk_tracer_persetujuan CHECK (persetujuan_data = 1),
    CONSTRAINT chk_tracer_kontak CHECK (
        (email IS NOT NULL AND CHAR_LENGTH(TRIM(email)) > 0)
        OR (nomor_telepon IS NOT NULL AND CHAR_LENGTH(TRIM(nomor_telepon)) > 0)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_asli VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL COMMENT 'Path relatif uploads, bukan URL atau path absolut',
    mime_type VARCHAR(100) NOT NULL,
    ukuran_bytes BIGINT UNSIGNED NOT NULL,
    lebar INT UNSIGNED NULL,
    tinggi INT UNSIGNED NULL,
    alt_text VARCHAR(255) NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_media_path (path),
    KEY idx_media_created (created_at),
    CONSTRAINT fk_media_pengunggah FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;
-- Semua contoh berupa draft, bukan informasi resmi atau lowongan aktif.
INSERT INTO berita (judul, slug, ringkasan, isi, status) VALUES
('DATA DEMO - Contoh berita BKK', 'data-demo-contoh-berita-bkk',
 'DATA DEMO: contoh tampilan berita, bukan informasi resmi sekolah.',
 'DATA DEMO. Konten ini hanya untuk pengujian portal. Ganti dengan berita yang telah diverifikasi pengelola sebelum dipublikasikan.', 'draft');

INSERT INTO lowongan (perusahaan, posisi, slug, lokasi, jenis_pekerjaan, pendidikan, persyaratan, deskripsi, status) VALUES
('DATA DEMO - Perusahaan Contoh', 'DATA DEMO - Posisi Contoh', 'data-demo-lowongan-contoh',
 'DATA DEMO - Lokasi contoh', 'DATA DEMO - Jenis pekerjaan contoh', 'DATA DEMO - Pendidikan contoh',
 'DATA DEMO: bukan persyaratan rekrutmen resmi.',
 'DATA DEMO. Tidak ada rekrutmen atau pendaftaran nyata. Contoh ini hanya untuk pengujian portal.', 'draft');

INSERT INTO program (kategori, judul, slug, ringkasan, isi, status) VALUES
('kelas_industri', 'DATA DEMO - Kelas Industri', 'data-demo-kelas-industri', 'DATA DEMO: bukan program resmi sekolah.', 'DATA DEMO. Contoh konten kelas industri untuk pengujian.', 'draft'),
('ppks', 'DATA DEMO - Program PPKS', 'data-demo-ppks', 'DATA DEMO: bukan program resmi sekolah.', 'DATA DEMO. Contoh konten pengembangan karir siswa untuk pengujian.', 'draft'),
('kelas_melanjutkan', 'DATA DEMO - Kelas Melanjutkan', 'data-demo-kelas-melanjutkan', 'DATA DEMO: bukan informasi kuliah atau beasiswa resmi.', 'DATA DEMO. Contoh konten kuliah dalam atau luar negeri untuk pengujian.', 'draft'),
('entrepreneur', 'DATA DEMO - Entrepreneur Program', 'data-demo-entrepreneur', 'DATA DEMO: bukan program resmi sekolah.', 'DATA DEMO. Contoh konten kewirausahaan untuk pengujian.', 'draft'),
('kelas_internasional', 'DATA DEMO - Kelas Internasional', 'data-demo-kelas-internasional', 'DATA DEMO: bukan program resmi sekolah.', 'DATA DEMO. Contoh konten program internasional untuk pengujian.', 'draft');

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'BKK SMK JAYA BUANA'),
('site_description', 'Bursa Kerja Khusus SMK JAYA BUANA'),
('contact_address', 'Belum diisi oleh pengelola'),
('contact_email', NULL),
('contact_phone', NULL),
('school_logo', NULL),
('bkk_logo', NULL),
('social_instagram', NULL),
('social_facebook', NULL),
('social_youtube', NULL);
COMMIT;
