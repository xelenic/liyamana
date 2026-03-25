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
        Schema::table('sheet_types', function (Blueprint $table) {
            $table->decimal('price_per_sheet', 8, 2)->default(0.00)->after('multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sheet_types', function (Blueprint $table) {
            $table->dropColumn('price_per_sheet');
        });
    }
};
