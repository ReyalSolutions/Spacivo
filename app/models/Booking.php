<?php
declare(strict_types=1);

final class Booking
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function createApproved(int $userId, int $roomId, string $startDate, ?string $endDate, float $totalAmount): int
    {
        $this->db->begin_transaction();
        try {
            $stmtRoom = $this->db->prepare('SELECT id, price, available_slots FROM rooms WHERE id = ? LIMIT 1 FOR UPDATE');
            $stmtRoom->bind_param('i', $roomId);
            $stmtRoom->execute();
            $room = $stmtRoom->get_result()->fetch_assoc();

            if (!$room) {
                throw new RuntimeException('Room not found.');
            }

            if ((int)$room['available_slots'] <= 0) {
                throw new RuntimeException('Room is not available.');
            }

            $stmt = $this->db->prepare('
                INSERT INTO bookings (user_id, room_id, start_date, end_date, status, total_amount, created_at)
                VALUES (?, ?, ?, ?, "approved", ?, NOW())
            ');
            $stmt->bind_param('iissd', $userId, $roomId, $startDate, $endDate, $totalAmount);
            $stmt->execute();
            
            $bookingId = (int)$this->db->insert_id;

            $stmtUpdateRoom = $this->db->prepare('UPDATE rooms SET available_slots = available_slots - 1 WHERE id = ?');
            $stmtUpdateRoom->bind_param('i', $roomId);
            $stmtUpdateRoom->execute();

            $this->db->commit();
            return $bookingId;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function getForTenant(int $tenantId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                b.id,
                b.status,
                b.is_moved_out,
                b.start_date,
                b.end_date,
                b.total_amount,
                r.room_name,
                r.price AS room_price,
                bh.id AS boarding_house_id,
                bh.name AS boarding_house_name,
                bh.address
            FROM bookings b
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
        ');
        $stmt->bind_param('i', $tenantId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getForOwner(int $ownerId, ?int $boardingHouseId = null): array
    {
        $sql = '
            SELECT
                b.id,
                b.status,
                b.is_moved_out,
                b.start_date,
                b.end_date,
                b.total_amount,
                b.created_at,
                r.room_name,
                bh.id AS boarding_house_id,
                bh.name AS boarding_house_name,
                bh.address,
                CONCAT(u.first_name, \' \', u.last_name) AS tenant_name,
                u.email AS tenant_email,
                u.phone AS tenant_phone
            FROM bookings b
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE bh.owner_id = ?
        ';

        if ($boardingHouseId !== null) {
            $sql .= ' AND bh.id = ?';
        }

        $sql .= ' ORDER BY b.created_at DESC';

        $stmt = $this->db->prepare($sql);
        if ($boardingHouseId !== null) {
            $stmt->bind_param('ii', $ownerId, $boardingHouseId);
        } else {
            $stmt->bind_param('i', $ownerId);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function countForOwnerPaged(int $ownerId, string $search = '', ?int $boardingHouseId = null, ?string $status = null, ?int $isMovedOut = null): int
    {
        $sql = '
            SELECT COUNT(b.id) as cnt 
            FROM bookings b
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE bh.owner_id = ?
        ';
        $types = 'i';
        $params = [$ownerId];

        if ($search !== '') {
            $sql .= ' AND (CONCAT(u.first_name, " ", u.last_name) LIKE ? OR bh.name LIKE ? OR r.room_name LIKE ?)';
            $like = '%' . $search . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($boardingHouseId !== null) {
            $sql .= ' AND bh.id = ?';
            $types .= 'i';
            $params[] = $boardingHouseId;
        }

        if ($status !== null) {
            $sql .= ' AND b.status = ?';
            $types .= 's';
            $params[] = $status;
        }
        
        if ($isMovedOut !== null) {
            $sql .= ' AND b.is_moved_out = ?';
            $types .= 'i';
            $params[] = $isMovedOut;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int)($res['cnt'] ?? 0);
    }

    public function getForOwnerPaged(int $ownerId, int $limit, int $offset, string $search = '', string $orderBy = 'b.created_at', string $orderDir = 'DESC', ?int $boardingHouseId = null, ?string $status = null, ?int $isMovedOut = null): array
    {
        $sql = "
            SELECT
                b.id,
                b.status,
                b.is_moved_out,
                b.start_date,
                b.end_date,
                b.total_amount,
                b.created_at,
                r.room_name,
                bh.id AS boarding_house_id,
                bh.name AS boarding_house_name,
                bh.address,
                CONCAT(u.first_name, ' ', u.last_name) AS tenant_name,
                u.email AS tenant_email,
                u.phone AS tenant_phone
            FROM bookings b
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE bh.owner_id = ?
        ";
        
        $types = 'i';
        $params = [$ownerId];

        if ($search !== '') {
            $sql .= ' AND (CONCAT(u.first_name, " ", u.last_name) LIKE ? OR bh.name LIKE ? OR r.room_name LIKE ?)';
            $like = '%' . $search . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($boardingHouseId !== null) {
            $sql .= ' AND bh.id = ?';
            $types .= 'i';
            $params[] = $boardingHouseId;
        }

        if ($status !== null) {
            $sql .= ' AND b.status = ?';
            $types .= 's';
            $params[] = $status;
        }
        
        if ($isMovedOut !== null) {
            $sql .= ' AND b.is_moved_out = ?';
            $types .= 'i';
            $params[] = $isMovedOut;
        }

        $allowedColumns = ['b.created_at', 'tenant_name', 'bh.name', 'b.total_amount', 'b.status'];
        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'b.created_at';
        }
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getById(int $bookingId, int $ownerId): ?array
    {
        $sql = '
            SELECT
                b.id,
                b.status,
                b.is_moved_out,
                b.start_date,
                b.end_date,
                b.total_amount,
                b.created_at,
                r.room_name,
                r.price AS room_price,
                bh.id AS boarding_house_id,
                bh.name AS boarding_house_name,
                bh.address,
                CONCAT(u.first_name, \' \', u.last_name) AS tenant_name,
                u.email AS tenant_email,
                u.phone AS tenant_phone,
                CONCAT(owner.first_name, \' \', owner.last_name) AS owner_name
            FROM bookings b
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN users owner ON owner.id = bh.owner_id
            WHERE b.id = ? AND bh.owner_id = ?
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $bookingId, $ownerId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    public function moveOut(int $ownerId, int $bookingId, ?string $endDate = null): bool
    {
        $this->db->begin_transaction();
        try {
            // Find the room ID if this active booking belongs to the owner
            $stmtGetInfo = $this->db->prepare('
                SELECT r.id AS room_id
                FROM bookings b
                INNER JOIN rooms r ON r.id = b.room_id
                INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
                WHERE b.id = ? AND bh.owner_id = ? AND b.status = "approved" AND b.is_moved_out = 0
                LIMIT 1
            ');
            $stmtGetInfo->bind_param('ii', $bookingId, $ownerId);
            $stmtGetInfo->execute();
            $res = $stmtGetInfo->get_result();
            if ($res->num_rows === 0) {
                $this->db->rollback();
                return false;
            }
            $row = $res->fetch_assoc();
            $roomId = (int)$row['room_id'];

            // Update to moved out and set the provided end_date (or today)
            $finalEndDate = $endDate ?: date('Y-m-d');
            $stmtUpdate = $this->db->prepare('UPDATE bookings SET is_moved_out = 1, end_date = ? WHERE id = ?');
            $stmtUpdate->bind_param('si', $finalEndDate, $bookingId);
            $stmtUpdate->execute();

            if ($stmtUpdate->affected_rows > 0) {
                // Return the reserved slot back to the room
                $stmtRoom = $this->db->prepare('UPDATE rooms SET available_slots = available_slots + 1 WHERE id = ?');
                $stmtRoom->bind_param('i', $roomId);
                $stmtRoom->execute();
            }

            $this->db->commit();
            return $stmtUpdate->affected_rows > 0;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getAllActiveForTenant(int $tenantId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                b.id AS booking_id,
                b.start_date,
                b.end_date,
                b.total_amount,
                r.id AS room_id,
                r.room_name,
                r.price AS room_price,
                bh.id AS boarding_house_id,
                bh.name AS boarding_house_name,
                bh.address AS boarding_house_address,
                u.first_name AS owner_name,
                u.phone AS owner_phone
            FROM bookings b
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            INNER JOIN users u ON u.id = bh.owner_id
            WHERE b.user_id = ? AND b.status = "approved" AND b.is_moved_out = 0
            ORDER BY b.created_at DESC
        ');
        $stmt->bind_param('i', $tenantId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function all(): array
    {
        $res = $this->db->query('SELECT * FROM bookings ORDER BY created_at DESC');
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function updateStatus(int $bookingId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $bookingId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    /**
     * Approve a pending booking: set status to 'approved' and decrement room slot.
     */
    public function approvePending(int $bookingId, int $ownerId): bool
    {
        $this->db->begin_transaction();
        try {
            $stmtCheck = $this->db->prepare('
                SELECT b.id, b.room_id FROM bookings b
                JOIN rooms r ON r.id = b.room_id
                JOIN boarding_houses bh ON bh.id = r.boarding_house_id
                WHERE b.id = ? AND bh.owner_id = ? AND b.status = "pending"
                LIMIT 1
            ');
            $stmtCheck->bind_param('ii', $bookingId, $ownerId);
            $stmtCheck->execute();
            $row = $stmtCheck->get_result()->fetch_assoc();
            if (!$row) { $this->db->rollback(); return false; }

            $roomId = (int)$row['room_id'];

            $stmtUpd = $this->db->prepare('UPDATE bookings SET status = "approved" WHERE id = ?');
            $stmtUpd->bind_param('i', $bookingId);
            $stmtUpd->execute();

            $stmtRoom = $this->db->prepare('UPDATE rooms SET available_slots = GREATEST(available_slots - 1, 0) WHERE id = ?');
            $stmtRoom->bind_param('i', $roomId);
            $stmtRoom->execute();

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Reject a pending booking: set status to 'rejected'.
     */
    public function rejectPending(int $bookingId, int $ownerId): bool
    {
        $stmt = $this->db->prepare('
            UPDATE bookings b
            JOIN rooms r ON r.id = b.room_id
            JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            SET b.status = "rejected"
            WHERE b.id = ? AND bh.owner_id = ? AND b.status = "pending"
        ');
        $stmt->bind_param('ii', $bookingId, $ownerId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}

