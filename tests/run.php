<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
$passed = 0;
$failed = 0;
$check = static function (bool $condition, string $label) use (&$passed, &$failed): void {
    echo ($condition ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    $condition ? $passed++ : $failed++;
};

$temporary = tempnam(sys_get_temp_dir(), 'spacivo-env-');
try {
    putenv('SPACIVO_TEST_OVERRIDE=external');
    file_put_contents($temporary, "SPACIVO_TEST_OVERRIDE=file\nSPACIVO_TEST_EMPTY=\nSPACIVO_TEST_QUOTED=\"value with spaces\"\n");
    App\Core\Environment::load($temporary);
    $check(getenv('SPACIVO_TEST_OVERRIDE') === 'external', 'Deployment environment wins');
    $check(getenv('SPACIVO_TEST_EMPTY') === '', 'Empty configuration value retained');
    $check(getenv('SPACIVO_TEST_QUOTED') === 'value with spaces', 'Quoted configuration loaded');
    file_put_contents($temporary, "INVALID LINE\n");
    try {
        App\Core\Environment::load($temporary);
        $check(false, 'Malformed configuration rejected');
    } catch (RuntimeException $error) {
        $check(true, 'Malformed configuration rejected');
    }
} finally {
    unlink($temporary);
    foreach (['SPACIVO_TEST_OVERRIDE', 'SPACIVO_TEST_EMPTY', 'SPACIVO_TEST_QUOTED'] as $key) {
        putenv($key);
        unset($_ENV[$key]);
    }
}
$check(class_exists(App\Core\MigrationRunner::class), 'Namespaced autoload works');
$check(class_exists(Csrf::class), 'Legacy autoload works');
$_SESSION = [];
$token = Csrf::token();
$check(strlen($token) === 64 && Csrf::verify($token), 'CSRF token round trip');
$check(!Csrf::verify('wrong') && !Csrf::verify(null), 'CSRF rejects invalid token');
$check(!Csrf::verify(['invalid']), 'CSRF rejects array input without throwing');
$check(!App\Modules\Identity\Services\RegistrationPolicy::publicRoleAllowed(['slug' => 'admin']), 'Public registration rejects administrator role');
$check(App\Modules\Identity\Services\RegistrationPolicy::publicRoleAllowed(['slug' => 'owner']), 'Public registration permits owner role');
$secret = '  pa&ss<word>\\123  ';
$check(App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($secret) === $secret, 'Passwords preserve spaces and special characters');
$check(!App\Modules\Identity\Services\RegistrationPolicy::validNewPassword(str_repeat('a', 73)), 'Passwords exceeding bcrypt byte limit rejected');

require __DIR__ . '/Fixtures/controllers/ProbeController.php';
require __DIR__ . '/mail.php';
foreach ([
    ['probe/index', 200, 'probe-ok'],
    ['probe/bookings/history', 200, 'history-ok'],
    ['probe/secret', 404, 'Page not found'],
    ['probe/__construct', 404, 'Page not found'],
    ['probe/checkSubscriptionPaywall', 404, 'Page not found'],
    ['probe/needsArgument', 404, 'Page not found'],
    ['../config/database', 404, 'Page not found'],
    ['probe/index/extra/segment', 404, 'Page not found'],
] as $case) {
    http_response_code(200);
    ob_start();
    (new Router($case[0], __DIR__ . '/Fixtures/controllers'))->dispatch();
    $body = ob_get_clean();
    $check(http_response_code() === $case[1] && $body === $case[2], 'Route ' . $case[0]);
}

if (in_array('--integration', $argv, true)) {
    // Integration tests must never point at the live application database.
    $databaseName = getenv('DB_NAME');
    if (!is_string($databaseName) || !preg_match('/^[a-zA-Z0-9_]+_test$/D', $databaseName)) {
        $check(false, 'Integration requires an explicit database name ending in _test');
    } else {
        try {
            $db = Database::get();
            $tables = $db->query("SHOW TABLES WHERE Tables_in_" . $databaseName . " IN ('migration_probe', 'schema_migrations')");
            if ($tables->num_rows > 0) {
                fwrite(STDERR, "Reserved integration tables already exist; refusing to modify them.\n");
                exit(1);
            }
            $cleanupAllowed = true;
            $runner = new App\Core\MigrationRunner($db, __DIR__ . '/Fixtures/migrations');
            $check($runner->migrate() === ['001_probe.php'], 'Fresh migration executes');
            $check($runner->migrate() === [], 'Migration rerun is idempotent');
            $check($db->query("SHOW TABLES LIKE 'migration_probe'")->num_rows === 1, 'Migration created its table');
            $db->query("UPDATE schema_migrations SET checksum = REPEAT('0', 64) WHERE version = '001_probe.php'");
            try {
                $runner->migrate();
                $check(false, 'Migration checksum mismatch rejected');
            } catch (RuntimeException $error) {
                $check(true, 'Migration checksum mismatch rejected');
            }
        } catch (Throwable $error) {
            $check(false, 'Database integration execution');
        } finally {
            if (isset($db) && !empty($cleanupAllowed)) {
                $db->query('DROP TABLE IF EXISTS migration_probe');
                $db->query('DROP TABLE IF EXISTS schema_migrations');
            }
        }
    }
} else {
    echo "SKIP database integration (use --integration with a disposable *_test database)\n";
}
echo 'Passed: ' . $passed . '; failed: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
