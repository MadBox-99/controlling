<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Jobs\AnalyticsImport;
use App\Jobs\SearchConsoleImport;
use App\Models\Settings as SettingsModel;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class Settings extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected string $view = 'filament.pages.settings';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Configuration;

    protected static ?int $navigationSort = 99;

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray() ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Google Analytics Configuration')
                        ->description('Configure Google Analytics 4 property settings for this team')
                        ->schema([
                            TextInput::make('property_id')
                                ->label('GA4 Property ID')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('123456789'),
                            TextInput::make('google_tag_id')
                                ->label('Google Tag ID')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('G-XXXXXXXXXX'),
                        ])
                        ->columns(2),

                    Section::make('Google Search Console Configuration')
                        ->description('Configure Search Console property settings for this team')
                        ->schema([
                            TextInput::make('site_url')
                                ->label('Site URL')
                                ->required()
                                ->url()
                                ->maxLength(500)
                                ->placeholder('https://example.com'),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = $this->getRecord();

        if (! $record instanceof SettingsModel) {
            $record = new SettingsModel();
            $record->team_id = Filament::getTenant()?->id;
        }

        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();
    }

    public function getRecord(): ?SettingsModel
    {
        return Filament::getTenant()?->settings;
    }

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
                ->action('performAnalyticsSync'),
            Action::make('syncSearchConsole')
                ->label('Sync Search Console')
                ->icon('heroicon-o-magnifying-glass')
                ->color('success')
                ->action('performSearchConsoleSync'),
        ];
    }
}
