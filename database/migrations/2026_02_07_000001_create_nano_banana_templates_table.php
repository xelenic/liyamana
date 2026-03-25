<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nano_banana_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('prompt');
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->json('defined_fields')->nullable();
            $table->boolean('upload_image')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nano_banana_templates');
    }
};
