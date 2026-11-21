<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Anime;
use App\Character;
use App\Manga;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class McpServerTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    private const TOKEN = 'test-mcp-token';

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchIndexModelCleanupList = ['App\\Anime', 'App\\Manga', 'App\\Character'];
        config(['mcp.api_key' => self::TOKEN]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     */
    private function rpc(array $payload, ?string $token = self::TOKEN, array $headers = []): TestResponse
    {
        $headers = ['Accept' => 'application/json', ...$headers];

        if ($token !== null) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $this->postJson('/mcp', $payload, $headers);
    }

    /**
     * @return array<string, mixed>
     */
    private function initializePayload(int|string $id = 1): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-06-18',
                'capabilities' => [],
                'clientInfo' => ['name' => 'jikan-test', 'version' => '1.0.0'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function toolCallPayload(string $name, array $arguments, int|string $id = 10): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => 'tools/call',
            'params' => ['name' => $name, 'arguments' => $arguments],
        ];
    }

    public function testMcpRejectsRequestsWithoutToken()
    {
        $this->rpc($this->initializePayload(), token: null)
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function testMcpRejectsRequestsWithWrongToken()
    {
        $this->rpc($this->initializePayload(), token: 'not-the-token')
            ->assertStatus(401);
    }

    public function testMcpFailsClosedWhenNoKeyIsConfigured()
    {
        config(['mcp.api_key' => null]);
        $this->rpc($this->initializePayload())->assertStatus(401);

        config(['mcp.api_key' => '']);
        $this->rpc($this->initializePayload())->assertStatus(401);
    }

    public function testMcpHandshakeAndToolListingSucceedWithToken()
    {
        $this->rpc($this->initializePayload())
            ->assertStatus(200)
            ->assertJsonPath('jsonrpc', '2.0')
            ->assertJsonPath('result.protocolVersion', '2025-06-18')
            ->assertJsonPath('result.serverInfo.name', 'Jikan REST');

        $response = $this->rpc([
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
            'params' => [],
        ])->assertStatus(200);

        $names = array_column($response->json('result.tools'), 'name');

        $this->assertEqualsCanonicalizing([
            'search_anime',
            'get_anime',
            'search_manga',
            'search_characters',
            'get_anime_relations',
            'get_seasonal_anime',
            'compare_anime',
        ], $names);

        foreach ($response->json('result.tools') as $tool) {
            $this->assertTrue($tool['annotations']['readOnlyHint'], $tool['name'].' is not read-only');
        }
    }

    public function testGetAnimeReturnsTheSeededEntry()
    {
        Anime::factory(1)->createOne([
            'mal_id' => 5114,
            'title' => 'Fullmetal Alchemist: Brotherhood',
        ]);

        $this->rpc($this->toolCallPayload('get_anime', ['mal_id' => 5114]))
            ->assertStatus(200)
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.mal_id', 5114)
            ->assertJsonPath('result.structuredContent.title', 'Fullmetal Alchemist: Brotherhood');
    }

    public function testGetAnimeReturnsToolErrorForUnknownId()
    {
        $this->mockJikanParserWith404RespondingUpstream();

        $response = $this->rpc($this->toolCallPayload('get_anime', ['mal_id' => 999999]))
            ->assertStatus(200)
            ->assertJsonPath('result.isError', true);

        $this->assertStringContainsString('999999', (string) $response->json('result.content.0.text'));
    }

    public function testGetAnimeReturnsToolErrorForInvalidId()
    {
        $response = $this->rpc($this->toolCallPayload('get_anime', ['mal_id' => 0]))
            ->assertStatus(200)
            ->assertJsonPath('result.isError', true);

        $this->assertStringContainsString('mal_id', (string) $response->json('result.content.0.text'));
    }

    public function testSearchAnimeReturnsBoundedCards()
    {
        $title = 'awesome anime';

        Anime::factory(3)->create([
            'titles' => [['type' => 'Default', 'title' => $title]],
            'title' => $title,
            'title_english' => $title,
            'title_japanese' => $title,
            'title_synonyms' => [$title],
        ]);

        $response = $this->rpc($this->toolCallPayload('search_anime', [
            'query' => 'awesome',
            'limit' => 2,
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.count', 2)
            ->assertJsonPath('result.structuredContent.has_next_page', true);

        $items = $response->json('result.structuredContent.items');

        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertArrayHasKey('mal_id', $items[0]);
        $this->assertArrayHasKey('title', $items[0]);
        $this->assertArrayHasKey('images', $items[0]);
        // Cards are intentionally compact: heavy fields stay out of the payload.
        $this->assertArrayNotHasKey('synopsis', $items[0]);
        $this->assertArrayNotHasKey('background', $items[0]);
    }

    public function testSearchAnimeRejectsOutOfRangeLimit()
    {
        $response = $this->rpc($this->toolCallPayload('search_anime', [
            'query' => 'awesome',
            'limit' => 26,
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', true);

        $this->assertStringContainsString('limit', (string) $response->json('result.content.0.text'));
    }

    public function testSearchAnimeRejectsMissingQuery()
    {
        $this->rpc($this->toolCallPayload('search_anime', []))
            ->assertStatus(200)
            ->assertJsonPath('result.isError', true);
    }

    public function testSearchMangaReturnsBoundedCards()
    {
        $title = 'awesome manga';

        Manga::factory(2)->create([
            'titles' => [['type' => 'Default', 'title' => $title]],
            'title' => $title,
            'title_english' => $title,
            'title_japanese' => $title,
            'title_synonyms' => [$title],
        ]);

        $response = $this->rpc($this->toolCallPayload('search_manga', [
            'query' => 'awesome',
            'limit' => 1,
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.count', 1)
            ->assertJsonPath('result.structuredContent.has_next_page', true);

        $items = $response->json('result.structuredContent.items');

        $this->assertCount(1, $items);
        $this->assertArrayHasKey('mal_id', $items[0]);
        $this->assertArrayHasKey('title', $items[0]);
        $this->assertArrayHasKey('chapters', $items[0]);
        $this->assertArrayNotHasKey('synopsis', $items[0]);
        $this->assertArrayNotHasKey('background', $items[0]);
    }

    public function testSearchCharactersReturnsCompactCards()
    {
        Character::factory()->createOne([
            'mal_id' => 98001,
            'name' => 'Okabe Rintarou',
            'name_kanji' => '岡部 倫太郎',
            'nicknames' => ['Okarin'],
            'member_favorites' => 42,
        ]);

        $response = $this->rpc($this->toolCallPayload('search_characters', [
            'query' => 'Okabe',
            'limit' => 5,
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.count', 1);

        $items = $response->json('result.structuredContent.items');

        $this->assertSame(98001, $items[0]['mal_id']);
        $this->assertSame('Okabe Rintarou', $items[0]['name']);
        $this->assertSame(['Okarin'], $items[0]['nicknames']);
        $this->assertSame(42, $items[0]['favorites']);
        $this->assertArrayNotHasKey('about', $items[0]);
    }

    public function testGetAnimeRelationsCapsEachRelationList()
    {
        $entries = [];

        for ($i = 0; $i < 55; $i++) {
            $entries[] = [
                'mal_id' => 1000 + $i,
                'type' => 'anime',
                'name' => 'Related '.$i,
                'url' => 'https://myanimelist.net/anime/'.(1000 + $i),
            ];
        }

        Anime::factory()->createOne([
            'mal_id' => 1,
            'related' => [
                ['relation' => 'Sequel', 'entry' => $entries],
            ],
        ]);

        $response = $this->rpc($this->toolCallPayload('get_anime_relations', ['mal_id' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.truncated', true)
            ->assertJsonPath('result.structuredContent.data.0.relation', 'Sequel')
            ->assertJsonPath('result.structuredContent.data.0.entry_total', 55)
            ->assertJsonPath('result.structuredContent.data.0.truncated', true);

        $entries = $response->json('result.structuredContent.data.0.entry');
        $this->assertCount(50, $entries);
        $this->assertSame(1000, $entries[0]['mal_id']);
    }

    public function testGetAnimeRelationsReturnsToolErrorForUnknownId()
    {
        $this->mockJikanParserWith404RespondingUpstream();

        $response = $this->rpc($this->toolCallPayload('get_anime_relations', ['mal_id' => 999999]))
            ->assertStatus(200)
            ->assertJsonPath('result.isError', true);

        $this->assertStringContainsString('999999', (string) $response->json('result.content.0.text'));
    }

    public function testGetSeasonalAnimeReturnsCompactCards()
    {
        Anime::factory()->createOne([
            'mal_id' => 777,
            'title' => 'Winter Show',
            'premiered' => 'Winter 2024',
        ]);

        $response = $this->rpc($this->toolCallPayload('get_seasonal_anime', [
            'year' => 2024,
            'season' => 'winter',
            'limit' => 10,
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.count', 1);

        $items = $response->json('result.structuredContent.items');
        $this->assertSame(777, $items[0]['mal_id']);
        $this->assertSame('Winter Show', $items[0]['title']);
        $this->assertArrayNotHasKey('synopsis', $items[0]);
    }

    public function testCompareAnimeReturnsRequestedCardsInOrderAndFields()
    {
        Anime::factory()->createOne(['mal_id' => 1, 'title' => 'First Entry', 'score' => 8.5]);
        Anime::factory()->createOne(['mal_id' => 2, 'title' => 'Second Entry', 'score' => 7.5]);

        $response = $this->rpc($this->toolCallPayload('compare_anime', [
            'ids' => [2, 1],
            'fields' => ['title', 'score', 'not_a_field'],
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.count', 2);

        $items = $response->json('result.structuredContent.items');

        $this->assertSame([2, 1], array_column($items, 'mal_id'));
        $this->assertSame('Second Entry', $items[0]['title']);
        $this->assertSame(['mal_id', 'title', 'score'], array_keys($items[0]));
        $this->assertArrayNotHasKey('not_a_field', $items[0]);
        $this->assertArrayNotHasKey('images', $items[0]);
    }

    public function testCompareAnimeReturnsToolErrorWhenAnIdIsMissing()
    {
        Anime::factory()->createOne(['mal_id' => 1, 'title' => 'Present Entry']);

        $response = $this->rpc($this->toolCallPayload('compare_anime', [
            'ids' => [1, 999999],
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', true);

        $this->assertStringContainsString('999999', (string) $response->json('result.content.0.text'));
    }

    public function testCompareAnimeRejectsFewerThanTwoIds()
    {
        $response = $this->rpc($this->toolCallPayload('compare_anime', ['ids' => [1]]))
            ->assertStatus(200)
            ->assertJsonPath('result.isError', true);

        $this->assertStringContainsString('ids', (string) $response->json('result.content.0.text'));
    }

    public function testSearchMangaRejectsOutOfRangeLimit()
    {
        $response = $this->rpc($this->toolCallPayload('search_manga', [
            'query' => 'awesome',
            'limit' => 99,
        ]))->assertStatus(200)
            ->assertJsonPath('result.isError', true);

        $this->assertStringContainsString('limit', (string) $response->json('result.content.0.text'));
    }
}
