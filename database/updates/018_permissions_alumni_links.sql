-- Tahap 1. Back up first. Additive and safe to re-import; no existing data removed.
-- Existing staff start with no operational grants. Admin assigns them in Pengguna.
CREATE TABLE IF NOT EXISTS user_permissions (
    user_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(64) NOT NULL,
    PRIMARY KEY (user_id,module),
    CONSTRAINT fk_permission_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alumni_form_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tracer_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    revoked_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_alumni_token (token_hash),
    KEY idx_alumni_tracer (tracer_id),
    CONSTRAINT fk_alumni_link_tracer FOREIGN KEY (tracer_id) REFERENCES tracer_responses(id) ON DELETE CASCADE,
    CONSTRAINT fk_alumni_link_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
