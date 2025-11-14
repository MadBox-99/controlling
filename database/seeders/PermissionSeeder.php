<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Team management
        $permissions = [
            // Team permissions
            'view teams',
            'create teams',
            'update teams',
            'delete teams',
            'manage team users',

            // User permissions
            'view users',
            'create users',
            'update users',
            'delete users',

            // Role permissions
            'view roles',
            'create roles',
            'update roles',
            'delete roles',

            // Permission permissions
            'view permissions',
            'create permissions',
            'update permissions',
            'delete permissions',

            // KPI permissions
            'view kpis',
            'create kpis',
            'update kpis',
            'delete kpis',

            // Analytics permissions
            'view analytics',
            'manage analytics',

            // Search Console permissions
            'view search data',
            'manage search data',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Assign all permissions to admin role
        $adminRole = Role::findByName('admin', 'web');
        $adminRole->givePermissionTo(Permission::all());

        // Assign limited permissions to user role
        $userRole = Role::findByName('user', 'web');
        $userRole->givePermissionTo([
            'view teams',
            'view users',
            'view kpis',
            'view analytics',
            'view search data',
        ]);
    }
}
