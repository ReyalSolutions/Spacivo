<?php
declare(strict_types=1);

$metadataRepository = new App\Modules\Properties\Repositories\PropertyMetadataRepository($server);
$metadataService = new App\Modules\Properties\Services\PropertyMetadataService($inventoryRepo, $metadataRepository, $inventoryOrganizations);
$metadataProperty = $inventoryService->save($inventoryOwner, $inventoryOrgId, 0, 0, $propertyInput);
$metadataUnit = $inventoryService->saveUnit($inventoryOwner, $inventoryOrgId, $metadataProperty, 0, 0, $unitInput);
$metadataVersion = static function () use ($inventoryService, $inventoryOwner, $inventoryOrgId, $metadataProperty): int {
    return (int)$inventoryService->show($inventoryOwner, $inventoryOrgId, $metadataProperty)['version'];
};
(new Amenity($server))->create(['name' => 'Fixture Wi-Fi', 'icon' => 'wifi']);
$metadataAmenity = (int)$server->insert_id;
$metadataService->setAmenities($inventoryOwner, $inventoryOrgId, $metadataProperty, 0, $metadataVersion(), [$metadataAmenity, $metadataAmenity]);
$check(count($metadataService->details($inventoryOwner, $inventoryOrgId, $metadataProperty)['amenities']) === 1, 'Property amenities deduplicated and scoped');
$beforeInvalidMetadata = $metadataVersion();
$categoryInvalid(static function () use ($metadataService, $inventoryOwner, $inventoryOrgId, $metadataProperty, $beforeInvalidMetadata): void {
    $metadataService->setAmenities($inventoryOwner, $inventoryOrgId, $metadataProperty, 0, $beforeInvalidMetadata, [99999999]);
}, 'Unknown amenity rejected');
$check($metadataVersion() === $beforeInvalidMetadata && count($metadataService->details($inventoryOwner, $inventoryOrgId, $metadataProperty)['amenities']) === 1, 'Failed amenity replacement preserves existing collection and version');
$metadataService->setAmenities($inventoryOwner, $inventoryOrgId, $metadataProperty, $metadataUnit, $metadataVersion(), [$metadataAmenity]);
$check(count($metadataService->details($inventoryOwner, $inventoryOrgId, $metadataProperty, $metadataUnit)['amenities']) === 1, 'Unit amenities stay within property and organization');
$metadataPhoto = $metadataService->attachPhoto($inventoryOwner, $inventoryOrgId, $metadataProperty, 0, $metadataVersion(), str_repeat('a', 32) . '.png');
$denied(static function () use ($metadataService, $inventoryOther, $inventoryOrgId, $metadataProperty): void { $metadataService->details($inventoryOther, $inventoryOrgId, $metadataProperty); }, 'Foreign owner cannot inspect listing photos or amenities');
$inventoryOrgService->addMember($inventoryOwner, $inventoryOrgId, $inventoryStaff, 'staff', ['inventory.read']);
$check(count($metadataService->details($inventoryStaff, $inventoryOrgId, $metadataProperty)['photos']) === 1, 'Read-only staff may preview scoped photos');
$denied(static function () use ($metadataService, $inventoryStaff, $inventoryOrgId, $metadataProperty, $metadataPhoto, $metadataVersion): void {
    $metadataService->deletePhoto($inventoryStaff, $inventoryOrgId, $metadataProperty, 0, $metadataVersion(), $metadataPhoto);
}, 'Read-only staff cannot remove photos');
$check(count($metadataService->details($inventoryAdmin, $inventoryOrgId, $metadataProperty, 0, true)['photos']) === 1, 'Platform reviewer can inspect listing photos through explicit review authority');
$denied(static function () use ($metadataService, $metadataProperty, $metadataPhoto): void { $metadataService->publicPhoto($metadataProperty, 0, $metadataPhoto); }, 'Draft listing photos are not public');
$inventoryService->review($inventoryAdmin, $inventoryOrgId, $metadataProperty, $metadataVersion(), 'approved');
$inventoryService->setState($inventoryOwner, $inventoryOrgId, $metadataProperty, $metadataVersion(), 'published');
$check($metadataService->publicPhoto($metadataProperty, 0, $metadataPhoto) === str_repeat('a', 32) . '.png', 'Approved published listing permits public photo reference');
$metadataService->setAmenities($inventoryOwner, $inventoryOrgId, $metadataProperty, 0, $metadataVersion(), []);
$check($inventoryService->show($inventoryOwner, $inventoryOrgId, $metadataProperty)['state'] === 'draft', 'Changing amenities withdraws published listing for review');
$denied(static function () use ($metadataService, $metadataProperty, $metadataPhoto): void { $metadataService->publicPhoto($metadataProperty, 0, $metadataPhoto); }, 'Photo access follows listing withdrawal immediately');
$filename = $metadataService->deletePhoto($inventoryOwner, $inventoryOrgId, $metadataProperty, 0, $metadataVersion(), $metadataPhoto);
$check($filename === str_repeat('a', 32) . '.png' && $metadataService->details($inventoryOwner, $inventoryOrgId, $metadataProperty)['photos'] === [], 'Scoped photo removal returns only its stored file reference');
try {
    $statement = $server->prepare('INSERT INTO unit_media (organization_id, property_id, unit_id, filename) VALUES (?, ?, ?, ?)');
    $badFilename = str_repeat('b', 32) . '.png';
    $statement->bind_param('iiis', $inventoryOtherOrgId, $metadataProperty, $metadataUnit, $badFilename);
    $statement->execute(); $check(false, 'Database rejects cross-organization unit media');
} catch (mysqli_sql_exception $error) { $check((int)$error->getCode() === 1452, 'Database rejects cross-organization unit media'); }
$categoryInvalid(static function (): void {
    (new App\Integrations\Storage\InventoryImageStorage(sys_get_temp_dir()))->path('../outside.png');
}, 'Image storage rejects path traversal');
