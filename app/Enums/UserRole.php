<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UserRole: string implements HasColor, HasLabel
{
    case SuperAdmin = 'Super-Admin';
    case Admin = 'Admin';
    case Manager = 'manager';
    case Subscriber = 'subscriber';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::SuperAdmin => __('Super Admin'),
            self::Admin => __('Admin'),
            self::Manager => __('Manager'),
            self::Subscriber => __('Subscriber'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Admin => 'warning',
            self::Manager => 'info',
            self::Subscriber => 'success',
        };
    }
}
