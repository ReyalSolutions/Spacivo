<?php
declare(strict_types=1);

namespace App\Modules\Identity\Repositories;

final class AuthenticationRepository
{
    private \mysqli $db;
    public function __construct(\mysqli $db) { $this->db = $db; }

    public function authenticate(string $identifier, string $password, int $now): ?array
    {
        $this->db->begin_transaction();
        try {
            $user = null;
            foreach (['username', 'email'] as $field) {
                $statement = $this->db->prepare('SELECT id, username, role_id, status, first_name, middle_name, last_name, email, password, phone, image, created_at, failed_attempts, lockout_until FROM users WHERE ' . $field . ' = ? AND is_deleted = 0 LIMIT 1 FOR UPDATE');
                $statement->bind_param('s', $identifier);
                $statement->execute();
                $user = $statement->get_result()->fetch_assoc();
                if ($user) { break; }
            }
            if (!$user || (int)$user['status'] !== 1 || empty($user['password'])) {
                $this->db->rollback();
                return null;
            }
            $nowString = gmdate('Y-m-d H:i:s', $now);
            if (!empty($user['lockout_until']) && $user['lockout_until'] > $nowString) {
                $this->db->rollback();
                return null;
            }
            $id = (int)$user['id'];
            if (!password_verify($password, $user['password'])) {
                $attempts = !empty($user['lockout_until']) ? 1 : (int)$user['failed_attempts'] + 1;
                $until = $attempts >= 5 ? gmdate('Y-m-d H:i:s', $now + 900) : null;
                $statement = $this->db->prepare('UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE id = ?');
                $statement->bind_param('isi', $attempts, $until, $id);
                $statement->execute();
                $this->db->commit();
                return null;
            }
            $statement = $this->db->prepare('SELECT name, slug FROM roles WHERE id = ?');
            $statement->bind_param('i', $user['role_id']);
            $statement->execute();
            $role = $statement->get_result()->fetch_assoc();
            if (!$role) {
                $this->db->rollback();
                return null;
            }
            $statement = $this->db->prepare('UPDATE users SET failed_attempts = 0, lockout_until = NULL, last_login = ? WHERE id = ?');
            $statement->bind_param('si', $nowString, $id);
            $statement->execute();
            $this->db->commit();
            unset($user['password'], $user['failed_attempts'], $user['lockout_until']);
            $user['role_slug'] = $role['slug'];
            $user['role_name'] = $role['name'];
            return $user;
        } catch (\Throwable $error) {
            $this->db->rollback();
            throw $error;
        }
    }
}
