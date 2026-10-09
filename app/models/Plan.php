<?php
class Plan
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE is_deleted = 0 ORDER BY price_monthly ASC");
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO plans (name, price_monthly, price_yearly, bhouse_limit, room_limit, length_free, features) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'sddiiis',
            $data['name'],
            $data['price_monthly'],
            $data['price_yearly'],
            $data['bhouse_limit'],
            $data['room_limit'],
            $data['length_free'],
            $data['features']
        );
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE plans SET name = ?, price_monthly = ?, price_yearly = ?, bhouse_limit = ?, room_limit = ?, length_free = ?, features = ? WHERE id = ?"
        );
        $stmt->bind_param(
            'sddiiisi',
            $data['name'],
            $data['price_monthly'],
            $data['price_yearly'],
            $data['bhouse_limit'],
            $data['room_limit'],
            $data['length_free'],
            $data['features'],
            $id
        );
        return $stmt->execute() && $stmt->affected_rows >= 0;
    }

    public function isPlanInUse(int $id): bool
    {
        // Check if any subscriptions have this plan_id and are not cancelled/expired
        $stmt = $this->db->prepare(
            "SELECT COUNT(id) as cnt FROM subscriptions WHERE plan_id = ? AND status NOT IN ('cancelled', 'expired')"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return ($res['cnt'] ?? 0) > 0;
    }

    public function delete(int $id): bool
    {
        // Still prevent deactivating plans that are actively being used by subscriptions
        if ($this->isPlanInUse($id)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE plans SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
