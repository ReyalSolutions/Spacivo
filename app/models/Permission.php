<?php
declare(strict_types=1);

final class Permission
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $res = $this->db->query("SELECT * FROM permissions ORDER BY category ASC, name ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getByCategory(): array
    {
        $permissions = $this->all();
        $grouped = [];
        foreach ($permissions as $p) {
            $grouped[$p['category']][] = $p;
        }
        return $grouped;
    }

    public function exists(string $slug): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM permissions WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function create(string $name, string $slug, string $category, ?string $description = null): bool
    {
        $stmt = $this->db->prepare("INSERT INTO permissions (name, slug, category, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $slug, $category, $description);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM permissions WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
