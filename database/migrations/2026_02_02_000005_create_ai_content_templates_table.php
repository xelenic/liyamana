<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_content_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('prompt');
            $table->json('fields')->nullable();
            $table->string('image_path')->nullable();
            $table->json('editor_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('ai_content_templates');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
