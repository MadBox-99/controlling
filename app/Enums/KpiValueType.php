<?php

declare(strict_types=1);

namespace App\Enums;

enum KpiValueType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
}
