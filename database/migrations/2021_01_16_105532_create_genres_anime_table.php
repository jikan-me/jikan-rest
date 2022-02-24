<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenresAnimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('genres_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');
        });

        Schema::create('demographics_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');
        });

        Schema::create('explicit_genres_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');
        });

        Schema::create('themes_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('genres_anime');
        Schema::dropIfExists('demographics_anime');
        Schema::dropIfExists('explicit_genres_anime');
        Schema::dropIfExists('themes_anime');
    }
}
