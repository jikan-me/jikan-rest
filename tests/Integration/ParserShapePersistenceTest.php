<?php

namespace Tests\Integration;

use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Jikan\Model\Common\Title;
use Jikan\Model\Common\Url;
use Jikan\Model\Producer\Producer;
use Jikan\Model\Resource\WrapImageResource\WrapImageResource;
use Jikan\MyAnimeList\MalClient;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\TestCase;

/**
 * End-to-end through HTTP with the MAL client returning parser-shaped models,
 * under APP_ENV=testing (Model::shouldBeStrict on). Regression coverage for the
 * Producers $fillable bug (parser emits `name` and `external_links`; strict mode
 * threw, production silently dropped them) and the user-list route/DTO chain.
 */
final class ParserShapePersistenceTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    /** Scraper-cache collections that SyntheticMongoDbTransaction does not know about (no model). */
    private const LIST_COLLECTIONS = ['users_animelist', 'users_mangalist'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearListCollections();
    }

    protected function tearDown(): void
    {
        $this->clearListCollections();

        parent::tearDown();
    }

    private function clearListCollections(): void
    {
        foreach (self::LIST_COLLECTIONS as $collection) {
            DB::table($collection)->delete();
        }
    }

    private function parserShapedProducer(): Producer
    {
        $values = [
            'malId' => 777,
            'url' => 'https://myanimelist.net/anime/producer/777/Studio_Pierrot',
            'titles' => [new Title('Default', 'Studio Pierrot')],
            'name' => 'Studio Pierrot',
            'images' => WrapImageResource::factory('https://cdn.myanimelist.net/images/company/1.png'),
            'established' => new \DateTimeImmutable('1979-05-01'),
            'favorites' => 321,
            'about' => 'An animation studio.',
            'externalLinks' => [new Url('Official Site', 'https://pierrot.example.test')],
            'count' => 12,
            'hasNextPage' => false,
            'lastVisiblePage' => 1,
        ];

        $reflection = new \ReflectionClass(Producer::class);
        $producer = $reflection->newInstanceWithoutConstructor();
        foreach ($values as $name => $value) {
            $reflection->getProperty($name)->setValue($producer, $value);
        }
        $producer->setResults([]);

        return $producer;
    }

    public function testProducerFromParserIsPersistedWithNameAndExternalLinks(): void
    {
        $client = \Mockery::mock(MalClient::class);
        $client->allows('getProducer')->andReturn($this->parserShapedProducer());
        $this->app->instance('JikanParser', $client);

        $this->get('/v4/producers/777')
            ->assertOk()
            ->assertJsonPath('data.mal_id', 777)
            ->assertJsonPath('data.titles.0.title', 'Studio Pierrot');

        $stored = DB::table('producers')->where('mal_id', 777)->first();
        $this->assertNotNull($stored, 'producer must be persisted');
        $stored = (array) $stored;
        $this->assertSame('Studio Pierrot', $stored['name']);
        $this->assertSame('Official Site', ((array) $stored['external_links'][0])['name']);

        $this->get('/v4/producers/777/external')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Official Site')
            ->assertJsonPath('data.0.url', 'https://pierrot.example.test');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function userListUris(): array
    {
        return [
            'anime, no status' => ['/v4/users/Xinil/animelist'],
            'anime, status path' => ['/v4/users/Xinil/animelist/watching'],
            'anime, date range' => ['/v4/users/Xinil/animelist?aired_from=1990-01-01&aired_to=2030-01-01'],
            'manga, no status' => ['/v4/users/Xinil/mangalist'],
            'manga, status path' => ['/v4/users/Xinil/mangalist/reading'],
            'manga, date range' => ['/v4/users/Xinil/mangalist?published_from=1990-01-01&published_to=2030-01-01'],
        ];
    }

    #[DataProvider('userListUris')]
    public function testUserListRoutesRenderAnEmptyListEndToEnd(string $uri): void
    {
        $response = \Mockery::mock(ResponseInterface::class);
        $response->allows([
            'getStatusCode' => 200,
            'getHeaders' => [],
            'getContent' => '[]',
            'getInfo' => 'https://myanimelist.net/mock-upstream',
        ]);
        $httpClient = \Mockery::mock(HttpClientInterface::class);
        $httpClient->allows('request')->andReturn($response);
        $this->app->instance('JikanParser', new MalClient($httpClient));

        $this->get($uri)->assertOk()->assertJsonPath('data', []);
    }
}
