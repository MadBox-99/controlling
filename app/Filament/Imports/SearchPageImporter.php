<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\SearchPage;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

final class SearchPageImporter extends Importer
{
    protected static ?string $model = SearchPage::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('page_url')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('country'),
            ImportColumn::make('device')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('impressions')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('clicks')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('ctr')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('position')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your search page import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): SearchPage
    {
        return SearchPage::firstOrNew([
            'page_url' => $this->data['page_url'],
        ]);
    }
}
