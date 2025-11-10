<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use BezhanSalleh\GoogleAnalytics\Widgets\ActiveUsersOneDayWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\ActiveUsersSevenDayWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\ActiveUsersTwentyEightDayWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\MostVisitedPagesWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\PageViewsWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\SessionsByCountryWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\SessionsByDeviceWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\SessionsDurationWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\SessionsWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\TopReferrersListWidget;
use BezhanSalleh\GoogleAnalytics\Widgets\VisitorsWidget;
use Filament\Pages\Page;
use UnitEnum;

final class MyAnalyticsDashboard extends Page
{
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Google Analytics Dashboard';

    protected static ?string $title = 'Google Analytics Dashboard';

    protected string $view = 'filament.pages.my-analytics-dashboard';

    public function getHeaderWidgetsColumns(): int
    {
        return 2; // 3 oszlopos grid layout
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PageViewsWidget::class,
            VisitorsWidget::class,
            ActiveUsersOneDayWidget::class,
            ActiveUsersSevenDayWidget::class,
            ActiveUsersTwentyEightDayWidget::class,
            SessionsWidget::class,
            SessionsByCountryWidget::class,
            SessionsDurationWidget::class,
            SessionsByDeviceWidget::class,
            MostVisitedPagesWidget::class,
            TopReferrersListWidget::class,
        ];
    }
}
