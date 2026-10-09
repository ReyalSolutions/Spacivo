<?php
declare(strict_types=1);

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Repositories\PasswordResetRepository;

final class PasswordResetService
{
    private PasswordResetRepository $repository;
    private PasswordResetDelivery $delivery;
    private string $applicationUrl;
    private $clock;

    public function __construct(PasswordResetRepository $repository, PasswordResetDelivery $delivery, string $applicationUrl, ?callable $clock = null)
    {
        $this->repository = $repository;
        $this->delivery = $delivery;
        $this->applicationUrl = rtrim($applicationUrl, '/');
        $this->clock = $clock ?? static function (): int { return time(); };
    }

    public function request(string $email): void
    {
        $account = $this->repository->findEligibleAccount(trim($email));
        if ($account === null) {
            return;
        }
        $token = bin2hex(random_bytes(32));
        $now = ($this->clock)();
        if ($this->repository->issue((int)$account['id'], hash('sha256', $token), $now, $now + 1800)) {
            $this->delivery->queue($account['email'], $this->applicationUrl . '/?url=auth/reset_password&token=' . $token);
        }
    }

    public function reset(string $token, string $password): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token) || !RegistrationPolicy::validNewPassword($password)) {
            return false;
        }
        return $this->repository->consume(hash('sha256', $token), password_hash($password, PASSWORD_BCRYPT), ($this->clock)());
    }
}
