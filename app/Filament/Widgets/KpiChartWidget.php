<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsPageview;
use App\Models\Kpi;
use App\Models\SearchPage;
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

        // Get current value based on data source
        $currentValue = $this->getCurrentValue($kpi);

        if (! $kpi->target_value) {
            return [
                'kpi' => $kpi,
                'currentValue' => $currentValue,
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
