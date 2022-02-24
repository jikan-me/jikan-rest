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
