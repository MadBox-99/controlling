<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis\Widgets;

use App\Models\AnalyticsPageview;
use App\Models\Kpi;
use App\Models\SearchPage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

final class KpiProgressWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record instanceof Kpi) {
            return [];
        }

        $kpi = $this->record;

        // Get current value based on data source
        $currentValue = $this->getCurrentValue($kpi);

        $targetValue = (float) ($kpi->target_value ?? 0);
        $progress = $targetValue > 0 ? ($currentValue / $targetValue) * 100 : 0;

        $daysUntilTarget = $kpi->target_date ? (int) now()->diffInDays($kpi->target_date, false) : null;

        return [
            Stat::make('Current Value', number_format($currentValue, 2))
                ->description($kpi->category->value)
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),

            Stat::make('Target Value', number_format($targetValue, 2))
                ->description($kpi->goal_type?->value ?? 'No goal set')
                ->descriptionIcon('heroicon-o-flag')
                ->color('success'),

            Stat::make('Progress', number_format($progress, 1) . '%')
                ->description($progress >= 100 ? 'Target achieved!' : 'towards goal')
                ->descriptionIcon($progress >= 100 ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-trending-up')
                ->color($progress >= 100 ? 'success' : ($progress >= 75 ? 'warning' : 'danger')),

            Stat::make('Days Until Target', $daysUntilTarget !== null ? abs($daysUntilTarget) : 'No target date')
                ->description($daysUntilTarget !== null ? ($daysUntilTarget >= 0 ? 'days remaining' : 'days overdue') : '')
                ->descriptionIcon($daysUntilTarget !== null ? ($daysUntilTarget >= 0 ? 'heroicon-o-calendar' : 'heroicon-o-exclamation-triangle') : 'heroicon-o-calendar')
                ->color($daysUntilTarget !== null ? ($daysUntilTarget >= 0 ? 'success' : 'danger') : 'gray'),
        ];
    }

    private function getCurrentValue(Kpi $kpi): float
    {
        if (! $kpi->page_path && ! $kpi->metric_type) {
            return 0;
        }

        // Search Console data
        if ($kpi->data_source->value === 'search_console') {
            $searchData = SearchPage::query()
                ->where('page_url', $kpi->page_path)
                ->latest('date')
                ->first();

            if ($searchData) {
                return (float) match ($kpi->metric_type) {
                    'impressions' => $searchData->impressions,
                    'clicks' => $searchData->clicks,
                    'ctr' => $searchData->ctr,
                    'position' => $searchData->position,
                    default => 0,
                };
            }

            return 0;
        }

        // Analytics data
        if ($kpi->data_source->value === 'analytics') {
            $analyticsData = AnalyticsPageview::query()
                ->where('page_path', $kpi->page_path)
                ->latest('date')
                ->first();

            if ($analyticsData) {
                return (float) match ($kpi->metric_type) {
                    'pageviews' => $analyticsData->pageviews,
                    'unique_pageviews' => $analyticsData->unique_pageviews,
                    'bounce_rate' => $analyticsData->bounce_rate,
                    default => 0,
                };
            }

            return 0;
        }

        return 0;
    }
}
