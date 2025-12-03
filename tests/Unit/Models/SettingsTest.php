<?php

declare(strict_types=1);

use App\Models\Settings;
use App\Models\Team;

it('can be created using factory', function (): void {
    $settings = Settings::factory()->create();

    expect($settings)->toBeInstanceOf(Settings::class)
        ->and($settings->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $settings = new Settings();

    expect($settings->getFillable())->toBe([
        'team_id',
        'property_id',
        'google_tag_id',
        'site_url',
        'last_sync_at',
    ]);
});

it('casts last_sync_at to datetime', function (): void {
    $settings = Settings::factory()->create(['last_sync_at' => now()]);

    expect($settings->last_sync_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $settings = Settings::factory()->create(['team_id' => $team->id]);

    expect($settings->team)->toBeInstanceOf(Team::class)
        ->and($settings->team->id)->toBe($team->id);
});

it('can access settings through team relationship', function (): void {
    $team = Team::factory()->create();
    $settings = Settings::factory()->create(['team_id' => $team->id]);

    expect($team->settings)->toBeInstanceOf(Settings::class)
        ->and($team->settings->id)->toBe($settings->id);
});
