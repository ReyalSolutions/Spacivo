<?php
declare(strict_types=1);

namespace App\Core;

final class MigrationRunner
{
    private \mysqli $db;
    private string $directory;

    public function __construct(\mysqli $db, string $directory)
    {
        $this->db = $db;
        $this->directory = $directory;
    }

    public function migrate(): array
    {
        $lock = 'spacivo:migrations:' . substr(hash('sha256', (string)$this->db->query('SELECT DATABASE()')->fetch_row()[0]), 0, 40);
        $stmt = $this->db->prepare('SELECT GET_LOCK(?, 10)');
        $stmt->bind_param('s', $lock);
        $stmt->execute();
        if ((int)$stmt->get_result()->fetch_row()[0] !== 1) {
            throw new \RuntimeException('Another migration process holds the lock.');
        }
        try {
            $this->db->query('CREATE TABLE IF NOT EXISTS schema_migrations (
                version VARCHAR(190) PRIMARY KEY,
                checksum CHAR(64) NOT NULL,
                applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB');
            $applied = [];
            $result = $this->db->query('SELECT version, checksum FROM schema_migrations');
            while ($row = $result->fetch_assoc()) {
                $applied[$row['version']] = $row['checksum'];
            }
            $files = glob($this->directory . '/*.php') ?: [];
            sort($files, SORT_STRING);
            $completed = [];
            foreach ($files as $file) {
                $version = basename($file);
                $checksum = hash_file('sha256', $file);
                if (isset($applied[$version])) {
                    if (!hash_equals($applied[$version], $checksum)) {
                        throw new \RuntimeException('Applied migration changed: ' . $version);
                    }
                    continue;
                }
                $migration = require $file;
                if (!is_callable($migration)) {
                    throw new \RuntimeException('Migration must return a callable: ' . $version);
                }
                // MySQL DDL auto-commits; migrations must be restartable after partial failures.
                $migration($this->db);
                $insert = $this->db->prepare('INSERT INTO schema_migrations (version, checksum) VALUES (?, ?)');
                $insert->bind_param('ss', $version, $checksum);
                $insert->execute();
                $completed[] = $version;
            }
            return $completed;
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $release->bind_param('s', $lock);
            $release->execute();
        }
    }

    public function status(): array
    {
        $applied = [];
        if ($this->db->query("SHOW TABLES LIKE 'schema_migrations'")->num_rows > 0) {
            $result = $this->db->query('SELECT version, checksum FROM schema_migrations');
            while ($row = $result->fetch_assoc()) {
                $applied[$row['version']] = $row['checksum'];
            }
        }
        $files = glob($this->directory . '/*.php') ?: [];
        sort($files, SORT_STRING);
        $statuses = [];
        foreach ($files as $file) {
            $version = basename($file);
            $statuses[$version] = !isset($applied[$version]) ? 'pending'
                : (hash_equals($applied[$version], hash_file('sha256', $file)) ? 'applied' : 'modified');
        }
        foreach ($applied as $version => $checksum) {
            if (!isset($statuses[$version])) {
                $statuses[$version] = 'missing';
            }
        }
        return $statuses;
    }
}
