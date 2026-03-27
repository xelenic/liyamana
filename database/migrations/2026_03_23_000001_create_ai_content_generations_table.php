<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_content_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_content_template_id')->nullable()->constrained('ai_content_templates')->nullOnDelete();
            $table->string('design_session_id', 64)->index();
            $table->string('name', 255);
            $table->longText('pages');
            $table->boolean('is_multi_page')->default(true);
            $table->unsignedSmallInteger('page_count')->default(1);
            $table->string('type', 50)->default('document');
            $table->string('thumbnail', 512)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'acg_user_created_idx');
            $table->index(['user_id', 'ai_content_template_id', 'created_at'], 'acg_user_tpl_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_content_generations');
    }
};
