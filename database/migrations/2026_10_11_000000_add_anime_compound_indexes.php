<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Compound (multi-field) indexes on the `anime` collection for the lookup
 * query families.
 *
 * Proposed by the Mongo index audit (DB_INDEXES.md). The schedule, season and
 * top-airing queries filter several fields at once and sort by `members` /
 * `score`; a covering compound index lets MongoDB satisfy the filters + sort
 * from the index instead of a collection scan + in-memory sort on the (large)
 * anime collection:
 *
 *  - `schedule`     { status, type, approved, broadcast, members }      (/v4/schedules)
 *  - `season`       { aired.from, aired.to, airing, members: -1 }      (/v4/seasons/*)
 *  - `top_airing`   { airing, score: -1 }                              (top airing)
 *
 * Additive only — no data or response changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->index(
                ['status' => 1, 'type' => 1, 'approved' => 1, 'broadcast' => 1, 'members' => 1],
                'schedule'
            );
            $table->index(
                ['aired.from' => 1, 'aired.to' => 1, 'airing' => 1, 'members' => -1],
                'season'
            );
            $table->index(
                ['airing' => 1, 'score' => -1],
                'top_airing'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropIndex('schedule');
            $table->dropIndex('season');
            $table->dropIndex('top_airing');
        });
    }
};