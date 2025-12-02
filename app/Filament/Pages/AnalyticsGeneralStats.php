<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AnalyticsSortEnum;
use App\Enums\NavigationGroup;
use App\Filament\Widgets\GeneralStatsOverview;
use Filament\Pages\Page;
use UnitEnum;

final class AnalyticsGeneralStats extends Page
{
    protected string $view = 'filament.pages.analytics-general-stats';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = AnalyticsSortEnum::AnalyticsGeneralStats->value;

    protected static ?string $navigationLabel = 'General Analytics';

    protected static ?string $title = 'General Analytics Dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            GeneralStatsOverview::class,
        ];
    }
}
