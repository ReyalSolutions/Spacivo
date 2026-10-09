<?php
declare(strict_types=1);

final class Favorite
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function exists(int $userId, int $boardingHouseId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM favorites WHERE user_id = ? AND boarding_house_id = ? LIMIT 1');
        $stmt->bind_param('ii', $userId, $boardingHouseId);
        $stmt->execute();
        $res = $stmt->get_result();
        return (bool)$res->fetch_assoc();
    }

    public function toggle(int $userId, int $boardingHouseId): array
    {
        if ($this->exists($userId, $boardingHouseId)) {
            $stmt = $this->db->prepare('DELETE FROM favorites WHERE user_id = ? AND boarding_house_id = ?');
            $stmt->bind_param('ii', $userId, $boardingHouseId);
            $stmt->execute();

            return ['favorited' => false];
        }

        $stmt = $this->db->prepare('INSERT INTO favorites (user_id, boarding_house_id, created_at) VALUES (?, ?, NOW())');
        $stmt->bind_param('ii', $userId, $boardingHouseId);
        $stmt->execute();

        return ['favorited' => true];
    }

    public function getForUser(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                f.boarding_house_id,
                bh.name,
                bh.address,
                bh.latitude,
                bh.longitude,
                bh.status,
                bh.description
            FROM favorites f
            INNER JOIN boarding_houses bh ON bh.id = f.boarding_house_id
            WHERE f.user_id = ?
            AND bh.status = "approved"
            ORDER BY f.created_at DESC
        ');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }
}

