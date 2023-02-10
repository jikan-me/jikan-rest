<?php

namespace Unit;

use App\Contracts\Repository;
use App\Services\DefaultCachedScraperService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\SerializerInterface;
use Mockery;
use MongoDB\BSON\UTCDateTime;
use Tests\TestCase;

final class DefaultCachedScraperServiceTest extends TestCase
{
    public function tearDown(): void
    {
        // reset time pinning
        Carbon::setTestNow();
    }

    private function requestHash(): string
    {
        return sha1("asdf");
    }

    private function makeCtorArgMocks(int $repositoryWhereCallCount = 1): array
    {
        $queryBuilderMock = Mockery::mock(Builder::class)->makePartial();
        $repositoryMock = Mockery::mock(Repository::class);
        $serializerMock = Mockery::mock(SerializerInterface::class);

        $repositoryMock
            ->expects()
            ->where("request_hash", $this->requestHash())
            ->times($repositoryWhereCallCount)
            ->andReturn($queryBuilderMock);

        return [
            $queryBuilderMock,
            $repositoryMock,
            $serializerMock
        ];
    }

    public function testIfFindListReturnsNotExpiredItems()
    {
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);
        // the cached data in the database
        // this should be an array of arrays as builder->get() returns multiple items
        $dummyResults = collect([[
            "request_hash" => $testRequestHash,
            "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "results" => [
                ["dummy" => "dummy1"],
                ["dummy" => "dummy2"]
            ]
        ]]);
        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        $queryBuilderMock->expects()->get()->once()->andReturn($dummyResults);

        $target = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);

        $result = $target->findList($testRequestHash, fn() => []);

        $this->assertEquals($dummyResults->first(), $result->toArray());
    }

    public function testIfFindListUpdatesCacheIfItemsExpired()
    {
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // the cached data in the database
        // this should be an array of arrays as builder->get() returns multiple items
        $dummyResults = collect([[
            "request_hash" => $testRequestHash,
            "modifiedAt" => new UTCDateTime($now->sub("2 days")->getPreciseTimestamp(3)),
            "results" => [
                ["dummy" => "dummy1"],
                ["dummy" => "dummy2"]
            ]
        ]]);

        // the data returned by the scraper
        $scraperData = [
            "results" => [
                ["dummy" => "dummy1"],
                ["dummy" => "dummy2"]
            ]
        ];
        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks(3);
        $queryBuilderMock->expects()->update(Mockery::capture($updatedData))->once()->andReturn(1);
        $queryBuilderMock->shouldReceive("get")->twice()->andReturnUsing(
            fn () => $dummyResults,
            function () use (&$updatedData) {
                // builder->get() returns multiple items
                return collect([
                    $updatedData
                ]);
            }
        );

        $serializerMock->allows([
            "toArray" => $scraperData
        ]);

        $target = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $target->findList($testRequestHash, fn() => []);

        $this->assertEquals([
            "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "results" => [
                ["dummy" => "dummy1"],
                ["dummy" => "dummy2"]
            ]
        ], $result->toArray());
    }

    public function testIfFindListUpdatesCacheIfCacheIsEmpty()
    {
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // the data returned by the scraper
        $scraperData = [
            "results" => [
                ["dummy" => "dummy1"],
                ["dummy" => "dummy2"]
            ]
        ];

        // the cached data in the database
        // this should be an array of arrays as builder->get() returns multiple items
        $cacheData = collect([[
            "request_hash" => $testRequestHash,
            "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "results" => [
                ["dummy" => "dummy1"],
                ["dummy" => "dummy2"]
            ]
        ]]);

        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks(2);
        $repositoryMock->expects()->insert(Mockery::capture($insertedData))->once()->andReturn(true);
        // at first the cache is empty, then it gets "inserted" into
        // so, we change the return value of builder->get accordingly
        $queryBuilderMock->expects()->get()->twice()->andReturnUsing(fn() => collect(), function () use (&$insertedData) {
            return collect([
                $insertedData
            ]);
        });

        $serializerMock->allows([
            "toArray" => $scraperData
        ]);

        $target = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $target->findList($testRequestHash, fn() => []);

        $this->assertEquals($cacheData->first(), $result->toArray());
    }
}
