<?php
declare(strict_types=1);

// Local test server: reproduce the existing /tenant Apache mount.
$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/tenant/') !== 0) {
    http_response_code(404);
    return;
}
$relative = substr($path, strlen('/tenant/'));
if ($relative === '' || $relative === 'index.php') {
    require $root . '/index.php';
    return;
}
if (preg_match('/^admin\/[a-z_]+\.php$/D', $relative) && is_file($root . '/' . $relative)) {
    $_SERVER['PHP_SELF'] = '/tenant/' . $relative;
    require $root . '/' . $relative;
    return;
}
http_response_code(404);
