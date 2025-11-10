<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SearchQuery;
use Carbon\CarbonInterface;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class SearchConsoleStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected $listeners = ['dateRangeUpdated' => '$refresh'];

    protected function getStats(): array
    {
        $startDate = $this->getStartDate();

        $stats = [
            'total_impressions' => SearchQuery::where('date', '>=', $startDate)->sum('impressions'),
            'total_clicks' => SearchQuery::where('date', '>=', $startDate)->sum('clicks'),
            'avg_ctr' => SearchQuery::where('date', '>=', $startDate)->avg('ctr') ?? 0,
            'avg_position' => SearchQuery::where('date', '>=', $startDate)->avg('position') ?? 0,
        ];

        return [
            Stat::make('Összes megjelenítés', $this->formatNumber((int) $stats['total_impressions']))
                ->description('Elmúlt 30 nap')
                ->color('info'),

            Stat::make('Összes kattintás', $this->formatNumber((int) $stats['total_clicks']))
                ->description('Elmúlt 30 nap')
                ->color('success'),

            Stat::make('Átlagos CTR', number_format((float) $stats['avg_ctr'], 2) . '%')
                ->description('Click-through rate')
                ->color('warning'),

            Stat::make('Átlagos pozíció', number_format((float) $stats['avg_position'], 1))
                ->description('Keresési eredmények')
                ->color('primary'),
        ];
    }

    private function formatNumber(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . ' M';
        }

        if ($number >= 1000) {
            return round($number / 1000, 1) . ' E';
        }

        return number_format($number);
    }

    private function getStartDate(): CarbonInterface
    {
        $dateRangeType = session('search_console_date_range', '28_days');

        return match ($dateRangeType) {
            '24_hours' => now()->subHours(24),
            '7_days' => now()->subDays(7),
            '28_days' => now()->subDays(28),
            '3_months' => now()->subMonths(3),
            default => now()->subDays(28),
        };
    }
}
