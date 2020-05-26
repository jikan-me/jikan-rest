<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mappings = config('controller-to-table-mapping');
        $mapped = [];

        foreach ($mappings as $table) {
            if (in_array($table, $mapped)) {
                continue;
            }

            Schema::create($table, function (Blueprint $table) {
                $table->index(['request_hash' => 1], 'request_hash');
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
