<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('modules')->where('name', 'nano-banana-module')->exists();
        if ($exists) {
            return;
        }

        $path = base_path('modules/nano-banana-module');
        $manifestPath = $path . '/module.json';
        $manifest = file_exists($manifestPath)
            ? json_decode(file_get_contents($manifestPath), true) ?? []
            : [];

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

    public function down(): void
    {
        DB::table('modules')->where('name', 'nano-banana-module')->delete();
    }
};
