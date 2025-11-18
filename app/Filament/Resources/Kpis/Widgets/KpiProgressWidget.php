<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis\Widgets;

use App\Models\AnalyticsPageview;
use App\Models\Kpi;
use App\Models\SearchPage;
use App\Models\SearchQuery;
use DateTimeInterface;
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

        // Get current period value
        $currentValue = $this->getCurrentValue($kpi);

        // Get comparison period value
        $comparisonValue = $this->getComparisonValue($kpi);

        // Calculate change percentage
        $changePercentage = 0;
        if ($comparisonValue > 0) {
            $changePercentage = (($currentValue - $comparisonValue) / $comparisonValue) * 100;
        }

        $targetValue = (float) ($kpi->target_value ?? 0);
        $progress = $targetValue > 0 ? ($currentValue / $targetValue) * 100 : 0;

        $daysUntilTarget = $kpi->target_date ? (int) now()->diffInDays($kpi->target_date, false) : null;

        return [
            Stat::make('Current Value', number_format($currentValue, 2))
                ->description($kpi->category->value)
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),

            Stat::make('Comparison Value', number_format($comparisonValue, 2))
                ->description(($changePercentage > 0 ? '+' : '') . number_format($changePercentage, 1) . '% change')
                ->descriptionIcon($changePercentage > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($changePercentage > 0 ? 'success' : ($changePercentage < 0 ? 'danger' : 'gray')),

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

        if (! $kpi->from_date || ! $kpi->target_date) {
            return 0;
        }

        return $this->getValueForPeriod($kpi, $kpi->from_date, $kpi->target_date);
    }

    private function getComparisonValue(Kpi $kpi): float
    {
        if (! $kpi->page_path && ! $kpi->metric_type) {
            return 0;
        }

        if (! $kpi->comparison_start_date || ! $kpi->comparison_end_date) {
            return 0;
        }

        return $this->getValueForPeriod($kpi, $kpi->comparison_start_date, $kpi->comparison_end_date);
    }

    private function getValueForPeriod(Kpi $kpi, DateTimeInterface $startDate, DateTimeInterface $endDate): float
    {
        // Search Console data
        if ($kpi->data_source->value === 'search_console') {
            // Check if it's a query or page source
            if ($kpi->source_type === 'query') {
                $query = SearchQuery::query()
                    ->where('team_id', $kpi->team_id)
                    ->where('query', $kpi->page_path) // page_path stores the query text for queries
                    ->whereBetween('date', [$startDate, $endDate]);
            } else {
                $query = SearchPage::query()
                    ->where('team_id', $kpi->team_id)
                    ->where('page_url', $kpi->page_path)
                    ->whereBetween('date', [$startDate, $endDate]);
            }

            // For metrics that should be averaged (like CTR, position)
            if (in_array($kpi->metric_type, ['ctr', 'position'])) {
                return (float) $query->avg($kpi->metric_type) ?? 0;
            }

            // For metrics that should be summed (like impressions, clicks)
            return (float) $query->sum($kpi->metric_type) ?? 0;
        }

        // Analytics data
        if ($kpi->data_source->value === 'analytics') {
            $query = AnalyticsPageview::query()
                ->where('team_id', $kpi->team_id)
                ->where('page_path', $kpi->page_path)
                ->whereBetween('date', [$startDate, $endDate]);

            // For metrics that should be averaged (like bounce_rate)
            if (in_array($kpi->metric_type, ['bounce_rate'])) {
                return (float) $query->avg($kpi->metric_type) ?? 0;
            }

            // For metrics that should be summed (like pageviews, unique_pageviews)
            return (float) $query->sum($kpi->metric_type) ?? 0;
        }

        return 0;
    }
}
