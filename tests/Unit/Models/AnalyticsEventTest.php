<?php

declare(strict_types=1);

use App\Models\AnalyticsEvent;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $event = AnalyticsEvent::factory()->create();

    expect($event)->toBeInstanceOf(AnalyticsEvent::class)
        ->and($event->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $event = new AnalyticsEvent();

    expect($event->getFillable())->toBe([
        'team_id',
        'date',
        'event_name',
        'event_category',
        'event_action',
        'event_label',
        'event_count',
        'event_value',
    ]);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $event = AnalyticsEvent::factory()->create(['team_id' => $team->id]);

    expect($event->team->id)->toBe($team->id);
});

it('casts date to date', function (): void {
    $event = AnalyticsEvent::factory()->create(['date' => '2024-01-01']);

    expect($event->date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts event_count to integer', function (): void {
    $event = AnalyticsEvent::factory()->create(['event_count' => '100']);

    expect($event->event_count)->toBeInt();
});
