<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\GlobalSetting;
use App\Services\GoogleClientFactory;
use Exception;
use Filament\Facades\Filament;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\RunReportRequest;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

/**
 * Temporary model for Sushi to work with Google Analytics data
 */
final class SourcePageModel extends Model
{
    use Sushi;

    protected $schema = [
        'source' => 'string',
        'medium' => 'string',
        'page_path' => 'string',
        'page_title' => 'string',
        'sessions' => 'integer',
        'users' => 'integer',
        'pageviews' => 'integer',
    ];

    public function getRows(): array
    {
        try {
            $globalSettings = GlobalSetting::instance();
            $serviceAccount = $globalSettings->getServiceAccount();

            if (! $serviceAccount) {
                return [];
            }

            $settings = Filament::getTenant()?->settings;

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

            $sourceDimension = new Dimension();
            $sourceDimension->setName('sessionSource');

            $mediumDimension = new Dimension();
            $mediumDimension->setName('sessionMedium');

            $pagePathDimension = new Dimension();
            $pagePathDimension->setName('pagePath');

            $pageTitleDimension = new Dimension();
            $pageTitleDimension->setName('pageTitle');

            $sessionsMetric = new Metric();
            $sessionsMetric->setName('sessions');

            $usersMetric = new Metric();
            $usersMetric->setName('activeUsers');

            $pageviewsMetric = new Metric();
            $pageviewsMetric->setName('screenPageViews');

            $request = new RunReportRequest();
            $request->setDateRanges([$dateRange]);
            $request->setDimensions([
                $sourceDimension,
                $mediumDimension,
                $pagePathDimension,
                $pageTitleDimension,
            ]);
            $request->setMetrics([
                $sessionsMetric,
                $usersMetric,
                $pageviewsMetric,
            ]);

            $response = $service->properties->runReport(
                property: "properties/{$settings->property_id}",
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
                    'page_path' => $dimensionValues[2]->getValue(),
                    'page_title' => $dimensionValues[3]->getValue(),
                    'sessions' => (int) $metricValues[0]->getValue(),
                    'users' => (int) $metricValues[1]->getValue(),
                    'pageviews' => (int) $metricValues[2]->getValue(),
                ];
            }

            return $rows;
        } catch (Exception) {
            return [];
        }
    }
}
