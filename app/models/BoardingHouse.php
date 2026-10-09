<?php
declare(strict_types=1);

final class BoardingHouse
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function getAllApproved(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $q = $filters['q'] ?? null;
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;

        // Keep it simple: filter by room price range if provided.
        $sql = '
            SELECT
                bh.id,
                bh.owner_id,
                bh.name,
                bh.description,
                bh.address,
                bh.latitude,
                bh.longitude,
                bh.status,
                bh.created_at
            FROM boarding_houses bh
            WHERE bh.status = "approved" AND bh.is_deleted = 0
        ';

        $params = [];
        $types = '';

        if ($q !== null && $q !== '') {
            $sql .= ' AND (bh.name LIKE ? OR bh.address LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
            $types .= 'ss';
        }

        if ($minPrice !== null && $minPrice !== '') {
            $sql .= ' AND bh.id IN (
                SELECT r.boarding_house_id FROM rooms r
                WHERE r.price >= ?
            )';
            $params[] = (float)$minPrice;
            $types .= 'd';
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $sql .= ' AND bh.id IN (
                SELECT r.boarding_house_id FROM rooms r
                WHERE r.price <= ?
            )';
            $params[] = (float)$maxPrice;
            $types .= 'd';
        }

        $sql .= ' ORDER BY bh.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        return $rows ?: [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, owner_id, name, description, address, latitude, longitude, status, created_at
            FROM boarding_houses
            WHERE id = ? AND is_deleted = 0
            LIMIT 1
        ');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    public function getRoomsByBoardingHouseId(int $boardingHouseId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, boarding_house_id, room_name, price, capacity, available_slots, created_at
            FROM rooms
            WHERE boarding_house_id = ?
            ORDER BY room_name ASC
        ');
        $stmt->bind_param('i', $boardingHouseId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rooms = $res->fetch_all(MYSQLI_ASSOC);

        if (!empty($rooms)) {
            $roomIds = array_column($rooms, 'id');
            $inClause = implode(',', array_fill(0, count($roomIds), '?'));
            $sql = "SELECT ra.room_id, a.id, a.name, a.icon 
                    FROM room_amenities ra 
                    JOIN amenities a ON ra.amenity_id = a.id 
                    WHERE ra.room_id IN ($inClause)
                    ORDER BY a.name ASC";
            $stmtAm = $this->db->prepare($sql);
            $types = str_repeat('i', count($roomIds));
            $stmtAm->bind_param($types, ...$roomIds);
            $stmtAm->execute();
            $amRes = $stmtAm->get_result();
            if ($amRes) {
                $allAm = $amRes->fetch_all(MYSQLI_ASSOC);
                $grouped = [];
                foreach ($allAm as $a) {
                    $grouped[$a['room_id']][] = $a;
                }
                foreach ($rooms as &$room) {
                    $room['amenities'] = $grouped[$room['id']] ?? [];
                }
            } else {
                foreach ($rooms as &$room) {
                    $room['amenities'] = [];
                }
            }
        }

        return $rooms ?: [];
    }

    public function syncAmenitiesForBoardingHouse(int $boardingHouseId, array $amenityIds): bool
    {
        $this->db->begin_transaction();
        try {
            $del = $this->db->prepare('DELETE FROM boarding_house_amenities WHERE boarding_house_id = ?');
            $del->bind_param('i', $boardingHouseId);
            $del->execute();

            if (!empty($amenityIds)) {
                $stmt = $this->db->prepare('INSERT INTO boarding_house_amenities (boarding_house_id, amenity_id) VALUES (?, ?)');
                foreach ($amenityIds as $amenityId) {
                    $aid = (int)$amenityId;
                    if ($aid > 0) {
                        $stmt->bind_param('ii', $boardingHouseId, $aid);
                        $stmt->execute();
                    }
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getAmenitiesForBoardingHouse(int $boardingHouseId): array
    {
        $stmt = $this->db->prepare('
            SELECT a.id, a.name, a.icon
            FROM amenities a
            INNER JOIN boarding_house_amenities bha ON bha.amenity_id = a.id
            WHERE bha.boarding_house_id = ?
            ORDER BY a.name ASC
        ');
        $stmt->bind_param('i', $boardingHouseId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        return $rows ?: [];
    }

    public function getAllAmenities(): array
    {
        $res = $this->db->query('SELECT id, name, icon FROM amenities ORDER BY name ASC');
        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function all(): array
    {
        $res = $this->db->query('
            SELECT bh.*, u.first_name, u.last_name 
            FROM boarding_houses bh
            JOIN users u ON bh.owner_id = u.id
            WHERE bh.is_deleted = 0
            ORDER BY bh.created_at DESC
        ');
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getByOwnerIdWithStats(int $ownerId, ?int $limit = null, ?int $offset = null): array
    {
        $sql = '
            SELECT bh.*,
                (SELECT COUNT(*) FROM rooms r WHERE r.boarding_house_id = bh.id) as total_rooms,
                (SELECT IFNULL(SUM(r.capacity - r.available_slots), 0) FROM rooms r WHERE r.boarding_house_id = bh.id) as utilized_slots,
                (SELECT IFNULL(SUM(r.available_slots), 0) FROM rooms r WHERE r.boarding_house_id = bh.id) as available_slots
            FROM boarding_houses bh
            WHERE bh.owner_id = ? AND bh.is_deleted = 0 
            ORDER BY bh.created_at DESC
        ';
        $params = [$ownerId];
        $types = 'i';

        if ($limit !== null) {
            $sql .= ' LIMIT ?';
            $params[] = $limit;
            $types .= 'i';
            if ($offset !== null) {
                $sql .= ' OFFSET ?';
                $params[] = $offset;
                $types .= 'i';
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getByOwnerId(int $ownerId, ?int $limit = null, ?int $offset = null): array
    {
        $sql = 'SELECT * FROM boarding_houses WHERE owner_id = ? AND is_deleted = 0 ORDER BY created_at DESC';
        $params = [$ownerId];
        $types = 'i';

        if ($limit !== null) {
            $sql .= ' LIMIT ?';
            $params[] = $limit;
            $types .= 'i';
            if ($offset !== null) {
                $sql .= ' OFFSET ?';
                $params[] = $offset;
                $types .= 'i';
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function countByOwnerId(int $ownerId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM boarding_houses WHERE owner_id = ? AND is_deleted = 0');
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_row()[0];
    }

    public function getRoomStatsByOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                COUNT(r.id) as total_rooms,
                SUM(r.capacity - r.available_slots) as occupied_slots,
                SUM(r.available_slots) as available_slots
            FROM rooms r
            JOIN boarding_houses bh ON r.boarding_house_id = bh.id
            WHERE bh.owner_id = ? AND bh.is_deleted = 0
        ');
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: ['total_rooms' => 0, 'occupied_slots' => 0, 'available_slots' => 0];
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE boarding_houses SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
        return $stmt->execute();
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO boarding_houses (owner_id, name, description, address, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?, 'approved')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('isssdd', 
            $data['owner_id'], 
            $data['name'], 
            $data['description'], 
            $data['address'], 
            $data['latitude'], 
            $data['longitude']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE boarding_houses SET name = ?, description = ?, address = ?, latitude = ?, longitude = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssddi', 
            $data['name'], 
            $data['description'], 
            $data['address'], 
            $data['latitude'], 
            $data['longitude'],
            $id
        );
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE boarding_houses SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getImages(int $boardingHouseId): array
    {
        $stmt = $this->db->prepare('SELECT id, image_path, sort_order FROM boarding_house_images WHERE boarding_house_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->bind_param('i', $boardingHouseId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function addImage(int $boardingHouseId, string $imagePath, int $sortOrder = 0): bool
    {
        $stmt = $this->db->prepare('INSERT INTO boarding_house_images (boarding_house_id, image_path, sort_order) VALUES (?, ?, ?)');
        $stmt->bind_param('isi', $boardingHouseId, $imagePath, $sortOrder);
        return $stmt->execute();
    }

    public function deleteImage(int $imageId, int $boardingHouseId): ?string
    {
        $stmt = $this->db->prepare('SELECT image_path FROM boarding_house_images WHERE id = ? AND boarding_house_id = ? LIMIT 1');
        $stmt->bind_param('ii', $imageId, $boardingHouseId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;

        $del = $this->db->prepare('DELETE FROM boarding_house_images WHERE id = ? AND boarding_house_id = ?');
        $del->bind_param('ii', $imageId, $boardingHouseId);
        $del->execute();
        return $row['image_path'];
    }

    public function countImages(int $boardingHouseId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM boarding_house_images WHERE boarding_house_id = ?');
        $stmt->bind_param('i', $boardingHouseId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_row()[0];
    }

    public function getRoomById(int $roomId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rooms WHERE id = ?');
        $stmt->bind_param('i', $roomId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    public function createRoom(array $data): bool
    {
        $sql = "INSERT INTO rooms 
                (boarding_house_id, room_name, price, capacity, available_slots) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'isdii', 
            $data['boarding_house_id'], 
            $data['room_name'], 
            $data['price'], 
            $data['capacity'], 
            $data['available_slots']
        );
        return $stmt->execute();
    }

    public function updateRoom(int $roomId, array $data): bool
    {
        $sql = "UPDATE rooms 
                SET room_name = ?, price = ?, capacity = ?, available_slots = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'sdiii', 
            $data['room_name'], 
            $data['price'], 
            $data['capacity'], 
            $data['available_slots'], 
            $roomId
        );
        return $stmt->execute();
    }

    public function deleteRoom(int $roomId): bool
    {
        // First delete associated amenities
        $delAm = $this->db->prepare('DELETE FROM room_amenities WHERE room_id = ?');
        $delAm->bind_param('i', $roomId);
        $delAm->execute();

        // Then delete the room
        $stmt = $this->db->prepare('DELETE FROM rooms WHERE id = ?');
        $stmt->bind_param('i', $roomId);
        return $stmt->execute();
    }
}

