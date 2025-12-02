<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsPageview;
use App\Models\Kpi;
use App\Models\SearchPage;
use App\Models\SearchQuery;
use DateTimeInterface;
use Filament\Widgets\Widget;

final class KpiChartWidget extends Widget
{
    public ?int $selectedKpiId = null;

    protected string $view = 'filament.widgets.kpi-chart-widget';

    protected int|string|array $columnSpan = 1;

    public function mount(): void
    {
        // Default to first active KPI
        if (! $this->selectedKpiId) {
            $this->selectedKpiId = Kpi::query()
                ->where('is_active', true)
                ->first()?->id;
        }
    }

    public function getKpis()
    {
        return Kpi::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    public function getSelectedKpi()
    {
        if (! $this->selectedKpiId) {
            return;
        }

        return Kpi::query()->find($this->selectedKpiId);
    }

    public function getKpiData(): ?array
    {
        if (! $this->selectedKpiId) {
            return null;
        }

        $kpi = $this->getSelectedKpi();

        if (! $kpi) {
            return null;
        }

        // Get current period value
        $currentValue = $this->getCurrentValue($kpi);

        // Get comparison period value
        $comparisonValue = $this->getComparisonValue($kpi);

        // Calculate change percentage
        $changePercentage = 0;
        if ($comparisonValue > 0) {
            $changePercentage = (($currentValue - $comparisonValue) / $comparisonValue) * 100;
        }

        if (! $kpi->target_value) {
            return [
                'kpi' => $kpi,
                'currentValue' => $currentValue,
                'comparisonValue' => $comparisonValue,
                'changePercentage' => round($changePercentage, 2),
                'achievedPercentage' => 0,
                'remainingPercentage' => 100,
                'isTargetMet' => false,
            ];
        }

        $achievedPercentage = min(100, ($currentValue / $kpi->target_value) * 100);
        $remainingPercentage = max(0, 100 - $achievedPercentage);

        return [
            'kpi' => $kpi,
            'currentValue' => $currentValue,
            'comparisonValue' => $comparisonValue,
            'changePercentage' => round($changePercentage, 2),
            'achievedPercentage' => round($achievedPercentage, 2),
            'remainingPercentage' => round($remainingPercentage, 2),
            'isTargetMet' => $currentValue >= $kpi->target_value,
        ];
    }

    public function updatedSelectedKpiId(?int $value): void
    {
        $this->selectedKpiId = $value;
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
            if ($kpi->metric_type === 'bounce_rate') {
                return (float) $query->avg($kpi->metric_type) ?? 0;
            }

            // For metrics that should be summed (like pageviews, unique_pageviews)
            return (float) $query->sum($kpi->metric_type) ?? 0;
        }

        return 0;
    }
}
