<?php
declare(strict_types=1);

namespace App\Modules\Identity\Services;

final class RegistrationPolicy
{
    public static function publicRoleAllowed(?array $role): bool
    {
        return $role !== null && in_array(strtolower($role['slug'] ?? ''), ['owner', 'tenant'], true);
    }

    public static function passwordFromInput($value): string
    {
        // Passwords are opaque secrets; HTML escaping and trimming change their meaning.
        return is_string($value) ? $value : '';
    }

    public static function validNewPassword(string $password): bool
    {
        return strlen($password) >= 8 && strlen($password) <= 72 && strpos($password, "\0") === false;
    }
}
