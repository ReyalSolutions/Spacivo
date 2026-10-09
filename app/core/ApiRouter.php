<?php
declare(strict_types=1);

namespace App\Core;

final class ApiRouter
{
    public function dispatch(string $path): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($this->inventory($path)) { return; }
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

    private function inventory(string $path): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $property = 0; $unit = 0;
        if ($path === 'api/v1/properties') { $action = 'public'; $expected = 'GET'; }
        elseif ($path === 'api/v1/owner/properties') {
            $action = $method === 'GET' ? 'list' : 'save'; $expected = $method === 'GET' ? 'GET' : 'POST';
        } elseif (preg_match('/^api\/v1\/owner\/properties\/([1-9][0-9]{0,8})(?:\/(state|units)(?:\/([1-9][0-9]{0,8}))?)?$/D', $path, $matches)) {
            $property = (int)$matches[1];
            if (($matches[2] ?? '') === 'state' && empty($matches[3])) { $action = 'state'; $expected = 'POST'; }
            elseif (($matches[2] ?? '') === 'units') {
                $unit = (int)($matches[3] ?? 0);
                $action = $method === 'DELETE' && $unit > 0 ? 'unit_archive' : 'unit_save';
                $expected = $action === 'unit_archive' ? 'DELETE' : ($unit > 0 ? 'PATCH' : 'POST');
            } elseif (empty($matches[2])) {
                $action = $method === 'GET' ? 'show' : 'save'; $expected = $method === 'GET' ? 'GET' : 'PATCH';
            } else { return false; }
        } elseif ($path === 'api/v1/admin/properties/pending') { $action = 'reviews'; $expected = 'GET'; }
        elseif ($path === 'api/v1/admin/properties') { $action = 'administration'; $expected = 'GET'; }
        elseif (preg_match('/^api\/v1\/admin\/properties\/([1-9][0-9]{0,8})\/(review|moderate)$/D', $path, $matches)) {
            $action = $matches[2]; $expected = 'POST'; $property = (int)$matches[1];
        } else { return false; }
        $db = \Database::get();
        $service = new \App\Modules\Properties\Services\PropertyService(
            new \App\Modules\Properties\Repositories\PropertyRepository($db),
            new \App\Modules\Organizations\Repositories\OrganizationRepository($db)
        );
        (new \App\Modules\Properties\Controllers\Api\PropertyController($service))->handle($action, $expected, $property, $unit);
        return true;
    }
}
