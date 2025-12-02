<?php

declare(strict_types=1);

use App\Models\SearchPage;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $page = SearchPage::factory()->create();

    expect($page)->toBeInstanceOf(SearchPage::class)
        ->and($page->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $page = new SearchPage();

    expect($page->getFillable())->toBe([
        'team_id',
        'date',
        'page_url',
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
    $page = SearchPage::factory()->create(['team_id' => $team->id]);

    expect($page->team->id)->toBe($team->id);
});

it('casts date to date', function (): void {
    $page = SearchPage::factory()->create(['date' => '2024-01-01']);

    expect($page->date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts impressions to integer', function (): void {
    $page = SearchPage::factory()->create(['impressions' => '1000']);

    expect($page->impressions)->toBeInt();
});

it('casts clicks to integer', function (): void {
    $page = SearchPage::factory()->create(['clicks' => '100']);

    expect($page->clicks)->toBeInt();
});
