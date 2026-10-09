<?php
declare(strict_types=1);

return static function (mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS password_resets (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash CHAR(64) NOT NULL,
        expires_at BIGINT NOT NULL,
        used_at BIGINT NULL,
        created_at BIGINT NOT NULL,
        UNIQUE KEY uq_password_reset_hash (token_hash),
        KEY idx_password_reset_user_time (user_id, created_at),
        CONSTRAINT fk_password_reset_user FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->query("CREATE TABLE IF NOT EXISTS identity_credentials (
        user_id INT PRIMARY KEY,
        auth_version INT NOT NULL DEFAULT 0,
        CONSTRAINT fk_identity_credentials_user FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};
