<?php

namespace App\Enums;

enum CloudProviderType: string
{
    case AWS = 'aws';
    case HETZNER = 'hetzner';
    case LEASEWEB = 'leaseweb';
    case GOOGLE_CLOUD = 'google_cloud';
    case DIGITAL_OCEAN = 'digital_ocean';
    case LINODE = 'linode';
    case VULTR = 'vultr';

    /**
     * Get the human-readable label for the cloud provider type.
     */
    public function label(): string
    {
        return match ($this) {
            self::AWS => 'Amazon Web Services',
            self::HETZNER => 'Hetzner Cloud',
            self::LEASEWEB => 'LeaseWeb',
            self::GOOGLE_CLOUD => 'Google Cloud Platform',
            self::DIGITAL_OCEAN => 'DigitalOcean',
            self::LINODE => 'Linode',
            self::VULTR => 'Vultr',
        };
    }

    /**
     * Get all available cloud provider type values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all cloud provider types formatted as options for form selects.
     * Returns an array with 'value' and 'label' keys for each type.
     */
    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    /**
     * Get the list of cloud provider types that have been implemented.
     * Only these types can be used for credential verification.
     */
    public static function implemented(): array
    {
        return [
            self::HETZNER,
            self::DIGITAL_OCEAN,
        ];
    }
}
