<?php
declare(strict_types=1);

return static function (mysqli $db): void {
    $index = $db->query("SELECT COUNT(*) FROM information_schema.statistics WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rental_units' AND INDEX_NAME = 'uq_units_media_scope'")->fetch_row();
    if ((int)$index[0] === 0) { $db->query('ALTER TABLE rental_units ADD UNIQUE KEY uq_units_media_scope (id, property_id, organization_id)'); }
    foreach ([false, true] as $unit) {
        $prefix = $unit ? 'unit' : 'property';
        $unitColumn = $unit ? 'unit_id INT NOT NULL,' : '';
        $scopeColumns = $unit ? 'unit_id, property_id, organization_id' : 'property_id, organization_id';
        $reference = $unit ? 'rental_units(id, property_id, organization_id)' : 'properties(id, organization_id)';
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}_media (
            id INT AUTO_INCREMENT PRIMARY KEY, organization_id INT NOT NULL, property_id INT NOT NULL,
            {$unitColumn} filename VARCHAR(40) NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_{$prefix}_media_file (filename),
            CONSTRAINT fk_{$prefix}_media_scope FOREIGN KEY ({$scopeColumns}) REFERENCES {$reference}
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}_amenities (
            organization_id INT NOT NULL, property_id INT NOT NULL, {$unitColumn} amenity_id INT NOT NULL,
            PRIMARY KEY ({$scopeColumns}, amenity_id),
            CONSTRAINT fk_{$prefix}_amenities_scope FOREIGN KEY ({$scopeColumns}) REFERENCES {$reference},
            CONSTRAINT fk_{$prefix}_amenities_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
};
