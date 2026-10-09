<?php
declare(strict_types=1);

$inventoryUsers = new User($server);
$inventoryAdmin = (int)$inventoryUsers->findByUsername('fixture_admin')['id'];
$inventoryOwner = (int)$inventoryUsers->findByUsername('fixture_owner')['id'];
$inventoryOther = (int)$inventoryUsers->findByUsername('other_owner')['id'];
$inventoryStaff = (int)$inventoryUsers->findByUsername('fixture_tenant')['id'];
$server->query('UPDATE users SET status = 1 WHERE id IN (' . $inventoryAdmin . ',' . $inventoryStaff . ')');
$inventoryOrganizations = new App\Modules\Organizations\Repositories\OrganizationRepository($server);
$inventoryOrgService = new App\Modules\Organizations\Services\OrganizationService($inventoryOrganizations);
$inventoryOrg = $inventoryOrgService->onboard($inventoryOwner, 'Inventory Organization');
$inventoryOrgId = (int)$inventoryOrg['id'];
$inventoryOtherOrgId = (int)$inventoryOrgService->onboard($inventoryOther, 'Other Inventory')['id'];
$inventoryRepo = new App\Modules\Properties\Repositories\PropertyRepository($server);
$inventoryService = new App\Modules\Properties\Services\PropertyService($inventoryRepo, $inventoryOrganizations);
$inventoryCategory = (int)$categoryService->publicCategories()[0]['id'];
$propertyInput = ['category_id' => $inventoryCategory, 'name' => 'Court Center', 'description' => 'A covered sports facility.',
    'address' => '123 Test Street', 'timezone' => 'Asia/Singapore', 'latitude' => 1.3, 'longitude' => 103.8];
