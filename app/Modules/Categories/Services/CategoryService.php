<?php
declare(strict_types=1);

namespace App\Modules\Categories\Services;

use App\Modules\Categories\Repositories\CategoryRepository;
use App\Modules\Organizations\Repositories\OrganizationRepository;
use App\Shared\Exceptions\AuthorizationException;

final class CategoryService
{
    private CategoryRepository $repository;
    private OrganizationRepository $actors;
    public function __construct(CategoryRepository $repository, OrganizationRepository $actors)
    { $this->repository = $repository; $this->actors = $actors; }

    public function publicCategories(): array { return $this->repository->all(); }
    public function administration(int $actor): array
    {
        $this->authorize($actor);
        return $this->repository->all(true);
    }
    public function save(int $actor, int $id, int $version, string $name, string $slug, bool $active, array $capabilities): int
    {
        $this->authorize($actor);
        if ($id < 0 || ($id > 0 && $version < 1)) {
            throw new \InvalidArgumentException('Invalid category version.');
        }
        return $this->repository->save($actor, $id, $version, CategoryConfiguration::validate($name, $slug, $active, $capabilities));
    }
    private function authorize(int $actor): void
    {
        if (($this->actors->activeUser($actor)['platform_role'] ?? null) !== 'admin') {
            throw new AuthorizationException('Forbidden');
        }
    }
}
