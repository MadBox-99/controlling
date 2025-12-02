<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsSessions\Pages;

use App\Filament\Resources\AnalyticsSessions\AnalyticsSessionResource;
use Filament\Resources\Pages\ListRecords;

final class ListAnalyticsSessions extends ListRecords
{
    protected static string $resource = AnalyticsSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
