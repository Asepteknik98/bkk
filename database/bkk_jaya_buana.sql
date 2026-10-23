-- Instalasi baru: pilih database KOSONG terlebih dahulu di phpMyAdmin.
-- Tidak membuat/menghapus database, tidak menyediakan akun atau password bawaan.
-- Jangan impor ulang pada database produksi. DDL MySQL melakukan implicit commit.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    foto_profil VARCHAR(255) NULL,
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
('ppks', 'DATA DEMO - Program PPKS', 'data-demo-ppks', 'DATA DEMO: bukan program resmi sekolah.', 'DATA DEMO. Contoh konten pengembangan karir peserta untuk pengujian.', 'draft'),
('kelas_melanjutkan', 'DATA DEMO - Kelas Melanjutkan', 'data-demo-kelas-melanjutkan', 'DATA DEMO: bukan informasi kuliah atau beapeserta resmi.', 'DATA DEMO. Contoh konten kuliah dalam atau luar negeri untuk pengujian.', 'draft'),
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


-- Lamaran internal untuk pengelola BKK.
CREATE TABLE IF NOT EXISTS lamaran (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lowongan_id BIGINT UNSIGNED NOT NULL,
    posisi VARCHAR(200) NOT NULL,
    perusahaan VARCHAR(200) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    telepon VARCHAR(30) NOT NULL,
    pendidikan VARCHAR(150) NOT NULL,
    alamat TEXT NOT NULL,
    pesan TEXT NOT NULL,
    submission_key CHAR(64) NOT NULL,
    status ENUM('baru','diproses','diterima','ditolak') NOT NULL DEFAULT 'baru',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lamaran_submission (submission_key),
    KEY idx_lamaran_created (created_at, id),
    KEY idx_lamaran_lowongan (lowongan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS lamaran_berkas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lamaran_id BIGINT UNSIGNED NOT NULL,
    jenis VARCHAR(30) NOT NULL,
    nama_asli VARCHAR(200) NOT NULL,
    nama_simpan VARCHAR(80) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    ukuran INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_lamaran_berkas_jenis (lamaran_id, jenis),
    CONSTRAINT fk_lamaran_berkas FOREIGN KEY (lamaran_id) REFERENCES lamaran(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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

-- Jalankan satu kali setelah migrasi 006. Bedakan belum tercatat dari permintaan berhenti.
ALTER TABLE lamaran ADD COLUMN wa_opted_out_at DATETIME NULL;
ALTER TABLE tracer_responses ADD COLUMN wa_opted_out_at DATETIME NULL;

UPDATE lamaran SET wa_opted_out_at=wa_consent_at WHERE wa_consent=0 AND wa_consent_at IS NOT NULL;
UPDATE tracer_responses SET wa_opted_out_at=wa_consent_at WHERE wa_consent=0 AND wa_consent_at IS NOT NULL;


CREATE TABLE IF NOT EXISTS program_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(50) NOT NULL,
    program_id BIGINT UNSIGNED NULL,
    program_judul VARCHAR(255) NULL,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    telepon VARCHAR(30) NOT NULL,
    pesan TEXT NOT NULL,
    status ENUM('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
    balasan TEXT NULL,
    replied_by BIGINT UNSIGNED NULL,
    updated_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    submission_key CHAR(64) NOT NULL UNIQUE,
    tracking_hash CHAR(64) NOT NULL UNIQUE,
    KEY idx_program_inquiries_category_status(kategori,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS siswa (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(30) NOT NULL,
    nisn VARCHAR(10) NOT NULL DEFAULT '',
    nama VARCHAR(150) NOT NULL,
    kelas VARCHAR(80) NOT NULL,
    jurusan VARCHAR(150) NOT NULL DEFAULT '',
    angkatan SMALLINT UNSIGNED NOT NULL,
    kelas_industri VARCHAR(150) NOT NULL DEFAULT '',
    telepon VARCHAR(25) NOT NULL DEFAULT '',
    email VARCHAR(190) NOT NULL DEFAULT '',
    tempat_pkl VARCHAR(190) NOT NULL DEFAULT '',
    tahun_pkl VARCHAR(9) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_siswa_nis (nis),
    KEY idx_peserta_angkatan_kelas (angkatan, kelas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learning_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(190) NOT NULL,
    deskripsi TEXT NOT NULL,
    angkatan SMALLINT UNSIGNED NOT NULL,
    kelas_industri VARCHAR(150) NOT NULL,
    nama_asli VARCHAR(200) NOT NULL,
    nama_simpan VARCHAR(68) NOT NULL,
    ukuran INT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_learning_file (nama_simpan),
    KEY idx_learning_cohort_industry (angkatan,kelas_industri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(190) NOT NULL,
    tanggal DATE NOT NULL,
    angkatan SMALLINT UNSIGNED NOT NULL,
    kelas_industri VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_attendance_group (angkatan,kelas_industri,tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    siswa_id BIGINT UNSIGNED NOT NULL,
    nis VARCHAR(30) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    kelas VARCHAR(80) NOT NULL,
    status ENUM('hadir','izin','sakit','alpa') NULL,
    keterangan VARCHAR(500) NOT NULL DEFAULT '',
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_attendance_student (event_id,siswa_id),
    KEY idx_attendance_student (siswa_id),
    CONSTRAINT fk_attendance_event FOREIGN KEY (event_id) REFERENCES attendance_events(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_student FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
