<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->nullable()->unique();
            $table->string('supplier_name')->nullable();
            $table->text('notes')->nullable();
            $table->date('purchased_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_purchase_id')->constrained()->cascadeOnDelete();
            $table->morphs('purchasable');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_purchase_items');
        Schema::dropIfExists('stock_purchases');
    }
};
