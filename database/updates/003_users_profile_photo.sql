-- Aman dijalankan ulang pada database yang sudah memiliki foto_profil.
SET @bkk_photo_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'foto_profil'
);
SET @bkk_photo_sql = IF(@bkk_photo_exists = 0,
    'ALTER TABLE users ADD COLUMN foto_profil VARCHAR(255) NULL AFTER email',
    'SELECT 1 AS foto_profil_sudah_tersedia');
PREPARE bkk_photo_update FROM @bkk_photo_sql;
EXECUTE bkk_photo_update;
DEALLOCATE PREPARE bkk_photo_update;
