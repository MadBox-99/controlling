<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsPageviews\Pages;

use App\Filament\Resources\AnalyticsPageviews\AnalyticsPageviewResource;
use Filament\Resources\Pages\ListRecords;

final class ListAnalyticsPageviews extends ListRecords
{
    protected static string $resource = AnalyticsPageviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
