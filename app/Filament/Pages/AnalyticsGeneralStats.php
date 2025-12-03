<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\GeneralStatsOverview;
use Filament\Pages\Page;
use UnitEnum;

final class AnalyticsGeneralStats extends Page
{
    protected string $view = 'filament.pages.analytics-general-stats';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = -5;

    public static function getNavigationLabel(): string
    {
        return __('General Analytics');
    }

    public function getTitle(): string
    {
        return __('General Analytics Dashboard');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GeneralStatsOverview::class,
        ];
    }
}
