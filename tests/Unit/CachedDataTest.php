<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Support\CachedData;
use App\Support\CacheOptions;
use Illuminate\Support\Carbon;
use MongoDB\BSON\UTCDateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CachedDataTest extends TestCase
{
    public static function dateTimeProvider(): array
    {
        $now = Carbon::now();

        return [
            'mongodb bson date time' => [
                $now, new UTCDateTime($now->getTimestampMs()),
            ],
            'built-in datetime' => [
                $now, $now->toDateTime(),
            ],
            'atom string datetime' => [
                $now, $now->toAtomString(),
            ],
        ];
    }

    public static function invalidModifiedAtValuesProvider(): array
    {
        return [
            'number value' => [
                ['modifiedAt' => 1],
            ],
            'float value' => [
                ['modifiedAt' => 1.3],
            ],
            'bool value' => [
                ['modifiedAt' => true],
            ],
            'modifiedAt key not present' => [
                ['someotherkey' => 1],
            ],
        ];
    }

    public function testLastModifiedReturnsNullIfInternalCollectionIsEmpty()
    {
        $sut = CachedData::from(collect());
        $this->assertNull($sut->lastModified());
    }

    #[DataProvider('invalidModifiedAtValuesProvider')]
    public function testLastModifiedReturnsNullIfModifiedAtIsUnknownFormat(array $contents)
    {
        $sut = CachedData::from(collect($contents));
        $this->assertNull($sut->lastModified());
    }

    #[DataProvider('dateTimeProvider')]
    public function testLastModifiedReturnsModifiedTime($now, $dateTime)
    {
        $sut = CachedData::from(collect(['modifiedAt' => $dateTime]));
        $this->assertEquals($now->getTimestamp(), $sut->lastModified());
    }

    public function testIsEmptyReturnsTrueIfEmpty()
    {
        $sut = CachedData::from(collect());
        $this->assertEquals(true, $sut->isEmpty());
    }

    public function testIsExpiredReturnTrueIfEmpty()
    {
        $sut = CachedData::from(collect());
        $this->assertEquals(true, $sut->isExpired());
    }

    public function testIsExpiredReturnsTrueIfLastModifiedIsMoreThanCacheTtlAgo()
    {
        $this->app->get(CacheOptions::class)->setTtl(99);
        $sut = CachedData::from(collect(['modifiedAt' => Carbon::now()->subSeconds(100)]));
        $this->assertEquals(true, $sut->isExpired());
    }
}
