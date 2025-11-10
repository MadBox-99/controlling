<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsSettings;
use App\Models\Settings;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        // AnalyticsSettings::factory()->create();
        Settings::create([
            'google_service_account' => 'google-service-account.json',
            'property_id' => '442849954',
            'google_tag_id' => 'G-12345678',
            'site_url' => 'https://cegem360.eu',
            'last_sync_at' => now(),
        ]);
    }
}
