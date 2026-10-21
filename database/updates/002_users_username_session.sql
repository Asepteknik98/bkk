-- Impor SATU KALI pada database terpasang, setelah pembaruan 001.
-- Tidak menghapus akun/password. Akun lama tetap dapat login dengan email.
ALTER TABLE users
  ADD COLUMN username VARCHAR(50) NULL AFTER nama,
  ADD COLUMN session_version INT UNSIGNED NOT NULL DEFAULT 0 AFTER aktif;
UPDATE users SET username = CONCAT('akun-', id) WHERE username IS NULL;
ALTER TABLE users MODIFY username VARCHAR(50) NOT NULL,
  ADD UNIQUE KEY uq_users_username (username);
