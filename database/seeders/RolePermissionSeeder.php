<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Activity Log Permissions
        |--------------------------------------------------------------------------
        */

        $viewPermission = Permission::firstOrCreate([
            'name' => 'activity-logs.view',
            'guard_name' => 'web',
        ]);

        $viewAllPermission = Permission::firstOrCreate([
            'name' => 'activity-logs.view-all',
            'guard_name' => 'web',
        ]);

        $deletePermission = Permission::firstOrCreate([
            'name' => 'activity-logs.delete',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Admin Permissions
        |--------------------------------------------------------------------------
        */

        $adminRole->givePermissionTo([
            $viewPermission,
            $viewAllPermission,
            $deletePermission,
        ]);

        /*
        |--------------------------------------------------------------------------
        | User Permissions
        |--------------------------------------------------------------------------
        */

        $userRole->givePermissionTo([
            $viewPermission,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
