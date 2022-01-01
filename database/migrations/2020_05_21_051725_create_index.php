<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndex extends Migration
{

    const IGNORE = [
        'anime', 'manga', 'people', 'characters', 'magazines', 'producers', 'clubs', 'users'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mappings = config('controller');
        $mapped = [];

        foreach ($mappings as $controller) {
            $table = $controller['table_name'];
            if (in_array($table, $mapped) || in_array($table, self::IGNORE) || Schema::hasTable($table)) {
                continue;
            }

            Schema::create($table, function (Blueprint $table) {
                $table->unique(['request_hash' => 1], 'request_hash');
            });

            $mapped[] = $table;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
