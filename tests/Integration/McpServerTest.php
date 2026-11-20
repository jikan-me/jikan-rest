<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Anime;
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
        $this->searchIndexModelCleanupList = ['App\\Anime'];
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

        $this->assertContains('search_anime', $names);
        $this->assertContains('get_anime', $names);

        $annotations = collect($response->json('result.tools'))
            ->firstWhere('name', 'get_anime')['annotations'];
        $this->assertTrue($annotations['readOnlyHint']);
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
}
