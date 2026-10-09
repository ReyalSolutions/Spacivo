<?php
declare(strict_types=1);

final class PropertyController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        if (getenv('INVENTORY_ENABLED') !== 'true') { http_response_code(503); echo 'Inventory management is not enabled.'; return; }
        $organizations = new App\Modules\Organizations\Repositories\OrganizationRepository($this->db());
        $service = new App\Modules\Properties\Services\PropertyService(new App\Modules\Properties\Repositories\PropertyRepository($this->db()), $organizations);
        $actor = (int)$_SESSION['user_id'];
        if (($_SESSION['role'] ?? '') === 'admin') {
            $this->render('property/reviews', ['properties' => $service->administrativeListings($actor)]); return;
        }
        $organization = filter_var($_GET['organization_id'] ?? null, FILTER_VALIDATE_INT);
        if ($organization === false || $organization < 1) { http_response_code(422); echo 'Choose an organization from your workspace.'; return; }
        try {
            $properties = $service->list($actor, $organization);
            foreach ($properties as &$property) { $property = $service->show($actor, $organization, (int)$property['id']); }
            unset($property);
            $canManage = true;
            try { (new App\Modules\Organizations\Policies\OrganizationPolicy($organizations))->authorize($actor, $organization, 'inventory.manage'); }
            catch (App\Shared\Exceptions\AuthorizationException $error) { $canManage = false; }
            $this->render('property/index', ['properties' => $properties, 'organization_id' => $organization, 'can_manage' => $canManage,
                'categories' => (new App\Modules\Categories\Repositories\CategoryRepository($this->db()))->all()]);
        } catch (App\Shared\Exceptions\AuthorizationException $error) { http_response_code(403); echo 'Forbidden'; }
    }
}
