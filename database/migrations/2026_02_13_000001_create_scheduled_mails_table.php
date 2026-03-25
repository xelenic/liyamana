<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->string('template_name');
            $table->foreignId('address_book_id')->nullable()->constrained()->nullOnDelete();
            $table->json('recipient_snapshot')->nullable(); // contact_name, email, address lines, etc.
            $table->dateTime('send_at');
            $table->decimal('credit_amount', 10, 2);
            $table->json('checkout_data')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status', 20)->default('pending'); // pending, sent, cancelled, failed
            $table->unsignedBigInteger('order_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_mails');
    }
};
