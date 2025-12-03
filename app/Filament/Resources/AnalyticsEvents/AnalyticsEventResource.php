<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsEvents;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AnalyticsEvents\Pages\CreateAnalyticsEvent;
use App\Filament\Resources\AnalyticsEvents\Pages\EditAnalyticsEvent;
use App\Filament\Resources\AnalyticsEvents\Pages\ListAnalyticsEvents;
use App\Filament\Resources\AnalyticsEvents\Schemas\AnalyticsEventForm;
use App\Filament\Resources\AnalyticsEvents\Tables\AnalyticsEventsTable;
use App\Models\AnalyticsEvent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class AnalyticsEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Analytics;

    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string
    {
        return __('Event');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Events');
    }

    public static function form(Schema $schema): Schema
    {
        return AnalyticsEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnalyticsEventsTable::configure($table);
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
            'index' => ListAnalyticsEvents::route('/'),
            'create' => CreateAnalyticsEvent::route('/create'),
            'edit' => EditAnalyticsEvent::route('/{record}/edit'),
        ];
    }
}
