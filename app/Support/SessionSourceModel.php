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
 * Temporary model for Sushi to work with Google Analytics session sources data
 */
final class SessionSourceModel extends Model
{
    use Sushi;

    protected $schema = [
        'source' => 'string',
        'medium' => 'string',
        'sessions' => 'integer',
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
                (new Dimension())->setName('sessionSource'),
                (new Dimension())->setName('sessionMedium'),
            ]);
            $request->setMetrics([
                (new Metric())->setName('sessions'),
            ]);

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
                    'source' => $dimensionValues[0]->getValue(),
                    'medium' => $dimensionValues[1]->getValue(),
                    'sessions' => (int) $metricValues[0]->getValue(),
                ];
            }

            return $rows;
        } catch (Exception) {
            return [];
        }
    }
}
