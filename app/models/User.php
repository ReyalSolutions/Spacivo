<?php
declare(strict_types=1);

final class User
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function findByPhoneAndRole(string $phone, int $roleId): ?array
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE phone = ? AND role_id = ? LIMIT 1');
        $stmt->bind_param('si', $phone, $roleId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.id, u.username, u.role_id, u.status, u.first_name, u.middle_name, u.last_name, u.email, u.password, u.phone, u.image, u.created_at, r.name as role_name, r.slug as role_slug 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ? LIMIT 1
        ');
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.id, u.username, u.role_id, u.status, u.first_name, u.middle_name, u.last_name, u.email, u.password, u.phone, u.image, u.created_at, r.name as role_name, r.slug as role_slug 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.username = ? LIMIT 1
        ');
        $stmt->bind_param('s', $username);
        $stmt->execute();

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.id, u.username, u.role_id, u.status, u.first_name, u.middle_name, u.last_name, u.email, u.phone, u.image, u.created_at, r.name as role_name, r.slug as role_slug 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ? LIMIT 1
        ');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    public function create(string $firstName, ?string $middleName, string $lastName, string $email, string $phone, int $roleId, ?string $username = null, ?string $password = null): int
    {
        $hash = $password ? password_hash($password, PASSWORD_BCRYPT) : null;
        
        // --- Fallback for NOT NULL constraints on multi-step registration ---
        // If username is null, create a temporary unique one until Step 2 updates it.
        $username = $username ?? 'temp_' . bin2hex(random_bytes(6)) . '_' . time();

        $stmt = $this->db->prepare('
            INSERT INTO users (role_id, username, first_name, middle_name, last_name, email, phone, password, image, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, \'\', NOW())
        ');
        $stmt->bind_param('isssssss', $roleId, $username, $firstName, $middleName, $lastName, $email, $phone, $hash);
        $stmt->execute();

        return (int)$this->db->insert_id;
    }

    public function update(int $userId, string $firstName, ?string $middleName, string $lastName, string $username, string $email, string $phone, int $roleId, ?string $password = null, ?string $image = null): bool
    {
        $query = 'UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, username = ?, email = ?, phone = ?, role_id = ?';
        $params = [$firstName, $middleName, $lastName, $username, $email, $phone, $roleId];
        $types = 'ssssssi';

        if ($password !== null && $password !== '') {
            $query .= ', password = ?';
            $params[] = password_hash($password, PASSWORD_BCRYPT);
            $types .= 's';
        }

        if ($image !== null) {
            $query .= ', image = ?';
            $params[] = $image;
            $types .= 's';
        }

        $query .= ' WHERE id = ?';
        $params[] = $userId;
        $types .= 'i';

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    public function updateRegistration(int $userId, string $username, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare('UPDATE users SET username = ?, password = ? WHERE id = ?');
        $stmt->bind_param('ssi', $username, $hash, $userId);
        return $stmt->execute();
    }

    public function toggleStatus(int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET status = 1 - status WHERE id = ?');
        $stmt->bind_param('i', $userId);
        return $stmt->execute();
    }


    public function verify(string $identifier, string $password): ?array
    {
        $user = $this->findByUsername($identifier);
        if (!$user) {
            $user = $this->findByEmail($identifier);
        }

        if (!$user || empty($user['password'])) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        // Deactivated users cannot be verified
        if (isset($user['status']) && (int)$user['status'] !== 1) {
            return null;
        }

        return $user;
    }

    public function all(): array
    {
        $res = $this->db->query('
            SELECT u.*, r.name as role 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            ORDER BY u.created_at DESC
        ');
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function isUsernameTaken(string $username, ?int $excludeId = null): bool
    {
        $query = 'SELECT 1 FROM users WHERE username = ?';
        if ($excludeId) $query .= ' AND id != ?';
        $stmt = $this->db->prepare($query);
        if ($excludeId) $stmt->bind_param('si', $username, $excludeId);
        else $stmt->bind_param('s', $username);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function isEmailTaken(string $email, ?int $excludeId = null): bool
    {
        $query = 'SELECT 1 FROM users WHERE email = ?';
        if ($excludeId) $query .= ' AND id != ?';
        $stmt = $this->db->prepare($query);
        if ($excludeId) $stmt->bind_param('si', $email, $excludeId);
        else $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function isPhoneTaken(string $phone, ?int $excludeId = null): bool
    {
        $query = 'SELECT 1 FROM users WHERE phone = ?';
        if ($excludeId) $query .= ' AND id != ?';
        $stmt = $this->db->prepare($query);
        if ($excludeId) $stmt->bind_param('si', $phone, $excludeId);
        else $stmt->bind_param('s', $phone);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function getGrowthStats(int $months = 6): array
    {
        $sql = "
            SELECT DATE_FORMAT(u.created_at, '%Y-%m') as label, r.name as role, COUNT(*) as total
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY label, role
            ORDER BY label ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $months);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Format for Chart.js
        $labels = [];
        $data = [
            'owner' => [],
            'tenant' => [],
            'staff' => []
        ];

        // Get unique labels
        foreach ($res as $row) {
            if (!in_array($row['label'], $labels)) $labels[] = $row['label'];
        }

        foreach ($labels as $l) {
            foreach (array_keys($data) as $role) {
                $val = 0;
                foreach ($res as $row) {
                    if ($row['label'] === $l && strtolower($row['role']) === $role) {
                        $val = (int)$row['total'];
                        break;
                    }
                }
                $data[$role][] = $val;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Owners', 'data' => $data['owner'], 'backgroundColor' => '#6366f1'],
                ['label' => 'Tenants', 'data' => $data['tenant'], 'backgroundColor' => '#10b981'],
                ['label' => 'Staff', 'data' => $data['staff'], 'backgroundColor' => '#f59e0b']
            ]
        ];
    }
}

