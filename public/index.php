<?php
declare(strict_types=1);

// Public entry point for the SPA-like PHP MVC app.
require __DIR__ . '/../bootstrap/app.php';

$url = $_GET['url'] ?? 'home/index';
if (!is_string($url)) {
    http_response_code(400);
    echo 'Invalid route';
    return;
}

if (strpos($url, 'api/') === 0) {
    (new App\Core\ApiRouter())->dispatch($url);
    return;
}
$router = new Router($url, __DIR__ . '/../app/controllers');
$router->dispatch();

