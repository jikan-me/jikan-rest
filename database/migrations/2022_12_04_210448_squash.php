<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('QUEUE_TABLE', 'jobs'), function (Blueprint $table) {
            $table->index(['queue', 'reserved_at']);
            $table->bigIncrements('id');
            $table->string('queue');
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned();
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create(env('QUEUE_FAILED_TABLE', 'jobs_failed'), function (Blueprint $table) {
            $table->increments('id');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

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

        Schema::create('people', function (Blueprint $table) {
            $table->unique(['request_hash' => 1], 'request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');

            $table->date('birthday')->index('birthday');
            $table->index('member_favorites', 'member_favorites');

            $table->index('name', 'name');
            $table->string('given_name')->index('given_name')->nullable();
            $table->string('family_name')->index('family_name')->nullable();
            $table->index('alternate_names', 'alternate_names');

            $table->index(
                [
                    'name' => 'text',
                    'given_name' => 'text',
                    'family_name' => 'text'
                ],
                'search',
                null,
                [
                    'weights' => [
                        'name' => 50,
                        'given_name' => 5,
                        'family_name' => 5,
                    ],
                    'name' => 'search'
                ]
            );

            $table->timestamps();
        });

        Schema::create('characters', function (Blueprint $table) {
            $table->unique(['request_hash' => 1], 'request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');

            $table->date('birthday')->index('birthday');
            $table->index('member_favorites', 'member_favorites');

            $table->index('name', 'name');
            $table->string('name_kanji')->index('name_kanji');
            $table->string('nicknames')->index('nicknames');

            $table->index(
                [
                    'name' => 'text',
                    'name_kanji' => 'text'
                ],
                'search',
                null,
                [
                    'weights' => [
                        'name' => 50,
                        'name_kanji' => 5
                    ],
                    'name' => 'search'
                ]
            );

            $table->timestamps();
        });

        Schema::create('magazines', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('clubs', function (Blueprint $table) {
            $table->unique(['request_hash' => 1], 'request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('name');
            $table->index('members', 'members');
            $table->index('category');
            $table->date('created')->index();
            $table->index('access');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('producers', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');
            $table->index('titles', 'titles');
            $table->index('established', 'established');
            $table->index('favorites', 'favorites');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('genres_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('demographics_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('explicit_genres_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('themes_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('genres_manga', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('explicit_genres_manga', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('demographics_manga', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('themes_manga', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
            );
        });

        Schema::create('users', function (Blueprint $table) {
            $table->unique(['request_hash' => 1], 'request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->unique(['internal_username' => 1], 'internal_username');

            $table->date('joined')->index('joined');
            $table->date('birthday')->index('birthday');
            $table->date('last_online')->index('last_online');

            $table->index('gender', 'gender');
            $table->index('location', 'location');

            $table->index(
                [
                    'internal_username' => 'text'
                ],
                'search'
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
        Schema::dropIfExists(env('QUEUE_TABLE', 'jobs'));
        Schema::dropIfExists(env('QUEUE_FAILED_TABLE', 'jobs_failed'));
        Schema::dropIfExists('anime');
        Schema::dropIfExists('manga');
        Schema::dropIfExists('people');
        Schema::dropIfExists('characters');
        Schema::dropIfExists('magazines');
        Schema::dropIfExists('clubs');
        Schema::dropIfExists('producers');
        Schema::dropIfExists('genres_anime');
        Schema::dropIfExists('demographics_anime');
        Schema::dropIfExists('explicit_genres_anime');
        Schema::dropIfExists('themes_anime');
        Schema::dropIfExists('genres_manga');
        Schema::dropIfExists('explicit_genres_manga');
        Schema::dropIfExists('demographics_manga');
        Schema::dropIfExists('themes_manga');
        Schema::dropIfExists('users');
    }
};
