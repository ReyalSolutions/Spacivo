<?php
declare(strict_types=1);

final class Review
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Get all reviews with boarding house and user details.
     */
    public function getAll(): array
    {
        $sql = "SELECT r.*, bh.name as boarding_house_name, CONCAT(u.first_name, ' ', u.last_name) as user_name 
                FROM reviews r
                JOIN boarding_houses bh ON r.boarding_house_id = bh.id
                JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Update the status of a review (approved, rejected, pending).
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Delete a review from the database.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Get a single review by ID.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT r.*, bh.name as boarding_house_name, CONCAT(u.first_name, ' ', u.last_name) as user_name 
                                    FROM reviews r
                                    JOIN boarding_houses bh ON r.boarding_house_id = bh.id
                                    JOIN users u ON r.user_id = u.id
                                    WHERE r.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }
}
