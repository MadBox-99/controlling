<?php

declare(strict_types=1);

use App\Enums\KpiCategory;
use App\Enums\KpiDataSource;
use App\Enums\KpiGoalType;
use App\Enums\KpiValueType;
use App\Models\Kpi;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $kpi = Kpi::factory()->create();

    expect($kpi)->toBeInstanceOf(Kpi::class)
        ->and($kpi->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $kpi = new Kpi();

    expect($kpi->getFillable())->toBe([
        'team_id',
        'code',
        'name',
        'description',
        'data_source',
        'source_type',
        'category',
        'formula',
        'format',
        'target_value',
        'target_date',
        'from_date',
        'comparison_start_date',
        'comparison_end_date',
        'goal_type',
        'value_type',
        'page_path',
        'metric_type',
        'is_active',
    ]);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $kpi = Kpi::factory()->create(['team_id' => $team->id]);

    expect($kpi->team->id)->toBe($team->id);
});

it('casts data_source to enum', function (): void {
    $kpi = Kpi::factory()->create();

    expect($kpi->data_source)->toBeInstanceOf(KpiDataSource::class);
});

it('casts category to enum', function (): void {
    $kpi = Kpi::factory()->create();

    expect($kpi->category)->toBeInstanceOf(KpiCategory::class);
});

it('casts goal_type to enum', function (): void {
    $kpi = Kpi::factory()->create();

    expect($kpi->goal_type)->toBeInstanceOf(KpiGoalType::class);
});

it('casts value_type to enum', function (): void {
    $kpi = Kpi::factory()->create();

    expect($kpi->value_type)->toBeInstanceOf(KpiValueType::class);
});

it('casts target_date to date', function (): void {
    $kpi = Kpi::factory()->create(['target_date' => '2024-12-31']);

    expect($kpi->target_date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts from_date to date', function (): void {
    $kpi = Kpi::factory()->create(['from_date' => '2024-01-01']);

    expect($kpi->from_date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts is_active to boolean', function (): void {
    $kpi = Kpi::factory()->create(['is_active' => 1]);

    expect($kpi->is_active)->toBeBool()
        ->and($kpi->is_active)->toBeTrue();
});
