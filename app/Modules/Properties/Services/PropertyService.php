<?php
declare(strict_types=1);

namespace App\Modules\Properties\Services;

use App\Modules\Properties\Repositories\PropertyRepository;
use App\Modules\Organizations\Repositories\OrganizationRepository;
use App\Modules\Organizations\Policies\OrganizationPolicy;
use App\Shared\Exceptions\AuthorizationException;

final class PropertyService
{
    private PropertyRepository $repository;
    private OrganizationRepository $organizations;
    private OrganizationPolicy $policy;
    public function __construct(PropertyRepository $repository, OrganizationRepository $organizations)
    { $this->repository = $repository; $this->organizations = $organizations; $this->policy = new OrganizationPolicy($organizations); }
    public function list(int $actor, int $organization): array
    { $this->policy->authorize($actor, $organization, 'inventory.read'); return $this->repository->list($organization); }
    public function show(int $actor, int $organization, int $id): array
    {
        $this->policy->authorize($actor, $organization, 'inventory.read');
        $property = $this->required($organization, $id);
        $property['units'] = $this->repository->units($organization, $id); return $property;
    }
    public function save(int $actor, int $organization, int $id, int $version, array $input): int
    {
        $this->policy->authorize($actor, $organization, 'inventory.manage');
        $data = ['category_id' => self::positiveInt($input['category_id'] ?? null),
            'name' => self::text($input['name'] ?? null, 150), 'description' => self::text($input['description'] ?? null, 10000),
            'address' => self::text($input['address'] ?? null, 500), 'timezone' => self::text($input['timezone'] ?? null, 64)];
        if (!in_array($data['timezone'], \DateTimeZone::listIdentifiers(), true)) { throw new \InvalidArgumentException('Choose a valid timezone.'); }
        $latitude = $input['latitude'] ?? null; $longitude = $input['longitude'] ?? null;
        if ($latitude === '' && $longitude === '') { $latitude = null; $longitude = null; }
        if (($latitude === null) !== ($longitude === null) || ($latitude !== null &&
            (!is_numeric($latitude) || !is_numeric($longitude) || !is_finite((float)$latitude) || !is_finite((float)$longitude)
                || (float)$latitude < -90 || (float)$latitude > 90 || (float)$longitude < -180 || (float)$longitude > 180))) {
            throw new \InvalidArgumentException('Enter valid latitude and longitude together.');
        }
        $data['latitude'] = $latitude; $data['longitude'] = $longitude;
        return $this->write($actor, $organization, function () use ($organization, $id, $version, $data): int {
            if ($id < 0 || ($id > 0 && $version < 1)) { throw new \InvalidArgumentException('Invalid listing version.'); }
            if ($id > 0) { $this->editable($organization, $id); }
            $this->category($data['category_id']);
            return $this->repository->save($organization, $id, $version, $data);
        });
    }
    public function saveUnit(int $actor, int $organization, int $property, int $id, int $version, array $input): int
    {
        $this->policy->authorize($actor, $organization, 'inventory.manage');
        $data = ['category_id' => self::positiveInt($input['category_id'] ?? null),
            'name' => self::text($input['name'] ?? null, 150), 'capacity' => self::positiveInt($input['capacity'] ?? null)];
        if ($data['capacity'] > 100000 || $id < 0 || ($id > 0 && $version < 1)) { throw new \InvalidArgumentException('Invalid unit capacity or version.'); }
        return $this->write($actor, $organization, function () use ($organization, $property, $id, $version, $data): int {
            $this->editable($organization, $property); $this->category($data['category_id']);
            $unitId = $this->repository->saveUnit($organization, $property, $id, $version, $data);
            $this->repository->invalidateApproval($organization, $property); return $unitId;
        });
    }
    public function archiveUnit(int $actor, int $organization, int $property, int $unit, int $version): void
    {
        $this->write($actor, $organization, function () use ($organization, $property, $unit, $version): void {
            $this->editable($organization, $property);
            $this->repository->archiveUnit($organization, $property, $unit, $version);
            $this->repository->invalidateApproval($organization, $property);
        });
    }
    public function setState(int $actor, int $organization, int $property, int $version, string $state): void
    {
        $this->write($actor, $organization, function () use ($organization, $property, $version, $state): void {
            $listing = $this->editable($organization, $property);
            if (!in_array($state, ['draft','published','archived'], true)) { throw new \InvalidArgumentException('Invalid listing state.'); }
            if ($state === 'published') {
                $organizationRow = $this->organizations->find($organization);
                if ($organizationRow['verification_status'] !== 'verified' || $listing['approval_status'] !== 'approved') {
                    throw new \InvalidArgumentException('Publishing requires a verified organization and approved listing.');
                }
                $this->category((int)$listing['category_id']);
                $eligibleUnit = false;
                foreach ($this->repository->units($organization, $property) as $unit) {
                    if ($unit['state'] === 'active' && $this->repository->activeCategory((int)$unit['category_id'])) { $eligibleUnit = true; }
                }
                if (!$eligibleUnit) { throw new \InvalidArgumentException('Add an active unit before publishing.'); }
            }
            $this->repository->state($organization, $property, $version, $state);
        });
    }
    public function pendingReviews(int $actor): array
    { $this->administrator($actor); return $this->reviewDetails($this->repository->pendingReviews()); }
    public function administrativeListings(int $actor): array
    { $this->administrator($actor); return $this->reviewDetails($this->repository->administrativeListings()); }
    private function reviewDetails(array $properties): array
    {
        foreach ($properties as &$property) { $property['units'] = $this->repository->units((int)$property['organization_id'], (int)$property['id']); }
        unset($property); return $properties;
    }
    public function moderate(int $actor, int $organization, int $property, int $version, string $state): void
    {
        $this->repository->transaction(function () use ($actor, $organization, $property, $version, $state): void {
            $this->repository->lockOrganization($organization); $this->administrator($actor); $this->required($organization, $property, true);
            if (!in_array($state, ['suspended','draft'], true)) { throw new \InvalidArgumentException('Invalid moderation state.'); }
            $this->repository->moderate($organization, $property, $version, $state);
        });
    }
    public function review(int $actor, int $organization, int $property, int $version, string $decision): void
    {
        $this->repository->transaction(function () use ($actor, $organization, $property, $version, $decision): void {
            $this->repository->lockOrganization($organization); $this->administrator($actor); $this->required($organization, $property, true);
            if (!in_array($decision, ['approved','rejected'], true)) { throw new \InvalidArgumentException('Invalid review decision.'); }
            $this->repository->review($organization, $property, $version, $decision, $actor);
        });
    }
    public function publicListings(): array { return $this->repository->publicListings(); }
    public function marketplace(?\App\Modules\Properties\Repositories\PropertyMetadataRepository $metadata = null): array
    {
        $listings = $this->repository->publicListings();
        if ($metadata !== null) {
            foreach ($listings as &$listing) {
                $scope = $this->repository->publicFind((int)$listing['id']);
                if ($scope === null) { continue; }
                $photos = $metadata->photos((int)$scope['organization_id'], (int)$listing['id']);
                $listing['cover_photo_id'] = (int)($photos[0]['id'] ?? 0);
            }
            unset($listing);
        }
        return $listings;
    }
    private function write(int $actor, int $organization, callable $operation)
    {
        return $this->repository->transaction(function () use ($actor, $organization, $operation) {
            $this->repository->lockOrganization($organization);
            $this->policy->authorize($actor, $organization, 'inventory.manage');
            return $operation();
        });
    }
    private function editable(int $organization, int $property): array
    {
        $row = $this->required($organization, $property, true);
        if (in_array($row['state'], ['suspended','archived'], true)) { throw new \InvalidArgumentException('This listing cannot be edited.'); }
        return $row;
    }
    private function required(int $organization, int $property, bool $lock = false): array
    {
        $row = $this->repository->find($organization, $property, $lock);
        if ($row === null) { throw new AuthorizationException('Forbidden'); } return $row;
    }
    private function category(int $category): void
    { if (!$this->repository->activeCategory($category)) { throw new \InvalidArgumentException('Choose an active category.'); } }
    private function administrator(int $actor): void
    { if (($this->organizations->activeUser($actor)['platform_role'] ?? null) !== 'admin') { throw new AuthorizationException('Forbidden'); } }
    private static function text($value, int $maximum): string
    {
        if (!is_string($value) || trim($value) === '' || strlen(trim($value)) > $maximum) { throw new \InvalidArgumentException('Required listing details are missing or too long.'); }
        return trim($value);
    }
    private static function positiveInt($value): int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);
        if ($integer === false || $integer < 1) { throw new \InvalidArgumentException('Enter a positive identifier or capacity.'); } return $integer;
    }
}
