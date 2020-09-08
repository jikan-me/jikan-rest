<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('request_hash');
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->unique(['username' => 1], 'username');
            $table->date('last_online')->index();
            $table->index('gender');
            $table->date('birthday')->index();
            $table->index('location');
            $table->date('joined')->index();
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
