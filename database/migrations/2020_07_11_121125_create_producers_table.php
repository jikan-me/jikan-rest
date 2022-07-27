<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProducersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->unique(['mal_id' => 1], 'mal_id');
            $table->index('count', 'count');
            $table->index('name', 'name');
            $table->index('titles', 'titles');
            $table->index('established', 'established');
            $table->index('favorites', 'favorites');

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
        Schema::dropIfExists('producers');
    }
}
