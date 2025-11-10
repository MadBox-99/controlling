<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AnalyticsConversion;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Settings;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\MetricOrderBy;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

final class AnaliticsImport implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public function handle(): void
    {
        $settings = Settings::query()->first();

        if (! $settings || ! $settings->google_service_account) {
            Notification::make()
                ->title('Google Service Account credentials not configured.')
                ->body('Please configure the credentials in Settings first.')
                ->danger()
                ->send();

            $this->fail('Google Service Account credentials not configured.');

            return;
        }

        if (! $settings->property_id) {
            Notification::make()
                ->title('GA4 Property ID not configured.')
                ->body('Please configure the Property ID in Settings first.')
                ->danger()
                ->send();

            $this->fail('GA4 Property ID not configured.');

            return;
        }

        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $client->setAuthConfig(Storage::json($settings->google_service_account));
        $service = new AnalyticsData($client);

        // Import all analytics data
        $this->importSessions($service, $settings->property_id);
        $this->importPageviews($service, $settings->property_id);
        $this->importEvents($service, $settings->property_id);
        $this->importConversions($service, $settings->property_id);

        // Sync Analytics data to KPI values
        SyncAnalyticsKpis::dispatch();

        Notification::make()
            ->title('Analytics import completed successfully.')
            ->body('All analytics data has been imported and KPIs have been synced.')
            ->success()
            ->send();
    }

    private function importSessions(AnalyticsData $service, string $propertyId): void
    {
        $dateRange = new DateRange();
        $dateRange->setStartDate('30daysAgo');
        $dateRange->setEndDate('today');

        $dateDimension = new Dimension();
        $dateDimension->setName('date');

        $sourceDimension = new Dimension();
        $sourceDimension->setName('sessionSource');

        $mediumDimension = new Dimension();
        $mediumDimension->setName('sessionMedium');

        $campaignDimension = new Dimension();
        $campaignDimension->setName('sessionCampaignName');

        $sessionsMetric = new Metric();
        $sessionsMetric->setName('sessions');

        $usersMetric = new Metric();
        $usersMetric->setName('totalUsers');

        $newUsersMetric = new Metric();
        $newUsersMetric->setName('newUsers');

        $bounceRateMetric = new Metric();
        $bounceRateMetric->setName('bounceRate');

        $avgSessionDurationMetric = new Metric();
        $avgSessionDurationMetric->setName('averageSessionDuration');

        $request = new RunReportRequest();
        $request->setDateRanges([$dateRange]);
        $request->setDimensions([$dateDimension, $sourceDimension, $mediumDimension, $campaignDimension]);
        $request->setMetrics([$sessionsMetric, $usersMetric, $newUsersMetric, $bounceRateMetric, $avgSessionDurationMetric]);

        $response = $service->properties->runReport(
            property: 'properties/' . $propertyId,
            postBody: $request,
        );

        $sessionData = [];

        foreach ($response->getRows() as $row) {
            $date = Carbon::createFromFormat('Ymd', $row->getDimensionValues()[0]->getValue())->format('Y-m-d');
            $source = $row->getDimensionValues()[1]->getValue();
            $medium = $row->getDimensionValues()[2]->getValue();
            $campaign = $row->getDimensionValues()[3]->getValue();
            $sessions = (int) $row->getMetricValues()[0]->getValue();
            $users = (int) $row->getMetricValues()[1]->getValue();
            $newUsers = (int) $row->getMetricValues()[2]->getValue();
            $bounceRate = (float) $row->getMetricValues()[3]->getValue() * 100;
            $avgDuration = (int) $row->getMetricValues()[4]->getValue();

            $key = $date . '|' . $source . '|' . $medium . '|' . $campaign;

            if (! isset($sessionData[$key])) {
                $sessionData[$key] = [
                    'date' => $date,
                    'source' => $source,
                    'medium' => $medium,
                    'campaign' => $campaign,
                    'sessions' => 0,
                    'users' => 0,
                    'new_users' => 0,
                    'bounce_rate' => 0,
                    'avg_session_duration' => 0,
                    'count' => 0,
                ];
            }

            $sessionData[$key]['sessions'] += $sessions;
            $sessionData[$key]['users'] += $users;
            $sessionData[$key]['new_users'] += $newUsers;
            $sessionData[$key]['bounce_rate'] += $bounceRate;
            $sessionData[$key]['avg_session_duration'] += $avgDuration;
            $sessionData[$key]['count']++;
        }

        foreach ($sessionData as $data) {
            $record = AnalyticsSession::query()
                ->whereDate('date', $data['date'])
                ->where('source', $data['source'])
                ->where('medium', $data['medium'])
                ->where('campaign', $data['campaign'])
                ->first();

            if ($record) {
                $record->update([
                    'sessions' => $data['sessions'],
                    'users' => $data['users'],
                    'new_users' => $data['new_users'],
                    'bounce_rate' => $data['bounce_rate'] / $data['count'],
                    'avg_session_duration' => (int) ($data['avg_session_duration'] / $data['count']),
                ]);
            } else {
                AnalyticsSession::create([
                    'date' => $data['date'],
                    'source' => $data['source'],
                    'medium' => $data['medium'],
                    'campaign' => $data['campaign'],
                    'sessions' => $data['sessions'],
                    'users' => $data['users'],
                    'new_users' => $data['new_users'],
                    'bounce_rate' => $data['bounce_rate'] / $data['count'],
                    'avg_session_duration' => (int) ($data['avg_session_duration'] / $data['count']),
                ]);
            }
        }
    }

    private function importPageviews(AnalyticsData $service, string $propertyId): void
    {
        $dateRange = new DateRange();
        $dateRange->setStartDate('30daysAgo');
        $dateRange->setEndDate('today');

        $dateDimension = new Dimension();
        $dateDimension->setName('date');

        $pagePathDimension = new Dimension();
        $pagePathDimension->setName('pagePath');

        $pageTitleDimension = new Dimension();
        $pageTitleDimension->setName('pageTitle');

        $pageviewsMetric = new Metric();
        $pageviewsMetric->setName('screenPageViews');

        $uniquePageviewsMetric = new Metric();
        $uniquePageviewsMetric->setName('sessions');

        $avgTimeMetric = new Metric();
        $avgTimeMetric->setName('averageSessionDuration');

        $bounceRateMetric = new Metric();
        $bounceRateMetric->setName('bounceRate');

        $request = new RunReportRequest();
        $request->setDateRanges([$dateRange]);
        $request->setDimensions([$dateDimension, $pagePathDimension, $pageTitleDimension]);
        $request->setMetrics([$pageviewsMetric, $uniquePageviewsMetric, $avgTimeMetric, $bounceRateMetric]);

        $response = $service->properties->runReport(
            property: 'properties/' . $propertyId,
            postBody: $request,
        );

        $pageviewData = [];

        foreach ($response->getRows() as $row) {
            $date = Carbon::createFromFormat('Ymd', $row->getDimensionValues()[0]->getValue())->format('Y-m-d');
            $pagePath = $row->getDimensionValues()[1]->getValue();
            $pageTitle = $row->getDimensionValues()[2]->getValue();
            $pageviews = (int) $row->getMetricValues()[0]->getValue();
            $uniquePageviews = (int) $row->getMetricValues()[1]->getValue();
            $avgTime = (int) $row->getMetricValues()[2]->getValue();
            $bounceRate = (float) $row->getMetricValues()[3]->getValue() * 100;

            $key = $date . '|' . $pagePath;

            if (! isset($pageviewData[$key])) {
                $pageviewData[$key] = [
                    'date' => $date,
                    'page_path' => $pagePath,
                    'page_title' => $pageTitle,
                    'pageviews' => 0,
                    'unique_pageviews' => 0,
                    'avg_time_on_page' => 0,
                    'bounce_rate' => 0,
                    'count' => 0,
                ];
            }

            $pageviewData[$key]['pageviews'] += $pageviews;
            $pageviewData[$key]['unique_pageviews'] += $uniquePageviews;
            $pageviewData[$key]['avg_time_on_page'] += $avgTime;
            $pageviewData[$key]['bounce_rate'] += $bounceRate;
            $pageviewData[$key]['count']++;
        }

        foreach ($pageviewData as $data) {
            $record = AnalyticsPageview::query()
                ->whereDate('date', $data['date'])
                ->where('page_path', $data['page_path'])
                ->first();

            if ($record) {
                $record->update([
                    'page_title' => $data['page_title'],
                    'pageviews' => $data['pageviews'],
                    'unique_pageviews' => $data['unique_pageviews'],
                    'avg_time_on_page' => (int) ($data['avg_time_on_page'] / $data['count']),
                    'bounce_rate' => $data['bounce_rate'] / $data['count'],
                ]);
            } else {
                AnalyticsPageview::create([
                    'date' => $data['date'],
                    'page_path' => $data['page_path'],
                    'page_title' => $data['page_title'],
                    'pageviews' => $data['pageviews'],
                    'unique_pageviews' => $data['unique_pageviews'],
                    'avg_time_on_page' => (int) ($data['avg_time_on_page'] / $data['count']),
                    'bounce_rate' => $data['bounce_rate'] / $data['count'],
                ]);
            }
        }
    }

    private function importEvents(AnalyticsData $service, string $propertyId): void
    {
        $dateRange = new DateRange();
        $dateRange->setStartDate('30daysAgo');
        $dateRange->setEndDate('today');

        $dateDimension = new Dimension();
        $dateDimension->setName('date');

        $eventNameDimension = new Dimension();
        $eventNameDimension->setName('eventName');

        $eventCountMetric = new Metric();
        $eventCountMetric->setName('eventCount');

        $eventValueMetric = new Metric();
        $eventValueMetric->setName('eventValue');

        $request = new RunReportRequest();
        $request->setDateRanges([$dateRange]);
        $request->setDimensions([$dateDimension, $eventNameDimension]);
        $request->setMetrics([$eventCountMetric, $eventValueMetric]);

        $orderBy = new OrderBy();
        $metricOrderBy = new MetricOrderBy();
        $metricOrderBy->setMetricName('eventCount');
        $orderBy->setMetric($metricOrderBy);
        $orderBy->setDesc(true);
        $request->setOrderBys([$orderBy]);

        $response = $service->properties->runReport(
            property: 'properties/' . $propertyId,
            postBody: $request,
        );

        $eventData = [];

        foreach ($response->getRows() as $row) {
            $date = Carbon::createFromFormat('Ymd', $row->getDimensionValues()[0]->getValue())->format('Y-m-d');
            $eventName = $row->getDimensionValues()[1]->getValue();
            $eventCount = (int) $row->getMetricValues()[0]->getValue();
            $eventValue = (float) $row->getMetricValues()[1]->getValue();

            $key = $date . '|' . $eventName;

            if (! isset($eventData[$key])) {
                $eventData[$key] = [
                    'date' => $date,
                    'event_name' => $eventName,
                    'event_count' => 0,
                    'event_value' => 0,
                ];
            }

            $eventData[$key]['event_count'] += $eventCount;
            $eventData[$key]['event_value'] += $eventValue;
        }

        foreach ($eventData as $data) {
            $record = AnalyticsEvent::query()
                ->whereDate('date', $data['date'])
                ->where('event_name', $data['event_name'])
                ->where('event_category', 'engagement')
                ->where('event_action', 'event')
                ->first();

            if ($record) {
                $record->update([
                    'event_count' => $data['event_count'],
                    'event_value' => $data['event_value'],
                ]);
            } else {
                AnalyticsEvent::create([
                    'date' => $data['date'],
                    'event_name' => $data['event_name'],
                    'event_category' => 'engagement',
                    'event_action' => 'event',
                    'event_count' => $data['event_count'],
                    'event_value' => $data['event_value'],
                ]);
            }
        }
    }

    private function importConversions(AnalyticsData $service, string $propertyId): void
    {
        $dateRange = new DateRange();
        $dateRange->setStartDate('30daysAgo');
        $dateRange->setEndDate('today');

        $dateDimension = new Dimension();
        $dateDimension->setName('date');

        $conversionsMetric = new Metric();
        $conversionsMetric->setName('conversions');

        $totalRevenueMetric = new Metric();
        $totalRevenueMetric->setName('totalRevenue');

        $sessionsMetric = new Metric();
        $sessionsMetric->setName('sessions');

        $request = new RunReportRequest();
        $request->setDateRanges([$dateRange]);
        $request->setDimensions([$dateDimension]);
        $request->setMetrics([$conversionsMetric, $totalRevenueMetric, $sessionsMetric]);

        $response = $service->properties->runReport(
            property: 'properties/' . $propertyId,
            postBody: $request,
        );

        $conversionData = [];

        foreach ($response->getRows() as $row) {
            $date = Carbon::createFromFormat('Ymd', $row->getDimensionValues()[0]->getValue())->format('Y-m-d');
            $conversions = (int) $row->getMetricValues()[0]->getValue();
            $revenue = (float) $row->getMetricValues()[1]->getValue();
            $sessions = (int) $row->getMetricValues()[2]->getValue();

            if (! isset($conversionData[$date])) {
                $conversionData[$date] = [
                    'date' => $date,
                    'conversions' => 0,
                    'revenue' => 0,
                    'sessions' => 0,
                ];
            }

            $conversionData[$date]['conversions'] += $conversions;
            $conversionData[$date]['revenue'] += $revenue;
            $conversionData[$date]['sessions'] += $sessions;
        }

        foreach ($conversionData as $data) {
            $conversionRate = $data['sessions'] > 0 ? ($data['conversions'] / $data['sessions']) * 100 : 0;

            $record = AnalyticsConversion::query()
                ->whereDate('date', $data['date'])
                ->where('goal_name', 'All Conversions')
                ->first();

            if ($record) {
                $record->update([
                    'goal_completions' => $data['conversions'],
                    'goal_value' => $data['revenue'],
                    'conversion_rate' => $conversionRate,
                ]);
            } else {
                AnalyticsConversion::create([
                    'date' => $data['date'],
                    'goal_name' => 'All Conversions',
                    'goal_completions' => $data['conversions'],
                    'goal_value' => $data['revenue'],
                    'conversion_rate' => $conversionRate,
                ]);
            }
        }
    }
}
