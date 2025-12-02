<?php

declare(strict_types=1);

use App\Models\SearchQuery;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $query = SearchQuery::factory()->create();

    expect($query)->toBeInstanceOf(SearchQuery::class)
        ->and($query->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $query = new SearchQuery();

    expect($query->getFillable())->toBe([
        'team_id',
        'date',
        'query',
        'country',
        'device',
        'impressions',
        'clicks',
        'ctr',
        'position',
    ]);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $query = SearchQuery::factory()->create(['team_id' => $team->id]);

    expect($query->team->id)->toBe($team->id);
});

it('casts date to date', function (): void {
    $query = SearchQuery::factory()->create(['date' => '2024-01-01']);

    expect($query->date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts impressions to integer', function (): void {
    $query = SearchQuery::factory()->create(['impressions' => '1000']);

    expect($query->impressions)->toBeInt();
});

it('casts clicks to integer', function (): void {
    $query = SearchQuery::factory()->create(['clicks' => '100']);

    expect($query->clicks)->toBeInt();
});
