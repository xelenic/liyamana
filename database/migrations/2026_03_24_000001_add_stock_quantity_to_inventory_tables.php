<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->after('sort_order');
        });

        Schema::table('sheet_types', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->after('sort_order');
        });

        Schema::table('envelope_types', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock_quantity');
        });

        Schema::table('sheet_types', function (Blueprint $table) {
            $table->dropColumn('stock_quantity');
        });

        Schema::table('envelope_types', function (Blueprint $table) {
            $table->dropColumn('stock_quantity');
        });
    }
};
