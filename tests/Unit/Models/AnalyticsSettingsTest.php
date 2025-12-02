<?php

declare(strict_types=1);

use App\Enums\GoogleAnalitycs\OrderByType;
use App\Models\AnalyticsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $settings = AnalyticsSettings::factory()->create();

    expect($settings)->toBeInstanceOf(AnalyticsSettings::class)
        ->and($settings->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $settings = new AnalyticsSettings();

    expect($settings->getFillable())->toBe([
        'dimensions',
        'metrics',
        'order_by',
        'order_by_type',
        'order_by_direction',
    ]);
});

it('casts dimensions to array', function (): void {
    $dimensions = ['date', 'country'];
    $settings = AnalyticsSettings::factory()->create(['dimensions' => $dimensions]);

    expect($settings->dimensions)->toBeArray()
        ->and($settings->dimensions)->toBe($dimensions);
});

it('casts metrics to array', function (): void {
    $metrics = ['sessions', 'users'];
    $settings = AnalyticsSettings::factory()->create(['metrics' => $metrics]);

    expect($settings->metrics)->toBeArray()
        ->and($settings->metrics)->toBe($metrics);
});

it('casts order_by to array', function (): void {
    $orderBy = ['sessions'];
    $settings = AnalyticsSettings::factory()->create(['order_by' => $orderBy]);

    expect($settings->order_by)->toBeArray()
        ->and($settings->order_by)->toBe($orderBy);
});

it('casts order_by_type to enum', function (): void {
    $settings = AnalyticsSettings::factory()->create();

    expect($settings->order_by_type)->toBeInstanceOf(OrderByType::class);
});
