<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define roles
        $superadminRole = Role::create(['name' => 'superadmin']);
        $adminRole = Role::create(['name' => 'admin']);


        $createAdminPermission = Permission::create(['name' => 'create admin']);
        $createUserPermission = Permission::create(['name' => 'create user']);
        $viewReports = Permission::create(['name' => 'view reports']);

        // Define permissions (optional)
        // For this setup, roles suffice, but permissions can be added for finer control.

        // Assign permissions to roles if needed
        // Example:
        $superadminRole->givePermissionTo($createAdminPermission, $createUserPermission);
        $adminRole->givePermissionTo($createUserPermission);

        $user = User::find(1);
        $user->assignRole('superadmin');


    }
}
