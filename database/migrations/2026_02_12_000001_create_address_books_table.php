<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('address_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 64)->nullable(); // e.g. Home, Office
            $table->string('contact_name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city', 64)->nullable();
            $table->string('state', 64)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->nullable(); // ISO 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_books');
    }
};
