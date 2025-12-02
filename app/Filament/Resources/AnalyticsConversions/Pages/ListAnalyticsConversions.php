<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsConversions\Pages;

use App\Filament\Resources\AnalyticsConversions\AnalyticsConversionResource;
use Filament\Resources\Pages\ListRecords;

final class ListAnalyticsConversions extends ListRecords
{
    protected static string $resource = AnalyticsConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
