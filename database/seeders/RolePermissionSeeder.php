<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

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
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
        $premiumRole = Role::firstOrCreate(['name' => 'premium', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'designer', 'guard_name' => $guard]); // Approved designers can save public templates

        // Assign all permissions to admin
        $adminRole->syncPermissions(Permission::where('guard_name', $guard)->get());

        // Assign basic permissions to user role
        $userRole->syncPermissions([
            'create flip books',
            'edit flip books',
            'view flip books',
        ]);

        // Assign extended permissions to premium role
        $premiumRole->syncPermissions([
            'create flip books',
            'edit flip books',
            'delete flip books',
            'view flip books',
            'publish flip books',
        ]);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@flipbook.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@flipbook.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('user');

        // Create premium user
        $premium = User::firstOrCreate(
            ['email' => 'premium@flipbook.com'],
            [
                'name' => 'Premium User',
                'password' => Hash::make('password'),
            ]
        );
        $premium->assignRole('premium');
    }
}
