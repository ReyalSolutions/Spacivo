<?php
declare(strict_types=1);

final class Payment
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function generateTransactionRef(string $method = 'manual'): string
    {
        $prefix = strtoupper(str_replace([' ', '_'], '-', $method));
        if (empty($prefix)) $prefix = 'PAY';
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function createPending(int $userId, int $bookingId, float $amount, string $paymentMethod, string $paymentType = 'rent', string $description = '', int $monthsCovered = 1): int
    {
        $transactionRef = $this->generateTransactionRef($paymentMethod);
        $stmt = $this->db->prepare('
            INSERT INTO payments (user_id, booking_id, amount, payment_method, status, transaction_ref, payment_type, description, months_covered, created_at)
            VALUES (?, ?, ?, ?, "pending", ?, ?, ?, ?, NOW())
        ');
        $stmt->bind_param('iidssssi', $userId, $bookingId, $amount, $paymentMethod, $transactionRef, $paymentType, $description, $monthsCovered);
        $stmt->execute();
        return (int)$this->db->insert_id;
    }

    public function createManual(int $userId, int $bookingId, float $amount, string $method, string $type, string $desc, string $date, int $months = 1): bool
    {
        $transactionRef = $this->generateTransactionRef($method);
        $stmt = $this->db->prepare('
            INSERT INTO payments (user_id, booking_id, amount, payment_method, status, transaction_ref, payment_type, description, created_at, months_covered)
            VALUES (?, ?, ?, ?, "paid", ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('iidsssssi', $userId, $bookingId, $amount, $method, $transactionRef, $type, $desc, $date, $months);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function getPendingById(int $paymentId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, user_id, booking_id, amount, payment_method, status, transaction_ref, payment_type, description, months_covered
            FROM payments
            WHERE id = ? AND status = "pending"
            LIMIT 1
        ');
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    public function markPaid(int $paymentId, string $transactionRef): bool
    {
        $stmt = $this->db->prepare('UPDATE payments SET status = "paid", transaction_ref = ? WHERE id = ?');
        $stmt->bind_param('si', $transactionRef, $paymentId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function updateAmountAndDescription(int $paymentId, float $amount, string $description): bool
    {
        $stmt = $this->db->prepare('UPDATE payments SET amount = ?, description = ? WHERE id = ? AND status = "pending"');
        $stmt->bind_param('dsi', $amount, $description, $paymentId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function markFailed(int $paymentId, string $reason = ''): void
    {
        // Reason is intentionally kept out of schema for MVP; it can be logged later.
        $stmt = $this->db->prepare('UPDATE payments SET status = "failed" WHERE id = ?');
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
    }

    public function getTotalPaidForBooking(int $bookingId): float
    {
        $stmt = $this->db->prepare('SELECT SUM(amount) as total FROM payments WHERE booking_id = ? AND status = "paid"');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (float)($res['total'] ?? 0.0);
    }

    public function getRecentByBooking(int $bookingId, int $limit = 5): array
    {
        $stmt = $this->db->prepare('
            SELECT id, amount, status, created_at, payment_method, transaction_ref, payment_type, description 
            FROM payments 
            WHERE booking_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ');
        $stmt->bind_param('ii', $bookingId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getForBooking(int $bookingId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, amount, status, created_at, payment_method, transaction_ref, payment_type, description 
            FROM payments 
            WHERE booking_id = ? 
            ORDER BY created_at DESC
        ');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getForBookingPaged(int $bookingId, int $limit, int $offset, string $search = ''): array
    {
        $sql = '
            SELECT id, amount, status, created_at, payment_method, transaction_ref, payment_type, description 
            FROM payments 
            WHERE booking_id = ?
        ';
        
        if ($search !== '') {
            $sql .= ' AND (transaction_ref LIKE ? OR description LIKE ? OR payment_type LIKE ?)';
        }
        
        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        
        $stmt = $this->db->prepare($sql);
        
        if ($search !== '') {
            $searchTerm = "%$search%";
            $stmt->bind_param('isssii', $bookingId, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
        } else {
            $stmt->bind_param('iii', $bookingId, $limit, $offset);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function countForBooking(int $bookingId, string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) as total FROM payments WHERE booking_id = ?';
        
        if ($search !== '') {
            $sql .= ' AND (transaction_ref LIKE ? OR description LIKE ? OR payment_type LIKE ?)';
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($search !== '') {
            $searchTerm = "%$search%";
            $stmt->bind_param('isss', $bookingId, $searchTerm, $searchTerm, $searchTerm);
        } else {
            $stmt->bind_param('i', $bookingId);
        }
        
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int)($res['total'] ?? 0);
    }

    public function getFilteredForTenant(int $userId, ?int $boardingHouseId, ?string $monthStr): array
    {
        $query = '
            SELECT p.id, p.amount, p.status, p.created_at, p.payment_method, p.transaction_ref, p.payment_type, p.description, bh.name as boarding_house_name
            FROM payments p
            INNER JOIN bookings b ON b.id = p.booking_id
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            WHERE p.user_id = ?
        ';
        
        $params = [$userId];
        $types = 'i';
        
        if ($boardingHouseId !== null) {
            $query .= ' AND bh.id = ?';
            $params[] = $boardingHouseId;
            $types .= 'i';
        }
        
        if ($monthStr !== null) {
            $query .= ' AND DATE_FORMAT(p.created_at, "%Y-%m") = ?';
            $params[] = $monthStr;
            $types .= 's';
        }
        
        $query .= ' ORDER BY p.created_at DESC';
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getTotalPaidForUser(int $userId): float
    {
        $stmt = $this->db->prepare('SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status = "paid"');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (float)($res['total'] ?? 0.0);
    }

    public function getRentPaymentCount(int $bookingId): int
    {
        // Counts how many "months" or "rent cycles" have been paid by summing months_covered.
        $stmt = $this->db->prepare('
            SELECT SUM(months_covered) as total_months 
            FROM payments 
            WHERE booking_id = ? 
              AND status = "paid" 
              AND (payment_type IN ("rent", "advance") OR payment_type IS NULL OR payment_type = "")
        ');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int)($res['total_months'] ?? 0);
    }

    public function all(): array
    {
        $res = $this->db->query('SELECT * FROM payments ORDER BY created_at DESC');
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Fetch all payments with associated user and property details for administrative auditing.
     */
    /**
     * Fetch all payments with associated details with pagination and search for server-side DataTables.
     */
    public function allWithDetailsPaged(int $ownerId, int $limit, int $offset, string $search = '', string $orderBy = 'p.created_at', string $orderDir = 'DESC', ?int $houseId = null, ?string $status = null): array
    {
        $query = '
            SELECT p.*, bh.name as boarding_house_name, u.first_name, u.last_name, u.email
            FROM payments p
            LEFT JOIN bookings b ON b.id = p.booking_id
            LEFT JOIN rooms r ON r.id = b.room_id
            LEFT JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            LEFT JOIN users u ON u.id = p.user_id
            WHERE bh.owner_id = ?
        ';
        
        $params = [$ownerId];
        $types = 'i';

        if ($houseId !== null) {
            $query .= ' AND bh.id = ?';
            $params[] = $houseId;
            $types .= 'i';
        }

        if ($status !== null && $status !== '') {
            $query .= ' AND p.status = ?';
            $params[] = $status;
            $types .= 's';
        }
        
        if ($search !== '') {
            $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR bh.name LIKE ? OR p.transaction_ref LIKE ? OR p.payment_method LIKE ?)';
            $searchTerm = "%$search%";
            for ($i = 0; $i < 6; $i++) {
                $params[] = $searchTerm;
                $types .= 's';
            }
        }

        // Sanitize OrderBy and OrderDir
        $allowedColumns = ['p.created_at', 'u.first_name', 'bh.name', 'p.payment_method', 'p.transaction_ref', 'p.amount', 'p.status'];
        if (!in_array($orderBy, $allowedColumns)) $orderBy = 'p.created_at';
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $query .= " ORDER BY $orderBy $orderDir LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function countAllWithDetails(int $ownerId, string $search = '', ?int $houseId = null, ?string $status = null): int
    {
        $query = '
            SELECT COUNT(*) as total
            FROM payments p
            LEFT JOIN bookings b ON b.id = p.booking_id
            LEFT JOIN rooms r ON r.id = b.room_id
            LEFT JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            LEFT JOIN users u ON u.id = p.user_id
            WHERE bh.owner_id = ?
        ';
        
        $params = [$ownerId];
        $types = 'i';

        if ($houseId !== null) {
            $query .= ' AND bh.id = ?';
            $params[] = $houseId;
            $types .= 'i';
        }

        if ($status !== null && $status !== '') {
            $query .= ' AND p.status = ?';
            $params[] = $status;
            $types .= 's';
        }
        
        if ($search !== '') {
            $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR bh.name LIKE ? OR p.transaction_ref LIKE ? OR p.payment_method LIKE ?)';
            $searchTerm = "%$search%";
            for ($i = 0; $i < 6; $i++) {
                $params[] = $searchTerm;
                $types .= 's';
            }
        }

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int)($res['total'] ?? 0);
    }

    public function allWithDetails(?int $ownerId = null, ?int $tenantId = null, ?int $houseId = null): array
    {
        $query = '
            SELECT p.*, bh.name as boarding_house_name, u.first_name, u.last_name, u.email
            FROM payments p
            LEFT JOIN bookings b ON b.id = p.booking_id
            LEFT JOIN rooms r ON r.id = b.room_id
            LEFT JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            LEFT JOIN users u ON u.id = p.user_id
            WHERE 1=1
        ';
        
        $params = [];
        $types = '';

        if ($ownerId !== null) {
            $query .= ' AND bh.owner_id = ?';
            $params[] = $ownerId;
            $types .= 'i';
        }
        if ($tenantId !== null) {
            $query .= ' AND p.user_id = ?';
            $params[] = $tenantId;
            $types .= 'i';
        }
        if ($houseId !== null) {
            $query .= ' AND bh.id = ?';
            $params[] = $houseId;
            $types .= 'i';
        }
        
        $query .= ' ORDER BY p.created_at DESC';
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $res = $this->db->query($query);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
    }

    public function getTotalRevenueForOwner(int $ownerId): float
    {
        $sql = "
            SELECT SUM(p.amount) as total
            FROM payments p
            INNER JOIN bookings b ON b.id = p.booking_id
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            WHERE bh.owner_id = ? AND p.status = 'paid'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (float)($res['total'] ?? 0.0);
    }

    public function getRevenueTrend(?int $ownerId = null, string $period = 'daily', int $days = 7): array
    {
        $sql = "SELECT SUM(p.amount) as total, ";
        if ($period === 'daily') {
            $sql .= "DATE(p.created_at) as label ";
        } else {
            $sql .= "DATE_FORMAT(p.created_at, '%Y-%m') as label ";
        }
        
        $sql .= " FROM payments p ";
        if ($ownerId !== null) {
            $sql .= " INNER JOIN bookings b ON b.id = p.booking_id ";
            $sql .= " INNER JOIN rooms r ON r.id = b.room_id ";
            $sql .= " INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id ";
            $sql .= " WHERE bh.owner_id = ? AND p.status = 'paid' ";
        } else {
            $sql .= " WHERE p.status = 'paid' ";
        }
        
        if ($period === 'daily') {
            $sql .= " AND p.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ";
            $sql .= " GROUP BY DATE(p.created_at) ";
        } else {
            $sql .= " AND p.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) ";
            $sql .= " GROUP BY DATE_FORMAT(p.created_at, '%Y-%m') ";
        }
        
        $sql .= " ORDER BY label ASC";
        
        $stmt = $this->db->prepare($sql);
        if ($ownerId !== null && $period === 'daily') {
            $stmt->bind_param('ii', $ownerId, $days);
        } elseif ($ownerId !== null) {
            $stmt->bind_param('i', $ownerId);
        } elseif ($period === 'daily') {
            $stmt->bind_param('i', $days);
        }
        
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function deleteManually(int $paymentId, int $bookingId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM payments WHERE id = ? AND booking_id = ? AND transaction_ref LIKE "MANUAL-%"');
        $stmt->bind_param('ii', $paymentId, $bookingId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}

