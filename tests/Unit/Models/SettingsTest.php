<?php

declare(strict_types=1);

use App\Models\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created using factory', function (): void {
    $settings = Settings::factory()->create();

    expect($settings)->toBeInstanceOf(Settings::class)
        ->and($settings->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $settings = new Settings();

    expect($settings->getFillable())->toBe([
        'google_service_account',
        'property_id',
        'google_tag_id',
        'site_url',
        'last_sync_at',
    ]);
});

it('casts google_service_account to array', function (): void {
    $serviceAccount = ['type' => 'service_account', 'project_id' => 'test'];
    $settings = Settings::factory()->create(['google_service_account' => $serviceAccount]);

    expect($settings->google_service_account)->toBeArray()
        ->and($settings->google_service_account)->toBe($serviceAccount);
});

it('casts last_sync_at to datetime', function (): void {
    $settings = Settings::factory()->create(['last_sync_at' => now()]);

    expect($settings->last_sync_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('can get service account', function (): void {
    $serviceAccount = ['type' => 'service_account', 'project_id' => 'test'];
    $settings = Settings::factory()->create(['google_service_account' => $serviceAccount]);

    expect($settings->getServiceAccount())->toBe($serviceAccount);
});

it('can set service account', function (): void {
    $settings = Settings::factory()->create();
    $newServiceAccount = ['type' => 'service_account', 'project_id' => 'new-test'];

    $settings->setServiceAccount($newServiceAccount);

    expect($settings->fresh()->google_service_account)->toBe($newServiceAccount);
});
