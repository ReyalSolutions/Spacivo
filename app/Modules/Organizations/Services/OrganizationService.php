<?php
declare(strict_types=1);

namespace App\Modules\Organizations\Services;

use App\Modules\Organizations\Repositories\OrganizationRepository;
use App\Modules\Organizations\Policies\OrganizationPolicy;
use App\Shared\Exceptions\AuthorizationException;

final class OrganizationService
{
    private OrganizationRepository $repository;
    private OrganizationPolicy $policy;

    public function __construct(OrganizationRepository $repository)
    {
        $this->repository = $repository;
        $this->policy = new OrganizationPolicy($repository);
    }

    public function onboard(int $actorId, string $name): array
    {
        $actor = $this->repository->activeUser($actorId);
        if ($actor === null || $actor['platform_role'] !== 'owner') {
            throw new AuthorizationException('Forbidden');
        }
        $name = trim($name);
        if ($name === '' || strlen($name) > 150) {
            throw new \InvalidArgumentException('Organization name must contain 1 to 150 bytes.');
        }
        return $this->repository->transaction(function () use ($actorId, $name): array {
            $id = $this->repository->create($actorId, $name);
            $this->repository->saveMember($id, $actorId, 'owner', []);
            return $this->repository->find($id);
        });
    }

    public function workspace(int $actorId): array
    {
        $actor = $this->repository->activeUser($actorId);
        if ($actor === null) {
            throw new AuthorizationException('Forbidden');
        }
        return [
            'platform_role' => $actor['platform_role'],
            'organizations' => $this->repository->listForMember($actorId),
            'pending_verifications' => $actor['platform_role'] === 'admin' ? $this->repository->listPendingVerification() : [],
        ];
    }

    public function addMemberByEmail(int $actorId, int $organizationId, string $email, string $role, array $permissions): void
    {
        $this->policy->authorize($actorId, $organizationId, 'organization.members.manage');
        $user = $this->repository->activeUserByEmail(trim($email));
        if ($user === null) {
            throw new \InvalidArgumentException('An active account with that email is required.');
        }
        $this->addMember($actorId, $organizationId, (int)$user['id'], $role, $permissions);
    }

    public function show(int $actorId, int $organizationId): array
    {
        $this->policy->authorize($actorId, $organizationId, 'organization.read');
        return $this->repository->find($organizationId);
    }

    public function addMember(int $actorId, int $organizationId, int $userId, string $role, array $permissions): void
    {
        $this->policy->authorize($actorId, $organizationId, 'organization.members.manage');
        $organization = $this->repository->find($organizationId);
        // Only the real owner may delegate access; a staff grant cannot escalate privileges.
        if ($organization === null || (int)$organization['owner_user_id'] !== $actorId) {
            throw new AuthorizationException('Forbidden');
        }
        if ($userId === (int)$organization['owner_user_id'] || !in_array($role, ['manager', 'staff'], true)
            || $this->repository->activeUser($userId) === null
            || array_diff($permissions, OrganizationPolicy::PERMISSIONS) !== []) {
            throw new \InvalidArgumentException('Invalid member or permission assignment.');
        }
        $this->repository->transaction(function () use ($organizationId, $userId, $role, $permissions): void {
            $this->repository->saveMember($organizationId, $userId, $role, $permissions);
        });
    }

    public function verify(int $actorId, int $organizationId, string $decision): void
    {
        $actor = $this->repository->activeUser($actorId);
        if ($actor === null || $actor['platform_role'] !== 'admin') {
            throw new AuthorizationException('Forbidden');
        }
        if (!in_array($decision, ['verified', 'rejected'], true) || $this->repository->find($organizationId) === null) {
            throw new \InvalidArgumentException('Invalid verification decision or organization.');
        }
        $this->repository->setVerification($organizationId, $decision);
    }
}
