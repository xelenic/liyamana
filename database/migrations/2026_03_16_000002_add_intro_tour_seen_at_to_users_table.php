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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('intro_tour_multi_page_seen_at')->nullable()->after('special_offers_modal_shown_at');
            $table->timestamp('intro_tour_explore_seen_at')->nullable()->after('intro_tour_multi_page_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['intro_tour_multi_page_seen_at', 'intro_tour_explore_seen_at']);
        });
    }
};
