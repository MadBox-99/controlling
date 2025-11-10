<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\GoogleAnalitycs\OrderByType;
use App\Enums\NavigationGroup;
use App\Models\AnalyticsSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class ManageAnalyticsSettings extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected string $view = 'filament.pages.manage-analytics-settings';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Configuration;

    protected static ?int $navigationSort = 97;

    protected static ?string $navigationLabel = 'Analytics Settings';

    protected static ?string $title = 'Analytics Settings';

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Dimensions')
                        ->description('Configure the dimensions for Analytics reports')
                        ->schema([
                            Repeater::make('dimensions')
                                ->schema([
                                    TextInput::make('name')
                                        ->label(__('Dimension Name'))
                                        ->required(),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel(__('Add Dimension'))
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                        ]),

                    Section::make('Metrics')
                        ->description('Configure the metrics for Analytics reports')
                        ->schema([
                            Repeater::make('metrics')
                                ->schema([
                                    TextInput::make('name')
                                        ->label(__('Metric Name'))
                                        ->required(),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel(__('Add Metric'))
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                        ]),

                    Section::make('Order By')
                        ->description('Configure sorting for the report results')
                        ->schema([
                            Select::make('order_by_type')
                                ->label('Order By Type')
                                ->required()
                                ->options(OrderByType::class)
                                ->live()
                                ->afterStateUpdated(fn (Set $set): mixed => $set('order_by', '')),

                            Select::make('order_by')
                                ->label('Field')
                                ->required()
                                ->options(function (callable $get) {
                                    $type = $get('type');

                                    $record = $this->getRecord();
                                    if (! $record instanceof AnalyticsSettings) {
                                        return [];
                                    }
                                    if ($record->order_by_type === OrderByType::DIMENSION) {
                                        return collect($record->dimensions ?? [])
                                            ->mapWithKeys(fn (array $dimension): array => [$dimension['name'] => $dimension['name']])
                                            ->toArray();
                                    }
                                    if ($record->order_by_type === OrderByType::METRIC) {
                                        return collect($record->metrics ?? [])
                                            ->mapWithKeys(fn (array $metric): array => [$metric['name'] => $metric['name']])
                                            ->toArray();
                                    }
                                })
                                ->searchable(),
                            Select::make('order_by_direction')
                                ->label('Direction')
                                ->required()
                                ->options([
                                    'asc' => __('Ascending'),
                                    'desc' => __('Descending'),
                                ])
                                ->default('desc'),
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

        if (! $record instanceof AnalyticsSettings) {
            $record = new AnalyticsSettings();
        }

        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }

        Notification::make()
            ->success()
            ->title('Analytics settings saved')
            ->send();
    }

    public function getRecord(): ?AnalyticsSettings
    {
        return AnalyticsSettings::query()->first();
    }
}
