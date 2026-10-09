<?php
declare(strict_types=1);

namespace App\Modules\Properties\Services;

use App\Modules\Properties\Repositories\PropertyRepository;
use App\Modules\Properties\Repositories\PropertyMetadataRepository;
use App\Modules\Organizations\Repositories\OrganizationRepository;
use App\Modules\Organizations\Policies\OrganizationPolicy;
use App\Shared\Exceptions\AuthorizationException;

final class PropertyMetadataService
{
    private PropertyRepository $properties;
    private PropertyMetadataRepository $metadata;
    private OrganizationRepository $organizations;
    private OrganizationPolicy $policy;
    public function __construct(PropertyRepository $properties, PropertyMetadataRepository $metadata, OrganizationRepository $organizations)
    { $this->properties = $properties; $this->metadata = $metadata; $this->organizations = $organizations; $this->policy = new OrganizationPolicy($organizations); }
    public function authorizeUpload(int $actor, int $organization, int $property, int $unit): void
    { $this->policy->authorize($actor, $organization, 'inventory.manage'); $this->target($organization, $property, $unit, true); }
    public function attachPhoto(int $actor, int $organization, int $property, int $unit, int $version, string $filename): int
    {
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $filename)) { throw new \InvalidArgumentException('Invalid photo reference.'); }
        return $this->write($actor, $organization, $property, $unit, $version, function () use ($organization, $property, $unit, $filename): int {
            if (count($this->metadata->photos($organization, $property, $unit)) >= 20) { throw new \InvalidArgumentException('Each property or unit supports up to 20 photos.'); }
            return $this->metadata->addPhoto($organization, $property, $unit, $filename);
        });
    }
    public function deletePhoto(int $actor, int $organization, int $property, int $unit, int $version, int $photo): string
    {
        return $this->write($actor, $organization, $property, $unit, $version, function () use ($organization, $property, $unit, $photo): string {
            $row = $this->metadata->photo($organization, $property, $unit, $photo);
            if ($row === null) { throw new AuthorizationException('Forbidden'); }
            $this->metadata->deletePhoto($organization, $property, $unit, $photo); return $row['filename'];
        });
    }
    public function setAmenities(int $actor, int $organization, int $property, int $unit, int $version, array $amenities): void
    {
        if (count($amenities) > 50) { throw new \InvalidArgumentException('Choose at most 50 amenities.'); }
        foreach ($amenities as $amenity) { if (!is_int($amenity) || $amenity < 1) { throw new \InvalidArgumentException('Invalid amenity selection.'); } }
        $this->write($actor, $organization, $property, $unit, $version, function () use ($organization, $property, $unit, $amenities): void {
            $this->metadata->replaceAmenities($organization, $property, $unit, array_values(array_unique($amenities)));
        });
    }
    public function details(int $actor, int $organization, int $property, int $unit = 0, bool $administrator = false): array
    {
        if ($administrator) {
            if (($this->organizations->activeUser($actor)['platform_role'] ?? null) !== 'admin') { throw new AuthorizationException('Forbidden'); }
        } else { $this->policy->authorize($actor, $organization, 'inventory.read'); }
        $this->target($organization, $property, $unit, false);
        return ['photos' => $this->metadata->photos($organization, $property, $unit), 'amenities' => $this->metadata->amenities($organization, $property, $unit)];
    }
    public function publicPhoto(int $property, int $unit, int $photo): string
    {
        // Public visibility is re-checked for every image request, including after suspension.
        $listing = $this->properties->publicFind($property);
        if ($listing === null) { throw new AuthorizationException('Forbidden'); }
        $organization = (int)$listing['organization_id'];
        if ($unit > 0) {
            $found = false;
            foreach ($this->properties->units($organization, $property) as $row) {
                if ((int)$row['id'] === $unit && $row['state'] === 'active' && $this->properties->activeCategory((int)$row['category_id'])) { $found = true; }
            }
            if (!$found) { throw new AuthorizationException('Forbidden'); }
        }
        $row = $this->metadata->photo($organization, $property, $unit, $photo);
        if ($row === null) { throw new AuthorizationException('Forbidden'); } return $row['filename'];
    }
    private function write(int $actor, int $organization, int $property, int $unit, int $version, callable $operation)
    {
        return $this->properties->transaction(function () use ($actor, $organization, $property, $unit, $version, $operation) {
            $this->properties->lockOrganization($organization);
            $this->policy->authorize($actor, $organization, 'inventory.manage');
            $row = $this->target($organization, $property, $unit, true);
            if ((int)$row['version'] !== $version) { throw new \InvalidArgumentException('The listing changed. Refresh before saving.'); }
            $result = $operation(); $this->properties->invalidateApproval($organization, $property); return $result;
        });
    }
    private function target(int $organization, int $property, int $unit, bool $editing): array
    {
        $row = $this->properties->find($organization, $property, $editing);
        if ($row === null) { throw new AuthorizationException('Forbidden'); }
        if ($editing && in_array($row['state'], ['suspended','archived'], true)) { throw new \InvalidArgumentException('This listing cannot be edited.'); }
        if ($unit > 0) {
            $found = false;
            foreach ($this->properties->units($organization, $property) as $unitRow) {
                if ((int)$unitRow['id'] === $unit && (!$editing || $unitRow['state'] === 'active')) { $found = true; }
            }
            if (!$found) { throw new AuthorizationException('Forbidden'); }
        }
        return $row;
    }
}
