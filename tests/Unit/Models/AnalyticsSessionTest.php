<?php

declare(strict_types=1);

use App\Models\AnalyticsSession;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $session = AnalyticsSession::factory()->create();

    expect($session)->toBeInstanceOf(AnalyticsSession::class)
        ->and($session->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $session = new AnalyticsSession();

    expect($session->getFillable())->toBe([
        'team_id',
        'date',
        'sessions',
        'users',
        'new_users',
        'bounce_rate',
        'avg_session_duration',
        'pages_per_session',
        'source',
        'medium',
        'campaign',
    ]);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $session = AnalyticsSession::factory()->create(['team_id' => $team->id]);

    expect($session->team->id)->toBe($team->id);
});

it('casts date to date', function (): void {
    $session = AnalyticsSession::factory()->create(['date' => '2024-01-01']);

    expect($session->date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts sessions to integer', function (): void {
    $session = AnalyticsSession::factory()->create(['sessions' => '100']);

    expect($session->sessions)->toBeInt();
});

it('casts users to integer', function (): void {
    $session = AnalyticsSession::factory()->create(['users' => '50']);

    expect($session->users)->toBeInt();
});

it('casts new_users to integer', function (): void {
    $session = AnalyticsSession::factory()->create(['new_users' => '25']);

    expect($session->new_users)->toBeInt();
});

it('casts avg_session_duration to integer', function (): void {
    $session = AnalyticsSession::factory()->create(['avg_session_duration' => '120']);

    expect($session->avg_session_duration)->toBeInt();
});
