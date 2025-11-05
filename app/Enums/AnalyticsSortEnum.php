<?php

declare(strict_types=1);

namespace App\Enums;

enum AnalyticsSortEnum: int
{
    case SearchConsoleGeneralStats = -10;
    case AnalyticsGeneralStats = -5;
    case AnalyticsStats = 0;
    case AnalyticsSession = 5;
    case AnalyticsPageview = 10;
    case AnalyticsEvent = 20;
    case AnalyticsConversions = 50;

}
