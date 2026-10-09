<?php
declare(strict_types=1);

final class Role
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $res = $this->db->query("SELECT * FROM roles ORDER BY id ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /**
     * @param string $name
     * @param string $slug
     * @param string|null $description
     * @return int|bool
     */
    public function create(string $name, string $slug, ?string $description = null)
    {
        $stmt = $this->db->prepare("INSERT INTO roles (name, slug, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $name, $slug, $description);
        if ($stmt->execute()) {
            return (int)$this->db->insert_id;
        }
        return false;
    }

    public function getPermissions(int $roleId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function syncPermissions(int $roleId, array $permissionIds): bool
    {
        if ($roleId <= 0 || !$this->findById($roleId)) return false;
        $permissionIds = array_values(array_unique($permissionIds));
        foreach ($permissionIds as $id) {
            if (!is_int($id) || $id <= 0 || !(new Permission($this->db))->findById($id)) return false;
        }
        // Start transaction
        $this->db->begin_transaction();
        try {
            // Remove existing
            $stmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->bind_param("i", $roleId);
            $stmt->execute();

            // Add new
            if (!empty($permissionIds)) {
                $stmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($permissionIds as $pId) {
                    $stmt->bind_param("ii", $roleId, $pId);
                    $stmt->execute();
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function update(int $id, string $name, ?string $description = null): bool
    {
        $stmt = $this->db->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
