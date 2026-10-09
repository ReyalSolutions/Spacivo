<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/core/Autoloader.php';
Autoloader::register();
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}
App\Core\ExceptionHandler::register(__DIR__ . '/../storage/logs');
App\Core\Environment::load(__DIR__ . '/../.env');

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}
if (PHP_SAPI !== 'cli' && !empty($_SESSION['user_id'])
    && !App\Modules\Identity\Services\SessionGuard::valid(Database::get(), (int)$_SESSION['user_id'])) {
    $_SESSION = [];
}
