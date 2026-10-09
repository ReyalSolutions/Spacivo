<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../bootstrap/app.php';
echo json_encode(['permissions_added' => App\Shared\Security\PermissionSeeder::seed(Database::get()), 'role_grants_changed' => false]) . PHP_EOL;
