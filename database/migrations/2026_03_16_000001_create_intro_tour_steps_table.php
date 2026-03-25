<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intro_tour_steps', function (Blueprint $table) {
            $table->id();
            $table->string('tour_slug', 64)->index()->comment('e.g. multi_page_editor');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('element_selector', 255)->nullable()->comment('CSS selector e.g. #designToolbar, empty for intro-only step');
            $table->string('title', 255)->nullable();
            $table->text('intro_text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intro_tour_steps');
    }
};
