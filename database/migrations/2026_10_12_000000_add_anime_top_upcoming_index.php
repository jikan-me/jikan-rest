<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Companion compound index for the top/upcoming-season queries — filters
 * `{status: "Not yet aired", type: ...}` and sorts by `members` desc
 * (getTopUpcomingItems / getUpcomingSeasonItems). Without it only the
 * single-field `status` and `members` indexes cover the sort. See
 * docs/DB_INDEXES.md §3 (companion, was "for priority").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->index(['status' => 1, 'type' => 1, 'members' => -1], 'top_upcoming');
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropIndex('top_upcoming');
        });
    }
};
