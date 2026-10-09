<?php
declare(strict_types=1);

namespace App\Core;

final class ApiRouter
{
    public function dispatch(string $path): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($path === 'api/v1/categories') { $action = 'public'; $id = null; }
        elseif ($path === 'api/v1/admin/categories') {
            $action = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' ? 'admin' : 'save'; $id = null;
        } elseif (preg_match('/^api\/v1\/admin\/categories\/([1-9][0-9]{0,8})$/D', $path, $matches)) {
            $action = 'save'; $id = (int)$matches[1];
        } else {
            http_response_code(404); echo json_encode(['success' => false, 'message' => 'API route not found']); return;
        }
        $db = \Database::get();
        $service = new \App\Modules\Categories\Services\CategoryService(
            new \App\Modules\Categories\Repositories\CategoryRepository($db),
            new \App\Modules\Organizations\Repositories\OrganizationRepository($db)
        );
        (new \App\Modules\Categories\Controllers\Api\CategoryController($service))->handle($action, $id, true);
    }
}
