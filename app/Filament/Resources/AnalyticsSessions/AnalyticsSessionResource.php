<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsSessions;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AnalyticsSessions\Pages\CreateAnalyticsSession;
use App\Filament\Resources\AnalyticsSessions\Pages\EditAnalyticsSession;
use App\Filament\Resources\AnalyticsSessions\Pages\ListAnalyticsSessions;
use App\Filament\Resources\AnalyticsSessions\Schemas\AnalyticsSessionForm;
use App\Filament\Resources\AnalyticsSessions\Tables\AnalyticsSessionsTable;
use App\Models\AnalyticsSession;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class AnalyticsSessionResource extends Resource
{
    protected static ?string $model = AnalyticsSession::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('Session');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sessions');
    }

    public static function form(Schema $schema): Schema
    {
        return AnalyticsSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnalyticsSessionsTable::configure($table);
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
            'index' => ListAnalyticsSessions::route('/'),
            'create' => CreateAnalyticsSession::route('/create'),
            'edit' => EditAnalyticsSession::route('/{record}/edit'),
        ];
    }
}
