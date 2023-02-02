<?php

namespace Unit;

use App\Contracts\Repository;
use App\Services\DefaultCachedScraperService;
use Illuminate\Support\Carbon;
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
        // the cached data in the database
        $dummyResults = collect([
            ["dummy" => "dummy1", "modifiedAt" => new UTCDateTime()],
            ["dummy" => "dummy2", "modifiedAt" => new UTCDateTime()]
        ]);
        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        $queryBuilderMock->expects()->get()->once()->andReturn($dummyResults);

        $target = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);

        $result = $target->findList($testRequestHash, fn() => []);

        $this->assertEquals($dummyResults->toArray(), $result->toArray());
    }

    public function testIfFindListUpdatesCacheIfItemsExpired()
    {
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // the cached data in the database
        $dummyResults = collect([
            ["dummy" => "dummy1", "modifiedAt" => new UTCDateTime($now->sub("2 days")->timestamp)],
            ["dummy" => "dummy2", "modifiedAt" => new UTCDateTime($now->sub("2 days")->timestamp)]
        ]);

        // the data returned by the scraper
        $scraperData = [
            "results" => [
                ["dummy" => "dummy1"],
                ["dummy" => "dummy2"]
            ]
        ];
        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks(3);
        $queryBuilderMock->expects()->get()->twice()->andReturn($dummyResults);
        $queryBuilderMock->expects()->update(Mockery::any())->once()->andReturn(1);

        $serializerMock->allows([
            "toArray" => $scraperData
        ]);

        $target = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $target->findList($testRequestHash, fn() => []);

        $this->assertEquals([
            ["dummy" => "dummy1", "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3))],
            ["dummy" => "dummy2", "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3))]
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

        $cacheData = [
            ["dummy" => "dummy1", "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3))],
            ["dummy" => "dummy2", "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3))]
        ];

        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks(2);
        // at first the cache is empty
        $queryBuilderMock->expects()->get()->once()->andReturn(collect());
        // then it gets "inserted" into
        $queryBuilderMock->expects()->get()->once()->andReturn(collect($cacheData));
        $repositoryMock->expects()->insert(Mockery::any())->once()->andReturn(true);

        $serializerMock->allows([
            "toArray" => $scraperData
        ]);

        $target = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $target->findList($testRequestHash, fn() => []);

        $this->assertEquals($cacheData, $result->toArray());
    }

    public function testIfModifiedAtValueSetCorrectlyDuringCacheUpdate()
    {

    }
}
