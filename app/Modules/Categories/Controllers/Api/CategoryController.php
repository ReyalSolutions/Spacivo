<?php
declare(strict_types=1);

namespace App\Modules\Categories\Controllers\Api;

use App\Modules\Categories\Services\CategoryService;
use App\Shared\Exceptions\AuthorizationException;

final class CategoryController
{
    private CategoryService $service;
    public function __construct(CategoryService $service) { $this->service = $service; }

    public function handle(string $action, ?int $routeId = null, bool $json = false): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (getenv('CATEGORIES_ENABLED') !== 'true') { $this->respond(503, 'Category management is not enabled.'); return; }
        $expected = $action === 'save' ? ($routeId === null ? 'POST' : 'PATCH') : 'GET';
        if ($_SERVER['REQUEST_METHOD'] !== $expected) {
            header('Allow: ' . $expected); $this->respond(405, 'Method Not Allowed'); return;
        }
        $actor = (int)($_SESSION['user_id'] ?? 0);
        if ($action !== 'public' && $actor <= 0) { $this->respond(401, 'Authentication required.'); return; }
        try {
            if ($action === 'public') { $data = $this->service->publicCategories(); }
            elseif ($action === 'admin') { $data = $this->service->administration($actor); }
            else {
                $input = $_POST;
                if ($json) {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!is_array($input) || json_last_error() !== JSON_ERROR_NONE) { $this->respond(400, 'Invalid JSON body.'); return; }
                }
                if (!\Csrf::verify($input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) {
                    $this->respond(403, 'Invalid CSRF token'); return;
                }
                $id = $routeId ?? filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT);
                $version = filter_var($input['version'] ?? 0, FILTER_VALIDATE_INT);
                $active = $input['active'] ?? false;
                $capabilities = $input['capabilities'] ?? [];
                if ($id === false || $version === false || !is_string($input['name'] ?? null)
                    || !is_string($input['slug'] ?? null) || !is_bool($active) || !is_array($capabilities)) {
                    throw new \InvalidArgumentException('Invalid category configuration.');
                }
                $data = ['id' => $this->service->save($actor, $id, $version, $input['name'], $input['slug'], $active, $capabilities)];
            }
            http_response_code($action === 'save' && ($id ?? 0) === 0 ? 201 : 200);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (AuthorizationException $error) { $this->respond(403, 'Forbidden'); }
        catch (\InvalidArgumentException $error) { $this->respond(422, $error->getMessage()); }
    }
    private function respond(int $status, string $message): void
    { http_response_code($status); echo json_encode(['success' => false, 'message' => $message]); }
}
