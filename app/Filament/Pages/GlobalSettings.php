<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\GlobalSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class GlobalSettings extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected string $view = 'filament.pages.global-settings';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Configuration;

    protected static ?int $navigationSort = 100;

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill($this->getRecord()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Google Service Account')
                        ->description('Upload the Google Service Account JSON key file for API authentication. This setting is shared across all teams.')
                        ->schema([
                            FileUpload::make('google_service_account')
                                ->label('Service Account JSON Key')
                                ->acceptedFileTypes(['application/json'])
                                ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => 'google-service-account.json')
                                ->maxSize(1024)
                                ->helperText('Upload the JSON key file from Google Cloud Console'),
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
        $record->fill($data);
        $record->save();

        Notification::make()
            ->success()
            ->title('Global settings saved')
            ->send();
    }

    public function getRecord(): GlobalSetting
    {
        return GlobalSetting::instance();
    }
}
