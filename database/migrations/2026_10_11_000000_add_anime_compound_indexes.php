<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Compound indexes for the most common anime query shapes (schedule, season,
 * top-airing). See docs/DB_INDEXES.md §3 for the serving queries and key
 * rationale. NOTE: this file was an empty stub at migration-record time — the
 * indexes had been applied live only. Re-created so fresh/CI/upstream
 * environments get them from the migration (2026-10-12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->index(['status' => 1, 'type' => 1, 'approved' => 1, 'broadcast' => 1, 'members' => 1], 'schedule');
            $table->index(['aired.from' => 1, 'aired.to' => 1, 'airing' => 1, 'members' => -1], 'season');
            $table->index(['airing' => 1, 'score' => -1], 'top_airing');
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropIndex('schedule');
            $table->dropIndex('season');
            $table->dropIndex('top_airing');
        });
    }
};