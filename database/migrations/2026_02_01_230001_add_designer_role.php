<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Do not use Role::create() — Spatie throws if the role exists; use the query builder.
        Role::query()->firstOrCreate(
            ['name' => 'designer', 'guard_name' => 'web'],
            []
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::where('name', 'designer')->where('guard_name', 'web')->first();
        if ($role) {
            $role->delete();
        }
    }
};
