<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsEvents\Pages;

use App\Filament\Resources\AnalyticsEvents\AnalyticsEventResource;
use Filament\Resources\Pages\ListRecords;

final class ListAnalyticsEvents extends ListRecords
{
    protected static string $resource = AnalyticsEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
