<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCachingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anime_characters_staff', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_episode', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_episodes', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_forum', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_moreinfo', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_news', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_pictures', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_recommendations', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_reviews', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_stats', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_userupdates', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('anime_videos', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('characters_pictures', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('clubs_members', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('common', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_characters', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_moreinfo', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_news', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_pictures', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_recommendations', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_reviews', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_stats', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('manga_userupdates', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('people_pictures', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('recommendations', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_animelist', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_mangalist', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_clubs', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_friends', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_history', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_recently_online', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_recommendations', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('users_reviews', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
        Schema::create('watch', function (Blueprint $table) {
            $table->index('request_hash', 'request_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anime_characters_staff');
        Schema::dropIfExists('anime_episode');
        Schema::dropIfExists('anime_episodes');
        Schema::dropIfExists('anime_forum');
        Schema::dropIfExists('anime_moreinfo');
        Schema::dropIfExists('anime_news');
        Schema::dropIfExists('anime_pictures');
        Schema::dropIfExists('anime_recommendations');
        Schema::dropIfExists('anime_reviews');
        Schema::dropIfExists('anime_stats');
        Schema::dropIfExists('anime_userupdates');
        Schema::dropIfExists('anime_videos');
        Schema::dropIfExists('characters_pictures');
        Schema::dropIfExists('clubs_members');
        Schema::dropIfExists('common');
        Schema::dropIfExists('manga_characters');
        Schema::dropIfExists('manga_moreinfo');
        Schema::dropIfExists('manga_news');
        Schema::dropIfExists('manga_pictures');
        Schema::dropIfExists('manga_recommendations');
        Schema::dropIfExists('manga_reviews');
        Schema::dropIfExists('manga_stats');
        Schema::dropIfExists('manga_userupdates');
        Schema::dropIfExists('people_pictures');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('users_animelist');
        Schema::dropIfExists('users_mangalist');
        Schema::dropIfExists('users_clubs');
        Schema::dropIfExists('users_friends');
        Schema::dropIfExists('users_history');
        Schema::dropIfExists('users_recently_online');
        Schema::dropIfExists('users_recommendations');
        Schema::dropIfExists('users_reviews');
        Schema::dropIfExists('watch');
    }
}
