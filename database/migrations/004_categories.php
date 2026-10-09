<?php
declare(strict_types=1);

return static function (mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS space_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(80) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 0,
        version INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_space_categories_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->query("CREATE TABLE IF NOT EXISTS category_capabilities (
        category_id INT NOT NULL,
        capability_slug VARCHAR(60) NOT NULL,
        enabled TINYINT(1) NOT NULL,
        PRIMARY KEY (category_id, capability_slug),
        CONSTRAINT fk_category_capabilities_category FOREIGN KEY (category_id)
            REFERENCES space_categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};
