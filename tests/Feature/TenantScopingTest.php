<?php

declare(strict_types=1);

use App\Models\AnalyticsPageview;
use App\Models\Kpi;
use App\Models\SearchPage;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->team1 = Team::factory()->create(['name' => 'Team 1']);
    $this->team2 = Team::factory()->create(['name' => 'Team 2']);

    $this->user = User::factory()->create();
    $this->user->teams()->attach([$this->team1->id, $this->team2->id]);

    actingAs($this->user);
});

it('scopes kpis to current tenant', function (): void {
    // Create KPIs for both teams
    $kpi1 = Kpi::factory()->create(['team_id' => $this->team1->id, 'name' => 'Team 1 KPI']);
    $kpi2 = Kpi::factory()->create(['team_id' => $this->team2->id, 'name' => 'Team 2 KPI']);

    // Set tenant to team1
    Filament::setTenant($this->team1);

    // Manually apply the scope (simulating middleware)
    Kpi::addGlobalScope('team', fn ($query) => $query->where('team_id', $this->team1->id));

    // Should only see team1's KPI
    $kpis = Kpi::all();

    expect($kpis)->toHaveCount(1)
        ->and($kpis->first()->id)->toBe($kpi1->id)
        ->and($kpis->first()->name)->toBe('Team 1 KPI');
});

it('scopes search pages to current tenant', function (): void {
    // Create search pages for both teams
    $page1 = SearchPage::factory()->create(['team_id' => $this->team1->id, 'page_url' => 'team1.com']);
    $page2 = SearchPage::factory()->create(['team_id' => $this->team2->id, 'page_url' => 'team2.com']);

    // Set tenant to team2
    Filament::setTenant($this->team2);

    // Manually apply the scope
    SearchPage::addGlobalScope('team', fn ($query) => $query->where('team_id', $this->team2->id));

    // Should only see team2's page
    $pages = SearchPage::all();

    expect($pages)->toHaveCount(1)
        ->and($pages->first()->id)->toBe($page2->id)
        ->and($pages->first()->page_url)->toBe('team2.com');
});

it('scopes analytics pageviews to current tenant', function (): void {
    // Create analytics for both teams
    $analytics1 = AnalyticsPageview::factory()->create(['team_id' => $this->team1->id, 'page_path' => '/team1']);
    $analytics2 = AnalyticsPageview::factory()->create(['team_id' => $this->team2->id, 'page_path' => '/team2']);

    // Set tenant to team1
    Filament::setTenant($this->team1);

    // Manually apply the scope
    AnalyticsPageview::addGlobalScope('team', fn ($query) => $query->where('team_id', $this->team1->id));

    // Should only see team1's analytics
    $pageviews = AnalyticsPageview::all();

    expect($pageviews)->toHaveCount(1)
        ->and($pageviews->first()->id)->toBe($analytics1->id)
        ->and($pageviews->first()->page_path)->toBe('/team1');
});

it('creates records with team_id', function (): void {
    // Create KPI with team_id
    $kpi = Kpi::factory()->create([
        'team_id' => $this->team1->id,
        'name' => 'Team 1 KPI',
    ]);

    expect($kpi->team_id)->toBe($this->team1->id)
        ->and($kpi->team)->toBeInstanceOf(Team::class)
        ->and($kpi->team->id)->toBe($this->team1->id);
});

it('automatically assigns team_id when creating records with tenant set', function (): void {
    // Set tenant to team1
    Filament::setTenant($this->team1);

    // Create KPI without explicitly setting team_id
    $kpi = Kpi::create([
        'code' => 'TEST_KPI',
        'name' => 'Test KPI',
        'data_source' => 'manual',
        'category' => 'traffic',
        'is_active' => true,
    ]);

    // The observer should automatically assign team_id
    expect($kpi->team_id)->toBe($this->team1->id)
        ->and($kpi->team)->toBeInstanceOf(Team::class);

    // Change tenant to team2 and create another KPI
    Filament::setTenant($this->team2);

    $kpi2 = Kpi::create([
        'code' => 'TEST_KPI_2',
        'name' => 'Test KPI 2',
        'data_source' => 'manual',
        'category' => 'conversion',
        'is_active' => true,
    ]);

    // Should automatically assign team2's id
    expect($kpi2->team_id)->toBe($this->team2->id)
        ->and($kpi2->team->id)->toBe($this->team2->id);
});

it('automatically assigns team_id to all tenant-scoped models', function (): void {
    // Set tenant to team1
    Filament::setTenant($this->team1);

    // Create a SearchPage without team_id
    $searchPage = SearchPage::create([
        'date' => now(),
        'page_url' => 'example.com/test',
        'country' => 'HU',
        'device' => 'desktop',
        'impressions' => 100,
        'clicks' => 10,
        'ctr' => 10.0,
        'position' => 5.5,
    ]);

    expect($searchPage->team_id)->toBe($this->team1->id);

    // Create an AnalyticsPageview without team_id
    $pageview = AnalyticsPageview::create([
        'date' => now(),
        'page_path' => '/test',
        'page_title' => 'Test Page',
        'pageviews' => 50,
        'unique_pageviews' => 40,
        'avg_time_on_page' => 120,
        'entrances' => 30,
        'bounce_rate' => 25.0,
        'exit_rate' => 20.0,
    ]);

    expect($pageview->team_id)->toBe($this->team1->id);
});
