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
$savedRecoveryFlag = getenv('PASSWORD_RECOVERY_ENABLED');
$savedOutbox = getenv('PASSWORD_RESET_OUTBOX');
putenv('PASSWORD_RECOVERY_ENABLED=true');
putenv('PASSWORD_RESET_OUTBOX=' . $temporaryDirectory . '/outbox');
mkdir($temporaryDirectory . '/sessions', 0700, true);
$command = [PHP_BINARY, '-d', 'session.save_path=' . $temporaryDirectory . '/sessions', '-S', '127.0.0.1:' . $port, __DIR__ . '/Fixtures/http-router.php'];
$pipes = [];
$process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $temporaryDirectory . '/http.log', 'a'], 2 => ['file', $temporaryDirectory . '/http.log', 'a']], $pipes, dirname(__DIR__));
putenv($savedOrganizationFlag === false ? 'ORGANIZATIONS_ENABLED' : 'ORGANIZATIONS_ENABLED=' . $savedOrganizationFlag);
putenv($savedRecoveryFlag === false ? 'PASSWORD_RECOVERY_ENABLED' : 'PASSWORD_RECOVERY_ENABLED=' . $savedRecoveryFlag);
putenv($savedOutbox === false ? 'PASSWORD_RESET_OUTBOX' : 'PASSWORD_RESET_OUTBOX=' . $savedOutbox);
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
$request = static function (string $path, ?array $data = null) use ($port, &$cookie): array {
    $headers = 'Cookie: ' . $cookie . "\r\n";
    if ($data !== null) {
        $headers .= "Content-Type: application/x-www-form-urlencoded\r\nX-Requested-With: XMLHttpRequest\r\n";
    }
    $context = stream_context_create(['http' => [
        'method' => $data === null ? 'GET' : 'POST', 'header' => $headers,
        'content' => $data === null ? '' : http_build_query($data),
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
        if ($role === 'admin') {
            $response = $request('admin/users.php');
            $check($response[0] === 200, 'Legacy admin user-management page boots');
            $adminId = (int)$server->query("SELECT id FROM users WHERE username = 'fixture_admin'")->fetch_row()[0];
            $server->query('UPDATE users SET role_id = 3 WHERE id = ' . $adminId);
            $response = $request('admin/users.php');
            $check($response[0] === 302, 'Persisted role change revokes existing admin session privileges');
            $server->query('UPDATE users SET role_id = 1 WHERE id = ' . $adminId);
        }
        if ($role === 'owner') {
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
            $response = $request('?url=organization/show&organization_id=' . $ownerOrganization);
            $check($response[0] === 200, 'Owner API reads own organization');
            $response = $request('?url=organization/index');
            $check($response[0] === 200 && strpos($response[1], 'HTTP fixture organization') !== false, 'Owner organization workspace renders');
        }
        if ($role === 'tenant' && $ownerOrganization !== null) {
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