$propertyId = $inventoryService->save($inventoryOwner, $inventoryOrgId, 0, 0, $propertyInput);
$property = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$check($property['state'] === 'draft' && $property['approval_status'] === 'pending', 'New inventory property starts draft and pending review');
$check($inventoryService->publicListings() === [], 'Draft property is not public');
$denied(static function () use ($inventoryService, $inventoryOther, $inventoryOrgId): void { $inventoryService->list($inventoryOther, $inventoryOrgId); }, 'Foreign owner cannot list organization properties');
$denied(static function () use ($inventoryService, $inventoryOther, $inventoryOtherOrgId, $propertyId): void { $inventoryService->show($inventoryOther, $inventoryOtherOrgId, $propertyId); }, 'Scoped property lookup rejects foreign property ID');
$denied(static function () use ($inventoryService, $inventoryAdmin, $inventoryOrgId): void { $inventoryService->list($inventoryAdmin, $inventoryOrgId); }, 'Platform administrator does not inherit inventory membership');
$unitInput = ['category_id' => $inventoryCategory, 'name' => 'Court A', 'capacity' => 12];
$unitId = $inventoryService->saveUnit($inventoryOwner, $inventoryOrgId, $propertyId, 0, 0, $unitInput);
$property = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$check(count($property['units']) === 1 && (int)$property['version'] === 2, 'Unit creation is scoped and invalidates property review');
$denied(static function () use ($inventoryService, $inventoryOther, $inventoryOtherOrgId, $propertyId, $unitInput): void {
    $inventoryService->saveUnit($inventoryOther, $inventoryOtherOrgId, $propertyId, 0, 0, $unitInput);
}, 'Foreign property cannot receive a unit');
$inventoryOrgService->addMember($inventoryOwner, $inventoryOrgId, $inventoryStaff, 'staff', ['inventory.read']);
$check(count($inventoryService->list($inventoryStaff, $inventoryOrgId)) === 1, 'Explicit inventory read grant works');
$denied(static function () use ($inventoryService, $inventoryStaff, $inventoryOrgId, $propertyInput): void {
    $inventoryService->save($inventoryStaff, $inventoryOrgId, 0, 0, $propertyInput);
}, 'Read-only staff cannot edit inventory');
$inventoryOrgService->addMember($inventoryOwner, $inventoryOrgId, $inventoryStaff, 'manager', ['inventory.manage']);
$check($inventoryService->show($inventoryStaff, $inventoryOrgId, $propertyId)['id'] === $propertyId, 'Inventory manage grant includes read');
$inventoryService->saveUnit($inventoryStaff, $inventoryOrgId, $propertyId, $unitId, 1, array_replace($unitInput, ['name' => 'Court A updated']));
$check($inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId)['units'][0]['name'] === 'Court A updated', 'Delegated inventory manager updates own organization unit');
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId, $propertyInput): void {
    $inventoryService->save($inventoryOwner, $inventoryOrgId, $propertyId, 1, $propertyInput);
}, 'Stale property update rejected');
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyInput): void {
    $inventoryService->save($inventoryOwner, $inventoryOrgId, 0, 0, array_replace($propertyInput, ['latitude' => 91]));
}, 'Out-of-range property location rejected');
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyInput): void {
    $inventoryService->save($inventoryOwner, $inventoryOrgId, 0, 0, array_replace($propertyInput, ['timezone' => 'Invalid/Timezone']));
}, 'Invalid property timezone rejected');
$property = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId, $property): void {
    $inventoryService->setState($inventoryOwner, $inventoryOrgId, $propertyId, (int)$property['version'], 'published');
}, 'Unverified unapproved property cannot publish');
$denied(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId, $property): void {
    $inventoryService->review($inventoryOwner, $inventoryOrgId, $propertyId, (int)$property['version'], 'approved');
}, 'Owner cannot approve own listing');
$inventoryService->review($inventoryAdmin, $inventoryOrgId, $propertyId, (int)$property['version'], 'approved');
$property = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$check((int)$property['reviewed_by'] === $inventoryAdmin && $property['reviewed_at'] !== null, 'Listing review records administrator and time');
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId, $property): void {
    $inventoryService->setState($inventoryOwner, $inventoryOrgId, $propertyId, (int)$property['version'], 'published');
}, 'Approved listing still requires organization verification');
$inventoryOrgService->verify($inventoryAdmin, $inventoryOrgId, 'verified');
$inventoryService->setState($inventoryOwner, $inventoryOrgId, $propertyId, (int)$property['version'], 'published');
$publicProperty = $inventoryService->publicListings()[0];
$check($publicProperty['id'] === $propertyId && !isset($publicProperty['organization_id']) && !isset($publicProperty['owner_user_id']), 'Verified approved property publishes without private owner fields');
$categoryService->save($inventoryAdmin, $inventoryCategory, 2, 'Courts', 'courts', false, $hourlyCapabilities);
$check($inventoryService->publicListings() === [], 'Inactive category removes published inventory');
$categoryService->save($inventoryAdmin, $inventoryCategory, 3, 'Courts', 'courts', true, $hourlyCapabilities);
$server->query('UPDATE users SET status = 0 WHERE id = ' . $inventoryOwner);
$check($inventoryService->publicListings() === [], 'Inactive organization owner removes published inventory');
$server->query('UPDATE users SET status = 1 WHERE id = ' . $inventoryOwner);
$denied(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId): void {
    $inventoryService->moderate($inventoryOwner, $inventoryOrgId, $propertyId, 5, 'suspended');
}, 'Owner cannot invoke platform listing moderation');
$inventoryCurrent = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$inventoryService->moderate($inventoryAdmin, $inventoryOrgId, $propertyId, (int)$inventoryCurrent['version'], 'suspended');
$check($inventoryService->publicListings() === [], 'Administrator suspension removes published listing');
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId): void {
    $inventoryService->setState($inventoryOwner, $inventoryOrgId, $propertyId, 6, 'published');
}, 'Owner cannot bypass listing suspension');
$inventoryCurrent = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$inventoryService->moderate($inventoryAdmin, $inventoryOrgId, $propertyId, (int)$inventoryCurrent['version'], 'draft');
$inventoryCurrent = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$check($inventoryCurrent['approval_status'] === 'pending', 'Restored suspended listing requires a new review');
$inventoryService->review($inventoryAdmin, $inventoryOrgId, $propertyId, (int)$inventoryCurrent['version'], 'approved');
$inventoryCurrent = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$inventoryService->setState($inventoryOwner, $inventoryOrgId, $propertyId, (int)$inventoryCurrent['version'], 'published');
$server->query("UPDATE organizations SET status = 'suspended' WHERE id = " . $inventoryOrgId);
$check($inventoryService->publicListings() === [], 'Suspended organization removes published inventory');
$denied(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId): void { $inventoryService->list($inventoryOwner, $inventoryOrgId); }, 'Suspended organization rejects inventory access');
$server->query("UPDATE organizations SET status = 'active' WHERE id = " . $inventoryOrgId);
$inventoryService->saveUnit($inventoryOwner, $inventoryOrgId, $propertyId, $unitId, 2, $unitInput);
$property = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$check($property['state'] === 'draft' && $property['approval_status'] === 'pending' && $inventoryService->publicListings() === [], 'Editing published unit withdraws listing for reapproval');
$inventoryService->archiveUnit($inventoryOwner, $inventoryOrgId, $propertyId, $unitId, 3);
$property = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$check($property['units'][0]['state'] === 'archived', 'Unit deletion archives instead of destroying inventory history');
$inventoryService->review($inventoryAdmin, $inventoryOrgId, $propertyId, (int)$property['version'], 'approved');
$property = $inventoryService->show($inventoryOwner, $inventoryOrgId, $propertyId);
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId, $property): void {
    $inventoryService->setState($inventoryOwner, $inventoryOrgId, $propertyId, (int)$property['version'], 'published');
}, 'Property without an active unit cannot publish');
$inventoryService->setState($inventoryOwner, $inventoryOrgId, $propertyId, (int)$property['version'], 'archived');
$categoryInvalid(static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $propertyId, $propertyInput): void {
    $inventoryService->save($inventoryOwner, $inventoryOrgId, $propertyId, 8, $propertyInput);
}, 'Archived property cannot be silently restored by editing');
