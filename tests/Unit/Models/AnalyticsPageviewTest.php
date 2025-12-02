<?php

declare(strict_types=1);

use App\Models\AnalyticsPageview;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $pageview = AnalyticsPageview::factory()->create();

    expect($pageview)->toBeInstanceOf(AnalyticsPageview::class)
        ->and($pageview->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $pageview = new AnalyticsPageview();

    expect($pageview->getFillable())->toBe([
        'team_id',
        'date',
        'page_path',
        'page_title',
        'pageviews',
        'unique_pageviews',
        'avg_time_on_page',
        'entrances',
        'bounce_rate',
        'exit_rate',
    ]);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $pageview = AnalyticsPageview::factory()->create(['team_id' => $team->id]);

    expect($pageview->team->id)->toBe($team->id);
});

it('casts date to date', function (): void {
    $pageview = AnalyticsPageview::factory()->create(['date' => '2024-01-01']);

    expect($pageview->date)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts pageviews to integer', function (): void {
    $pageview = AnalyticsPageview::factory()->create(['pageviews' => '1000']);

    expect($pageview->pageviews)->toBeInt();
});

it('casts unique_pageviews to integer', function (): void {
    $pageview = AnalyticsPageview::factory()->create(['unique_pageviews' => '800']);

    expect($pageview->unique_pageviews)->toBeInt();
});

it('casts avg_time_on_page to integer', function (): void {
    $pageview = AnalyticsPageview::factory()->create(['avg_time_on_page' => '60']);

    expect($pageview->avg_time_on_page)->toBeInt();
});

it('casts entrances to integer', function (): void {
    $pageview = AnalyticsPageview::factory()->create(['entrances' => '500']);

    expect($pageview->entrances)->toBeInt();
});
