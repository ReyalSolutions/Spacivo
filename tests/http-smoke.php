<?php
declare(strict_types=1);

// Included by database.php; all fixtures are written only to its disposable database.
foreach ([1 => 'admin', 2 => 'owner', 3 => 'tenant'] as $roleId => $slug) {
    $statement = $server->prepare('INSERT INTO roles (id, name, slug) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), slug = VALUES(slug)');
    $statement->bind_param('iss', $roleId, $slug, $slug);
    $statement->execute();
    $username = 'fixture_' . $slug;
    $email = $username . '@example.test';
    $passwordHash = password_hash('FixturePassword123', PASSWORD_BCRYPT);
    $statement = $server->prepare("INSERT INTO users (role_id, username, first_name, last_name, email, password, image, status) VALUES (?, ?, 'Fixture', 'User', ?, ?, '', 1)");
    $statement->bind_param('isss', $roleId, $username, $email, $passwordHash);
    $statement->execute();
}
$server->query("INSERT INTO permissions (name, slug, category, module) VALUES ('Dashboard', 'view_dashboard', 'dashboard', 'dashboard')");
$permissionId = $server->insert_id;
$server->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (1, ' . $permissionId . '), (2, ' . $permissionId . ')');
foreach (['view_houses', 'add_houses', 'edit_houses', 'delete_houses', 'approve_houses'] as $listingPermission) {
    $statement = $server->prepare("INSERT INTO permissions (name, slug, category, module) VALUES (?, ?, 'Property', 'Boarding House')");
    $statement->bind_param('ss', $listingPermission, $listingPermission);
    $statement->execute();
    $listingPermissionId = (int)$server->insert_id;
    $server->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (1, ' . $listingPermissionId . ')');
    if ($listingPermission === 'view_houses') $server->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (2, ' . $listingPermissionId . ')');
}
App\Shared\Security\PermissionSeeder::seed($server);
$server->query('INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT 1, id FROM permissions');
$server->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT 2, id FROM permissions WHERE slug IN ('view_rooms','view_tenants','booking_ops','view_payments','record_payments','print_payment_receipt','view_revenue','manage_subscriptions')");
$fixtureUsers = new User($server);
$fixtureOwner = (int)$fixtureUsers->findByUsername('fixture_owner')['id'];
$fixtureOtherOwner = $fixtureUsers->create('Foreign', null, 'Owner', 'foreign-owner@example.test', '09991111110', 2, 'foreign_owner', 'FixturePassword123');
$fixtureOtherTenant = $fixtureUsers->create('Foreign', null, 'Tenant', 'foreign-tenant@example.test', '09991111111', 3, 'foreign_tenant', 'FixturePassword123');
$server->query("INSERT INTO plans (name, price_monthly, price_yearly, bhouse_limit, room_limit) VALUES ('Fixture Plan', 100, 1200, 10, 50)");
$fixturePlan = $server->insert_id;
$server->query("INSERT INTO subscriptions (owner_id, plan_id, status, start_date) VALUES (" . $fixtureOwner . ', ' . $fixturePlan . ", 'active', CURRENT_DATE)");
$fixtureSubscription = $server->insert_id;
$server->query("INSERT INTO plan_payments (owner_id, subscription_id, plan_id, billing_cycle, amount, gateway, payment_method, status, paid_at) VALUES (" . $fixtureOwner . ', ' . $fixtureSubscription . ', ' . $fixturePlan . ", 'monthly', 100, 'fixture', 'Cash', 'paid', NOW())");
$server->query("INSERT INTO boarding_houses (owner_id, name, status) VALUES (" . $fixtureOtherOwner . ", 'Foreign private house', 'approved')");
$fixtureForeignHouse = $server->insert_id;
$server->query("INSERT INTO boarding_houses (owner_id, name, address, status) VALUES (" . $fixtureOwner . ", 'Own scoped listing', 'Street, Barangay, City, Province, Country', 'pending')");
$fixtureOwnHouse = (int)$server->insert_id;
$server->query("INSERT INTO rooms (boarding_house_id, room_name, price) VALUES (" . $fixtureForeignHouse . ", 'Foreign room', 100)");
$fixtureForeignRoom = $server->insert_id;
$server->query("INSERT INTO bookings (user_id, room_id, start_date, status, is_moved_out) VALUES (" . $fixtureOtherTenant . ', ' . $fixtureForeignRoom . ", CURRENT_DATE, 'pending', 0)");
$fixtureForeignBooking = $server->insert_id;
$fixtureForeignPayment = (new Payment($server))->createPending($fixtureOtherTenant, $fixtureForeignBooking, 100, 'Cash');

