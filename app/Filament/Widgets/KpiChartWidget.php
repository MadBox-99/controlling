<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Kpi;
use App\Models\KpiValue;
use Filament\Widgets\Widget;

final class KpiChartWidget extends Widget
{
    public ?int $selectedKpiId = null;

    protected string $view = 'filament.widgets.kpi-chart-widget';

    protected int|string|array $columnSpan = 1;

    public function mount(): void
    {
        // Default to first active KPI
        if (! $this->selectedKpiId) {
            $this->selectedKpiId = Kpi::query()
                ->where('is_active', true)
                ->first()?->id;
        }
    }

    public function getKpis()
    {
        return Kpi::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    public function getSelectedKpi()
    {
        if (! $this->selectedKpiId) {
            return;
        }

        return Kpi::query()->find($this->selectedKpiId);
    }

    public function getKpiData(): ?array
    {
        if (! $this->selectedKpiId) {
            return null;
        }

        $kpi = $this->getSelectedKpi();

        if (! $kpi) {
            return null;
        }

        // Get latest KPI value
        $latestValue = KpiValue::query()
            ->where('kpi_id', $this->selectedKpiId)
            ->orderBy('period', 'desc')
            ->first();

        if (! $latestValue || ! $kpi->target_value) {
            return [
                'kpi' => $kpi,
                'latestValue' => $latestValue,
                'achievedPercentage' => 0,
                'remainingPercentage' => 100,
                'isTargetMet' => false,
            ];
        }

        $achievedPercentage = min(100, ($latestValue->actual_value / $kpi->target_value) * 100);
        $remainingPercentage = max(0, 100 - $achievedPercentage);

        return [
            'kpi' => $kpi,
            'latestValue' => $latestValue,
            'achievedPercentage' => round($achievedPercentage, 2),
            'remainingPercentage' => round($remainingPercentage, 2),
            'isTargetMet' => $latestValue->actual_value >= $kpi->target_value,
        ];
    }

    public function updatedSelectedKpiId(?int $value): void
    {
        $this->selectedKpiId = $value;
    }
}
