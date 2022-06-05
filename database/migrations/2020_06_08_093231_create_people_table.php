<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('people');
    }
}
