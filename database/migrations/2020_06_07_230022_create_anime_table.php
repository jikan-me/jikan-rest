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
            $table->string('url');
            $table->string('image_url');
            $table->string('trailer_url');
            $table->index('title');
            $table->index('title_english');
            $table->index('title_japanese');
            $table->enum('type', ['TV', 'Movie', 'OVA', 'Special', 'ONA', 'Music']);
            $table->index('source');
            $table->integer('episodes')->index('episodes');
            $table->string('status')->index();
            $table->boolean('airing');
            $table->string('duration');
            $table->string('rating')->index('rating');
            $table->float('score')->index('score');
            $table->integer('rank')->index('rank')->nullable();
            $table->integer('popularity')->index('popularity');
            $table->integer('members')->index('members');
            $table->integer('favorites')->index('favorites');
            $table->string('synopsis')->nullable();
            $table->string('background')->nullable();
            $table->index('genres');
            $table->index(['aired.from' => 1], 'start_date');
            $table->index(['aired.to' => 1], 'end_date');
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
