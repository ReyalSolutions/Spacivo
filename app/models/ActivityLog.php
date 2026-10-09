<?php

class ActivityLog
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Record an administrative event.
     * 
     * @param int $adminId The admin performing the action
     * @param string $action The action type (e.g., 'CREATE_USER', 'UPDATE_USER')
     * @param array $details Detailed data about the change
     * @param int|null $targetId The ID of the affected user/resourse
     * @return bool
     */
    public function log(int $adminId, string $action, array $details, ?int $targetId = null): bool
    {
        $detailsJson = json_encode($details, JSON_PRETTY_PRINT);
        $stmt = $this->db->prepare("INSERT INTO activity_logs (admin_id, action, details, target_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $adminId, $action, $detailsJson, $targetId);
        return $stmt->execute();
    }

    /**
     * Fetch logs for administrative review.
     */
    public function all(int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, u.first_name, u.last_name, 
                   t.first_name as target_first, t.last_name as target_last
            FROM activity_logs l
            JOIN users u ON l.admin_id = u.id
            LEFT JOIN users t ON l.target_id = t.id
            ORDER BY l.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteMany(array $ids): bool
    {
        if (empty($ids)) return false;
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM activity_logs WHERE id IN ($placeholders)");
        
        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);
        
        return $stmt->execute();
    }
    public function getRecentLogs(int $limit = 5, ?int $ownerId = null): array
    {
        $query = "
            SELECT l.*, u.first_name, u.last_name
            FROM activity_logs l
            JOIN users u ON l.admin_id = u.id
        ";
        
        if ($ownerId !== null) {
            // Filter actions relevant to this owner if needed, or just show their own actions
            // For now, let's just show logs where they are the 'admin' (meaning performer)
            $query .= " WHERE l.admin_id = ? ";
        }
        
        $query .= " ORDER BY l.created_at DESC LIMIT ? ";
        
        $stmt = $this->db->prepare($query);
        if ($ownerId !== null) {
            $stmt->bind_param("ii", $ownerId, $limit);
        } else {
            $stmt->bind_param("i", $limit);
        }
        
        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $refined = [];
        foreach ($logs as $log) {
            $time = strtotime($log['created_at']);
            $diff = time() - $time;
            
            if ($diff < 60) $timeStr = 'Just now';
            elseif ($diff < 3600) $timeStr = round($diff/60) . ' mins ago';
            elseif ($diff < 86400) $timeStr = round($diff/3600) . ' hours ago';
            else $timeStr = date('M j', $time);

            $icon = 'fa-circle-info';
            $color = 'blue';
            $text = $log['action']; // Fallback

            switch ($log['action']) {
                case 'CREATE_USER': $icon = 'fa-user-plus'; $color = 'blue'; $text = "New user registered: " . $log['first_name']; break;
                case 'UPGRADE_SUBSCRIPTION': $icon = 'fa-credit-card'; $color = 'purple'; $text = "Plan upgraded to Yearly"; break;
                case 'APPROVE_HOUSE': $icon = 'fa-building-circle-check'; $color = 'emerald'; $text = "Property approved by admin"; break;
                case 'CREATE_BOOKING': $icon = 'fa-calendar-check'; $color = 'emerald'; $text = "New booking request received"; break;
                case 'PAYMENT_RECEIVED': $icon = 'fa-receipt'; $color = 'emerald'; $text = "Payment confirmed"; break;
            }

            $refined[] = [
                'type' => strtolower($log['action']),
                'icon' => $icon,
                'text' => $text,
                'time' => $timeStr,
                'color' => $color
            ];
        }
        return $refined;
    }
}
