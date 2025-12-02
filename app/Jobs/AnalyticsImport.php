<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AnalyticsConversion;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Settings;
use App\Services\GoogleClientFactory;
use Closure;
use Filament\Notifications\Notification;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\MetricOrderBy;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\AnalyticsData\Row;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;

final class AnalyticsImport implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public function handle(): void
    {
        $settings = Settings::query()->first();

        if (! $settings || ! $settings->google_service_account) {
            $this->failWithNotification('Google Service Account credentials not configured.', 'Please configure the credentials in Settings first.');

            return;
        }

        if (! $settings->property_id) {
            $this->failWithNotification('GA4 Property ID not configured.', 'Please configure the Property ID in Settings first.');

            return;
        }

        $client = GoogleClientFactory::make(
            'https://www.googleapis.com/auth/analytics.readonly',
            $settings->google_service_account,
        );
        $service = new AnalyticsData($client);

        $this->importSessions($service, $settings->property_id);
        $this->importPageviews($service, $settings->property_id);
        $this->importEvents($service, $settings->property_id);
        $this->importConversions($service, $settings->property_id);

        Notification::make()
            ->title('Analytics import completed successfully.')
            ->body('All analytics data has been imported and KPIs have been synced.')
            ->success()
            ->send();
    }

    private function importSessions(AnalyticsData $service, string $propertyId): void
    {
        $this->processImport(
            service: $service,
            propertyId: $propertyId,
            dimensions: ['date', 'sessionSource', 'sessionMedium', 'sessionCampaignName'],
            metrics: ['sessions', 'totalUsers', 'newUsers', 'bounceRate', 'averageSessionDuration'],
            extractRow: fn (Row $row): array => [
                'date' => $this->parseDate($row->getDimensionValues()[0]->getValue()),
                'source' => $row->getDimensionValues()[1]->getValue(),
                'medium' => $row->getDimensionValues()[2]->getValue(),
                'campaign' => $row->getDimensionValues()[3]->getValue(),
                'sessions' => (int) $row->getMetricValues()[0]->getValue(),
                'users' => (int) $row->getMetricValues()[1]->getValue(),
                'new_users' => (int) $row->getMetricValues()[2]->getValue(),
                'bounce_rate' => (float) $row->getMetricValues()[3]->getValue() * 100,
                'avg_session_duration' => (int) $row->getMetricValues()[4]->getValue(),
            ],
            getKey: fn (array $d): string => "{$d['date']}|{$d['source']}|{$d['medium']}|{$d['campaign']}",
            sumFields: ['sessions', 'users', 'new_users', 'bounce_rate', 'avg_session_duration'],
            avgFields: ['bounce_rate', 'avg_session_duration'],
            findRecord: fn (array $d): ?Model => AnalyticsSession::query()
                ->whereDate('date', $d['date'])
                ->where('source', $d['source'])
                ->where('medium', $d['medium'])
                ->where('campaign', $d['campaign'])
                ->first(),
            modelClass: AnalyticsSession::class,
        );
    }

    private function importPageviews(AnalyticsData $service, string $propertyId): void
    {
        $this->processImport(
            service: $service,
            propertyId: $propertyId,
            dimensions: ['date', 'pagePath', 'pageTitle'],
            metrics: ['screenPageViews', 'sessions', 'averageSessionDuration', 'bounceRate'],
            extractRow: fn (Row $row): array => [
                'date' => $this->parseDate($row->getDimensionValues()[0]->getValue()),
                'page_path' => $row->getDimensionValues()[1]->getValue(),
                'page_title' => $row->getDimensionValues()[2]->getValue(),
                'pageviews' => (int) $row->getMetricValues()[0]->getValue(),
                'unique_pageviews' => (int) $row->getMetricValues()[1]->getValue(),
                'avg_time_on_page' => (int) $row->getMetricValues()[2]->getValue(),
                'bounce_rate' => (float) $row->getMetricValues()[3]->getValue() * 100,
            ],
            getKey: fn (array $d): string => "{$d['date']}|{$d['page_path']}",
            sumFields: ['pageviews', 'unique_pageviews', 'avg_time_on_page', 'bounce_rate'],
            avgFields: ['avg_time_on_page', 'bounce_rate'],
            findRecord: fn (array $d): ?Model => AnalyticsPageview::query()
                ->whereDate('date', $d['date'])
                ->where('page_path', $d['page_path'])
                ->first(),
            modelClass: AnalyticsPageview::class,
        );
    }

    private function importEvents(AnalyticsData $service, string $propertyId): void
    {
        $this->processImport(
            service: $service,
            propertyId: $propertyId,
            dimensions: ['date', 'eventName'],
            metrics: ['eventCount', 'eventValue'],
            extractRow: fn (Row $row): array => [
                'date' => $this->parseDate($row->getDimensionValues()[0]->getValue()),
                'event_name' => $row->getDimensionValues()[1]->getValue(),
                'event_category' => 'engagement',
                'event_action' => 'event',
                'event_count' => (int) $row->getMetricValues()[0]->getValue(),
                'event_value' => (float) $row->getMetricValues()[1]->getValue(),
            ],
            getKey: fn (array $d): string => "{$d['date']}|{$d['event_name']}",
            sumFields: ['event_count', 'event_value'],
            avgFields: [],
            findRecord: fn (array $d): ?Model => AnalyticsEvent::query()
                ->whereDate('date', $d['date'])
                ->where('event_name', $d['event_name'])
                ->where('event_category', 'engagement')
                ->where('event_action', 'event')
                ->first(),
            modelClass: AnalyticsEvent::class,
            orderByMetric: 'eventCount',
        );
    }

    private function importConversions(AnalyticsData $service, string $propertyId): void
    {
        $this->processImport(
            service: $service,
            propertyId: $propertyId,
            dimensions: ['date'],
            metrics: ['conversions', 'totalRevenue', 'sessions'],
            extractRow: fn (Row $row): array => [
                'date' => $this->parseDate($row->getDimensionValues()[0]->getValue()),
                'goal_name' => 'All Conversions',
                'goal_completions' => (int) $row->getMetricValues()[0]->getValue(),
                'goal_value' => (float) $row->getMetricValues()[1]->getValue(),
                'sessions' => (int) $row->getMetricValues()[2]->getValue(),
            ],
            getKey: fn (array $d): string => $d['date'],
            sumFields: ['goal_completions', 'goal_value', 'sessions'],
            avgFields: [],
            findRecord: fn (array $d): ?Model => AnalyticsConversion::query()
                ->whereDate('date', $d['date'])
                ->where('goal_name', 'All Conversions')
                ->first(),
            modelClass: AnalyticsConversion::class,
            transformBeforeSave: function (array $d): array {
                $d['conversion_rate'] = $d['sessions'] > 0 ? ($d['goal_completions'] / $d['sessions']) * 100 : 0;
                unset($d['sessions']);

                return $d;
            },
        );
    }

    /**
     * @param  array<string>  $dimensions
     * @param  array<string>  $metrics
     * @param  array<string>  $sumFields
     * @param  array<string>  $avgFields
     * @param  class-string<Model>  $modelClass
     */
    private function processImport(
        AnalyticsData $service,
        string $propertyId,
        array $dimensions,
        array $metrics,
        Closure $extractRow,
        Closure $getKey,
        array $sumFields,
        array $avgFields,
        Closure $findRecord,
        string $modelClass,
        ?string $orderByMetric = null,
        ?Closure $transformBeforeSave = null,
    ): void {
        $request = $this->createReportRequest($dimensions, $metrics, $orderByMetric);
        $rows = $this->runReport($service, $propertyId, $request);

        if ($rows === []) {
            return;
        }

        $aggregated = [];

        foreach ($rows as $row) {
            $data = $extractRow($row);
            $key = $getKey($data);

            if (! isset($aggregated[$key])) {
                $aggregated[$key] = [...$data, '_count' => 0];
                foreach ($sumFields as $field) {
                    $aggregated[$key][$field] = 0;
                }
            }

            foreach ($sumFields as $field) {
                $aggregated[$key][$field] += $data[$field];
            }
            $aggregated[$key]['_count']++;
        }

        foreach ($aggregated as $data) {
            foreach ($avgFields as $field) {
                $data[$field] /= $data['_count'];
            }
            unset($data['_count']);

            if ($transformBeforeSave instanceof Closure) {
                $data = $transformBeforeSave($data);
            }

            $record = $findRecord($data);

            if ($record) {
                $record->update($data);
            } else {
                $modelClass::query()->create($data);
            }
        }
    }

    /**
     * @param  array<string>  $dimensions
     * @param  array<string>  $metrics
     */
    private function createReportRequest(array $dimensions, array $metrics, ?string $orderByMetric = null): RunReportRequest
    {
        $dateRange = new DateRange();
        $dateRange->setStartDate('30daysAgo');
        $dateRange->setEndDate('today');

        $request = new RunReportRequest();
        $request->setDateRanges([$dateRange]);
        $request->setDimensions(array_map(fn (string $name) => (new Dimension())->setName($name), $dimensions));
        $request->setMetrics(array_map(fn (string $name) => (new Metric())->setName($name), $metrics));

        if ($orderByMetric) {
            $metricOrderBy = new MetricOrderBy();
            $metricOrderBy->setMetricName($orderByMetric);

            $orderBy = new OrderBy();
            $orderBy->setMetric($metricOrderBy);
            $orderBy->setDesc(true);

            $request->setOrderBys([$orderBy]);
        }

        return $request;
    }

    /**
     * @return array<Row>
     */
    private function runReport(AnalyticsData $service, string $propertyId, RunReportRequest $request): array
    {
        $response = $service->properties->runReport(
            property: 'properties/' . $propertyId,
            postBody: $request,
        );

        return $response->getRows() ?? [];
    }

    private function parseDate(string $date): string
    {
        return Date::createFromFormat('Ymd', $date)->format('Y-m-d');
    }

    private function failWithNotification(string $title, string $body): void
    {
        Notification::make()->title($title)->body($body)->danger()->send();
        $this->fail($title);
    }
}
