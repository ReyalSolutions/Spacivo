<?php
declare(strict_types=1);

// Included only inside the disposable database harness.
$users = new User($server);
$owner = (int)$users->findByUsername('fixture_owner')['id'];
$tenant = (int)$users->findByUsername('fixture_tenant')['id'];
$administrator = (int)$users->findByUsername('fixture_admin')['id'];
$otherOwner = $users->create('Other', null, 'Owner', 'other-owner@example.test', '09990000002', 2, 'other_owner', 'FixturePassword123');
$repository = new App\Modules\Organizations\Repositories\OrganizationRepository($server);
$service = new App\Modules\Organizations\Services\OrganizationService($repository);
$organizationA = $service->onboard($owner, 'Organization A');
$organizationB = $service->onboard($otherOwner, 'Organization B');
$organizationAId = (int)$organizationA['id'];
$organizationBId = (int)$organizationB['id'];
$check($organizationA['verification_status'] === 'pending', 'New owners require verification');
$check($service->show($owner, $organizationAId)['name'] === 'Organization A', 'Owner reads own organization');
$workspace = $service->workspace($owner);
$visibleIds = array_map('intval', array_column($workspace['organizations'], 'id'));
$check(in_array($organizationAId, $visibleIds, true) && !in_array($organizationBId, $visibleIds, true), 'Workspace list respects organization boundary');
$denied = static function (callable $operation, string $label) use ($check): void {
    try {
        $operation();
        $check(false, $label);
    } catch (App\Shared\Exceptions\AuthorizationException $error) {
        $check(true, $label);
    }
};
$denied(static function () use ($service, $owner, $organizationBId): void { $service->show($owner, $organizationBId); }, 'Cross-organization owner read denied');
$denied(static function () use ($service, $tenant, $organizationAId): void { $service->show($tenant, $organizationAId); }, 'Unassigned account read denied');
$denied(static function () use ($service, $administrator, $organizationAId): void { $service->show($administrator, $organizationAId); }, 'Platform admin does not inherit organization membership');
$denied(static function () use ($service, $tenant): void { $service->onboard($tenant, 'Forbidden'); }, 'Tenant cannot onboard owner organization');
$service->addMember($owner, $organizationAId, $tenant, 'staff', ['organization.read']);
$check($service->show($tenant, $organizationAId)['id'] === $organizationAId, 'Explicit staff read grant works');
$denied(static function () use ($service, $tenant, $organizationBId): void { $service->show($tenant, $organizationBId); }, 'Staff grant does not cross organization boundary');
$denied(static function () use ($service, $tenant, $organizationAId, $otherOwner): void { $service->addMember($tenant, $organizationAId, $otherOwner, 'staff', ['organization.read']); }, 'Read-only staff cannot assign permissions');
$service->addMember($owner, $organizationAId, $tenant, 'manager', ['organization.read', 'organization.members.manage']);
$denied(static function () use ($service, $tenant, $organizationAId, $otherOwner): void { $service->addMember($tenant, $organizationAId, $otherOwner, 'manager', ['organization.read']); }, 'Delegated manager cannot escalate membership grants');
try {
    $service->addMember($owner, $organizationAId, $owner, 'staff', []);
    $check(false, 'Owner membership cannot be overwritten');
} catch (InvalidArgumentException $error) {
    $check(true, 'Owner membership cannot be overwritten');
}
try {
    $service->addMember($owner, $organizationAId, $tenant, 'staff', ['system.admin']);
    $check(false, 'Unknown permission cannot be granted');
} catch (InvalidArgumentException $error) {
    $check(true, 'Unknown permission cannot be granted');
}
$server->query('UPDATE users SET status = 0 WHERE id = ' . $tenant);
$denied(static function () use ($service, $tenant, $organizationAId): void { $service->show($tenant, $organizationAId); }, 'Deactivated user loses organization access');
$server->query('UPDATE users SET status = 1 WHERE id = ' . $tenant);
$server->query("UPDATE organization_members SET status = 'suspended' WHERE organization_id = " . $organizationAId . ' AND user_id = ' . $tenant);
$denied(static function () use ($service, $tenant, $organizationAId): void { $service->show($tenant, $organizationAId); }, 'Suspended member loses organization access');
$server->query("UPDATE organizations SET status = 'suspended' WHERE id = " . $organizationAId);
$denied(static function () use ($service, $owner, $organizationAId): void { $service->show($owner, $organizationAId); }, 'Suspended organization denies owner access');
$server->query("UPDATE organizations SET status = 'active' WHERE id = " . $organizationAId);
$denied(static function () use ($service, $owner, $organizationAId): void { $service->verify($owner, $organizationAId, 'verified'); }, 'Owner cannot self-verify');
$service->verify($administrator, $organizationAId, 'verified');
$check($service->show($owner, $organizationAId)['verification_status'] === 'verified', 'Administrator can verify organization');
$server->query('UPDATE users SET status = 0 WHERE id = ' . $administrator);
$denied(static function () use ($service, $administrator, $organizationBId): void { $service->verify($administrator, $organizationBId, 'verified'); }, 'Deactivated administrator cannot verify organization');
