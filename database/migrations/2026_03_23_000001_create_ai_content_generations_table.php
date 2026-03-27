<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $name = 'ai_content_generations';

        if (! Schema::hasTable($name)) {
            try {
                Schema::create($name, function (Blueprint $table) {
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
            } catch (QueryException $e) {
                if (! $this->isMysqlTableAlreadyExists($e)) {
                    throw $e;
                }
            }
        }
    }

    private function isMysqlTableAlreadyExists(QueryException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '1050') || str_contains($m, 'Base table or view already exists');
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_content_generations');
    }
};
