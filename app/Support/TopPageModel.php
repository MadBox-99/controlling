<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Settings;
use Exception;
use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Sushi\Sushi;

/**
 * Temporary model for Sushi to work with Google Analytics top pages data
 */
final class TopPageModel extends Model
{
    use Sushi;

    protected $schema = [
        'page_title' => 'string',
        'page_path' => 'string',
        'views' => 'integer',
        'active_users' => 'integer',
        'event_count' => 'integer',
        'bounce_rate' => 'integer',
    ];

    public function getRows(): array
    {
        try {
            $settings = Settings::query()->first();

            if (! $settings || ! $settings->google_service_account || ! $settings->property_id) {
                return [];
            }

            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
            $client->setAuthConfig(Storage::json($settings->google_service_account));

            $service = new AnalyticsData($client);

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
            $request->setLimit(100);

            $response = $service->properties->runReport(
                property: 'properties/' . $settings->property_id,
                postBody: $request,
            );

            $rows = [];
            $id = 1;

            foreach ($response->getRows() as $row) {
                $dimensionValues = $row->getDimensionValues();
                $metricValues = $row->getMetricValues();

                $rows[] = [
                    'id' => $id++,
                    'page_title' => $dimensionValues[0]->getValue(),
                    'page_path' => $dimensionValues[1]->getValue(),
                    'views' => (int) $metricValues[0]->getValue(),
                    'active_users' => (int) $metricValues[1]->getValue(),
                    'event_count' => (int) $metricValues[2]->getValue(),
                    'bounce_rate' => round((float) $metricValues[3]->getValue() * 100, 2),
                ];
            }

            return $rows;
        } catch (Exception) {
            return [];
        }
    }
}
