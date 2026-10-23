ALTER TABLE users MODIFY role ENUM('admin','staf_bkk','siswa') NOT NULL;

CREATE TABLE IF NOT EXISTS student_accounts (
    user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_student_account (siswa_id),
    CONSTRAINT fk_student_account_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_student_account_student FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
