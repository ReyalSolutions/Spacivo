<?php
declare(strict_types=1);
$catalog = require __DIR__ . '/../config/management_permissions.php';
foreach (['AdminController', 'OwnerController'] as $class) {
    $reflection = new ReflectionClass($class);
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->getDeclaringClass()->getName() !== $class) continue;
        $lines = file($method->getFileName());
        $body = implode('', array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
        $check(isset($catalog[$method->getName()]) && strpos($body, "requireActionPermission('" . $method->getName() . "')") !== false, $class . ' action grant covered: ' . $method->getName());
    }
}
$check(App\Shared\Security\PermissionSeeder::seed($server) === 0, 'Complete permission seeder is idempotent');
$server->query("DELETE FROM permissions WHERE slug = 'export_analytics_pdf'");
$grantCount = (int)$server->query('SELECT COUNT(*) FROM role_permissions')->fetch_row()[0];
$check(App\Shared\Security\PermissionSeeder::seed($server) === 1, 'Permission seeder repairs a missing definition');
$check((int)$server->query('SELECT COUNT(*) FROM role_permissions')->fetch_row()[0] === $grantCount, 'Permission seeder never broadens role grants');
