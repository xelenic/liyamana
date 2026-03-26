<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_heatmap_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path', 1024);
            $table->decimal('x_pct', 6, 3);
            $table->decimal('y_pct', 6, 3);
            $table->unsignedSmallInteger('viewport_w')->nullable();
            $table->unsignedSmallInteger('viewport_h')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });

        // MySQL/MariaDB: utf8mb4 index byte limit — full (user_id, path) exceeds 3072 bytes when path is 1024 chars.
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('user_heatmap_clicks', function (Blueprint $table) {
                $table->rawIndex('user_id, path(191)', 'user_heatmap_clicks_user_id_path_index');
            });
        } else {
            Schema::table('user_heatmap_clicks', function (Blueprint $table) {
                $table->index(['user_id', 'path'], 'user_heatmap_clicks_user_id_path_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_heatmap_clicks');
    }
};
