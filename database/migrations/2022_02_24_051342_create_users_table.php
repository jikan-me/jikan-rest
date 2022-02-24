<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
        Schema::dropIfExists('users');
    }
}
