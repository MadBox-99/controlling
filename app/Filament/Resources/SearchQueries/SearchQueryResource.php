<?php

declare(strict_types=1);

namespace App\Filament\Resources\SearchQueries;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SearchQueries\Pages\CreateSearchQuery;
use App\Filament\Resources\SearchQueries\Pages\EditSearchQuery;
use App\Filament\Resources\SearchQueries\Pages\ListSearchQueries;
use App\Filament\Resources\SearchQueries\Schemas\SearchQueryForm;
use App\Filament\Resources\SearchQueries\Tables\SearchQueriesTable;
use App\Models\SearchQuery;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class SearchQueryResource extends Resource
{
    protected static ?string $model = SearchQuery::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SearchConsole;

    public static function getModelLabel(): string
    {
        return __('Search Query');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Search Queries');
    }

    public static function form(Schema $schema): Schema
    {
        return SearchQueryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SearchQueriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSearchQueries::route('/'),
            'create' => CreateSearchQuery::route('/create'),
            'edit' => EditSearchQuery::route('/{record}/edit'),
        ];
    }
}
