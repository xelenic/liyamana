<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_documentation_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documentation_id')->constrained('documentations')->cascadeOnDelete();
            $table->foreignId('documentation_category_id')->constrained('documentation_categories')->cascadeOnDelete();
            $table->unique(['documentation_id', 'documentation_category_id']);
            $table->timestamps();
        });

        Schema::table('documentations', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('slug')->constrained('documentation_categories')->nullOnDelete();
        });

        Schema::dropIfExists('documentation_documentation_category');
    }
};
