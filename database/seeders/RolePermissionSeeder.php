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
     *
     * Never use Role::create() / Permission::create() — Spatie overrides them and throws
     * if the row exists. Use query()->create() after an exists check, or query()->firstOrCreate().
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
            $this->ensurePermission($permission, $guard);
        }

        // Create roles (designer may already exist from migration 2026_02_01_230001_add_designer_role)
        $adminRole = $this->ensureRole('admin', $guard);
        $userRole = $this->ensureRole('user', $guard);
        $premiumRole = $this->ensureRole('premium', $guard);
        $this->ensureRole('designer', $guard);

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

    private function ensurePermission(string $name, string $guard): void
    {
        Permission::query()->firstOrCreate(
            ['name' => $name, 'guard_name' => $guard],
            []
        );
    }

    private function ensureRole(string $name, string $guard): Role
    {
        return Role::query()->firstOrCreate(
            ['name' => $name, 'guard_name' => $guard],
            []
        );
    }
}
