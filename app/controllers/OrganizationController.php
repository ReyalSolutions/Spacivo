<?php
declare(strict_types=1);

final class OrganizationController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        if (getenv('ORGANIZATIONS_ENABLED') !== 'true') {
            http_response_code(503);
            echo 'Organization management is temporarily unavailable.';
            return;
        }
        $service = new App\Modules\Organizations\Services\OrganizationService(
            new App\Modules\Organizations\Repositories\OrganizationRepository($this->db())
        );
        $this->render('organization/index', $service->workspace((int)$_SESSION['user_id']));
    }
    private function api(string $action): void
    {
        if (!empty($_SESSION['user_id']) && !App\Modules\Identity\Services\SessionGuard::valid($this->db(), (int)$_SESSION['user_id'])) {
            $_SESSION = [];
        }
        $repository = new App\Modules\Organizations\Repositories\OrganizationRepository($this->db());
        $service = new App\Modules\Organizations\Services\OrganizationService($repository);
        (new App\Modules\Organizations\Controllers\Api\OrganizationController($service))->handle($action);
    }
    public function create(): void { $this->api('create'); }
    public function show(): void { $this->api('show'); }
    public function add_member(): void { $this->api('add_member'); }
    public function verify(): void { $this->api('verify'); }
}
