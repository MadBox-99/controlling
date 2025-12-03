<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Jobs\AnalyticsImport;
use App\Jobs\SearchConsoleImport;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

final class ManualSync extends Page
{
    public ?array $data = [];

    protected string $view = 'filament.pages.manual-sync';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Configuration;

    protected static ?int $navigationSort = 98;

    public function performAnalyticsSync(): void
    {
        $teamId = Filament::getTenant()?->id;

        if (! $teamId) {
            Notification::make()
                ->title('No team selected.')
                ->danger()
                ->send();

            return;
        }

        dispatch(new AnalyticsImport($teamId));

        Notification::make()
            ->title('Analytics sync started successfully.')
            ->body('The Analytics synchronization process has been initiated in the background.')
            ->success()
            ->send();
    }

    public function performSearchConsoleSync(): void
    {
        $teamId = Filament::getTenant()?->id;

        if (! $teamId) {
            Notification::make()
                ->title('No team selected.')
                ->danger()
                ->send();

            return;
        }

        dispatch(new SearchConsoleImport($teamId));

        Notification::make()
            ->title('Search Console sync started successfully.')
            ->body('The Search Console synchronization process has been initiated in the background.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        $isAdmin = Auth::user()?->isAdmin() ?? false;

        return [
            Action::make('syncAnalytics')
                ->label('Sync Analytics')
                ->icon('heroicon-o-chart-bar')
                ->color('primary')
                ->action('performAnalyticsSync')
                ->disabled(! $isAdmin),
            Action::make('syncSearchConsole')
                ->label('Sync Search Console')
                ->icon('heroicon-o-magnifying-glass')
                ->color('success')
                ->action('performSearchConsoleSync')
                ->disabled(! $isAdmin),
        ];
    }
}
