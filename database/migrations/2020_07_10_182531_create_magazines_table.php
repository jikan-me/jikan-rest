<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagazinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('magazines', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');

            $table->index(
                [
                    'name' => 'text'
                ],
                'search'
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
        Schema::dropIfExists('magazines');
    }
}
