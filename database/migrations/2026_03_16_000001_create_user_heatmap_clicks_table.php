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

            $table->index(['user_id', 'path']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_heatmap_clicks');
    }
};
