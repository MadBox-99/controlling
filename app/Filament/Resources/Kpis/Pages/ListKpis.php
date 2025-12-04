<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis\Pages;

use App\Filament\Pages\AnalyticsGeneralStats;
use App\Filament\Pages\SearchConsoleGeneralStats;
use App\Filament\Resources\Kpis\KpiResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

final class ListKpis extends ListRecords
{
    protected static string $resource = KpiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('google_analytics_kpi')
                ->url(AnalyticsGeneralStats::getUrl()),
            Action::make('google_search_console_kpi')
                ->url(SearchConsoleGeneralStats::getUrl()),
        ];
    }
}
