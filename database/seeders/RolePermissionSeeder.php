<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Flip Book permissions
            'create flip books',
            'edit flip books',
            'delete flip books',
            'view flip books',
            'publish flip books',

            // User management permissions
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role & Permission management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',

            // Settings
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $premiumRole = Role::create(['name' => 'premium']);
        Role::create(['name' => 'designer']); // Approved designers can save public templates

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign basic permissions to user role
        $userRole->givePermissionTo([
            'create flip books',
            'edit flip books',
            'view flip books',
        ]);

        // Assign extended permissions to premium role
        $premiumRole->givePermissionTo([
            'create flip books',
            'edit flip books',
            'delete flip books',
            'view flip books',
            'publish flip books',
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@flipbook.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create regular user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@flipbook.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('user');

        // Create premium user
        $premium = User::create([
            'name' => 'Premium User',
            'email' => 'premium@flipbook.com',
            'password' => Hash::make('password'),
        ]);
        $premium->assignRole('premium');
    }
}

