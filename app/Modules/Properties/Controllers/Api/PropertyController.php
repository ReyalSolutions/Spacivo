<?php
declare(strict_types=1);

namespace App\Modules\Properties\Controllers\Api;

use App\Modules\Properties\Services\PropertyService;
use App\Shared\Exceptions\AuthorizationException;

final class PropertyController
{
    private PropertyService $service;
    private ?\App\Modules\Properties\Services\PropertyMetadataService $metadata;
    public function __construct(PropertyService $service, ?\App\Modules\Properties\Services\PropertyMetadataService $metadata = null)
    { $this->service = $service; $this->metadata = $metadata; }
    public function handle(string $action, string $method, int $property = 0, int $unit = 0): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (getenv('INVENTORY_ENABLED') !== 'true') { $this->error(503, 'Inventory management is not enabled.'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== $method) { header('Allow: ' . $method); $this->error(405, 'Method Not Allowed'); return; }
        $actor = (int)($_SESSION['user_id'] ?? 0);
        if ($action !== 'public' && $actor <= 0) { $this->error(401, 'Authentication required.'); return; }
        try {
            if ($action === 'public') { $data = $this->service->publicListings(); }
            elseif ($action === 'reviews') { $data = $this->service->pendingReviews($actor); }
            elseif ($action === 'administration') { $data = $this->service->administrativeListings($actor); }
            else {
                $input = $_GET;
                if ($method !== 'GET') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!is_array($input) || json_last_error() !== JSON_ERROR_NONE) { $this->error(400, 'Invalid JSON body.'); return; }
                    if (!\Csrf::verify($input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) { $this->error(403, 'Invalid CSRF token'); return; }
                }
                $organization = filter_var($input['organization_id'] ?? null, FILTER_VALIDATE_INT);
                $version = filter_var($input['version'] ?? 0, FILTER_VALIDATE_INT);
                if ($organization === false || $organization < 1 || $version === false) { throw new \InvalidArgumentException('Invalid organization or version.'); }
                $data = null;
                if ($action === 'list') { $data = $this->service->list($actor, $organization); }
                elseif ($action === 'show') {
                    $data = $this->service->show($actor, $organization, $property);
                    if ($this->metadata !== null) {
                        $data['metadata'] = $this->metadata->details($actor, $organization, $property);
                        foreach ($data['units'] as &$row) { $row['metadata'] = $this->metadata->details($actor, $organization, $property, (int)$row['id']); }
                        unset($row);
                    }
                }
                elseif ($action === 'save') { $data = ['id' => $this->service->save($actor, $organization, $property, $version, $input)]; }
                elseif ($action === 'unit_save') { $data = ['id' => $this->service->saveUnit($actor, $organization, $property, $unit, $version, $input)]; }
                elseif ($action === 'unit_archive') { $this->service->archiveUnit($actor, $organization, $property, $unit, $version); }
                elseif ($action === 'state') {
                    if (!is_string($input['state'] ?? null)) { throw new \InvalidArgumentException('Listing state is required.'); }
                    $this->service->setState($actor, $organization, $property, $version, $input['state']);
                } elseif ($action === 'review') {
                    if (!is_string($input['decision'] ?? null)) { throw new \InvalidArgumentException('Review decision is required.'); }
                    $this->service->review($actor, $organization, $property, $version, $input['decision']);
                } elseif ($action === 'moderate') {
                    if (!is_string($input['state'] ?? null)) { throw new \InvalidArgumentException('Moderation state is required.'); }
                    $this->service->moderate($actor, $organization, $property, $version, $input['state']);
                }
            }
            http_response_code(($action === 'save' && $property === 0) || ($action === 'unit_save' && $unit === 0) ? 201 : 200);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (AuthorizationException $error) { $this->error(403, 'Forbidden'); }
        catch (\InvalidArgumentException $error) { $this->error(422, $error->getMessage()); }
    }
    private function error(int $status, string $message): void
    { http_response_code($status); echo json_encode(['success' => false, 'message' => $message]); }
}
