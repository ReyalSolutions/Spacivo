<?php
declare(strict_types=1);

final class Subscription
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function getLimitsForOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare('
            SELECT p.bhouse_limit, p.room_limit
            FROM subscriptions s
            JOIN plans p ON p.id = s.plan_id
            WHERE s.owner_id = ? AND s.status = "active"
            ORDER BY s.created_at DESC
            LIMIT 1
        ');
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return [
                'bhouse_limit' => (int)$row['bhouse_limit'],
                'room_limit' => (int)$row['room_limit']
            ];
        }
        return ['bhouse_limit' => 0, 'room_limit' => 0];
    }

    public function allWithDetails(?int $ownerId = null): array
    {
        $query = '
            SELECT 
                s.*, 
                p.name as plan_name, 
                p.price_monthly, 
                p.price_yearly, 
                p.room_limit,
                p.features,
                u.first_name, 
                u.last_name, 
                u.email
            FROM subscriptions s
            LEFT JOIN plans p ON p.id = s.plan_id
            LEFT JOIN users u ON u.id = s.owner_id
        ';

        if ($ownerId !== null) {
            $query .= ' WHERE s.owner_id = ?';
        }

        $query .= ' ORDER BY s.created_at DESC';

        if ($ownerId !== null) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $ownerId);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $res = $this->db->query($query);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
    }

    public function upgradeToYearly(int $id, ?int $ownerId = null): bool
    {
        $query = "UPDATE subscriptions SET billing_cycle = 'yearly' WHERE id = ?";
        if ($ownerId !== null) {
            $query .= " AND owner_id = ?";
        }

        $stmt = $this->db->prepare($query);
        if ($ownerId !== null) {
            $stmt->bind_param('ii', $id, $ownerId);
        } else {
            $stmt->bind_param('i', $id);
        }

        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function downgradeToMonthly(int $id, ?int $ownerId = null): bool
    {
        $query = "UPDATE subscriptions SET billing_cycle = 'monthly' WHERE id = ?";
        if ($ownerId !== null) {
            $query .= " AND owner_id = ?";
        }

        $stmt = $this->db->prepare($query);
        if ($ownerId !== null) {
            $stmt->bind_param('ii', $id, $ownerId);
        } else {
            $stmt->bind_param('i', $id);
        }

        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function cancelSubscription(int $id, ?int $ownerId = null): bool
    {
        $query = "UPDATE subscriptions SET status = 'cancelled' WHERE id = ?";
        if ($ownerId !== null) {
            $query .= " AND owner_id = ?";
        }

        $stmt = $this->db->prepare($query);
        if ($ownerId !== null) {
            $stmt->bind_param('ii', $id, $ownerId);
        } else {
            $stmt->bind_param('i', $id);
        }

        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function changePlan(int $id, int $newPlanId, string $billingCycle, ?int $ownerId = null): bool
    {
        $query = "UPDATE subscriptions SET plan_id = ?, billing_cycle = ? WHERE id = ?";
        if ($ownerId !== null) {
            $query .= " AND owner_id = ?";
        }

        $stmt = $this->db->prepare($query);
        if ($ownerId !== null) {
            $stmt->bind_param('isii', $newPlanId, $billingCycle, $id, $ownerId);
        } else {
            $stmt->bind_param('isi', $newPlanId, $billingCycle, $id);
        }

        return $stmt->execute() && $stmt->affected_rows > 0;
    }
    public function getOwnerSubscriptionStatus(int $ownerId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, p.name AS plan_name, p.price_monthly, p.price_yearly, p.bhouse_limit, p.room_limit
            FROM subscriptions s
            JOIN plans p ON p.id = s.plan_id
            WHERE s.owner_id = ? AND s.status = 'active'
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;

        $startRaw = (!empty($row['start_date']) && $row['start_date'] !== '0000-00-00')
            ? $row['start_date']
            : substr((string)$row['created_at'], 0, 10);

        try {
            $start = new DateTime($startRaw);
            $today = new DateTime('today');
            $expires = clone $start;
            $interval = ($row['billing_cycle'] === 'yearly') ? new DateInterval('P1Y') : new DateInterval('P1M');
            
            while ($expires <= $today) {
                $expires->add($interval);
            }
            $cycleEnd = clone $expires;
            $cycleEnd->sub($interval);

            $currentCycleStartStr = $cycleEnd->format('Y-m-d');
            $subId = (int)$row['id'];
            
            $paidStmt = $this->db->prepare("
                SELECT id FROM plan_payments
                WHERE owner_id = ? AND subscription_id = ? AND status = 'paid'
                  AND paid_at >= ?
                LIMIT 1
            ");
            $paidStmt->bind_param('iis', $ownerId, $subId, $currentCycleStartStr);
            $paidStmt->execute();
            $hasPaid = (bool)$paidStmt->get_result()->fetch_assoc();

            $isExpired = ($today >= $cycleEnd) && !$hasPaid;

            return array_merge($row, [
                'current_cycle_end' => $expires->format('Y-m-d'), // Next due date
                'expires_on'        => $cycleEnd->format('Y-m-d'), // When the current period actually ended
                'is_expired'        => $isExpired,
                'has_paid'          => $hasPaid,
                'cycle_start'       => $currentCycleStartStr,
            ]);
        } catch (\Exception $e) {
            return $row;
        }
    }

    public function getSubscriptionTrend(string $period = 'monthly'): array
    {
        $sql = "SELECT SUM(amount) as total, ";
        if ($period === 'daily') {
            $sql .= "DATE(paid_at) as label ";
        } else {
            $sql .= "DATE_FORMAT(paid_at, '%Y-%m') as label ";
        }
        $sql .= " FROM plan_payments WHERE status = 'paid' ";
        
        if ($period === 'daily') {
            $sql .= " AND paid_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(paid_at) ";
        } else {
            $sql .= " AND paid_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(paid_at, '%Y-%m') ";
        }
        $sql .= " ORDER BY label ASC";
        
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
