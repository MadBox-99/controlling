<?php

declare(strict_types=1);

use App\Models\AnalyticsConversion;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $conversion = AnalyticsConversion::factory()->create();

    expect($conversion)->toBeInstanceOf(AnalyticsConversion::class)
        ->and($conversion->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $conversion = new AnalyticsConversion();

    expect($conversion->getFillable())->toBe([
        'team_id',
        'date',
        'goal_name',
        'goal_completions',
        'goal_value',
        'conversion_rate',
    ]);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $conversion = AnalyticsConversion::factory()->create(['team_id' => $team->id]);

    expect($conversion->team->id)->toBe($team->id);
});

it('casts date to date', function (): void {
    $conversion = AnalyticsConversion::factory()->create(['date' => '2024-01-01']);

    expect($conversion->date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts goal_completions to integer', function (): void {
    $conversion = AnalyticsConversion::factory()->create(['goal_completions' => '50']);

    expect($conversion->goal_completions)->toBeInt();
});
