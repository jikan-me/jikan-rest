<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manga', function (Blueprint $table) {
            $table->unique(['request_hash' => 1], 'request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');

            $table->index('published', 'published');
            $table->index(['published.from' => 1], 'published.from');
            $table->index(['published.to' => 1], 'published.to');
            $table->index('publishing', 'publishing');
            $table->index('demographics.mal_id', 'demographics.mal_id');
            $table->index('explicit_genres.mal_id', 'explicit_genres.mal_id');
            $table->index('genres.mal_id', 'genres.mal_id');
            $table->index('authors.mal_id', 'authors.mal_id');
            $table->index('serializations.mal_id', 'serializations.mal_id');
            $table->index('themes.mal_id', 'themes.mal_id');

            $table->index('chapters', 'chapters');
            $table->index('volumes', 'volumes');
            $table->integer('members')->index('members');
            $table->integer('favorites')->index('favorites');
            $table->integer('popularity')->index('popularity');
            $table->integer('rank')->index('rank')->nullable();
            $table->float('score')->index('score');
            $table->integer('scored_by')->index('scored_by');
            $table->index('status', 'status');
            $table->index('type', 'type');

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
        Schema::dropIfExists('manga');
    }
}
