<?php
declare(strict_types=1);

namespace App\Modules\Properties\Controllers\Api;

use App\Modules\Properties\Services\PropertyMetadataService;
use App\Integrations\Storage\InventoryImageStorage;
use App\Shared\Exceptions\AuthorizationException;

final class PropertyMetadataController
{
    private PropertyMetadataService $service;
    private InventoryImageStorage $storage;
    public function __construct(PropertyMetadataService $service, InventoryImageStorage $storage)
    { $this->service = $service; $this->storage = $storage; }
    public function handle(string $action, string $method, int $property, int $unit, int $photo, string $audience): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (getenv('INVENTORY_ENABLED') !== 'true' || getenv('INVENTORY_METADATA_ENABLED') !== 'true') { $this->error(503, 'Listing photos and amenities are not enabled.'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== $method) { header('Allow: ' . $method); $this->error(405, 'Method Not Allowed'); return; }
        $actor = (int)($_SESSION['user_id'] ?? 0);
        if ($audience !== 'public' && $actor <= 0) { $this->error(401, 'Authentication required.'); return; }
        $stored = null;
        try {
            if ($audience === 'public') { $filename = $this->service->publicPhoto($property, $unit, $photo); }
            else {
                $input = $_GET;
                if ($method !== 'GET') {
                    $input = $action === 'upload' ? $_POST : json_decode(file_get_contents('php://input'), true);
                    if (!is_array($input)) { $this->error(400, 'Invalid request body.'); return; }
                    if (!\Csrf::verify($input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) { $this->error(403, 'Invalid CSRF token'); return; }
                }
                $organization = filter_var($input['organization_id'] ?? null, FILTER_VALIDATE_INT);
                $version = filter_var($input['version'] ?? 0, FILTER_VALIDATE_INT);
                if ($organization === false || $organization < 1 || $version === false) { throw new \InvalidArgumentException('Invalid organization or version.'); }
                if ($action === 'read') {
                    $details = $this->service->details($actor, $organization, $property, $unit, $audience === 'admin');
                    $filename = null;
                    foreach ($details['photos'] as $row) { if ((int)$row['id'] === $photo) { $filename = $row['filename']; } }
                    if ($filename === null) { throw new AuthorizationException('Forbidden'); }
                } elseif ($action === 'upload') {
                    $this->service->authorizeUpload($actor, $organization, $property, $unit);
                    $stored = $this->storage->storeUploaded($_FILES['photo'] ?? []);
                    $id = $this->service->attachPhoto($actor, $organization, $property, $unit, $version, $stored);
                    $stored = null;
                    http_response_code(201); echo json_encode(['success' => true, 'data' => ['id' => $id]]); return;
                } elseif ($action === 'delete') {
                    $filename = $this->service->deletePhoto($actor, $organization, $property, $unit, $version, $photo);
                    $this->storage->remove($filename); echo json_encode(['success' => true]); return;
                } else {
                    if (!is_array($input['amenities'] ?? null)) { throw new \InvalidArgumentException('Choose amenities.'); }
                    $this->service->setAmenities($actor, $organization, $property, $unit, $version, $input['amenities']);
                    echo json_encode(['success' => true]); return;
                }
            }
            $path = $this->storage->path($filename);
            if (!is_file($path)) { $this->error(404, 'Photo not found'); return; }
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            header('Content-Type: image/' . ($extension === 'jpg' ? 'jpeg' : $extension));
            header('X-Content-Type-Options: nosniff'); header('Cross-Origin-Resource-Policy: same-origin');
            header('Cache-Control: private, no-store'); header('Content-Length: ' . filesize($path));
            readfile($path);
        } catch (AuthorizationException $error) { $this->error(403, 'Forbidden'); }
        catch (\InvalidArgumentException $error) { $this->error(422, $error->getMessage()); }
        finally { if ($stored !== null) { $this->storage->remove($stored); } }
    }
    private function error(int $status, string $message): void
    { http_response_code($status); echo json_encode(['success' => false, 'message' => $message]); }
}
