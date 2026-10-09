<?php
declare(strict_types=1);

namespace App\Modules\Categories\Repositories;

final class CategoryRepository
{
    private \mysqli $db;
    public function __construct(\mysqli $db) { $this->db = $db; }

    public function all(bool $includeInactive = false): array
    {
        $statement = $this->db->prepare('SELECT id, name, slug, active, version FROM space_categories'
            . ($includeInactive ? '' : ' WHERE active = 1') . ' ORDER BY name, id');
        $statement->execute();
        $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $capabilities = $this->db->prepare('SELECT capability_slug, enabled FROM category_capabilities WHERE category_id = ?');
        foreach ($rows as &$row) {
            $row['active'] = (bool)$row['active'];
            $row['capabilities'] = [];
            $capabilities->bind_param('i', $row['id']);
            $capabilities->execute();
            foreach ($capabilities->get_result()->fetch_all(MYSQLI_ASSOC) as $capability) {
                $row['capabilities'][$capability['capability_slug']] = (bool)$capability['enabled'];
            }
        }
        unset($row);
        return $rows;
    }

    public function save(int $actor, int $id, int $version, array $configuration): int
    {
        $this->db->begin_transaction();
        try {
            // Serialize with account changes and re-check persisted administrator authorization.
            $actorQuery = $this->db->prepare('SELECT r.slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.status = 1 AND u.is_deleted = 0 FOR UPDATE');
            $actorQuery->bind_param('i', $actor);
            $actorQuery->execute();
            if (($actorQuery->get_result()->fetch_assoc()['slug'] ?? null) !== 'admin') {
                throw new \App\Shared\Exceptions\AuthorizationException('Forbidden');
            }
            $name = $configuration['name']; $slug = $configuration['slug']; $active = (int)$configuration['active'];
            if ($id === 0) {
                $statement = $this->db->prepare('INSERT INTO space_categories (name, slug, active) VALUES (?, ?, ?)');
                $statement->bind_param('ssi', $name, $slug, $active);
                $statement->execute();
                $id = (int)$this->db->insert_id;
            } else {
                $statement = $this->db->prepare('UPDATE space_categories SET name = ?, slug = ?, active = ?, version = version + 1 WHERE id = ? AND version = ?');
                $statement->bind_param('ssiii', $name, $slug, $active, $id, $version);
                $statement->execute();
                if ($statement->affected_rows !== 1) {
                    throw new \InvalidArgumentException('The category changed. Refresh before saving.');
                }
            }
            $statement = $this->db->prepare('INSERT INTO category_capabilities (category_id, capability_slug, enabled) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE enabled = VALUES(enabled)');
            foreach ($configuration['capabilities'] as $capability => $value) {
                $enabled = (int)$value;
                $statement->bind_param('isi', $id, $capability, $enabled);
                $statement->execute();
            }
            $this->db->commit();
            return $id;
        } catch (\Throwable $error) {
            $this->db->rollback();
            if ($error instanceof \mysqli_sql_exception && (int)$error->getCode() === 1062) {
                throw new \InvalidArgumentException('That category code is already in use.');
            }
            throw $error;
        }
    }
}
