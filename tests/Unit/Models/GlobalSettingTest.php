<?php

declare(strict_types=1);

use App\Models\GlobalSetting;

it('can be created using factory', function (): void {
    $globalSetting = GlobalSetting::factory()->create();

    expect($globalSetting)->toBeInstanceOf(GlobalSetting::class)
        ->and($globalSetting->id)->toBeInt();
});

it('has correct fillable attributes', function (): void {
    $globalSetting = new GlobalSetting();

    expect($globalSetting->getFillable())->toBe([
        'google_service_account',
    ]);
});

it('returns singleton instance', function (): void {
    $instance1 = GlobalSetting::instance();
    $instance2 = GlobalSetting::instance();

    expect($instance1->id)->toBe($instance2->id);
});

it('creates instance if none exists', function (): void {
    expect(GlobalSetting::query()->count())->toBe(0);

    $instance = GlobalSetting::instance();

    expect(GlobalSetting::query()->count())->toBe(1)
        ->and($instance)->toBeInstanceOf(GlobalSetting::class);
});

it('returns null for service account when not configured', function (): void {
    $globalSetting = GlobalSetting::factory()->create(['google_service_account' => null]);

    expect($globalSetting->getServiceAccount())->toBeNull();
});
