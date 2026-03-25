<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('slug')->constrained('documentation_categories')->nullOnDelete();
        });
        Schema::table('documentations', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        Schema::table('documentations', function (Blueprint $table) {
            $table->string('category')->nullable()->after('slug');
        });
    }
};
