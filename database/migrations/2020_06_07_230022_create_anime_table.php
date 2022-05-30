<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('anime', function (Blueprint $table) {
            $table->unique(['request_hash' => 1], 'request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');

            $table->index('aired', 'aired');
            $table->index(['aired.from' => 1], 'aired.from');
            $table->index(['aired.to' => 1], 'aired.to');
            $table->index('airing', 'airing');
            $table->index('demographics.mal_id', 'demographics.mal_id');
            $table->index('explicit_genres.mal_id', 'explicit_genres.mal_id');
            $table->index('genres.mal_id', 'genres.mal_id');
            $table->index('licensors.mal_id', 'licensors.mal_id');
            $table->index('producers.mal_id', 'producers.mal_id');
            $table->index('studios.mal_id', 'studios.mal_id');
            $table->index('themes.mal_id', 'themes.mal_id');

            $table->index('episodes', 'episodes');
            $table->integer('members')->index('members');
            $table->integer('favorites')->index('favorites');
            $table->integer('popularity')->index('popularity');
            $table->integer('rank')->index('rank')->nullable();
            $table->index('rating', 'rating');
            $table->float('score')->index('score');
            $table->integer('scored_by')->index('scored_by');
            $table->index('status', 'status');
            $table->index('type', 'type');
            $table->index('source', 'source');

            $table->index('title', 'title');
            $table->index('title_english', 'title_english');
            $table->index('title_japanese', 'title_japanese');
            $table->index('title_synonyms', 'title_synonyms');


            $table->index(
                [
                    'title' => 'text',
                    'title_japanese' => 'text'
                ],
                'search',
                null,
                [
                    'weights' => [
                        'title' => 50,
                        'title_japanese' => 5
                    ],
                    'name' => 'search'
                ]
            );

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anime');
    }
}
