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
        Schema::table('templates', function (Blueprint $table) {
            // Template as product
            $table->boolean('is_product')->default(false)->after('is_public');
            $table->boolean('stock_enabled')->default(false)->after('is_product');
            $table->unsignedInteger('stock_qty')->nullable()->after('stock_enabled');
            $table->decimal('selling_price', 10, 2)->nullable()->after('stock_qty');
            $table->decimal('cost', 10, 2)->nullable()->after('selling_price');
            $table->text('product_description')->nullable()->after('cost');

            // Advanced checkout/options
            $table->boolean('disable_sheet_selection')->default(false)->after('product_description');
            $table->boolean('disable_material_selection')->default(false)->after('disable_sheet_selection');
            $table->boolean('disable_envelope_option')->default(false)->after('disable_material_selection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn([
                'is_product',
                'stock_enabled',
                'stock_qty',
                'selling_price',
                'cost',
                'product_description',
                'disable_sheet_selection',
                'disable_material_selection',
                'disable_envelope_option',
            ]);
        });
    }
};
