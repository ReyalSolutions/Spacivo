<?php
declare(strict_types=1);

final class PropertyController extends BaseController
{
    public function browse(): void
    {
        if (getenv('INVENTORY_ENABLED') !== 'true') { http_response_code(503); echo 'Space listings are not enabled.'; return; }
        $service = new App\Modules\Properties\Services\PropertyService(new App\Modules\Properties\Repositories\PropertyRepository($this->db()), new App\Modules\Organizations\Repositories\OrganizationRepository($this->db()));
        $metadata = getenv('INVENTORY_METADATA_ENABLED') === 'true' ? new App\Modules\Properties\Repositories\PropertyMetadataRepository($this->db()) : null;
        header('Cache-Control: no-store');
        $this->render('property/browse', ['properties' => $service->marketplace($metadata)]);
    }
    public function index(): void
    {
        $this->requireLogin();
        if (getenv('INVENTORY_ENABLED') !== 'true') { http_response_code(503); echo 'Inventory management is not enabled.'; return; }
        $organizations = new App\Modules\Organizations\Repositories\OrganizationRepository($this->db());
        $service = new App\Modules\Properties\Services\PropertyService(new App\Modules\Properties\Repositories\PropertyRepository($this->db()), $organizations);
        $actor = (int)$_SESSION['user_id'];
        $metadata = null;
        if (getenv('INVENTORY_METADATA_ENABLED') === 'true') {
            $metadata = new App\Modules\Properties\Services\PropertyMetadataService(new App\Modules\Properties\Repositories\PropertyRepository($this->db()), new App\Modules\Properties\Repositories\PropertyMetadataRepository($this->db()), $organizations);
        }
        if (($_SESSION['role'] ?? '') === 'admin') {
            $properties = $service->administrativeListings($actor);
            $this->withMetadata($properties, $metadata, $actor, true);
            $this->render('property/reviews', ['properties' => $properties]); return;
        }
        $organization = filter_var($_GET['organization_id'] ?? null, FILTER_VALIDATE_INT);
        if ($organization === false || $organization < 1) { http_response_code(422); echo 'Choose an organization from your workspace.'; return; }
        try {
            $properties = $service->list($actor, $organization);
            foreach ($properties as &$property) { $property = $service->show($actor, $organization, (int)$property['id']); }
            unset($property);
            $this->withMetadata($properties, $metadata, $actor, false);
            $canManage = true;
            try { (new App\Modules\Organizations\Policies\OrganizationPolicy($organizations))->authorize($actor, $organization, 'inventory.manage'); }
            catch (App\Shared\Exceptions\AuthorizationException $error) { $canManage = false; }
            $this->render('property/index', ['properties' => $properties, 'organization_id' => $organization, 'can_manage' => $canManage,
                'categories' => (new App\Modules\Categories\Repositories\CategoryRepository($this->db()))->all(), 'amenity_options' => (new Amenity($this->db()))->all()]);
        } catch (App\Shared\Exceptions\AuthorizationException $error) { http_response_code(403); echo 'Forbidden'; }
    }
    private function withMetadata(array &$properties, ?App\Modules\Properties\Services\PropertyMetadataService $metadata, int $actor, bool $admin): void
    {
        if ($metadata === null) { return; }
        foreach ($properties as &$property) {
            $organization = (int)$property['organization_id']; $id = (int)$property['id'];
            $property['metadata'] = $metadata->details($actor, $organization, $id, 0, $admin);
            foreach ($property['units'] as &$unit) { $unit['metadata'] = $metadata->details($actor, $organization, $id, (int)$unit['id'], $admin); }
            unset($unit);
        }
        unset($property);
    }
}