$socket = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);
if ($socket === false) {
    throw new RuntimeException('Unable to allocate test HTTP port.');
}
$address = stream_socket_get_name($socket, false);
fclose($socket);
$port = (int)substr(strrchr($address, ':'), 1);
$savedDatabase = getenv('DB_NAME');
putenv('DB_NAME=' . $name);
$savedOrganizationFlag = getenv('ORGANIZATIONS_ENABLED');
putenv('ORGANIZATIONS_ENABLED=true');
$savedCategoryFlag = getenv('CATEGORIES_ENABLED');
putenv('CATEGORIES_ENABLED=true');
$savedInventoryFlag = getenv('INVENTORY_ENABLED');
putenv('INVENTORY_ENABLED=true');
$savedMetadataFlag = getenv('INVENTORY_METADATA_ENABLED');
$savedUploadDirectory = getenv('INVENTORY_UPLOAD_DIR');
putenv('INVENTORY_METADATA_ENABLED=true');
putenv('INVENTORY_UPLOAD_DIR=' . $temporaryDirectory . '/inventory-images');
$savedRecoveryFlag = getenv('PASSWORD_RECOVERY_ENABLED');
$savedOutbox = getenv('PASSWORD_RESET_OUTBOX');
$savedMailDriver = getenv('MAIL_DRIVER');
$savedAppEnvironment = getenv('APP_ENV');
putenv('MAIL_DRIVER=local');
putenv('APP_ENV=testing');
putenv('PASSWORD_RECOVERY_ENABLED=true');
putenv('PASSWORD_RESET_OUTBOX=' . $temporaryDirectory . '/outbox');
mkdir($temporaryDirectory . '/sessions', 0700, true);
$command = [PHP_BINARY, '-d', 'session.save_path=' . $temporaryDirectory . '/sessions', '-S', '127.0.0.1:' . $port, __DIR__ . '/Fixtures/http-router.php'];
$pipes = [];
$process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $temporaryDirectory . '/http.log', 'a'], 2 => ['file', $temporaryDirectory . '/http.log', 'a']], $pipes, dirname(__DIR__));
putenv($savedOrganizationFlag === false ? 'ORGANIZATIONS_ENABLED' : 'ORGANIZATIONS_ENABLED=' . $savedOrganizationFlag);
putenv($savedCategoryFlag === false ? 'CATEGORIES_ENABLED' : 'CATEGORIES_ENABLED=' . $savedCategoryFlag);
putenv($savedInventoryFlag === false ? 'INVENTORY_ENABLED' : 'INVENTORY_ENABLED=' . $savedInventoryFlag);
putenv($savedMetadataFlag === false ? 'INVENTORY_METADATA_ENABLED' : 'INVENTORY_METADATA_ENABLED=' . $savedMetadataFlag);
putenv($savedUploadDirectory === false ? 'INVENTORY_UPLOAD_DIR' : 'INVENTORY_UPLOAD_DIR=' . $savedUploadDirectory);
putenv($savedRecoveryFlag === false ? 'PASSWORD_RECOVERY_ENABLED' : 'PASSWORD_RECOVERY_ENABLED=' . $savedRecoveryFlag);
putenv($savedOutbox === false ? 'PASSWORD_RESET_OUTBOX' : 'PASSWORD_RESET_OUTBOX=' . $savedOutbox);
putenv($savedMailDriver === false ? 'MAIL_DRIVER' : 'MAIL_DRIVER=' . $savedMailDriver);
putenv($savedAppEnvironment === false ? 'APP_ENV' : 'APP_ENV=' . $savedAppEnvironment);
if ($savedDatabase === false) {
    putenv('DB_NAME');
} else {
    putenv('DB_NAME=' . $savedDatabase);
}
if (!is_resource($process)) {
    throw new RuntimeException('Unable to start isolated HTTP server.');
}
fclose($pipes[0]);
$cookie = '';
$request = static function (string $path, ?array $data = null, ?string $jsonMethod = null) use ($port, &$cookie): array {
    $headers = 'Cookie: ' . $cookie . "\r\n";
    if ($data !== null) {
        $headers .= 'Content-Type: ' . ($jsonMethod ? 'application/json' : 'application/x-www-form-urlencoded') . "\r\nX-Requested-With: XMLHttpRequest\r\n";
    }
    $context = stream_context_create(['http' => [
        'method' => $jsonMethod ?? ($data === null ? 'GET' : 'POST'), 'header' => $headers,
        'content' => $data === null ? '' : ($jsonMethod ? json_encode($data) : http_build_query($data)),
        'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5,
    ]]);
    $body = file_get_contents('http://127.0.0.1:' . $port . '/tenant/' . $path, false, $context);
    $responseHeaders = $http_response_header ?? [];
    preg_match('/\s(\d{3})\s/', $responseHeaders[0] ?? '', $status);
    foreach ($responseHeaders as $header) {
        if (preg_match('/^Set-Cookie:\s*(PHPSESSID=[^;]*)/i', $header, $matches)) {
            $cookie = $matches[1];
        }
    }
    return [(int)($status[1] ?? 0), (string)$body, $responseHeaders];
};
$uploadPhoto = static function (string $path, array $fields, string $pixels) use ($port, &$cookie): array {
    $boundary = 'spacivo-' . bin2hex(random_bytes(16));
    $body = '';
    foreach ($fields as $key => $value) { $body .= '--' . $boundary . "\r\nContent-Disposition: form-data; name=\"" . $key . "\"\r\n\r\n" . $value . "\r\n"; }
    $body .= '--' . $boundary . "\r\nContent-Disposition: form-data; name=\"photo\"; filename=\"unsafe.php\"\r\nContent-Type: image/png\r\n\r\n" . $pixels . "\r\n--" . $boundary . "--\r\n";
    $context = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Cookie: ' . $cookie . "\r\nContent-Type: multipart/form-data; boundary=" . $boundary . "\r\n", 'content' => $body, 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5]]);
    $response = file_get_contents('http://127.0.0.1:' . $port . '/tenant/' . $path, false, $context);
    preg_match('/\s(\d{3})\s/', $http_response_header[0] ?? '', $status);
    return [(int)($status[1] ?? 0), (string)$response];
};
try {
    $ready = false;
    for ($attempt = 0; $attempt < 60; $attempt++) {
        $probe = @stream_socket_client('tcp://127.0.0.1:' . $port, $errorNumber, $errorMessage, 0.1);
        if (is_resource($probe)) {
            fclose($probe);
            $ready = true;
            break;
        }
        usleep(50000);
    }
    if (!$ready) {
        throw new RuntimeException('Test HTTP server failed to start.');
    }
    $response = $request('?url=boarding/index');
    $check($response[0] === 200 && strpos($response[1], '<!doctype html>') !== false, 'Fresh database marketplace renders shared layout');
    $response = $request('?url=organization/show&organization_id=1');
    $check($response[0] === 401, 'Organization API rejects unauthenticated requests');
    $response = $request('api/v1/categories');
    $check($response[0] === 200 && json_decode($response[1], true)['data'] === [], 'Versioned public category API boots with empty catalog');
    $response = $request('api/v1/admin/categories');
    $check($response[0] === 401, 'Category administration API requires authentication');
    $response = $request('api/v1/owner/properties?organization_id=1');
    $check($response[0] === 401, 'Inventory API requires authentication');
    $ownerOrganization = null;
    foreach (['admin', 'owner', 'tenant'] as $role) {
        $cookie = '';
        $response = $request('?url=auth/login');
        preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/', $response[1], $matches);
        $token = $matches[1] ?? '';
        $check($response[0] === 200 && strlen($token) === 64, $role . ' login renders with CSRF');
        $response = $request('?url=auth/login', ['username' => 'fixture_' . $role, 'password' => 'FixturePassword123', 'csrf_token' => $token]);
        $payload = json_decode($response[1], true);
        $check($response[0] === 200 && ($payload['success'] ?? false) === true, $role . ' login works against baseline');
        $dashboard = $role === 'admin' ? 'admin/index.php' : ($role === 'owner' ? '?url=admin/index' : '?url=tenant/dashboard');
        $response = $request($dashboard);
        $check($response[0] === 200 && stripos($response[1], '<html') !== false && strpos($response[1], 'Something went wrong') === false, $role . ' portal boots');
        if ($role === 'admin' || $role === 'owner') {
            $check(strpos($response[1], 'class="left-sidebar"') !== false && strpos($response[1], '/tenant/admin/assets/css/custom_modern.css') !== false && strpos($response[1], 'class="admin-sidebar"') === false, $role . ' uses shared light management shell');
        }
        if ($role === 'owner') {
            $server->query("UPDATE subscriptions SET status = 'expired', start_date = DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH) WHERE id = " . $fixtureSubscription);
            foreach (['admin/index', 'owner/houses', 'owner/rooms', 'owner/bookings', 'owner/tenants', 'owner/payments'] as $ownerRoute) {
                $renewedPage = $request('?url=' . $ownerRoute);
                $check($renewedPage[0] === 200 && strpos($renewedPage[1], 'No Active Subscription Plan') === false && strpos($renewedPage[1], 'paywall-wrapper') === false, 'Paid expired subscription permits ' . $ownerRoute);
            }
            $server->query("UPDATE plan_payments SET paid_at = DATE_SUB(NOW(), INTERVAL 2 MONTH) WHERE subscription_id = " . $fixtureSubscription);
            $overduePage = $request('?url=owner/houses');
            $check($overduePage[0] === 200 && strpos($overduePage[1], 'paywall-wrapper') !== false && strpos($overduePage[1], 'No Active Subscription Plan') === false && strpos($overduePage[1], 'class="left-sidebar"') !== false, 'Unpaid overdue period shows renewal notice in shared shell');
            $server->query("UPDATE subscriptions SET status = 'active', start_date = CURRENT_DATE WHERE id = " . $fixtureSubscription);
            $server->query("UPDATE plan_payments SET paid_at = NOW() WHERE subscription_id = " . $fixtureSubscription);
            $response = $request('?url=admin/subscriptions');
            $check($response[0] === 200 && strpos($response[1], 'class="left-sidebar"') !== false && strpos($response[1], '/tenant/public/assets/css/admin.css') === false, 'Owner subscriptions use new management design');
            $check(strpos($response[1], 'href="/tenant/admin/users.php"') === false && strpos($response[1], 'href="/tenant/admin/roles.php"') === false && strpos($response[1], 'href="/tenant/?url=admin/subscriptions"') !== false, 'Owner navigation excludes administrator account and role controls');
            $response = $request('admin/users.php');
            $check($response[0] === 403, 'Shared interface does not grant owner administrator access');
        }
        if ($role === 'admin') {
            $roleEditor = $request('admin/roles.php');
            $check($roleEditor[0] === 200 && strpos($roleEditor[1], 'update_role_permissions') !== false && strpos($roleEditor[1], 'name="csrf_token"') !== false, 'Physical roles URL exposes complete canonical permission editor with CSRF');
            $ownerGrants = array_column($server->query('SELECT permission_id FROM role_permissions WHERE role_id = 2')->fetch_all(MYSQLI_ASSOC), 'permission_id');
            $editHouseGrant = (int)$server->query("SELECT id FROM permissions WHERE slug = 'edit_houses'")->fetch_row()[0];
            $newGrants = array_merge($ownerGrants, [$editHouseGrant]);
            $deniedSync = $request('?url=admin/update_role_permissions', ['role_id' => 2, 'permissions' => $newGrants]);
            $check($deniedSync[0] === 403, 'Role permission assignment rejects missing CSRF');
            $configuredSync = $request('?url=admin/update_role_permissions', ['role_id' => 2, 'permissions' => $newGrants, 'csrf_token' => $token]);
            $check($configuredSync[0] === 302 && $server->query('SELECT 1 FROM role_permissions WHERE role_id = 2 AND permission_id = ' . $editHouseGrant)->num_rows === 1, 'Administrator configures owner action grant through role editor endpoint');
            $invalidSync = $request('?url=admin/update_role_permissions', ['role_id' => 2, 'permissions' => [99999999], 'csrf_token' => $token]);
            $check($invalidSync[0] === 302 && $server->query('SELECT 1 FROM role_permissions WHERE role_id = 2 AND permission_id = ' . $editHouseGrant)->num_rows === 1, 'Invalid permission IDs preserve existing role grants');
            $request('?url=admin/update_role_permissions', ['role_id' => 2, 'permissions' => $ownerGrants, 'csrf_token' => $token]);
            $syncGrant = (int)$server->query("SELECT id FROM permissions WHERE slug = 'sync_permissions'")->fetch_row()[0];
            $server->query('DELETE FROM role_permissions WHERE role_id = 1 AND permission_id = ' . $syncGrant);
            $revokedSync = $request('?url=admin/update_role_permissions', ['role_id' => 2, 'permissions' => [], 'csrf_token' => $token]);
            $check($revokedSync[0] === 403, 'Role configuration grant revocation applies immediately');
            $server->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (1, ' . $syncGrant . ')');
            $allListings = $request('?url=admin/houses_data');
            $check($allListings[0] === 200 && strpos($allListings[1], 'Foreign private house') !== false && strpos($allListings[1], 'Own scoped listing') !== false, 'Shared admin listing data includes every owner');
            $response = $request('?url=property/index');
            $check($response[0] === 200 && strpos($response[1], 'Listing review') !== false, 'Administrator listing review page renders');
            $response = $request('?url=category/index');
            $check($response[0] === 200 && strpos($response[1], 'Space categories') !== false, 'Category administration page renders');
            $configuration = ['name' => 'HTTP Court', 'slug' => 'http_court', 'active' => true, 'capabilities' => ['hourly_booking' => true]];
            $response = $request('api/v1/admin/categories', $configuration, 'POST');
            $check($response[0] === 403, 'Category writes reject missing CSRF');
            $configuration['csrf_token'] = $token;
            $response = $request('api/v1/admin/categories', $configuration, 'POST');
            $categoryPayload = json_decode($response[1], true);
            $httpCategoryId = (int)($categoryPayload['data']['id'] ?? 0);
            $check($response[0] === 201 && $httpCategoryId > 0, 'Administrator creates category over versioned API');
            $configuration['version'] = 1; $configuration['active'] = false;
            $response = $request('api/v1/admin/categories/' . $httpCategoryId, $configuration, 'PATCH');
            $check($response[0] === 200, 'Administrator updates category over PATCH API');
            $response = $request('api/v1/categories');
            $check(json_decode($response[1], true)['data'] === [], 'Public API excludes deactivated category');
            $server->query('DELETE FROM space_categories WHERE id = ' . $httpCategoryId);
            $response = $request('admin/users.php');
            $check($response[0] === 200, 'Legacy admin user-management page boots');
            $adminId = (int)$server->query("SELECT id FROM users WHERE username = 'fixture_admin'")->fetch_row()[0];
            $server->query('UPDATE users SET role_id = 3 WHERE id = ' . $adminId);
            $response = $request('admin/users.php');
            $check(in_array($response[0], [302,403], true), 'Persisted role change revokes existing admin session privileges');
            $server->query('UPDATE users SET role_id = 1 WHERE id = ' . $adminId);
        }
        if ($role === 'owner') {
            $upgradePage = $request('?url=admin/upgrade&cycle=yearly');
            $check($upgradePage[0] === 200 && strpos($upgradePage[1], '<section id="upgradePlans"') !== false && strpos($upgradePage[1], 'id="upgradePlanModal"') === false, 'Upgrade plans render as a page without a plan modal');
            $check(strpos($upgradePage[1], '<aside') === false && strpos($upgradePage[1], '<header') === false && strpos($upgradePage[1], '<main class="upgrade-page">') !== false, 'Upgrade page has no dashboard sidebar or header');
            $check(strpos($upgradePage[1], 'setUpgradeBilling("yearly")') !== false && strpos($upgradePage[1], 'id="paymentGatewayModal"') !== false, 'Upgrade page retains billing cycle and payment selection');
            $server->query("INSERT INTO subscriptions (owner_id, plan_id, status, start_date) VALUES (" . $fixtureOtherOwner . ', ' . $fixturePlan . ", 'active', CURRENT_DATE)");
            $otherSubscription = (int)$server->insert_id;
            $foreignUpgrade = $request('?url=admin/upgrade&subscription_id=' . $otherSubscription);
            $check($foreignUpgrade[0] === 403, 'Upgrade page rejects another owner subscription');
            $server->query('DELETE FROM subscriptions WHERE id = ' . $otherSubscription);
            foreach (['?url=admin/houses', '?url=owner/houses', 'admin/houses.php'] as $listingRoute) {
                $listingPage = $request($listingRoute);
                $check($listingPage[0] === 200 && strpos($listingPage[1], 'id="houses-table"') !== false, 'Both listing entry routes render canonical admin page: ' . $listingRoute);
            }
            $ownedListings = $request('?url=admin/houses_data&owner_id=' . $fixtureOtherOwner);
            $check($ownedListings[0] === 200 && strpos($ownedListings[1], 'Own scoped listing') !== false && strpos($ownedListings[1], 'Foreign private house') === false, 'Owner listing query ignores attempted foreign-owner filter');
            foreach (['store_house', 'update_house', 'delete_house', 'upload_house_images', 'update_house_amenities'] as $listingAction) {
                $deniedListingWrite = $request('?url=admin/' . $listingAction, ['house_id' => $fixtureOwnHouse, 'csrf_token' => $token]);
                $check($deniedListingWrite[0] === 403, 'Read-only listing grant denies direct ' . $listingAction);
            }
            $editPermission = (int)$server->query("SELECT id FROM permissions WHERE slug = 'edit_houses'")->fetch_row()[0];
            $server->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (2, ' . $editPermission . ')');
            $foreignListingEdit = $request('?url=admin/update_house', ['house_id' => $fixtureForeignHouse, 'name' => 'Unauthorized change', 'csrf_token' => $token]);
            $check($foreignListingEdit[0] === 302 && $server->query('SELECT name FROM boarding_houses WHERE id = ' . $fixtureForeignHouse)->fetch_row()[0] === 'Foreign private house', 'Edit permission never overrides owner listing boundary');
            // A successful listing POST must survive the redirect as a single global toast.
            $savedListing = $request('?url=admin/update_house', ['house_id' => $fixtureOwnHouse, 'name' => 'Own scoped listing', 'address' => 'Street, Barangay, City, Province, Country', 'description' => 'Updated toast fixture', 'latitude' => '0', 'longitude' => '0', 'csrf_token' => $token]);
            $check($savedListing[0] === 302, 'Successful listing update redirects to shared page');
            $savedPage = $request('?url=admin/houses');
            $check($savedPage[0] === 200 && strpos($savedPage[1], 'Property details successfully updated.') !== false && strpos($savedPage[1], "ToastStack.create({type: 'success'") !== false && strpos($savedPage[1], 'toast.js?v=') !== false, 'Redirected listing update renders global success toast with current assets');
            $savedPageAgain = $request('?url=admin/houses');
            $check(strpos($savedPageAgain[1], 'Property details successfully updated.') === false, 'Listing success toast flash is consumed exactly once');
            $server->query('DELETE FROM role_permissions WHERE role_id = 2 AND permission_id = ' . $editPermission);
            $viewPermission = (int)$server->query("SELECT id FROM permissions WHERE slug = 'view_houses'")->fetch_row()[0];
            $server->query('DELETE FROM role_permissions WHERE role_id = 2 AND permission_id = ' . $viewPermission);
            $revokedListings = $request('?url=admin/houses_data');
            $check($revokedListings[0] === 403, 'Listing permission revocation applies on next request');
            $server->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (2, ' . $viewPermission . ')');
            $response = $request('api/v1/admin/categories');
            $check($response[0] === 403, 'Owner cannot read category administration API');
            $response = $request('?url=owner/get_room&id=' . $fixtureForeignRoom);
            $check($response[0] === 403, 'Legacy owner cannot read another owner room');
            $response = $request('?url=owner/get_residency_payments_json&tenancy_id=' . $fixtureForeignBooking);
            $check($response[0] === 403, 'Legacy owner cannot read another owner tenant ledger');
            $response = $request('?url=owner/print_statement&tenancy_id=' . $fixtureForeignBooking);
            $check($response[0] === 403, 'Legacy owner cannot print another owner statement');
            $response = $request('?url=owner/record_manual_payment', ['tenancy_id' => $fixtureForeignBooking, 'amount' => 100]);
            $check($response[0] === 403, 'Manual payment rejects missing CSRF');
            $response = $request('?url=organization/create', ['name' => 'HTTP fixture organization', 'csrf_token' => $token]);
            $payload = json_decode($response[1], true);
            $ownerOrganization = $payload['data']['id'] ?? null;
            $check($response[0] === 201 && $ownerOrganization !== null, 'Organization onboarding API creates owner membership');
            $httpCategoryService = new App\Modules\Categories\Services\CategoryService(new App\Modules\Categories\Repositories\CategoryRepository($server), new App\Modules\Organizations\Repositories\OrganizationRepository($server));
            $httpInventoryCategory = $httpCategoryService->save($adminId, 0, 0, 'Inventory HTTP', 'inventory_http', true, ['monthly_rental' => true]);
            $httpPropertyInput = ['organization_id' => $ownerOrganization, 'category_id' => $httpInventoryCategory,
                'name' => 'HTTP Property', 'description' => 'Private organization inventory.', 'address' => 'Fixture Address', 'timezone' => 'Asia/Singapore'];
            $response = $request('api/v1/owner/properties', $httpPropertyInput, 'POST');
            $check($response[0] === 403, 'Inventory creation requires CSRF');
            $httpPropertyInput['csrf_token'] = $token;
            $response = $request('api/v1/owner/properties', $httpPropertyInput, 'POST');
            $httpInventoryProperty = (int)(json_decode($response[1], true)['data']['id'] ?? 0);
            $check($response[0] === 201 && $httpInventoryProperty > 0, 'Owner creates scoped property through REST API');
            $response = $request('?url=property/index&organization_id=' . $ownerOrganization);
            $check($response[0] === 200 && strpos($response[1], 'HTTP Property') !== false, 'Owner inventory page renders property and unit forms');
            $httpUnitInput = ['organization_id' => $ownerOrganization, 'category_id' => $httpInventoryCategory, 'name' => 'HTTP Unit', 'capacity' => 2, 'csrf_token' => $token];
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '/units', $httpUnitInput, 'POST');
            $httpInventoryUnit = (int)(json_decode($response[1], true)['data']['id'] ?? 0);
            $check($response[0] === 201 && $httpInventoryUnit > 0, 'Owner creates scoped rental unit through REST API');
            $testImage = imagecreatetruecolor(2, 2); ob_start(); imagepng($testImage); $testPixels = ob_get_clean(); imagedestroy($testImage);
            $photoFields = ['organization_id' => $ownerOrganization, 'version' => 2];
            $response = $uploadPhoto('api/v1/owner/properties/' . $httpInventoryProperty . '/photos', $photoFields, $testPixels);
            $check($response[0] === 403, 'Photo upload rejects missing CSRF');
            $photoFields['csrf_token'] = $token;
            $response = $uploadPhoto('api/v1/owner/properties/' . $httpInventoryProperty . '/photos', $photoFields, $testPixels . '<?php echo "EXECUTABLE_TEST_MARKER"; ?>');
            $httpInventoryPhoto = (int)(json_decode($response[1], true)['data']['id'] ?? 0);
            $check($response[0] === 201 && $httpInventoryPhoto > 0, 'Multipart raster upload stored with generated safe filename');
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '/photos/' . $httpInventoryPhoto . '?organization_id=' . $ownerOrganization);
            $check($response[0] === 200 && @getimagesizefromstring($response[1]) !== false && strpos($response[1], 'EXECUTABLE_TEST_MARKER') === false, 'Private photo preview contains reencoded pixels without executable payload');
            $response = $request('api/v1/properties/' . $httpInventoryProperty . '/photos/' . $httpInventoryPhoto);
            $check($response[0] === 403, 'Draft photo cannot be downloaded publicly');
            $response = $uploadPhoto('api/v1/owner/properties/' . $httpInventoryProperty . '/photos', $photoFields, $testPixels);
            $check($response[0] === 422 && count(glob($temporaryDirectory . '/inventory-images/*')) === 1, 'Stale photo upload rolls back metadata and cleans its file');
            $photoFields['version'] = 3;
            $response = $uploadPhoto('api/v1/owner/properties/' . $httpInventoryProperty . '/photos', $photoFields, '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
            $check($response[0] === 422 && count(glob($temporaryDirectory . '/inventory-images/*')) === 1, 'SVG active content rejected without storing a file');
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '/amenities', ['organization_id' => $ownerOrganization, 'version' => 3, 'amenities' => [], 'csrf_token' => $token], 'PUT');
            $check($response[0] === 200, 'Owner updates scoped amenities through REST API');
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '/state', ['organization_id' => $ownerOrganization, 'version' => 2, 'state' => 'published', 'csrf_token' => $token], 'POST');
            $check($response[0] === 422, 'REST API refuses unverified unapproved publication');
            $response = $request('api/v1/properties');
            $check(json_decode($response[1], true)['data'] === [], 'Public property API excludes drafts');
            $httpOrgRepository = new App\Modules\Organizations\Repositories\OrganizationRepository($server);
            (new App\Modules\Organizations\Services\OrganizationService($httpOrgRepository))->verify($adminId, $ownerOrganization, 'verified');
            $httpPropertyService = new App\Modules\Properties\Services\PropertyService(new App\Modules\Properties\Repositories\PropertyRepository($server), $httpOrgRepository);
            $httpPropertyService->review($adminId, $ownerOrganization, $httpInventoryProperty, 4, 'approved');
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '/state', ['organization_id' => $ownerOrganization, 'version' => 5, 'state' => 'published', 'csrf_token' => $token], 'POST');
            $check($response[0] === 200, 'Verified owner publishes approved listing through REST API');
            $savedPhotoCookie = $cookie; $cookie = '';
            $response = $request('api/v1/properties/' . $httpInventoryProperty . '/photos/' . $httpInventoryPhoto);
            $check($response[0] === 200 && @getimagesizefromstring($response[1]) !== false, 'Published photo is available to anonymous visitors');
            $response = $request('?url=property/browse');
            $check($response[0] === 200 && strpos($response[1], 'HTTP Property') !== false && strpos($response[1], '/photos/' . $httpInventoryPhoto) !== false, 'Published approved inventory appears in the public marketplace');
            $cookie = $savedPhotoCookie;
            $httpPropertyService->moderate($adminId, $ownerOrganization, $httpInventoryProperty, 6, 'suspended');
            $response = $request('api/v1/properties/' . $httpInventoryProperty . '/photos/' . $httpInventoryPhoto);
            $check($response[0] === 403, 'Suspending listing revokes public photo downloads');
            $response = $request('?url=property/browse');
            $check($response[0] === 200 && strpos($response[1], 'HTTP Property') === false, 'Suspended inventory disappears from the public marketplace');
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '/state', ['organization_id' => $ownerOrganization, 'version' => 7, 'state' => 'published', 'csrf_token' => $token], 'POST');
            $check($response[0] === 422, 'Owner REST API cannot bypass platform suspension');
            $httpPropertyService->moderate($adminId, $ownerOrganization, $httpInventoryProperty, 7, 'draft');
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '?organization_id=' . $ownerOrganization);
            $photoDetails = json_decode($response[1], true)['data'] ?? [];
            $check($response[0] === 200 && count($photoDetails['metadata']['photos'] ?? []) === 1, 'Scoped property API includes photo and amenity metadata');
            $response = $request('?url=property/index&organization_id=' . $ownerOrganization);
            $check($response[0] === 200 && strpos($response[1], '/photos/' . $httpInventoryPhoto) !== false && strpos($response[1], 'Upload photo') !== false, 'Inventory page renders private photo preview and metadata controls');
            $response = $request('?url=organization/show&organization_id=' . $ownerOrganization);
            $check($response[0] === 200, 'Owner API reads own organization');
            $response = $request('?url=organization/index');
            $check($response[0] === 200 && strpos($response[1], 'HTTP fixture organization') !== false, 'Owner organization workspace renders');
        }
        if ($role === 'tenant' && $ownerOrganization !== null) {
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '?organization_id=' . $ownerOrganization);
            $check($response[0] === 403, 'Cross-account inventory API access denied');
            $response = $request('api/v1/owner/properties/' . $httpInventoryProperty . '/photos/' . $httpInventoryPhoto . '?organization_id=' . $ownerOrganization);
            $check($response[0] === 403, 'Foreign account cannot download private draft photos');
            $response = $request('?url=organization/show&organization_id=' . $ownerOrganization);
            $check($response[0] === 403, 'Cross-account organization API access denied');
            $response = $request('?url=payment/checkout&payment_id=' . $fixtureForeignPayment);
            $check($response[0] === 404, 'Tenant cannot read another account checkout');
            $response = $request('?url=payment/initiate', ['booking_id' => $fixtureForeignBooking]);
            $check($response[0] === 403, 'Payment initiation rejects missing CSRF');
        }
        $response = $request('?url=auth/logout');
        $check($response[0] === 405, $role . ' logout rejects GET');
        $response = $request('?url=auth/logout', ['csrf_token' => 'wrong']);
        $check($response[0] === 403, $role . ' logout rejects invalid CSRF');
        $response = $request('?url=auth/logout', ['csrf_token' => $token]);
        $check($response[0] === 302, $role . ' logout accepts valid CSRF');
    }
    $server->query('DELETE FROM property_media WHERE property_id = ' . $httpInventoryProperty);
    $server->query('DELETE FROM property_amenities WHERE property_id = ' . $httpInventoryProperty);
    $server->query('DELETE FROM rental_units WHERE property_id = ' . $httpInventoryProperty);
    $server->query('DELETE FROM properties WHERE id = ' . $httpInventoryProperty);
    $server->query('DELETE FROM space_categories WHERE id = ' . $httpInventoryCategory);
    $cookie = '';
    $response = $request('?url=auth/register');
    preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/', $response[1], $matches);
    $token = $matches[1] ?? '';
    $beforeRegistration = (int)$server->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
    $registration = ['first_name' => 'Test', 'last_name' => 'Registration', 'email' => 'registration@example.test', 'phone' => '09990000001', 'role_id' => 1, 'csrf_token' => $token];
    $response = $request('?url=auth/register', $registration);
    $payload = json_decode($response[1], true);
    $check(($payload['success'] ?? null) === false && (int)$server->query('SELECT COUNT(*) FROM users')->fetch_row()[0] === $beforeRegistration, 'HTTP public registration cannot create administrator');
    $registration['role_id'] = 3;
    $response = $request('?url=auth/register', $registration);
    $payload = json_decode($response[1], true);
    $check(($payload['success'] ?? false) === true, 'Public tenant registration succeeds under strict schema');
    $secret = '  Pa&ss<word>\\123  ';
    $response = $request('?url=auth/register_step2', ['username' => 'new_fixture_tenant', 'password' => $secret, 'confirm_password' => $secret, 'csrf_token' => $token]);
    $payload = json_decode($response[1], true);
    $check(($payload['success'] ?? false) === true, 'Registration preserves opaque password');
    $response = $request('?url=auth/login', ['username' => 'new_fixture_tenant', 'password' => $secret, 'csrf_token' => $token]);
    $payload = json_decode($response[1], true);
    $check(($payload['success'] ?? false) === true, 'Login accepts exact spaces and special characters in password');
    $response = $request('?url=auth/forgot_password', ['email' => 'registration@example.test', 'csrf_token' => $token]);
    $check($response[0] === 200 && strpos($response[1], 'recovery instructions have been queued') !== false, 'Password recovery renders generic confirmation');
    $files = glob($temporaryDirectory . '/outbox/*.json') ?: [];
    $check(count($files) === 1, 'Development recovery queues one private outbox message');
    $delivery = json_decode(file_get_contents($files[0]), true);
    parse_str(parse_url($delivery['reset_url'], PHP_URL_QUERY), $resetParameters);
    $resetToken = $resetParameters['token'];
    $newSecret = 'New&Password123';
    $response = $request('?url=auth/reset_password', ['token' => $resetToken, 'password' => $newSecret, 'confirm_password' => $newSecret, 'csrf_token' => $token]);
    $check($response[0] === 200 && strpos($response[1], 'Your password has been changed') !== false, 'Password reset completes over HTTP');
    $response = $request('?url=tenant/dashboard');
    $check($response[0] === 302, 'Password reset invalidates existing authenticated session');
    $response = $request('?url=auth/login');
    preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/', $response[1], $matches);
    $token = $matches[1];
    $response = $request('?url=auth/login', ['username' => 'new_fixture_tenant', 'password' => $newSecret, 'csrf_token' => $token]);
    $payload = json_decode($response[1], true);
    $check(($payload['success'] ?? false) === true, 'New password signs in with updated session version');
    $response = $request('?url=auth/reset_password', ['token' => $resetToken, 'password' => $newSecret, 'confirm_password' => $newSecret, 'csrf_token' => $token]);
    $check($response[0] === 422, 'Used reset link rejected over HTTP');
    $response = $request('?url=auth/forgot_password', ['email' => 'missing@example.test', 'csrf_token' => $token]);
    $check($response[0] === 200 && strpos($response[1], 'recovery instructions have been queued') !== false && count(glob($temporaryDirectory . '/outbox/*.json') ?: []) === 1, 'Unknown account receives same recovery confirmation without delivery');
    $response = $request('?url=auth/logout', ['csrf_token' => $token]);
    $response = $request('?url=auth/login');
    preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/', $response[1], $matches);
    $token = $matches[1];
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $request('?url=auth/login', ['username' => 'new_fixture_tenant', 'password' => 'WrongPassword123', 'csrf_token' => $token]);
    }
    $response = $request('?url=auth/login', ['username' => 'new_fixture_tenant', 'password' => $newSecret, 'csrf_token' => $token]);
    $payload = json_decode($response[1], true);
    $check(($payload['success'] ?? null) === false, 'Five failed logins lock the account temporarily');
    $server->query("UPDATE users SET lockout_until = '2000-01-01 00:00:00' WHERE username = 'new_fixture_tenant'");
    $response = $request('?url=auth/login', ['username' => 'new_fixture_tenant', 'password' => $newSecret, 'csrf_token' => $token]);
    $payload = json_decode($response[1], true);
    $check(($payload['success'] ?? false) === true, 'Login succeeds after lockout expiration');
} finally {
    proc_terminate($process);
    proc_close($process);
    foreach (glob($temporaryDirectory . '/inventory-images/*') ?: [] as $file) { unlink($file); }
    if (is_dir($temporaryDirectory . '/inventory-images')) { rmdir($temporaryDirectory . '/inventory-images'); }
    foreach (glob($temporaryDirectory . '/sessions/*') ?: [] as $file) {
        unlink($file);
    }
    rmdir($temporaryDirectory . '/sessions');
    foreach (glob($temporaryDirectory . '/outbox/*') ?: [] as $file) {
        unlink($file);
    }
    if (is_dir($temporaryDirectory . '/outbox')) {
        rmdir($temporaryDirectory . '/outbox');
    }
}
