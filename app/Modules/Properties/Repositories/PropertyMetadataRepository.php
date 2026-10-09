<?php
declare(strict_types=1);

namespace App\Modules\Properties\Repositories;

final class PropertyMetadataRepository
{
    private \mysqli $db;
    public function __construct(\mysqli $db) { $this->db = $db; }
    private function query(string $sql, string $types, array $parameters): \mysqli_stmt
    { $statement = $this->db->prepare($sql); $statement->bind_param($types, ...$parameters); $statement->execute(); return $statement; }
    private function scope(int $organization, int $property, int $unit): array
    { return $unit > 0 ? [$organization, $property, $unit] : [$organization, $property]; }
    private function where(int $unit): string
    { return 'organization_id = ? AND property_id = ?' . ($unit > 0 ? ' AND unit_id = ?' : ''); }
    public function photos(int $organization, int $property, int $unit = 0): array
    {
        $table = $unit > 0 ? 'unit_media' : 'property_media';
        return $this->query("SELECT id, filename, created_at FROM {$table} WHERE " . $this->where($unit) . ' ORDER BY id', $unit > 0 ? 'iii' : 'ii', $this->scope($organization, $property, $unit))->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function photo(int $organization, int $property, int $unit, int $photo): ?array
    {
        $table = $unit > 0 ? 'unit_media' : 'property_media';
        return $this->query("SELECT id, filename FROM {$table} WHERE " . $this->where($unit) . ' AND id = ?', $unit > 0 ? 'iiii' : 'iii', array_merge($this->scope($organization, $property, $unit), [$photo]))->get_result()->fetch_assoc() ?: null;
    }
    public function addPhoto(int $organization, int $property, int $unit, string $filename): int
    {
        $table = $unit > 0 ? 'unit_media' : 'property_media';
        $columns = $unit > 0 ? 'organization_id, property_id, unit_id, filename' : 'organization_id, property_id, filename';
        $this->query("INSERT INTO {$table} ({$columns}) VALUES (" . ($unit > 0 ? '?, ?, ?, ?' : '?, ?, ?') . ')', $unit > 0 ? 'iiis' : 'iis', array_merge($this->scope($organization, $property, $unit), [$filename]));
        return (int)$this->db->insert_id;
    }
    public function deletePhoto(int $organization, int $property, int $unit, int $photo): void
    {
        $table = $unit > 0 ? 'unit_media' : 'property_media';
        $this->query("DELETE FROM {$table} WHERE " . $this->where($unit) . ' AND id = ?', $unit > 0 ? 'iiii' : 'iii', array_merge($this->scope($organization, $property, $unit), [$photo]));
    }
    public function amenities(int $organization, int $property, int $unit = 0): array
    {
        $table = $unit > 0 ? 'unit_amenities' : 'property_amenities';
        $scope = 'm.organization_id = ? AND m.property_id = ?' . ($unit > 0 ? ' AND m.unit_id = ?' : '');
        return $this->query("SELECT a.id, a.name FROM {$table} m JOIN amenities a ON a.id = m.amenity_id WHERE {$scope} ORDER BY a.name", $unit > 0 ? 'iii' : 'ii', $this->scope($organization, $property, $unit))->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function replaceAmenities(int $organization, int $property, int $unit, array $amenities): void
    {
        $table = $unit > 0 ? 'unit_amenities' : 'property_amenities';
        foreach ($amenities as $amenity) {
            if ($this->query('SELECT id FROM amenities WHERE id = ?', 'i', [$amenity])->get_result()->num_rows !== 1) {
                throw new \InvalidArgumentException('Choose existing amenities.');
            }
        }
        $this->query("DELETE FROM {$table} WHERE " . $this->where($unit), $unit > 0 ? 'iii' : 'ii', $this->scope($organization, $property, $unit));
        $columns = $unit > 0 ? 'organization_id, property_id, unit_id, amenity_id' : 'organization_id, property_id, amenity_id';
        foreach ($amenities as $amenity) {
            $this->query("INSERT INTO {$table} ({$columns}) VALUES (" . ($unit > 0 ? '?, ?, ?, ?' : '?, ?, ?') . ')', $unit > 0 ? 'iiii' : 'iii', array_merge($this->scope($organization, $property, $unit), [$amenity]));
        }
    }
}
