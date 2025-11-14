<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis\Widgets;

use App\Models\AnalyticsPageview;
use App\Models\Kpi;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

final class KpiValuesChartWidget extends ChartWidget
{
    public ?Model $record = null;

    public function getHeading(): ?string
    {
        if (! $this->record instanceof Kpi) {
            return 'KPI Progress Over Time';
        }

        $kpi = $this->record;

        if (! $kpi->from_date || ! $kpi->target_date) {
            return 'Missing Configuration';
        }

        return 'KPI Progress Over Time';
    }

    public function getDescription(): ?string
    {
        if (! $this->record instanceof Kpi) {
            return null;
        }

        $kpi = $this->record;

        $missingFields = [];

        if (! $kpi->from_date) {
            $missingFields[] = 'Start Date';
        }

        if (! $kpi->target_date) {
            $missingFields[] = 'Target Date';
        }

        if (! empty($missingFields)) {
            return 'Missing required field(s): ' . implode(', ', $missingFields) . '.';
        }

        return null;
    }

    protected function getData(): array
    {
        if (! $this->record instanceof Kpi) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $kpi = $this->record;

        // Ha nincs from_date és target_date, akkor ne mutassunk semmit
        if (! $kpi->from_date || ! $kpi->target_date) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Get analytics data between from_date and target_date
        if (! $kpi->page_path || ! $kpi->metric_type) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $analyticsData = AnalyticsPageview::query()
            ->where('page_path', $kpi->page_path)
            ->where('date', '>=', $kpi->from_date)
            ->where('date', '<=', $kpi->target_date)
            ->orderBy('date')
            ->get();

        if ($analyticsData->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = $analyticsData->pluck('date')->map(fn ($date) => $date->format('M d'))->toArray();

        $actualData = $analyticsData->map(function ($record) use ($kpi) {
            return (float) match ($kpi->metric_type) {
                'pageviews' => $record->pageviews,
                'unique_pageviews' => $record->unique_pageviews,
                'bounce_rate' => $record->bounce_rate,
                default => 0,
            };
        })->toArray();

        $metricLabel = match ($kpi->metric_type) {
            'pageviews' => 'Pageviews',
            'unique_pageviews' => 'Unique Pageviews',
            'bounce_rate' => 'Bounce Rate',
            default => 'Value',
        };

        return [
            'datasets' => [
                [
                    'label' => $metricLabel,
                    'data' => $actualData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
