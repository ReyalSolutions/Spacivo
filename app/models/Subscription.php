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
        $row = $this->getOwnerSubscriptionStatus($ownerId);
        if ($row && !$row['is_expired']) {
            return ['bhouse_limit' => (int)$row['bhouse_limit'], 'room_limit' => (int)$row['room_limit']];
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
            return $res ? array_map(function (array $row): array { return $this->withEntitlement($row); }, $res->fetch_all(MYSQLI_ASSOC)) : [];
        } else {
            $res = $this->db->query($query);
            return $res ? array_map(function (array $row): array { return $this->withEntitlement($row); }, $res->fetch_all(MYSQLI_ASSOC)) : [];
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

        if (!$stmt->execute()) return false;
        if ($stmt->affected_rows > 0) return true;
        // Renewing the same plan is a valid operation even when no plan columns change.
        // Still verify the scoped row exists; a foreign/missing subscription must fail.
        $exists = $this->db->prepare('SELECT id FROM subscriptions WHERE id = ?' . ($ownerId !== null ? ' AND owner_id = ?' : ''));
        if ($ownerId !== null) {
            $exists->bind_param('ii', $id, $ownerId);
        } else {
            $exists->bind_param('i', $id);
        }
        $exists->execute();
        return (bool)$exists->get_result()->fetch_assoc();
    }
    public function getOwnerSubscriptionStatus(int $ownerId): ?array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.name AS plan_name, p.price_monthly, p.price_yearly, p.bhouse_limit, p.room_limit
            FROM subscriptions s JOIN plans p ON p.id = s.plan_id
            WHERE s.owner_id = ? ORDER BY s.created_at DESC, s.id DESC");
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $fallback = null;
        foreach ($rows as $row) {
            $status = $this->withEntitlement($row);
            if (!$status['is_expired']) return $status;
            if ($fallback === null) $fallback = $status;
        }
        return $fallback;
    }

    private function withEntitlement(array $row): array
    {
        // Only a settled payment belonging to this owner, subscription, plan and cycle counts.
        $stmt = $this->db->prepare("SELECT paid_at FROM plan_payments
            WHERE owner_id = ? AND subscription_id = ? AND plan_id = ? AND billing_cycle = ?
              AND status = 'paid' AND paid_at IS NOT NULL AND paid_at <= NOW()
            ORDER BY paid_at DESC, id DESC LIMIT 1");
        $ownerId = (int)$row['owner_id'];
        $subscriptionId = (int)$row['id'];
        $planId = (int)$row['plan_id'];
        $cycle = $row['billing_cycle'];
        $stmt->bind_param('iiis', $ownerId, $subscriptionId, $planId, $cycle);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        return self::evaluateEntitlement($row, $payment ?: null, new DateTimeImmutable('today'));
    }

    public static function evaluateEntitlement(array $row, ?array $payment, DateTimeImmutable $today): array
    {
        $result = array_merge($row, ['is_expired' => true, 'has_paid' => false,
            'expires_on' => null, 'current_cycle_end' => null, 'cycle_start' => null]);
        try {
            $startRaw = !empty($row['start_date']) && $row['start_date'] !== '0000-00-00'
                ? $row['start_date'] : substr((string)$row['created_at'], 0, 10);
            $start = new DateTimeImmutable($startRaw);
            $start = $start->setTime(0, 0);
            $anchor = $start;
            $hasPaid = false;
            if ($payment && !empty($payment['paid_at'])) {
                $paid = (new DateTimeImmutable($payment['paid_at']))->setTime(0, 0);
                if ($paid >= $start && $paid <= $today) {
                    $anchor = $paid;
                    $hasPaid = true;
                }
            }
            // One paid renewal covers one period; duplicate receipts do not stack periods.
            // Clamp month/year anniversaries so January 31 and leap days cannot overflow.
            $months = ($row['billing_cycle'] ?? 'monthly') === 'yearly' ? 12 : 1;
            $targetMonth = $anchor->modify('first day of this month')->modify('+' . $months . ' months');
            $end = $targetMonth->setDate((int)$targetMonth->format('Y'), (int)$targetMonth->format('m'),
                min((int)$anchor->format('d'), (int)$targetMonth->format('t')));
            if (!empty($row['end_date']) && $row['end_date'] !== '0000-00-00') {
                $explicitEnd = (new DateTimeImmutable($row['end_date']))->setTime(0, 0)->modify('+1 day');
                if ($explicitEnd < $end) $end = $explicitEnd;
            }
            $eligible = $row['status'] === 'active' || ($row['status'] === 'expired' && $hasPaid);
            $expired = !$eligible || $today < $start || $today >= $end;
            return array_merge($result, [
                'status' => !$expired ? 'active' : ($row['status'] === 'active' ? 'expired' : $row['status']),
                'stored_status' => $row['status'],
                'is_expired' => $expired, 'has_paid' => $hasPaid,
                'expires_on' => $end->format('Y-m-d'), 'current_cycle_end' => $end->format('Y-m-d'),
                'cycle_start' => $anchor->format('Y-m-d'),
            ]);
        } catch (Exception $error) {
            return $result;
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
