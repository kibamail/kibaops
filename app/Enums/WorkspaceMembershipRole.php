<?php

namespace App\Enums;

enum WorkspaceMembershipRole: string
{
    case DEVELOPER = 'developer';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::DEVELOPER => 'Developer',
            self::ADMIN => 'Admin',
        };
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
