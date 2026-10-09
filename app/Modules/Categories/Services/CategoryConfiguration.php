<?php
declare(strict_types=1);

namespace App\Modules\Categories\Services;

final class CategoryConfiguration
{
    public const CAPABILITIES = [
        'hourly_booking' => 'Hourly booking', 'nightly_booking' => 'Nightly booking',
        'monthly_rental' => 'Monthly rental', 'calendar_availability' => 'Calendar availability',
        'tenant_ledger' => 'Tenant ledger', 'check_in_out' => 'Check-in and check-out',
        'deposit' => 'Deposit', 'lease_contract' => 'Lease contract',
        'time_slot_reservation' => 'Time-slot reservation', 'instant_booking' => 'Instant booking',
    ];

    public static function validate(string $name, string $slug, bool $active, array $capabilities): array
    {
        $name = trim($name);
        if ($name === '' || strlen($name) > 100 || !preg_match('/^[a-z][a-z0-9_]{1,79}$/D', $slug)) {
            throw new \InvalidArgumentException('Enter a category name and a lowercase category code.');
        }
        foreach ($capabilities as $key => $enabled) {
            if (!array_key_exists($key, self::CAPABILITIES) || !is_bool($enabled)) {
                throw new \InvalidArgumentException('Invalid category capability.');
            }
        }
        $normalized = array_fill_keys(array_keys(self::CAPABILITIES), false);
        $normalized = array_replace($normalized, $capabilities);
        $hasMode = $normalized['hourly_booking'] || $normalized['nightly_booking'] || $normalized['monthly_rental'];
        if (($active || $normalized['instant_booking']) && !$hasMode) {
            throw new \InvalidArgumentException('Choose at least one rental mode before activating booking.');
        }
        if ($normalized['time_slot_reservation'] && (!$normalized['hourly_booking'] || !$normalized['calendar_availability'])) {
            throw new \InvalidArgumentException('Time slots require hourly booking and calendar availability.');
        }
        if ($normalized['lease_contract'] && !$normalized['monthly_rental']) {
            throw new \InvalidArgumentException('Lease contracts require monthly rental.');
        }
        return ['name' => $name, 'slug' => $slug, 'active' => $active, 'capabilities' => $normalized];
    }
}
