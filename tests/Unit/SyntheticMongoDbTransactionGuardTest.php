<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SyntheticMongoDbTransactionGuardTest extends TestCase
{
    public function test_it_refuses_loudly_when_the_connection_is_not_the_test_database(): void
    {
        $originalDatabase = config('database.connections.mongodb.database');
        $disposableDatabase = 'jikan_guard_'.bin2hex(random_bytes(6));

        try {
            config(['database.connections.mongodb.database' => $disposableDatabase]);
            DB::purge('mongodb');

            $subject = new class
            {
                use SyntheticMongoDbTransaction;
            };

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage(sprintf(
                'Refusing to truncate MongoDB database "%s"',
                $disposableDatabase
            ));

            $subject->setUpSyntheticMongoDbTransaction();
        } finally {
            config(['database.connections.mongodb.database' => $originalDatabase]);
            DB::purge('mongodb');
        }
    }
}
