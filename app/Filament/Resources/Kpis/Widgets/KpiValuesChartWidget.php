<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kpis\Widgets;

use App\Models\AnalyticsPageview;
use App\Models\Kpi;
use App\Models\SearchPage;
use App\Models\SearchQuery;
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

        // Check for invalid date range
        if ($kpi->from_date && $kpi->target_date) {
            if ($kpi->from_date->gt($kpi->target_date)) {
                return 'Invalid date range: Start date must be before target date.';
            }

            $daysDiff = $kpi->from_date->diffInDays($kpi->target_date);
            if ($daysDiff > 365) {
                return 'Date range too large (max 365 days). Please adjust your date range.';
            }
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

        // Get data based on data source
        if (! $kpi->page_path || ! $kpi->metric_type) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Search Console data
        if ($kpi->data_source->value === 'search_console') {
            // Validate date range
            if ($kpi->from_date->gt($kpi->target_date)) {
                return [
                    'datasets' => [],
                    'labels' => [],
                ];
            }

            // Limit date range to prevent performance issues (max 365 days)
            $daysDiff = $kpi->from_date->diffInDays($kpi->target_date);
            if ($daysDiff > 365) {
                return [
                    'datasets' => [],
                    'labels' => [],
                ];
            }

            // Generate all dates in the range
            $dateRange = [];
            $maxDays = min($daysDiff + 1, 365); // Safety limit

            for ($i = 0; $i < $maxDays; $i++) {
                $dateRange[] = $kpi->from_date->copy()->addDays($i);
            }

            // Fetch actual data based on source_type (page or query)

            if ($kpi->source_type === 'query') {
                // Search Query data
                $searchData = SearchQuery::query()
                    ->where('team_id', $kpi->team_id)
                    ->where('query', $kpi->page_path) // page_path stores the query text for queries
                    ->where('date', '>=', $kpi->from_date)
                    ->where('date', '<=', $kpi->target_date)
                    ->orderBy('date')
                    ->get()
                    ->keyBy(fn ($record) => $record->date->format('Y-m-d'));
            } else {
                // Search Page data (default)
                $searchData = SearchPage::query()
                    ->where('team_id', $kpi->team_id)
                    ->where('page_url', $kpi->page_path)
                    ->where('date', '>=', $kpi->from_date)
                    ->where('date', '<=', $kpi->target_date)
                    ->orderBy('date')
                    ->get()
                    ->keyBy(fn ($record) => $record->date->format('Y-m-d'));
            }

            // Build labels and data for all dates
            $labels = [];
            $actualData = [];

            foreach ($dateRange as $date) {
                $dateKey = $date->format('Y-m-d');
                $labels[] = $date->format('M d');

                if (isset($searchData[$dateKey])) {
                    $record = $searchData[$dateKey];
                    $actualData[] = (float) match ($kpi->metric_type) {
                        'impressions' => $record->impressions,
                        'clicks' => $record->clicks,
                        'ctr' => $record->ctr,
                        'position' => $record->position,
                        default => 0,
                    };
                } else {
                    // No data for this date, use null so the line shows gaps
                    $actualData[] = null;
                }
            }

            $metricLabel = match ($kpi->metric_type) {
                'impressions' => 'Impressions',
                'clicks' => 'Clicks',
                'ctr' => 'CTR (%)',
                'position' => 'Position',
                default => 'Value',
            };

            return [
                'datasets' => [
                    [
                        'label' => $metricLabel,
                        'data' => $actualData,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $labels,
            ];
        }

        // Analytics data
        if ($kpi->data_source->value === 'analytics') {
            // Validate date range
            if ($kpi->from_date->gt($kpi->target_date)) {
                return [
                    'datasets' => [],
                    'labels' => [],
                ];
            }

            // Limit date range to prevent performance issues (max 365 days)
            $daysDiff = $kpi->from_date->diffInDays($kpi->target_date);
            if ($daysDiff > 365) {
                return [
                    'datasets' => [],
                    'labels' => [],
                ];
            }

            // Generate all dates in the range
            $dateRange = [];
            $maxDays = min($daysDiff + 1, 365); // Safety limit

            for ($i = 0; $i < $maxDays; $i++) {
                $dateRange[] = $kpi->from_date->copy()->addDays($i);
            }

            // Fetch actual data
            $analyticsData = AnalyticsPageview::query()
                ->where('team_id', $kpi->team_id)
                ->where('page_path', $kpi->page_path)
                ->where('date', '>=', $kpi->from_date)
                ->where('date', '<=', $kpi->target_date)
                ->orderBy('date')
                ->get()
                ->keyBy(fn ($record) => $record->date->format('Y-m-d'));

            // Build labels and data for all dates
            $labels = [];
            $actualData = [];

            foreach ($dateRange as $date) {
                $dateKey = $date->format('Y-m-d');
                $labels[] = $date->format('M d');

                if (isset($analyticsData[$dateKey])) {
                    $record = $analyticsData[$dateKey];
                    $actualData[] = (float) match ($kpi->metric_type) {
                        'pageviews' => $record->pageviews,
                        'unique_pageviews' => $record->unique_pageviews,
                        'bounce_rate' => $record->bounce_rate,
                        default => 0,
                    };
                } else {
                    // No data for this date, use null so the line shows gaps
                    $actualData[] = null;
                }
            }

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

        return [
            'datasets' => [],
            'labels' => [],
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
                'x' => [
                    'display' => true,
                    'grid' => [
                        'display' => true,
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'spanGaps' => false, // Show gaps where data is missing
                ],
                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 5,
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
