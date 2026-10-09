<?php
declare(strict_types=1);

$categoryActors = new App\Modules\Organizations\Repositories\OrganizationRepository($server);
$categoryRepository = new App\Modules\Categories\Repositories\CategoryRepository($server);
$categoryService = new App\Modules\Categories\Services\CategoryService($categoryRepository, $categoryActors);
$categoryAdmin = (int)(new User($server))->findByUsername('fixture_admin')['id'];
$categoryOwner = (int)(new User($server))->findByUsername('fixture_owner')['id'];
$server->query('UPDATE users SET status = 1 WHERE id = ' . $categoryAdmin);
$hourlyCapabilities = ['hourly_booking' => true, 'calendar_availability' => true, 'time_slot_reservation' => true];
$categoryId = $categoryService->save($categoryAdmin, 0, 0, 'Courts', 'courts', false, $hourlyCapabilities);
$check(count($categoryService->administration($categoryAdmin)) === 1, 'Administrator creates inactive category');
$check($categoryService->publicCategories() === [], 'Inactive categories are not public');
$categoryService->save($categoryAdmin, $categoryId, 1, 'Courts', 'courts', true, $hourlyCapabilities);
$categories = $categoryService->publicCategories();
$check(count($categories) === 1 && $categories[0]['capabilities']['time_slot_reservation'] === true, 'Activated category exposes configured capabilities');
$check((int)$categories[0]['version'] === 2, 'Category configuration increments version');
$categoryInvalid = static function (callable $operation, string $label) use ($check): void {
    try { $operation(); $check(false, $label); }
    catch (InvalidArgumentException $error) { $check(true, $label); }
};
$categoryInvalid(static function () use ($categoryService, $categoryAdmin, $categoryId): void {
    $categoryService->save($categoryAdmin, $categoryId, 1, 'Lost update', 'courts', true, ['monthly_rental' => true]);
}, 'Stale category updates rejected');
$check($categoryService->publicCategories()[0]['name'] === 'Courts', 'Stale update preserves category and capabilities');
$categoryInvalid(static function () use ($categoryService, $categoryAdmin, $hourlyCapabilities): void {
    $categoryService->save($categoryAdmin, 0, 0, 'Duplicate', 'courts', true, $hourlyCapabilities);
}, 'Duplicate category codes rejected');
$check(count($categoryService->administration($categoryAdmin)) === 1, 'Duplicate creation leaves no partial category');
foreach ([
    [true, [], 'Activation without rental mode rejected'],
    [false, ['system_admin' => true], 'Unknown category capability rejected'],
    [false, ['hourly_booking' => 'true'], 'Nonboolean capability rejected'],
    [true, ['monthly_rental' => true, 'time_slot_reservation' => true], 'Time slots without hourly calendar rejected'],
    [true, ['hourly_booking' => true, 'lease_contract' => true], 'Lease without monthly rental rejected'],
] as $categoryCase) {
    $categoryInvalid(static function () use ($categoryService, $categoryAdmin, $categoryCase): void {
        $categoryService->save($categoryAdmin, 0, 0, 'Invalid', 'invalid_category', $categoryCase[0], $categoryCase[1]);
    }, $categoryCase[2]);
}
$denied(static function () use ($categoryService, $categoryOwner): void { $categoryService->administration($categoryOwner); }, 'Owner cannot administer categories');
$denied(static function () use ($categoryService, $categoryOwner, $hourlyCapabilities): void {
    $categoryService->save($categoryOwner, 0, 0, 'Forbidden', 'forbidden', true, $hourlyCapabilities);
}, 'Owner cannot create categories');
$server->query('UPDATE users SET status = 0 WHERE id = ' . $categoryAdmin);
$denied(static function () use ($categoryService, $categoryAdmin): void { $categoryService->administration($categoryAdmin); }, 'Inactive administrator cannot configure categories');
$server->query('UPDATE users SET status = 1 WHERE id = ' . $categoryAdmin);
