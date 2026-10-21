-- Tahap 8. Pilih database BKK yang sudah memiliki tabel tracer_responses.
-- Impor SATU KALI setelah SQL instalasi awal. Tidak menghapus respons lama.
-- Respons lama tetap NULL; kunci unik hanya untuk pengiriman formulir baru.
ALTER TABLE tracer_responses
    ADD COLUMN submission_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL AFTER id,
    ADD UNIQUE KEY uq_tracer_submission_key (submission_key);
