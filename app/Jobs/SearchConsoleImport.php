<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SearchPage;
use App\Models\SearchQuery;
use App\Models\Settings;
use App\Services\GoogleClientFactory;

use function array_slice;

use Filament\Notifications\Notification;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;

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

        $client = GoogleClientFactory::make(
            'https://www.googleapis.com/auth/webmasters.readonly',
            $settings->google_service_account,
        );
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
        $request = $this->createSearchRequest(['date', 'query', 'country', 'device']);
        $response = $service->searchanalytics->query($siteUrl, $request);

        if (! $response->getRows()) {
            return;
        }

        collect($response->getRows())
            ->groupBy(fn ($row): string => implode('|', array_slice($row->getKeys(), 0, 4)))
            ->each(function ($rows): void {
                $first = $rows->first();

                $values = [
                    'impressions' => $rows->sum(fn ($r): int => (int) $r->getImpressions()),
                    'clicks' => $rows->sum(fn ($r): int => (int) $r->getClicks()),
                    'ctr' => $rows->avg(fn ($r): int|float => $r->getCtr() * 100),
                    'position' => $rows->avg(fn ($r) => $r->getPosition()),
                ];

                $record = SearchQuery::query()
                    ->whereDate('date', $first->getKeys()[0])
                    ->where('query', $first->getKeys()[1])
                    ->where('country', $first->getKeys()[2])
                    ->where('device', $first->getKeys()[3])
                    ->first();

                if ($record) {
                    $record->update($values);
                } else {
                    SearchQuery::query()->create([
                        'date' => $first->getKeys()[0],
                        'query' => $first->getKeys()[1],
                        'country' => $first->getKeys()[2],
                        'device' => $first->getKeys()[3],
                        ...$values,
                    ]);
                }
            });
    }

    private function importSearchPages(SearchConsole $service, string $siteUrl): void
    {
        $request = $this->createSearchRequest(['date', 'page', 'country', 'device']);
        $response = $service->searchanalytics->query($siteUrl, $request);

        if (! $response->getRows()) {
            return;
        }

        collect($response->getRows())
            ->groupBy(fn ($row): string => implode('|', array_slice($row->getKeys(), 0, 4)))
            ->each(function ($rows): void {
                $first = $rows->first();

                $values = [
                    'impressions' => $rows->sum(fn ($r): int => (int) $r->getImpressions()),
                    'clicks' => $rows->sum(fn ($r): int => (int) $r->getClicks()),
                    'ctr' => $rows->avg(fn ($r): int|float => $r->getCtr() * 100),
                    'position' => $rows->avg(fn ($r) => $r->getPosition()),
                ];

                $record = SearchPage::query()
                    ->whereDate('date', $first->getKeys()[0])
                    ->where('page_url', $first->getKeys()[1])
                    ->where('country', $first->getKeys()[2])
                    ->where('device', $first->getKeys()[3])
                    ->first();

                if ($record) {
                    $record->update($values);
                } else {
                    SearchPage::query()->create([
                        'date' => $first->getKeys()[0],
                        'page_url' => $first->getKeys()[1],
                        'country' => $first->getKeys()[2],
                        'device' => $first->getKeys()[3],
                        ...$values,
                    ]);
                }
            });
    }

    /**
     * @param  array<string>  $dimensions
     */
    private function createSearchRequest(array $dimensions): SearchAnalyticsQueryRequest
    {
        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate(Date::now()->subDays(90)->format('Y-m-d'));
        $request->setEndDate(Date::now()->format('Y-m-d'));
        $request->setDimensions($dimensions);
        $request->setRowLimit(25000);

        return $request;
    }
}
