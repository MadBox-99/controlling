<?php

declare(strict_types=1);

namespace App\Enums;

enum KpiGoalType: string
{
    case Increase = 'increase';
    case Decrease = 'decrease';
}
