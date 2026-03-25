<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $module = DB::table('modules')->where('name', 'nano-banana-module')->first();
        if (! $module) {
            return;
        }

        $manifest = json_decode($module->manifest, true) ?: [];
        $manifest['admin_menu'] = $manifest['admin_menu'] ?? [];
        $manifest['admin_menu']['route'] = 'admin.nanobanana.templates.index';

        DB::table('modules')
            ->where('name', 'nano-banana-module')
            ->update([
                'manifest' => json_encode($manifest),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Revert if needed
    }
};
