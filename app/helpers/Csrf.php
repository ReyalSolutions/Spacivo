<?php
declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }

    public static function verify($token): bool
    {
        if (empty($_SESSION['csrf_token']) || !is_string($token) || $token === '') {
            return false;
        }
        return hash_equals((string)$_SESSION['csrf_token'], $token);
    }
}

