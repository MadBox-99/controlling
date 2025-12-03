<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\GlobalSetting;
use App\Models\Settings;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Assign Super-Admin role to the admin user
        $admin->assignRole(UserRole::SuperAdmin);

        // Create regular user with single team
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Assign subscriber role to the regular user
        $user->assignRole(UserRole::Subscriber);

        // Create multi-team user (not admin, but has 2 teams)
        $multiTeamUser = User::factory()->create([
            'name' => 'Multi Team User',
            'email' => 'team@team.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Assign subscriber role to the multi-team user
        $multiTeamUser->assignRole(UserRole::Subscriber);

        // Create default team for admin and user
        $team = Team::factory()->create([
            'name' => 'Default Team',
            'slug' => 'default-team',
        ]);

        // Create second team for multi-team user
        $team2 = Team::factory()->create([
            'name' => 'Second Team',
            'slug' => 'second-team',
        ]);

        // Attach users to teams
        $admin->teams()->attach($team);
        $user->teams()->attach($team);
        $multiTeamUser->teams()->attach([$team->id, $team2->id]);

        // Create global settings with service account
        GlobalSetting::query()->create([
            'google_service_account' => 'google-service-account.json',
        ]);

        // Create analytics settings for default team
        Settings::query()->create([
            'team_id' => $team->id,
            'property_id' => '442849954',
            'google_tag_id' => 'G-12345678',
            'site_url' => 'https://cegem360.eu',
            'last_sync_at' => now(),
        ]);

        // Seed test data only in local/testing environments
        if (app()->environment('local', 'testing')) {
            $this->call([
                KpiSeeder::class,
                AnalyticsPageviewSeeder::class,
                AnalyticsSessionSeeder::class,
                AnalyticsEventSeeder::class,
                AnalyticsConversionSeeder::class,
                SearchPageSeeder::class,
                SearchQuerySeeder::class,
            ]);
        }
    }
}
