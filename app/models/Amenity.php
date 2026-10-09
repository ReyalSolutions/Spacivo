<?php
declare(strict_types=1);

final class Amenity
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $res = $this->db->query("SELECT * FROM amenities ORDER BY name ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM amenities WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO amenities (name, icon, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('ss', $data['name'], $data['icon']);
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE amenities SET name = ?, icon = ? WHERE id = ?");
        $stmt->bind_param('ssi', $data['name'], $data['icon'], $id);
        return $stmt->execute() && $stmt->affected_rows >= 0;
    }

    public function isAmenityInUse(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(id) as cnt FROM boarding_house_amenities WHERE amenity_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return ($res['cnt'] ?? 0) > 0;
    }

    public function delete(int $id): bool
    {
        // Prevent deleting amenities that are actively being used by boarding houses
        if ($this->isAmenityInUse($id)) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM amenities WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
