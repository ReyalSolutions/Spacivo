<?php
declare(strict_types=1);

return static function (mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS organizations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_user_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
        verification_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_organizations_owner (owner_user_id),
        CONSTRAINT fk_organizations_owner FOREIGN KEY (owner_user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->query("CREATE TABLE IF NOT EXISTS organization_members (
        organization_id INT NOT NULL,
        user_id INT NOT NULL,
        role ENUM('owner', 'manager', 'staff') NOT NULL,
        status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (organization_id, user_id),
        KEY idx_organization_members_user (user_id, status),
        CONSTRAINT fk_org_members_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
        CONSTRAINT fk_org_members_user FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->query("CREATE TABLE IF NOT EXISTS organization_member_permissions (
        organization_id INT NOT NULL,
        user_id INT NOT NULL,
        permission_slug VARCHAR(80) NOT NULL,
        PRIMARY KEY (organization_id, user_id, permission_slug),
        CONSTRAINT fk_org_permissions_members FOREIGN KEY (organization_id, user_id)
            REFERENCES organization_members(organization_id, user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};
