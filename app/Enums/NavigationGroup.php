<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum NavigationGroup implements HasIcon, HasLabel
{
    case Analytics;
    case SearchConsole;
    case Kpis;
    case Reports;
    case Configuration;
    case Settings;

    public function getLabel(): string
    {
        return match ($this) {
            self::Analytics => 'Analytics',
            self::SearchConsole => 'Search Console',
            self::Kpis => 'KPIs',
            self::Reports => 'Reports',
            self::Configuration => 'Configuration',
            self::Settings => 'Settings',
        };
    }

    public function getIcon(): string|Heroicon|null
    {
        return match ($this) {
            self::Analytics => Heroicon::OutlinedChartBar,
            self::SearchConsole => Heroicon::OutlinedMagnifyingGlass,
            self::Kpis => Heroicon::OutlinedChartPie,
            self::Reports => Heroicon::OutlinedDocumentChartBar,
            self::Configuration => Heroicon::OutlinedCog6Tooth,
            self::Settings => Heroicon::OutlinedCog,
        };
    }
}
