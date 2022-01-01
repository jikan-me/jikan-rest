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
            $table->string('url');
            $table->string('images');
            $table->string('website_url');
            $table->index('name');
            $table->string('given_name')->index()->nullable();
            $table->string('family_name')->index()->nullable();
            $table->index('alternate_names');
            $table->date('birthday')->index();
            $table->integer('member_favorites')->index('member_favorites');
            $table->string('about')->nullable();
            $table->index('voice_acting_roles');
            $table->index('anime_staff_positions');
            $table->index('published_manga');
            $table->index([
                'name' => 'text',
                'given_name' => 'text',
                'family_name' => 'text',
                'alternate_names' => 'text',
            ],
                'people_search_index',
                null,
                [
                    'weights' => [
                        'name' => 50,
                        'given_name' => 10,
                        'family_name' => 10,
                        'alternate_names' => 1
                    ],
                    'name' => 'people_search_index'
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
        Schema::dropIfExists('people');
    }
}
