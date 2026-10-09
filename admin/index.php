<?php
declare(strict_types=1);
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET','HEAD'], true)) { http_response_code(405); exit; }
$_GET['url'] = 'admin/index';
require __DIR__ . '/../index.php';
