<?php
declare(strict_types=1);

namespace App\Modules\Identity\Services;

interface PasswordResetDelivery
{
    public function queue(string $email, string $resetUrl): void;
}
