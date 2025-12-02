<?php

declare(strict_types=1);

namespace App\Support;

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
 * Temporary model for Sushi to work with Google Analytics user sources data
 */
final class UserSourceModel extends Model
{
    use Sushi;

    protected $schema = [
        'source' => 'string',
        'medium' => 'string',
        'users' => 'integer',
    ];

    public function getRows(): array
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

            $dimensions = ['firstUserSource', 'firstUserMedium'];
            $metrics = ['activeUsers'];

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
                    'source' => $dimensionValues[0]->getValue(),
                    'medium' => $dimensionValues[1]->getValue(),
                    'users' => (int) $metricValues[0]->getValue(),
                ];
            }

            return $rows;
        } catch (Exception) {
            return [];
        }
    }
}
