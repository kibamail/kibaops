<?php

namespace App\Enums;

enum ClusterStatus: string
{
    case HEALTHY = 'Healthy';
    case UNHEALTHY = 'Unhealthy';
    case PENDING = 'Pending';

    public function label(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
