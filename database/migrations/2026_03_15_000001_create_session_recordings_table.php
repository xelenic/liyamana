<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_recordings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->string('landing_path', 2048)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->unsignedSmallInteger('viewport_w')->nullable();
            $table->unsignedSmallInteger('viewport_h')->nullable();
            $table->unsignedBigInteger('byte_size')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_recordings');
    }
};
