-- Apply only after verifying student_accounts is empty and no users have role='siswa'.
-- Back up first. Participant data tables and their columns are unchanged.
DROP TABLE IF EXISTS student_accounts;
ALTER TABLE users MODIFY role ENUM('admin','staf_bkk') NOT NULL;
