<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->unique(['request_hash' => 1], 'request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->string('url');
            $table->string('images');
            $table->index('name');
            $table->index('name_kanji');
            $table->index('nicknames');
            $table->integer('member_favorites')->index('member_favorites');
            $table->string('about')->nullable();
            $table->index([
                'name' => 'text',
                'nicknames' => 'text',
            ],
                'characters_search_index',
                null,
                [
                    'weights' => [
                        'name' => 50,
                        'nicknames' => 10,
                    ],
                    'name' => 'characters_search_index'
                ]
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characters');
    }
}
