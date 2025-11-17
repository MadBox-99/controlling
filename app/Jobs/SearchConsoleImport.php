<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SearchPage;
use App\Models\SearchQuery;
use App\Models\Settings;
use Filament\Notifications\Notification;
use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

final class SearchConsoleImport implements ShouldQueue
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

        if (! $settings->site_url) {
            Notification::make()
                ->title('Site URL not configured.')
                ->body('Please configure the Site URL in Settings first.')
                ->danger()
                ->send();

            $this->fail('Site URL not configured.');

            return;
        }

        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes(['https://www.googleapis.com/auth/webmasters.readonly']);
        $client->setAuthConfig(Storage::json($settings->google_service_account));
        $service = new SearchConsole($client);

        // Import Search Console data
        $this->importSearchQueries($service, $settings->site_url);
        $this->importSearchPages($service, $settings->site_url);

        Notification::make()
            ->title('Search Console import completed successfully.')
            ->body('All Search Console data has been imported and KPIs have been synced.')
            ->success()
            ->send();
    }

    private function importSearchQueries(SearchConsole $service, string $siteUrl): void
    {
        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate(Date::now()->subDays(30)->format('Y-m-d'));
        $request->setEndDate(Date::now()->format('Y-m-d'));
        $request->setDimensions(['date', 'query', 'country', 'device']);
        $request->setRowLimit(25000);

        $response = $service->searchanalytics->query($siteUrl, $request);

        if (! $response->getRows()) {
            return;
        }

        $queryData = [];

        foreach ($response->getRows() as $row) {
            $date = $row->getKeys()[0];
            $query = $row->getKeys()[1];
            $country = $row->getKeys()[2];
            $device = $row->getKeys()[3];

            $key = $date . '|' . $query . '|' . $country . '|' . $device;

            if (! isset($queryData[$key])) {
                $queryData[$key] = [
                    'date' => $date,
                    'query' => $query,
                    'country' => $country,
                    'device' => $device,
                    'impressions' => 0,
                    'clicks' => 0,
                    'ctr' => 0,
                    'position' => 0,
                    'count' => 0,
                ];
            }

            $queryData[$key]['impressions'] += (int) $row->getImpressions();
            $queryData[$key]['clicks'] += (int) $row->getClicks();
            $queryData[$key]['ctr'] += $row->getCtr() * 100;
            $queryData[$key]['position'] += $row->getPosition();
            $queryData[$key]['count']++;
        }

        foreach ($queryData as $data) {
            $record = SearchQuery::query()
                ->whereDate('date', $data['date'])
                ->where('query', $data['query'])
                ->where('country', $data['country'])
                ->where('device', $data['device'])
                ->first();

            if ($record) {
                $record->update([
                    'impressions' => $data['impressions'],
                    'clicks' => $data['clicks'],
                    'ctr' => $data['ctr'] / $data['count'],
                    'position' => $data['position'] / $data['count'],
                ]);
            } else {
                SearchQuery::query()->create([
                    'date' => $data['date'],
                    'query' => $data['query'],
                    'country' => $data['country'],
                    'device' => $data['device'],
                    'impressions' => $data['impressions'],
                    'clicks' => $data['clicks'],
                    'ctr' => $data['ctr'] / $data['count'],
                    'position' => $data['position'] / $data['count'],
                ]);
            }
        }
    }

    private function importSearchPages(SearchConsole $service, string $siteUrl): void
    {
        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate(Date::now()->subDays(30)->format('Y-m-d'));
        $request->setEndDate(Date::now()->format('Y-m-d'));
        $request->setDimensions(['date', 'page', 'country', 'device']);
        $request->setRowLimit(25000);

        $response = $service->searchanalytics->query($siteUrl, $request);

        if (! $response->getRows()) {
            return;
        }

        $pageData = [];

        foreach ($response->getRows() as $row) {
            $date = $row->getKeys()[0];
            $pageUrl = $row->getKeys()[1];
            $country = $row->getKeys()[2];
            $device = $row->getKeys()[3];

            $key = $date . '|' . $pageUrl . '|' . $country . '|' . $device;

            if (! isset($pageData[$key])) {
                $pageData[$key] = [
                    'date' => $date,
                    'page_url' => $pageUrl,
                    'country' => $country,
                    'device' => $device,
                    'impressions' => 0,
                    'clicks' => 0,
                    'ctr' => 0,
                    'position' => 0,
                    'count' => 0,
                ];
            }

            $pageData[$key]['impressions'] += (int) $row->getImpressions();
            $pageData[$key]['clicks'] += (int) $row->getClicks();
            $pageData[$key]['ctr'] += $row->getCtr() * 100;
            $pageData[$key]['position'] += $row->getPosition();
            $pageData[$key]['count']++;
        }

        foreach ($pageData as $data) {
            $record = SearchPage::query()
                ->whereDate('date', $data['date'])
                ->where('page_url', $data['page_url'])
                ->where('country', $data['country'])
                ->where('device', $data['device'])
                ->first();

            if ($record) {
                $record->update([
                    'impressions' => $data['impressions'],
                    'clicks' => $data['clicks'],
                    'ctr' => $data['ctr'] / $data['count'],
                    'position' => $data['position'] / $data['count'],
                ]);
            } else {
                SearchPage::query()->create([
                    'date' => $data['date'],
                    'page_url' => $data['page_url'],
                    'country' => $data['country'],
                    'device' => $data['device'],
                    'impressions' => $data['impressions'],
                    'clicks' => $data['clicks'],
                    'ctr' => $data['ctr'] / $data['count'],
                    'position' => $data['position'] / $data['count'],
                ]);
            }
        }
    }
}
