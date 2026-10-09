<?php
declare(strict_types=1);

namespace App\Modules\Identity\Repositories;

final class PasswordResetRepository
{
    private \mysqli $db;
    public function __construct(\mysqli $db) { $this->db = $db; }

    public function findEligibleAccount(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT id, email FROM users WHERE email = ? AND status = 1 AND is_deleted = 0 AND password IS NOT NULL LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function issue(int $userId, string $hash, int $now, int $expires): bool
    {
        $this->db->begin_transaction();
        try {
            $statement = $this->db->prepare('SELECT id FROM users WHERE id = ? AND status = 1 AND is_deleted = 0 FOR UPDATE');
            $statement->bind_param('i', $userId);
            $statement->execute();
            if ($statement->get_result()->num_rows !== 1) {
                $this->db->rollback();
                return false;
            }
            $since = $now - 3600;
            $statement = $this->db->prepare('SELECT COUNT(*) FROM password_resets WHERE user_id = ? AND created_at >= ?');
            $statement->bind_param('ii', $userId, $since);
            $statement->execute();
            if ((int)$statement->get_result()->fetch_row()[0] >= 3) {
                $this->db->rollback();
                return false;
            }
            $statement = $this->db->prepare('UPDATE password_resets SET used_at = ? WHERE user_id = ? AND used_at IS NULL');
            $statement->bind_param('ii', $now, $userId);
            $statement->execute();
            $statement = $this->db->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?)');
            $statement->bind_param('isii', $userId, $hash, $expires, $now);
            $statement->execute();
            $this->db->commit();
            return true;
        } catch (\Throwable $error) {
            $this->db->rollback();
            throw $error;
        }
    }

    public function credentialVersion(int $userId): int
    {
        $statement = $this->db->prepare('SELECT auth_version FROM identity_credentials WHERE user_id = ?');
        $statement->bind_param('i', $userId);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();
        return (int)($row['auth_version'] ?? 0);
    }

    public function consume(string $tokenHash, string $passwordHash, int $now): bool
    {
        // Read the user ID before taking locks; consistently lock user then token to avoid inversions with issue().
        $statement = $this->db->prepare('SELECT user_id FROM password_resets WHERE token_hash = ?');
        $statement->bind_param('s', $tokenHash);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();
        if (!$row) {
            return false;
        }
        $userId = (int)$row['user_id'];
        $this->db->begin_transaction();
        try {
            $statement = $this->db->prepare('SELECT id FROM users WHERE id = ? AND status = 1 AND is_deleted = 0 FOR UPDATE');
            $statement->bind_param('i', $userId);
            $statement->execute();
            if ($statement->get_result()->num_rows !== 1) {
                $this->db->rollback();
                return false;
            }
            $statement = $this->db->prepare('SELECT id FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > ? FOR UPDATE');
            $statement->bind_param('si', $tokenHash, $now);
            $statement->execute();
            if ($statement->get_result()->num_rows !== 1) {
                $this->db->rollback();
                return false;
            }
            $statement = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $statement->bind_param('si', $passwordHash, $userId);
            $statement->execute();
            $statement = $this->db->prepare('UPDATE password_resets SET used_at = ? WHERE user_id = ? AND used_at IS NULL');
            $statement->bind_param('ii', $now, $userId);
            $statement->execute();
            $statement = $this->db->prepare('INSERT INTO identity_credentials (user_id, auth_version) VALUES (?, 1) ON DUPLICATE KEY UPDATE auth_version = auth_version + 1');
            $statement->bind_param('i', $userId);
            $statement->execute();
            $this->db->commit();
            return true;
        } catch (\Throwable $error) {
            $this->db->rollback();
            throw $error;
        }
    }
}
