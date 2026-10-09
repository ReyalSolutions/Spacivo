<?php
declare(strict_types=1);

namespace App\Modules\Organizations\Repositories;

final class OrganizationRepository
{
    private \mysqli $db;

    public function __construct(\mysqli $db) { $this->db = $db; }

    private function one(string $sql, string $types, array $parameters): ?array
    {
        $statement = $this->db->prepare($sql);
        $statement->bind_param($types, ...$parameters);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();
        $statement->close();
        return $row ?: null;
    }

    public function activeUser(int $userId): ?array
    {
        return $this->one('SELECT u.id, r.slug AS platform_role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.status = 1 AND u.is_deleted = 0', 'i', [$userId]);
    }

    public function activeMembership(int $organizationId, int $userId): ?array
    {
        return $this->one("SELECT m.role, o.owner_user_id FROM organization_members m
            JOIN organizations o ON o.id = m.organization_id
            JOIN users u ON u.id = m.user_id
            WHERE m.organization_id = ? AND m.user_id = ? AND m.status = 'active'
                AND o.status = 'active' AND u.status = 1 AND u.is_deleted = 0", 'ii', [$organizationId, $userId]);
    }

    public function permissionGranted(int $organizationId, int $userId, string $permission): bool
    {
        return $this->one('SELECT 1 FROM organization_member_permissions WHERE organization_id = ? AND user_id = ? AND permission_slug = ?', 'iis', [$organizationId, $userId, $permission]) !== null;
    }

    public function find(int $organizationId): ?array
    {
        return $this->one('SELECT id, owner_user_id, name, status, verification_status, created_at FROM organizations WHERE id = ?', 'i', [$organizationId]);
    }

    public function listForMember(int $userId): array
    {
        $statement = $this->db->prepare("SELECT o.id, o.name, o.owner_user_id, o.verification_status, m.role FROM organizations o
            JOIN organization_members m ON m.organization_id = o.id
            WHERE m.user_id = ? AND m.status = 'active' AND o.status = 'active'
                AND ((m.role = 'owner' AND o.owner_user_id = m.user_id) OR EXISTS (
                    SELECT 1 FROM organization_member_permissions p WHERE p.organization_id = m.organization_id
                        AND p.user_id = m.user_id AND p.permission_slug IN ('organization.read', 'inventory.read', 'inventory.manage')
                )) ORDER BY o.id");
        $statement->bind_param('i', $userId);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function listPendingVerification(): array
    {
        $status = 'pending';
        $statement = $this->db->prepare('SELECT id, name, owner_user_id FROM organizations WHERE verification_status = ? ORDER BY id');
        $statement->bind_param('s', $status);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function activeUserByEmail(string $email): ?array
    {
        return $this->one('SELECT id FROM users WHERE email = ? AND status = 1 AND is_deleted = 0', 's', [$email]);
    }

    public function create(int $ownerId, string $name): int
    {
        $statement = $this->db->prepare('INSERT INTO organizations (owner_user_id, name) VALUES (?, ?)');
        $statement->bind_param('is', $ownerId, $name);
        $statement->execute();
        return (int)$this->db->insert_id;
    }

    public function saveMember(int $organizationId, int $userId, string $role, array $permissions): void
    {
        $statement = $this->db->prepare("INSERT INTO organization_members (organization_id, user_id, role, status) VALUES (?, ?, ?, 'active') ON DUPLICATE KEY UPDATE role = VALUES(role), status = 'active'");
        $statement->bind_param('iis', $organizationId, $userId, $role);
        $statement->execute();
        $statement = $this->db->prepare('DELETE FROM organization_member_permissions WHERE organization_id = ? AND user_id = ?');
        $statement->bind_param('ii', $organizationId, $userId);
        $statement->execute();
        $statement = $this->db->prepare('INSERT INTO organization_member_permissions (organization_id, user_id, permission_slug) VALUES (?, ?, ?)');
        foreach (array_unique($permissions) as $permission) {
            $statement->bind_param('iis', $organizationId, $userId, $permission);
            $statement->execute();
        }
    }

    public function setVerification(int $organizationId, string $decision): bool
    {
        $statement = $this->db->prepare('UPDATE organizations SET verification_status = ? WHERE id = ?');
        $statement->bind_param('si', $decision, $organizationId);
        $statement->execute();
        return $statement->affected_rows > 0;
    }

    public function transaction(callable $operation)
    {
        $this->db->begin_transaction();
        try {
            $result = $operation();
            $this->db->commit();
            return $result;
        } catch (\Throwable $error) {
            $this->db->rollback();
            throw $error;
        }
    }
}
