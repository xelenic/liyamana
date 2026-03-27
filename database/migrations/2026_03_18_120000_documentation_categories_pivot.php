<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_documentation_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentation_id');
            $table->unsignedBigInteger('documentation_category_id');
            $table->foreign('documentation_id', 'doc_doc_cat_doc_fk')
                ->references('id')->on('documentations')->cascadeOnDelete();
            $table->foreign('documentation_category_id', 'doc_doc_cat_cat_fk')
                ->references('id')->on('documentation_categories')->cascadeOnDelete();
            $table->unique(['documentation_id', 'documentation_category_id'], 'doc_doc_cat_pair_uidx');
            $table->timestamps();
        });

        Schema::table('documentations', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('documentations', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('slug')->constrained('documentation_categories')->nullOnDelete();
        });

        Schema::dropIfExists('documentation_documentation_category');
    }
};
