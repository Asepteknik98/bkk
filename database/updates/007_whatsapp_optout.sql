-- Jalankan satu kali setelah migrasi 006. Bedakan belum tercatat dari permintaan berhenti.
ALTER TABLE lamaran ADD COLUMN wa_opted_out_at DATETIME NULL;
ALTER TABLE tracer_responses ADD COLUMN wa_opted_out_at DATETIME NULL;

UPDATE lamaran SET wa_opted_out_at=wa_consent_at WHERE wa_consent=0 AND wa_consent_at IS NOT NULL;
UPDATE tracer_responses SET wa_opted_out_at=wa_consent_at WHERE wa_consent=0 AND wa_consent_at IS NOT NULL;
