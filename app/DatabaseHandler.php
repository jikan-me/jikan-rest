<?php
namespace App;

use Illuminate\Support\Facades\DB;

class DatabaseHandler
{
    public function resolve(string $table, $fingerprint, array $data)
    {

        if (DB::table($table)->where('fingerprint', $fingerprint)) {
            return DB::table($table)->get();
        }

        DB::table('anime')->insert($data);
    }

    public static function getMappedTableName(string $controller)
    {
        return config('controller-to-table-mapping.'.$controller);
    }

    public function prepare(array $response)
    {
    }
}