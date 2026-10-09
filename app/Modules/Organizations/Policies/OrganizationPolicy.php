<?php
declare(strict_types=1);

namespace App\Modules\Organizations\Policies;

use App\Modules\Organizations\Repositories\OrganizationRepository;
use App\Shared\Exceptions\AuthorizationException;

final class OrganizationPolicy
{
    public const PERMISSIONS = ['organization.read', 'organization.members.manage'];
    private OrganizationRepository $repository;

    public function __construct(OrganizationRepository $repository) { $this->repository = $repository; }

    public function authorize(int $actorId, int $organizationId, string $permission): void
    {
        if (!in_array($permission, self::PERMISSIONS, true)) {
            throw new AuthorizationException('Forbidden');
        }
        $member = $this->repository->activeMembership($organizationId, $actorId);
        if ($member === null) {
            throw new AuthorizationException('Forbidden');
        }
        if ($member['role'] === 'owner' && (int)$member['owner_user_id'] === $actorId) {
            return;
        }
        if (!$this->repository->permissionGranted($organizationId, $actorId, $permission)) {
            throw new AuthorizationException('Forbidden');
        }
    }
}
