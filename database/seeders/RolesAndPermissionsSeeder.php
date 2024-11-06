<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define roles
        $superadminRole = Role::create(['name' => 'superadmin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);


        $createAdminPermission = Permission::create(['name' => 'create admin']);

        $createUserPermission = Permission::create(['name' => 'create user']);

        // Define permissions (optional)
        // For this setup, roles suffice, but permissions can be added for finer control.

        // Assign permissions to roles if needed
        // Example:
        $superadminRole->givePermissionTo($createAdminPermission, $createUserPermission);
        $adminRole->givePermissionTo($createUserPermission);
    }
}
