<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\GlobalSetting;
use App\Models\Settings;
use App\Services\GoogleClientFactory;
use Exception;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Database\Eloquent\Model;
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
            $globalSettings = GlobalSetting::instance();
            $serviceAccount = $globalSettings->getServiceAccount();

            if (! $serviceAccount) {
                return [];
            }

            $settings = Settings::query()->first();

            if (! $settings || ! $settings->property_id) {
                return [];
            }

            $client = GoogleClientFactory::make(
                'https://www.googleapis.com/auth/analytics.readonly',
                $globalSettings->google_service_account,
            );
            $service = new AnalyticsData($client);

            $dateRange = new DateRange();
            $dateRange->setStartDate('30daysAgo');
            $dateRange->setEndDate('today');

            $dimensions = ['pageTitle', 'pagePath'];
            $metrics = ['screenPageViews', 'activeUsers', 'eventCount', 'bounceRate'];

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
            $request->setLimit(100);

            $response = $service->properties->runReport(
                'properties/' . $settings->property_id,
                $request,
            );

            $rows = [];
            $id = 1;

            foreach ($response->getRows() ?? [] as $row) {
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
