<?php
declare(strict_types=1);

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Repositories\AuthenticationRepository;

final class AuthenticationService
{
    private AuthenticationRepository $repository;
    public function __construct(AuthenticationRepository $repository) { $this->repository = $repository; }
    public function authenticate(string $identifier, string $password): ?array
    {
        if ($identifier === '' || strlen($identifier) > 100 || $password === '' || strlen($password) > 72 || strpos($password, "\0") !== false) {
            return null;
        }
        return $this->repository->authenticate($identifier, $password, time());
    }
}
