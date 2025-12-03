<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsConversions;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AnalyticsConversions\Pages\CreateAnalyticsConversion;
use App\Filament\Resources\AnalyticsConversions\Pages\EditAnalyticsConversion;
use App\Filament\Resources\AnalyticsConversions\Pages\ListAnalyticsConversions;
use App\Filament\Resources\AnalyticsConversions\Schemas\AnalyticsConversionForm;
use App\Filament\Resources\AnalyticsConversions\Tables\AnalyticsConversionsTable;
use App\Models\AnalyticsConversion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class AnalyticsConversionResource extends Resource
{
    protected static ?string $model = AnalyticsConversion::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string
    {
        return __('Conversion');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Conversions');
    }

    public static function form(Schema $schema): Schema
    {
        return AnalyticsConversionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnalyticsConversionsTable::configure($table);
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
            'index' => ListAnalyticsConversions::route('/'),
            'create' => CreateAnalyticsConversion::route('/create'),
            'edit' => EditAnalyticsConversion::route('/{record}/edit'),
        ];
    }
}
