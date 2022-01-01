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
        });

        Schema::create('demographics_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
        });

        Schema::create('explicit_genres_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
        });

        Schema::create('themes_anime', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
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
