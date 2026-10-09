<?php
declare(strict_types=1);

namespace App\Core;

final class ApiRouter
{
    public function dispatch(string $path): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($this->metadata($path)) { return; }
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
        $metadata = null;
        if (getenv('INVENTORY_METADATA_ENABLED') === 'true') {
            $metadata = new \App\Modules\Properties\Services\PropertyMetadataService(new \App\Modules\Properties\Repositories\PropertyRepository($db), new \App\Modules\Properties\Repositories\PropertyMetadataRepository($db), new \App\Modules\Organizations\Repositories\OrganizationRepository($db));
        }
        (new \App\Modules\Properties\Controllers\Api\PropertyController($service, $metadata))->handle($action, $expected, $property, $unit);
        return true;
    }

    private function metadata(string $path): bool
    {
        if (!preg_match('/^api\/v1\/(owner\/|admin\/)?properties\/([1-9][0-9]{0,8})(?:\/units\/([1-9][0-9]{0,8}))?\/(photos|amenities)(?:\/([1-9][0-9]{0,8}))?$/D', $path, $matches)) { return false; }
        $audience = empty($matches[1]) ? 'public' : rtrim($matches[1], '/');
        $photo = (int)($matches[5] ?? 0);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($matches[4] === 'amenities' && $photo === 0 && $audience === 'owner') { $action = 'amenities'; $expected = 'PUT'; }
        elseif ($matches[4] === 'photos' && $photo > 0) {
            $action = $method === 'DELETE' && $audience === 'owner' ? 'delete' : 'read'; $expected = $action === 'delete' ? 'DELETE' : 'GET';
        } elseif ($matches[4] === 'photos' && $photo === 0 && $audience === 'owner') { $action = 'upload'; $expected = 'POST'; }
        else { return false; }
        $db = \Database::get();
        $service = new \App\Modules\Properties\Services\PropertyMetadataService(
            new \App\Modules\Properties\Repositories\PropertyRepository($db), new \App\Modules\Properties\Repositories\PropertyMetadataRepository($db),
            new \App\Modules\Organizations\Repositories\OrganizationRepository($db)
        );
        $storage = new \App\Integrations\Storage\InventoryImageStorage(getenv('INVENTORY_UPLOAD_DIR') ?: dirname(__DIR__, 2) . '/storage/private/inventory');
        (new \App\Modules\Properties\Controllers\Api\PropertyMetadataController($service, $storage))->handle($action, $expected, (int)$matches[2], (int)($matches[3] ?? 0), $photo, $audience);
        return true;
    }
}
