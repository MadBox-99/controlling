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

    public function getHeading(): string
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

        if ($missingFields !== []) {
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

            // Determine the full date range (from earliest to latest date)
            $startDate = $kpi->from_date;
            $endDate = $kpi->target_date;

            if ($kpi->comparison_start_date && $kpi->comparison_end_date) {
                $startDate = min($kpi->from_date, $kpi->comparison_start_date);
                $endDate = max($kpi->target_date, $kpi->comparison_end_date);
            }

            // Generate all dates in the full range
            $fullDateRange = [];
            $fullDaysDiff = $startDate->diffInDays($endDate);
            $maxDays = min($fullDaysDiff + 1, 365); // Safety limit

            for ($i = 0; $i < $maxDays; $i++) {
                $fullDateRange[] = $startDate->copy()->addDays($i);
            }

            // Fetch actual data based on source_type (page or query)
            if ($kpi->source_type === 'query') {
                // Search Query data
                $searchData = SearchQuery::query()
                    ->where('team_id', $kpi->team_id)
                    ->where('query', $kpi->page_path) // page_path stores the query text for queries
                    ->whereBetween('date', [$kpi->from_date, $kpi->target_date])
                    ->orderBy('date')
                    ->get()
                    ->keyBy(fn ($record) => $record->date->format('Y-m-d'));
            } else {
                // Search Page data (default)
                $searchData = SearchPage::query()
                    ->where('team_id', $kpi->team_id)
                    ->where('page_url', $kpi->page_path)
                    ->whereBetween('date', [$kpi->from_date, $kpi->target_date])
                    ->orderBy('date')
                    ->get()
                    ->keyBy(fn ($record) => $record->date->format('Y-m-d'));
            }

            // Build labels and data for all dates
            $labels = [];
            $actualData = [];

            foreach ($fullDateRange as $date) {
                $dateKey = $date->format('Y-m-d');
                $labels[] = $date->format('M d');

                // Only show data if date is within current period
                if ($date >= $kpi->from_date && $date <= $kpi->target_date && isset($searchData[$dateKey])) {
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

            $datasets = [
                [
                    'label' => $metricLabel . ' (Current)',
                    'data' => $actualData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.3,
                ],
            ];

            // Add target value line if available
            if ($kpi->target_value) {
                // Calculate dynamic target values based on actual data
                $targetData = [];
                foreach ($actualData as $value) {
                    if ($value === null) {
                        $targetData[] = null;

                        continue;
                    }

                    // Calculate target based on goal_type and value_type
                    if ($kpi->value_type === 'percentage') {
                        if ($kpi->goal_type === 'increase') {
                            $targetData[] = $value * (1 + $kpi->target_value / 100);
                        } else {
                            $targetData[] = $value * (1 - $kpi->target_value / 100);
                        }
                    } elseif ($kpi->goal_type === 'increase') {
                        // fixed value
                        $targetData[] = $value + $kpi->target_value;
                    } else {
                        $targetData[] = $value - $kpi->target_value;
                    }
                }

                $datasets[] = [
                    'label' => 'Target',
                    'data' => $targetData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'transparent',
                    'borderDash' => [5, 5],
                    'tension' => 0,
                    'pointRadius' => 0,
                ];
            }

            // Add comparison period data if available
            if ($kpi->comparison_start_date && $kpi->comparison_end_date) {
                if ($kpi->source_type === 'query') {
                    $comparisonData = SearchQuery::query()
                        ->where('team_id', $kpi->team_id)
                        ->where('query', $kpi->page_path)
                        ->whereBetween('date', [$kpi->comparison_start_date, $kpi->comparison_end_date])
                        ->orderBy('date')
                        ->get()
                        ->keyBy(fn ($record) => $record->date->format('Y-m-d'));
                } else {
                    $comparisonData = SearchPage::query()
                        ->where('team_id', $kpi->team_id)
                        ->where('page_url', $kpi->page_path)
                        ->whereBetween('date', [$kpi->comparison_start_date, $kpi->comparison_end_date])
                        ->orderBy('date')
                        ->get()
                        ->keyBy(fn ($record) => $record->date->format('Y-m-d'));
                }

                $comparisonValues = [];
                foreach ($fullDateRange as $date) {
                    $dateKey = $date->format('Y-m-d');

                    // Only show data if date is within comparison period
                    if ($date >= $kpi->comparison_start_date && $date <= $kpi->comparison_end_date && isset($comparisonData[$dateKey])) {
                        $record = $comparisonData[$dateKey];
                        $comparisonValues[] = (float) match ($kpi->metric_type) {
                            'impressions' => $record->impressions,
                            'clicks' => $record->clicks,
                            'ctr' => $record->ctr,
                            'position' => $record->position,
                            default => 0,
                        };
                    } else {
                        $comparisonValues[] = null;
                    }
                }

                $datasets[] = [
                    'label' => $metricLabel . ' (Comparison)',
                    'data' => $comparisonValues,
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'tension' => 0.3,
                ];
            }

            return [
                'datasets' => $datasets,
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

            // Determine the full date range (from earliest to latest date)
            $startDate = $kpi->from_date;
            $endDate = $kpi->target_date;

            if ($kpi->comparison_start_date && $kpi->comparison_end_date) {
                $startDate = min($kpi->from_date, $kpi->comparison_start_date);
                $endDate = max($kpi->target_date, $kpi->comparison_end_date);
            }

            // Generate all dates in the full range
            $fullDateRange = [];
            $fullDaysDiff = $startDate->diffInDays($endDate);
            $maxDays = min($fullDaysDiff + 1, 365); // Safety limit

            for ($i = 0; $i < $maxDays; $i++) {
                $fullDateRange[] = $startDate->copy()->addDays($i);
            }

            // Fetch actual data
            $analyticsData = AnalyticsPageview::query()
                ->where('team_id', $kpi->team_id)
                ->where('page_path', $kpi->page_path)
                ->whereBetween('date', [$kpi->from_date, $kpi->target_date])
                ->orderBy('date')
                ->get()
                ->keyBy(fn ($record) => $record->date->format('Y-m-d'));

            // Build labels and data for all dates
            $labels = [];
            $actualData = [];

            foreach ($fullDateRange as $date) {
                $dateKey = $date->format('Y-m-d');
                $labels[] = $date->format('M d');

                // Only show data if date is within current period
                if ($date >= $kpi->from_date && $date <= $kpi->target_date && isset($analyticsData[$dateKey])) {
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

            $datasets = [
                [
                    'label' => $metricLabel . ' (Current)',
                    'data' => $actualData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.3,
                ],
            ];

            // Add target value line if available
            if ($kpi->target_value) {
                // Calculate dynamic target values based on actual data
                $targetData = [];
                foreach ($actualData as $value) {
                    if ($value === null) {
                        $targetData[] = null;

                        continue;
                    }

                    // Calculate target based on goal_type and value_type
                    if ($kpi->value_type === 'percentage') {
                        if ($kpi->goal_type === 'increase') {
                            $targetData[] = $value * (1 + $kpi->target_value / 100);
                        } else {
                            $targetData[] = $value * (1 - $kpi->target_value / 100);
                        }
                    } elseif ($kpi->goal_type === 'increase') {
                        // fixed value
                        $targetData[] = $value + $kpi->target_value;
                    } else {
                        $targetData[] = $value - $kpi->target_value;
                    }
                }

                $datasets[] = [
                    'label' => 'Target',
                    'data' => $targetData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'transparent',
                    'borderDash' => [5, 5],
                    'tension' => 0,
                    'pointRadius' => 0,
                ];
            }

            // Add comparison period data if available
            if ($kpi->comparison_start_date && $kpi->comparison_end_date) {
                $comparisonData = AnalyticsPageview::query()
                    ->where('team_id', $kpi->team_id)
                    ->where('page_path', $kpi->page_path)
                    ->whereBetween('date', [$kpi->comparison_start_date, $kpi->comparison_end_date])
                    ->orderBy('date')
                    ->get()
                    ->keyBy(fn ($record) => $record->date->format('Y-m-d'));

                $comparisonValues = [];
                foreach ($fullDateRange as $date) {
                    $dateKey = $date->format('Y-m-d');

                    // Only show data if date is within comparison period
                    if ($date >= $kpi->comparison_start_date && $date <= $kpi->comparison_end_date && isset($comparisonData[$dateKey])) {
                        $record = $comparisonData[$dateKey];
                        $comparisonValues[] = (float) match ($kpi->metric_type) {
                            'pageviews' => $record->pageviews,
                            'unique_pageviews' => $record->unique_pageviews,
                            'bounce_rate' => $record->bounce_rate,
                            default => 0,
                        };
                    } else {
                        $comparisonValues[] = null;
                    }
                }

                $datasets[] = [
                    'label' => $metricLabel . ' (Comparison)',
                    'data' => $comparisonValues,
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'tension' => 0.3,
                ];
            }

            return [
                'datasets' => $datasets,
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
