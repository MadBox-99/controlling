<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Jobs\AnalyticsImport;
use App\Jobs\SearchConsoleImport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

final class ManualSync extends Page
{
    public ?array $data = [];

    protected string $view = 'filament.pages.manual-sync';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Configuration;

    protected static ?int $navigationSort = 98;

    public function performAnalyticsSync(): void
    {
        dispatch(new AnalyticsImport());

        Notification::make()
            ->title('Analytics sync started successfully.')
            ->body('The Analytics synchronization process has been initiated in the background.')
            ->success()
            ->send();
    }

    public function performSearchConsoleSync(): void
    {
        dispatch(new SearchConsoleImport());

        Notification::make()
            ->title('Search Console sync started successfully.')
            ->body('The Search Console synchronization process has been initiated in the background.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncAnalytics')
                ->label('Sync Analytics')
                ->icon('heroicon-o-chart-bar')
                ->color('primary')
                ->action('performAnalyticsSync'),
            Action::make('syncSearchConsole')
                ->label('Sync Search Console')
                ->icon('heroicon-o-magnifying-glass')
                ->color('success')
                ->action('performSearchConsoleSync'),
        ];
    }
}
