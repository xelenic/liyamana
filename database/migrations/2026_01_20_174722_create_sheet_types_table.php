<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sheet_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Standard", "Glossy", "Matte"
            $table->string('slug')->unique(); // e.g., "standard", "glossy", "matte"
            $table->decimal('multiplier', 5, 2)->default(1.0); // Price multiplier
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // For ordering
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheet_types');
    }
};
