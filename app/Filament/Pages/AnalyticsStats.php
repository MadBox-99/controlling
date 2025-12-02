<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AnalyticsSortEnum;
use App\Enums\NavigationGroup;
use App\Filament\Pages\Actions\SetAnalyticsKpiGoalAction;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Settings;
use App\Services\GoogleClientFactory;
use Exception;
use Filament\Pages\Page;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\Row;
use Google\Service\AnalyticsData\RunReportRequest;
use UnitEnum;

final class AnalyticsStats extends Page
{
    public array $topPages = [];

    public array $userSources = [];

    public array $stats = [];

    protected string $view = 'filament.pages.analytics-stats';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = AnalyticsSortEnum::AnalyticsStats->value;

    protected static ?string $navigationLabel = 'Analytics Statistics';

    protected static ?string $title = 'Analytics Statistics';

    public function mount(): void
    {
        $this->loadAnalyticsData();
    }

    protected function getHeaderActions(): array
    {
        return [
            SetAnalyticsKpiGoalAction::make(fn (): array => $this->topPages),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // SourcePageBreakdown::class,
        ];
    }

    private function loadAnalyticsData(): void
    {
        // Load top pages from database
        $topPagesFromDb = AnalyticsPageview::query()
            ->orderBy('pageviews', 'desc')
            ->limit(10)
            ->get();

        if ($topPagesFromDb->isNotEmpty()) {
            $this->topPages = $topPagesFromDb
                ->map(fn (AnalyticsPageview $page): array => [
                    'page_path' => $page->page_path,
                    'page_title' => $page->page_title,
                    'pageviews' => $page->pageviews,
                    'unique_pageviews' => $page->unique_pageviews,
                    'bounce_rate' => $page->bounce_rate,
                ])
                ->toArray();
        } else {
            // Load from Google Analytics API if no database data
            $this->loadTopPagesFromApi();
        }

        // Load user sources from Google Analytics API
        $this->loadUserSources();

        // Load overall stats
        $this->stats = [
            'total_pageviews' => AnalyticsPageview::query()->sum('pageviews') ?: 0,
            'total_sessions' => AnalyticsSession::query()->count() ?: 0,
            'avg_bounce_rate' => AnalyticsPageview::query()->avg('bounce_rate') ?: 0,
        ];
    }

    private function loadTopPagesFromApi(): void
    {
        $this->topPages = $this->runReport(
            dimensions: ['pagePath', 'pageTitle'],
            metrics: ['screenPageViews', 'sessions', 'bounceRate'],
            extractRow: fn (Row $row): array => [
                'page_path' => $row->getDimensionValues()[0]->getValue(),
                'page_title' => $row->getDimensionValues()[1]->getValue(),
                'pageviews' => (int) $row->getMetricValues()[0]->getValue(),
                'unique_pageviews' => (int) $row->getMetricValues()[1]->getValue(),
                'bounce_rate' => (float) $row->getMetricValues()[2]->getValue() * 100,
            ],
            sortBy: 'pageviews',
        );
    }

    private function loadUserSources(): void
    {
        $this->userSources = $this->runReport(
            dimensions: ['sessionSource', 'sessionMedium'],
            metrics: ['sessions', 'activeUsers'],
            extractRow: fn (Row $row): array => [
                'source' => $row->getDimensionValues()[0]->getValue(),
                'medium' => $row->getDimensionValues()[1]->getValue(),
                'sessions' => (int) $row->getMetricValues()[0]->getValue(),
                'users' => (int) $row->getMetricValues()[1]->getValue(),
            ],
            sortBy: 'sessions',
        );
    }

    /**
     * @param  array<string>  $dimensions
     * @param  array<string>  $metrics
     * @param  callable(Row): array  $extractRow
     * @return array<array<string, mixed>>
     */
    private function runReport(array $dimensions, array $metrics, callable $extractRow, string $sortBy): array
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                return [];
            }

            $client = GoogleClientFactory::make(
                'https://www.googleapis.com/auth/analytics.readonly',
                $settings->google_service_account,
            );
            $service = new AnalyticsData($client);

            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions(array_map(function (string $name): Dimension {
                $dimension = new Dimension();
                $dimension->setName($name);

                return $dimension;
            }, $dimensions));
            $request->setMetrics(array_map(function (string $name): Metric {
                $metric = new Metric();
                $metric->setName($name);

                return $metric;
            }, $metrics));

            $response = $service->properties->runReport(
                property: 'properties/' . $settings->property_id,
                postBody: $request,
            );

            $rows = array_map($extractRow, $response->getRows() ?? []);

            usort($rows, fn (array $a, array $b): int => $b[$sortBy] <=> $a[$sortBy]);

            return array_slice($rows, 0, 10);
        } catch (Exception) {
            return [];
        }
    }
}
