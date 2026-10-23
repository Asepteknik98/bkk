-- Read state is shared by admin and staff, independently of processing status.
ALTER TABLE lamaran ADD COLUMN viewed_at DATETIME NULL DEFAULT NULL,
    ADD KEY idx_lamaran_viewed (viewed_at);

-- Previously processed applications are considered already viewed.
UPDATE lamaran SET viewed_at=created_at WHERE status<>'baru';
