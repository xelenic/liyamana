<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $path = base_path('modules/nano-banana-module');
        $manifestPath = $path . '/module.json';
        if (! file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (json_last_error() !== JSON_ERROR_NONE || ! isset($manifest['name'])) {
            return;
        }

        $module = DB::table('modules')->where('name', 'nano-banana-module')->first();
        if ($module) {
            DB::table('modules')
                ->where('name', 'nano-banana-module')
                ->update([
                    'manifest' => json_encode($manifest),
                    'path' => $path,
                    'version' => $manifest['version'] ?? '1.0.0',
                    'enabled' => true,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('modules')->insert([
                'name' => 'nano-banana-module',
                'version' => $manifest['version'] ?? '1.0.0',
                'enabled' => true,
                'path' => $path,
                'manifest' => json_encode($manifest),
                'installed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Cache::forget('module_registry_admin_menu');
    }

    public function down(): void
    {
        //
    }
};
