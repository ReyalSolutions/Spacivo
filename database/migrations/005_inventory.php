<?php
declare(strict_types=1);

return static function (mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        category_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT NOT NULL,
        address VARCHAR(500) NOT NULL,
        timezone VARCHAR(64) NOT NULL,
        latitude DECIMAL(10,7) DEFAULT NULL,
        longitude DECIMAL(10,7) DEFAULT NULL,
        state ENUM('draft','published','suspended','archived') NOT NULL DEFAULT 'draft',
        approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        reviewed_by INT DEFAULT NULL,
        reviewed_at DATETIME DEFAULT NULL,
        version INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_properties_scope (id, organization_id),
        KEY idx_properties_org_state (organization_id, state),
        CONSTRAINT fk_properties_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
        CONSTRAINT fk_properties_category FOREIGN KEY (category_id) REFERENCES space_categories(id),
        CONSTRAINT fk_properties_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->query("CREATE TABLE IF NOT EXISTS rental_units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        property_id INT NOT NULL,
        category_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        capacity INT NOT NULL,
        state ENUM('active','archived') NOT NULL DEFAULT 'active',
        version INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_units_property_scope (property_id, organization_id, state),
        CONSTRAINT fk_units_property_scope FOREIGN KEY (property_id, organization_id) REFERENCES properties(id, organization_id),
        CONSTRAINT fk_units_category FOREIGN KEY (category_id) REFERENCES space_categories(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};
