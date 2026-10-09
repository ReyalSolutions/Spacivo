<?php
declare(strict_types=1);

namespace App\Modules\Properties\Repositories;

final class PropertyRepository
{
    private \mysqli $db;
    public function __construct(\mysqli $db) { $this->db = $db; }
    private function query(string $sql, string $types = '', array $parameters = []): \mysqli_stmt
    {
        $statement = $this->db->prepare($sql);
        if ($types !== '') { $statement->bind_param($types, ...$parameters); }
        $statement->execute(); return $statement;
    }
    public function transaction(callable $operation)
    {
        $this->db->begin_transaction();
        try { $result = $operation(); $this->db->commit(); return $result; }
        catch (\Throwable $error) { $this->db->rollback(); throw $error; }
    }
    public function lockOrganization(int $organization): void
    {
        $this->query('SELECT id FROM organizations WHERE id = ? FOR UPDATE', 'i', [$organization]);
    }
    public function activeCategory(int $category): bool
    {
        return $this->query('SELECT id FROM space_categories WHERE id = ? AND active = 1 LOCK IN SHARE MODE', 'i', [$category])->get_result()->num_rows === 1;
    }
    public function list(int $organization): array
    {
        return $this->query('SELECT * FROM properties WHERE organization_id = ? ORDER BY id DESC', 'i', [$organization])->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function find(int $organization, int $id, bool $lock = false): ?array
    {
        return $this->query('SELECT * FROM properties WHERE organization_id = ? AND id = ?' . ($lock ? ' FOR UPDATE' : ''), 'ii', [$organization, $id])->get_result()->fetch_assoc() ?: null;
    }
    public function units(int $organization, int $property): array
    {
        return $this->query('SELECT id, property_id, category_id, name, capacity, state, version FROM rental_units WHERE organization_id = ? AND property_id = ? ORDER BY id', 'ii', [$organization, $property])->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function save(int $organization, int $id, int $version, array $data): int
    {
        $parameters = [$data['category_id'], $data['name'], $data['description'], $data['address'], $data['timezone'], $data['latitude'], $data['longitude']];
        if ($id === 0) {
            $this->query('INSERT INTO properties (category_id, name, description, address, timezone, latitude, longitude, organization_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', 'issssddi', array_merge($parameters, [$organization]));
            return (int)$this->db->insert_id;
        }
        $statement = $this->query("UPDATE properties SET category_id = ?, name = ?, description = ?, address = ?, timezone = ?, latitude = ?, longitude = ?, state = 'draft', approval_status = 'pending', reviewed_by = NULL, reviewed_at = NULL, version = version + 1 WHERE organization_id = ? AND id = ? AND version = ?", 'issssddiii', array_merge($parameters, [$organization, $id, $version]));
        $this->changed($statement); return $id;
    }
    public function saveUnit(int $organization, int $property, int $id, int $version, array $data): int
    {
        if ($id === 0) {
            $this->query('INSERT INTO rental_units (organization_id, property_id, category_id, name, capacity) VALUES (?, ?, ?, ?, ?)', 'iiisi', [$organization, $property, $data['category_id'], $data['name'], $data['capacity']]);
            return (int)$this->db->insert_id;
        }
        $statement = $this->query("UPDATE rental_units SET category_id = ?, name = ?, capacity = ?, version = version + 1 WHERE organization_id = ? AND property_id = ? AND id = ? AND version = ? AND state = 'active'", 'isiiiii', [$data['category_id'], $data['name'], $data['capacity'], $organization, $property, $id, $version]);
        $this->changed($statement); return $id;
    }
    public function invalidateApproval(int $organization, int $property): void
    {
        $this->query("UPDATE properties SET state = 'draft', approval_status = 'pending', reviewed_by = NULL, reviewed_at = NULL, version = version + 1 WHERE organization_id = ? AND id = ?", 'ii', [$organization, $property]);
    }
    public function archiveUnit(int $organization, int $property, int $id, int $version): void
    {
        $statement = $this->query("UPDATE rental_units SET state = 'archived', version = version + 1 WHERE organization_id = ? AND property_id = ? AND id = ? AND version = ? AND state = 'active'", 'iiii', [$organization, $property, $id, $version]);
        $this->changed($statement);
    }
    public function state(int $organization, int $id, int $version, string $state): void
    {
        $statement = $this->query('UPDATE properties SET state = ?, version = version + 1 WHERE organization_id = ? AND id = ? AND version = ?', 'siii', [$state, $organization, $id, $version]);
        $this->changed($statement);
    }
    public function review(int $organization, int $id, int $version, string $decision, int $actor): void
    {
        $statement = $this->query("UPDATE properties SET approval_status = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, state = 'draft', version = version + 1 WHERE organization_id = ? AND id = ? AND version = ? AND state = 'draft'", 'siiii', [$decision, $actor, $organization, $id, $version]);
        $this->changed($statement);
    }
    private function changed(\mysqli_stmt $statement): void
    {
        if ($statement->affected_rows !== 1) { throw new \InvalidArgumentException('The listing changed or is unavailable. Refresh before saving.'); }
    }
    public function pendingReviews(): array
    {
        return $this->query("SELECT p.* FROM properties p JOIN organizations o ON o.id = p.organization_id WHERE p.state = 'draft' AND p.approval_status = 'pending' AND o.status = 'active' ORDER BY p.id")->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function administrativeListings(): array
    {
        return $this->query("SELECT p.* FROM properties p WHERE p.state <> 'archived' ORDER BY p.id DESC LIMIT 100")->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function moderate(int $organization, int $property, int $version, string $state): void
    {
        $statement = $this->query("UPDATE properties SET state = ?, approval_status = 'pending', reviewed_by = NULL, reviewed_at = NULL, version = version + 1 WHERE organization_id = ? AND id = ? AND version = ? AND state <> 'archived'", 'siii', [$state, $organization, $property, $version]);
        $this->changed($statement);
    }
    public function publicListings(): array
    {
        return $this->query("SELECT p.id, p.category_id, p.name, p.description, p.address, p.timezone, p.latitude, p.longitude
            FROM properties p JOIN organizations o ON o.id = p.organization_id JOIN space_categories c ON c.id = p.category_id
            JOIN users u ON u.id = o.owner_user_id
            WHERE p.state = 'published' AND p.approval_status = 'approved' AND o.status = 'active'
                AND o.verification_status = 'verified' AND c.active = 1 AND u.status = 1 AND u.is_deleted = 0
                AND EXISTS (SELECT 1 FROM rental_units r JOIN space_categories rc ON rc.id = r.category_id
                    WHERE r.organization_id = p.organization_id AND r.property_id = p.id AND r.state = 'active' AND rc.active = 1)
            ORDER BY p.id DESC LIMIT 100")->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function publicFind(int $id): ?array
    {
        return $this->query("SELECT p.id, p.organization_id FROM properties p
            JOIN organizations o ON o.id = p.organization_id JOIN space_categories c ON c.id = p.category_id
            JOIN users u ON u.id = o.owner_user_id
            WHERE p.id = ? AND p.state = 'published' AND p.approval_status = 'approved'
                AND o.status = 'active' AND o.verification_status = 'verified' AND c.active = 1
                AND u.status = 1 AND u.is_deleted = 0
                AND EXISTS (SELECT 1 FROM rental_units r JOIN space_categories rc ON rc.id = r.category_id
                    WHERE r.organization_id = p.organization_id AND r.property_id = p.id AND r.state = 'active' AND rc.active = 1)", 'i', [$id])->get_result()->fetch_assoc() ?: null;
    }
}
