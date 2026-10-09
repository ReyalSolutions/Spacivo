<?php
declare(strict_types=1);
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET','HEAD'], true)) { http_response_code(405); exit('Use the canonical action endpoint.'); }
$_GET['url'] = 'admin/reports';
require __DIR__ . '/../index.php';
