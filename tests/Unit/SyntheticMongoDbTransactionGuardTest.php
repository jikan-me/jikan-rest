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
        config(['database.connections.mongodb.database' => 'jikan']);
        DB::purge('mongodb');

        $subject = new class
        {
            use SyntheticMongoDbTransaction;
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refusing to truncate MongoDB database "jikan"');

        $subject->setUpSyntheticMongoDbTransaction();
    }
}
