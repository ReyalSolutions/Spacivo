<?php
declare(strict_types=1);

final class CategoryController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin']);
        if (getenv('CATEGORIES_ENABLED') !== 'true') {
            http_response_code(503); echo 'Category management is not enabled.'; return;
        }
        $service = new App\Modules\Categories\Services\CategoryService(
            new App\Modules\Categories\Repositories\CategoryRepository($this->db()),
            new App\Modules\Organizations\Repositories\OrganizationRepository($this->db())
        );
        $this->render('category/index', ['categories' => $service->administration((int)$_SESSION['user_id'])]);
    }
}
