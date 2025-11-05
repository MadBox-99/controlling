<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SearchQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class SearchConsoleStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        $stats = [
            'total_impressions' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->sum('impressions'),
            'total_clicks' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->sum('clicks'),
            'avg_ctr' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->avg('ctr') ?? 0,
            'avg_position' => SearchQuery::where('date', '>=', $thirtyDaysAgo)->avg('position') ?? 0,
        ];

        return [
            Stat::make('Összes megjelenítés', $this->formatNumber((int) $stats['total_impressions']))
                ->description('Elmúlt 30 nap')
                ->color('info'),

            Stat::make('Összes kattintás', $this->formatNumber((int) $stats['total_clicks']))
                ->description('Elmúlt 30 nap')
                ->color('success'),

            Stat::make('Átlagos CTR', number_format((float) $stats['avg_ctr'], 2).'%')
                ->description('Click-through rate')
                ->color('warning'),

            Stat::make('Átlagos pozíció', number_format((float) $stats['avg_position'], 1))
                ->description('Keresési eredmények')
                ->color('primary'),
        ];
    }

    protected function formatNumber(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1).' M';
        }

        if ($number >= 1000) {
            return round($number / 1000, 1).' E';
        }

        return number_format($number);
    }
}
