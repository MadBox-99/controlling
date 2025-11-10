<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AnalyticsSortEnum;
use App\Enums\NavigationGroup;
use App\Filament\Widgets\GeneralStatsOverview;
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

final class AnalyticsGeneralStats extends Page
{
    public array $stats = [];

    public array $topPages = [];

    public array $userSources = [];

    public array $sessionSources = [];

    public array $newVsReturningUsers = [];

    protected string $view = 'filament.pages.analytics-general-stats';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = AnalyticsSortEnum::AnalyticsGeneralStats->value;

    protected static ?string $navigationLabel = 'General Analytics';

    protected static ?string $title = 'General Analytics Dashboard';

    public function mount(): void
    {
        $this->loadAnalyticsData();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GeneralStatsOverview::class,
        ];
    }

    private function loadAnalyticsData(): void
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                $this->initializeEmptyData();

                return;
            }

            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $client->setAuthConfig(Storage::json($settings->google_service_account));

            $service = new AnalyticsData($client);
            $propertyId = 'properties/' . $settings->property_id;

            // Load general stats
            $this->loadGeneralStats($service, $propertyId);

            // Load top pages
            $this->loadTopPages($service, $propertyId);

            // Load user sources
            $this->loadUserSourcesData($service, $propertyId);

            // Load session sources
            $this->loadSessionSourcesData($service, $propertyId);

            // Load new vs returning users
            $this->loadNewVsReturningUsers($service, $propertyId);
        } catch (Exception) {
            $this->initializeEmptyData();
        }
    }

    private function initializeEmptyData(): void
    {
        $this->stats = [
            'active_users' => 0,
            'new_users' => 0,
            'avg_session_duration' => 0,
            'event_count' => 0,
        ];
        $this->topPages = [];
        $this->userSources = [];
        $this->sessionSources = [];
        $this->newVsReturningUsers = [];
    }

    private function loadGeneralStats(AnalyticsData $service, string $propertyId): void
    {
        try {
            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setMetrics([
                (new Metric())->setName('activeUsers'),
                (new Metric())->setName('newUsers'),
                (new Metric())->setName('averageSessionDuration'),
                (new Metric())->setName('eventCount'),
            ]);

            $response = $service->properties->runReport($propertyId, $request);

            if ($response->getRows() && count($response->getRows()) > 0) {
                $row = $response->getRows()[0];
                $this->stats = [
                    'active_users' => (int) $row->getMetricValues()[0]->getValue(),
                    'new_users' => (int) $row->getMetricValues()[1]->getValue(),
                    'avg_session_duration' => (float) $row->getMetricValues()[2]->getValue(),
                    'event_count' => (int) $row->getMetricValues()[3]->getValue(),
                ];
            } else {
                $this->initializeEmptyData();
            }
        } catch (Exception) {
            $this->stats = [
                'active_users' => 0,
                'new_users' => 0,
                'avg_session_duration' => 0,
                'event_count' => 0,
            ];
        }
    }

    private function loadTopPages(AnalyticsData $service, string $propertyId): void
    {
        try {
            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([
                (new Dimension())->setName('pageTitle'),
                (new Dimension())->setName('pagePath'),
            ]);
            $request->setMetrics([
                (new Metric())->setName('screenPageViews'),
                (new Metric())->setName('activeUsers'),
                (new Metric())->setName('eventCount'),
                (new Metric())->setName('bounceRate'),
            ]);
            $request->setLimit(10);

            $response = $service->properties->runReport($propertyId, $request);

            $pages = [];
            foreach ($response->getRows() as $row) {
                $pages[] = [
                    'page_title' => $row->getDimensionValues()[0]->getValue(),
                    'page_path' => $row->getDimensionValues()[1]->getValue(),
                    'views' => (int) $row->getMetricValues()[0]->getValue(),
                    'active_users' => (int) $row->getMetricValues()[1]->getValue(),
                    'event_count' => (int) $row->getMetricValues()[2]->getValue(),
                    'bounce_rate' => (float) $row->getMetricValues()[3]->getValue() * 100,
                ];
            }

            $this->topPages = $pages;
        } catch (Exception) {
            $this->topPages = [];
        }
    }

    private function loadUserSourcesData(AnalyticsData $service, string $propertyId): void
    {
        try {
            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([
                (new Dimension())->setName('firstUserSource'),
                (new Dimension())->setName('firstUserMedium'),
            ]);
            $request->setMetrics([
                (new Metric())->setName('activeUsers'),
            ]);
            $request->setLimit(10);

            $response = $service->properties->runReport($propertyId, $request);

            $sources = [];
            foreach ($response->getRows() as $row) {
                $sources[] = [
                    'source' => $row->getDimensionValues()[0]->getValue(),
                    'medium' => $row->getDimensionValues()[1]->getValue(),
                    'users' => (int) $row->getMetricValues()[0]->getValue(),
                ];
            }

            $this->userSources = $sources;
        } catch (Exception) {
            $this->userSources = [];
        }
    }

    private function loadSessionSourcesData(AnalyticsData $service, string $propertyId): void
    {
        try {
            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([
                (new Dimension())->setName('sessionSource'),
                (new Dimension())->setName('sessionMedium'),
            ]);
            $request->setMetrics([
                (new Metric())->setName('sessions'),
            ]);
            $request->setLimit(10);

            $response = $service->properties->runReport($propertyId, $request);

            $sources = [];
            foreach ($response->getRows() as $row) {
                $sources[] = [
                    'source' => $row->getDimensionValues()[0]->getValue(),
                    'medium' => $row->getDimensionValues()[1]->getValue(),
                    'sessions' => (int) $row->getMetricValues()[0]->getValue(),
                ];
            }

            $this->sessionSources = $sources;
        } catch (Exception) {
            $this->sessionSources = [];
        }
    }

    private function loadNewVsReturningUsers(AnalyticsData $service, string $propertyId): void
    {
        try {
            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([
                (new Dimension())->setName('date'),
            ]);
            $request->setMetrics([
                (new Metric())->setName('newUsers'),
                (new Metric())->setName('activeUsers'),
            ]);

            $response = $service->properties->runReport($propertyId, $request);

            $data = [];
            foreach ($response->getRows() as $row) {
                $date = $row->getDimensionValues()[0]->getValue();
                $newUsers = (int) $row->getMetricValues()[0]->getValue();
                $activeUsers = (int) $row->getMetricValues()[1]->getValue();

                $data[] = [
                    'date' => $date,
                    'new_users' => $newUsers,
                    'returning_users' => max(0, $activeUsers - $newUsers),
                ];
            }

            $this->newVsReturningUsers = $data;
        } catch (Exception) {
            $this->newVsReturningUsers = [];
        }
    }
}
