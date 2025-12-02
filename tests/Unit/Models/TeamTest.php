<?php

declare(strict_types=1);

use App\Models\AnalyticsConversion;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsSession;
use App\Models\Kpi;
use App\Models\SearchPage;
use App\Models\SearchQuery;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $team = Team::factory()->create();

    expect($team)->toBeInstanceOf(Team::class)
        ->and($team->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $team = new Team();

    expect($team->getFillable())->toBe([
        'name',
        'slug',
    ]);
});

it('belongs to many users', function (): void {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    $team->users()->attach($user);

    expect($team->users)->toHaveCount(1)
        ->and($team->users->first()->id)->toBe($user->id);
});

it('has many kpis', function (): void {
    $team = Team::factory()->create();
    Kpi::factory()->count(3)->create(['team_id' => $team->id]);

    expect($team->kpis)->toHaveCount(3);
});

it('has many search pages', function (): void {
    $team = Team::factory()->create();
    SearchPage::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->searchPages)->toHaveCount(2);
});

it('has many search queries', function (): void {
    $team = Team::factory()->create();
    SearchQuery::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->searchQueries)->toHaveCount(2);
});

it('has many analytics pageviews', function (): void {
    $team = Team::factory()->create();
    AnalyticsPageview::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->analyticsPageviews)->toHaveCount(2);
});

it('has many analytics sessions', function (): void {
    $team = Team::factory()->create();
    AnalyticsSession::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->analyticsSessions)->toHaveCount(2);
});

it('has many analytics events', function (): void {
    $team = Team::factory()->create();
    AnalyticsEvent::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->analyticsEvents)->toHaveCount(2);
});

it('has many analytics conversions', function (): void {
    $team = Team::factory()->create();
    AnalyticsConversion::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->analyticsConversions)->toHaveCount(2);
});
