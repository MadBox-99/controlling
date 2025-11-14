<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Kpi;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

final class KpiImporter extends Importer
{
    protected static ?string $model = Kpi::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('description'),
            ImportColumn::make('data_source')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('category')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('target_value')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your kpi import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): Kpi
    {
        return Kpi::query()->firstOrNew([
            'code' => $this->data['code'],
        ]);
    }
}
