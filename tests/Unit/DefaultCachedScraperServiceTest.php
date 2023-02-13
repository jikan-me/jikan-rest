<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Unit;

use App\Anime;
use App\Contracts\Repository;
use App\Profile;
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
    protected function tearDown(): void
    {
        parent::tearDown();
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
            ->allows()
            ->where("request_hash", $this->requestHash())
            ->atMost()
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

    public function testIfFindReturnsNotExpiredItems()
    {
        $malId = 1;
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $mockModel = Anime::factory()->makeOne([
            "mal_id" => $malId,
            "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime($now->getPreciseTimestamp(3))
        ]);
        [, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        $repositoryMock->expects()->getAllByMalId($malId)->andReturns(collect([
            $mockModel
        ]));

        $sut = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $sut->find($malId, $testRequestHash);

        $this->assertEquals($mockModel->toArray(), $result->toArray());
    }

    public function testIfFindScrapesNotFoundKey()
    {
        $malId = 1;
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $mockModel = Anime::factory()->makeOne([
            "mal_id" => $malId,
            "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime($now->getPreciseTimestamp(3))
        ]);
        [, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        // nothing in db:
        $repositoryMock->expects()->getAllByMalId($malId)->andReturns(collect());
        // scrape returns data:
        $repositoryMock->expects()->scrape($malId)->andReturns(
            collect($mockModel->toArray())->except(["request_hash", "modifiedAt", "createdAt"])->toArray()
        );
        $repositoryMock->expects()->insert(Mockery::any())->andReturns(true);
        $repositoryMock->expects()->getByMalId($malId)->andReturns($mockModel);

        $sut = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $sut->find($malId, $testRequestHash);

        $this->assertEquals($mockModel->toArray(), $result->toArray());
    }

    public function testIfFindUpdatesExpiredDbItem()
    {
        $malId = 1;
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        $mockModel = Anime::factory()->makeOne([
            "mal_id" => $malId,
            "modifiedAt" => new UTCDateTime($now->sub("3 days")->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime($now->sub("3 days")->getPreciseTimestamp(3))
        ]);
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $updatedMockModel = Anime::factory()->makeOne([
            ...$mockModel->toArray(),
            "mal_id" => $malId,
            "modifiedAt" => new UTCDateTime(Carbon::now()->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime(Carbon::now()->getPreciseTimestamp(3))
        ]);

        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        // stale record in db
        $repositoryMock->expects()->getAllByMalId($malId)->andReturns(collect([
            $mockModel
        ]));

        $repositoryMock->expects()->scrape($malId)->andReturns(
            collect($mockModel->toArray())->except(["request_hash", "createdAt"])->toArray()
        );

        $repositoryMock->expects()->queryByMalId($malId)->andReturns($queryBuilderMock);
        $queryBuilderMock->expects()->update(Mockery::any())->andReturns($malId);
        $repositoryMock->expects()->getByMalId($malId)->andReturns($updatedMockModel);

        $sut = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $sut->find($malId, $testRequestHash);

        $this->assertEquals($updatedMockModel->toArray(), $result->toArray());
    }

    public function testIfFindByKeyReturnsNotExpiredItems()
    {
        $username = "kompot";
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $mockModel = Profile::factory()->makeOne([
            "username" => $username,
            "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime($now->getPreciseTimestamp(3))
        ]);
        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        $repositoryMock->expects()->where("username", $username)->andReturns($queryBuilderMock);
        $queryBuilderMock->expects()->get()->andReturns(collect([
            $mockModel
        ]));

        $sut = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $sut->findByKey("username", $username, $testRequestHash);

        $this->assertEquals($mockModel->toArray(), $result->toArray());
    }

    public function testIfFindByKeyScrapesNotFoundKey()
    {
        $username = "kompot";
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $mockModel = Profile::factory()->makeOne([
            "username" => $username,
            "modifiedAt" => new UTCDateTime($now->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime($now->getPreciseTimestamp(3))
        ]);

        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        $repositoryMock->expects()->where("username", $username)->atMost()->times(2)->andReturns($queryBuilderMock);
        // nothing in db
        $queryBuilderMock->expects()->get()->andReturns(collect());
        // scrape returns data
        $repositoryMock->expects()->scrape($username)->andReturns(
            collect($mockModel->toArray())->except(["request_hash", "modifiedAt", "createdAt"])->toArray()
        );
        $repositoryMock->expects()->insert(Mockery::any())->andReturns(true);
        $queryBuilderMock->expects()->get()->andReturns(collect([
            $mockModel
        ]));

        $sut = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $sut->findByKey("username", $username, $testRequestHash);

        $this->assertEquals($mockModel->toArray(), $result->toArray());
    }

    public function testIfFindByKeyUpdatesCache()
    {
        $malId = 1;
        $username = "kompot";
        $testRequestHash = $this->requestHash();
        $now = Carbon::now();
        $mockModel = Profile::factory()->makeOne([
            "mal_id" => $malId,
            "username" => $username,
            "modifiedAt" => new UTCDateTime($now->sub("3 days")->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime($now->sub("3 days")->getPreciseTimestamp(3))
        ]);
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $updatedMockModel = Profile::factory()->makeOne([
            ...$mockModel->toArray(),
            "location" => "North Pole",
            "modifiedAt" => new UTCDateTime(Carbon::now()->getPreciseTimestamp(3)),
            "createdAt" => new UTCDateTime(Carbon::now()->getPreciseTimestamp(3))
        ]);
        [$queryBuilderMock, $repositoryMock, $serializerMock] = $this->makeCtorArgMocks();
        // stale record in db
        $repositoryMock->expects()->where("username", $username)->atMost()->times(3)->andReturns($queryBuilderMock);
        $queryBuilderMock->expects()->get()->andReturns(collect([
            $mockModel
        ]));
        $repositoryMock->expects()->scrape($username)->andReturns(
            collect($updatedMockModel->toArray())->except(["request_hash", "modifiedAt", "createdAt"])->toArray()
        );
        // mock out update
        $queryBuilderMock->expects()->update(Mockery::any())->andReturns($malId);
        // second call to ->get() should return the updated value
        $queryBuilderMock->expects()->get()->andReturns(collect([
            $updatedMockModel
        ]));

        $sut = new DefaultCachedScraperService($repositoryMock, new MalClient(), $serializerMock);
        $result = $sut->findByKey("username", $username, $testRequestHash);

        $this->assertEquals($updatedMockModel->toArray(), $result->toArray());
    }
}
