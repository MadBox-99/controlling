<?php

declare(strict_types=1);

namespace App\Filament\Resources\SearchPages\Pages;

use App\Filament\Resources\SearchPages\SearchPageResource;
use Filament\Resources\Pages\ListRecords;

final class ListSearchPages extends ListRecords
{
    protected static string $resource = SearchPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
