<?php
declare(strict_types=1);

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Repositories\PasswordResetRepository;

final class SessionGuard
{
    public static function recordVersion(\mysqli $db, int $userId): void
    {
        if (getenv('PASSWORD_RECOVERY_ENABLED') === 'true') {
            $_SESSION['auth_version'] = (new PasswordResetRepository($db))->credentialVersion($userId);
        }
    }

    public static function valid(\mysqli $db, int $userId): bool
    {
        return getenv('PASSWORD_RECOVERY_ENABLED') !== 'true'
            || (int)($_SESSION['auth_version'] ?? 0) === (new PasswordResetRepository($db))->credentialVersion($userId);
    }
}
