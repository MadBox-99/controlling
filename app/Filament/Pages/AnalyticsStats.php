<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Analytics;
use App\Enums\AnalyticsSortEnum;
use App\Enums\NavigationGroup;
use App\Filament\Widgets\SourcePageBreakdown;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Settings;
use Exception;
use Filament\Pages\Page;
use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Support\Facades\Storage;
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

    protected function getHeaderWidgets(): array
    {
        return [

            // SourcePageBreakdown::class,
        ];
    }

    protected function loadAnalyticsData(): void
    {
        // Load top pages from database
        $topPagesFromDb = AnalyticsPageview::query()
            ->orderBy('pageviews', 'desc')
            ->limit(10)
            ->get();

        if ($topPagesFromDb->isNotEmpty()) {
            $this->topPages = $topPagesFromDb
                ->map(fn (AnalyticsPageview $page) => [
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
            'total_pageviews' => AnalyticsPageview::sum('pageviews') ?: 0,
            'total_sessions' => AnalyticsSession::count() ?: 0,
            'avg_bounce_rate' => AnalyticsPageview::avg('bounce_rate') ?: 0,
        ];
    }

    protected function loadTopPagesFromApi(): void
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                $this->topPages = [];

                return;
            }

            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $client->setAuthConfig(Storage::json($settings->google_service_account));

            $service = new AnalyticsData($client);

            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $pagePathDimension = new Dimension();
            $pagePathDimension->setName('pagePath');

            $pageTitleDimension = new Dimension();
            $pageTitleDimension->setName('pageTitle');

            $pageviewsMetric = new Metric();
            $pageviewsMetric->setName('screenPageViews');

            $uniquePageviewsMetric = new Metric();
            $uniquePageviewsMetric->setName('sessions');

            $bounceRateMetric = new Metric();
            $bounceRateMetric->setName('bounceRate');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([$pagePathDimension, $pageTitleDimension]);
            $request->setMetrics([$pageviewsMetric, $uniquePageviewsMetric, $bounceRateMetric]);

            $response = $service->properties->runReport(
                property: 'properties/'.$settings->property_id,
                postBody: $request
            );

            $pages = [];

            foreach ($response->getRows() as $row) {
                $pagePath = $row->getDimensionValues()[0]->getValue();
                $pageTitle = $row->getDimensionValues()[1]->getValue();
                $pageviews = (int) $row->getMetricValues()[0]->getValue();
                $uniquePageviews = (int) $row->getMetricValues()[1]->getValue();
                $bounceRate = (float) $row->getMetricValues()[2]->getValue() * 100;

                $pages[] = [
                    'page_path' => $pagePath,
                    'page_title' => $pageTitle,
                    'pageviews' => $pageviews,
                    'unique_pageviews' => $uniquePageviews,
                    'bounce_rate' => $bounceRate,
                ];
            }

            // Sort by pageviews descending and take top 10
            usort($pages, fn ($a, $b) => $b['pageviews'] <=> $a['pageviews']);
            $this->topPages = array_slice($pages, 0, 10);

        } catch (Exception $e) {
            $this->topPages = [];
        }
    }

    protected function loadUserSources(): void
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                $this->userSources = [];

                return;
            }

            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $client->setAuthConfig(Storage::json($settings->google_service_account));

            $service = new AnalyticsData($client);

            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $sourceDimension = new Dimension();
            $sourceDimension->setName('sessionSource');

            $mediumDimension = new Dimension();
            $mediumDimension->setName('sessionMedium');

            $sessionsMetric = new Metric();
            $sessionsMetric->setName('sessions');

            $usersMetric = new Metric();
            $usersMetric->setName('activeUsers');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([$sourceDimension, $mediumDimension]);
            $request->setMetrics([$sessionsMetric, $usersMetric]);

            $response = $service->properties->runReport(
                property: 'properties/'.$settings->property_id,
                postBody: $request
            );

            $sources = [];

            foreach ($response->getRows() as $row) {
                $source = $row->getDimensionValues()[0]->getValue();
                $medium = $row->getDimensionValues()[1]->getValue();
                $sessions = (int) $row->getMetricValues()[0]->getValue();
                $users = (int) $row->getMetricValues()[1]->getValue();

                $sources[] = [
                    'source' => $source,
                    'medium' => $medium,
                    'sessions' => $sessions,
                    'users' => $users,
                ];
            }

            // Sort by sessions descending and take top 10
            usort($sources, fn ($a, $b) => $b['sessions'] <=> $a['sessions']);
            $this->userSources = array_slice($sources, 0, 10);

        } catch (Exception $e) {
            $this->userSources = [];
        }
    }
}
