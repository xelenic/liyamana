<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_purchases', function (Blueprint $table) {
            $table->decimal('subtotal', 14, 4)->nullable()->after('payment_method');
            $table->decimal('discount', 14, 4)->default(0)->after('subtotal');
            $table->decimal('deduction', 14, 4)->default(0)->after('discount');
            $table->decimal('additional_charges', 14, 4)->default(0)->after('deduction');
            $table->decimal('total_cost', 14, 4)->nullable()->after('additional_charges');
        });
    }

    public function down(): void
    {
        Schema::table('stock_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount',
                'deduction',
                'additional_charges',
                'total_cost',
            ]);
        });
    }
};
