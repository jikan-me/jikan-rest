<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\V4ApiClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_anime')]
#[Description('Fetch a single anime entry from the Jikan v4 API by its MyAnimeList id. Returns the same detail payload as GET /v4/anime/{id}. Read-only.')]
#[IsReadOnly]
#[IsIdempotent]
final class GetAnimeTool extends Tool
{
    public function __construct(
        private readonly V4ApiClient $api,
    ) {}

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'mal_id' => ['required', 'integer', 'min:1'],
        ], [
            'mal_id.required' => 'Provide a MyAnimeList id in `mal_id` (a positive integer).',
            'mal_id.integer' => 'The `mal_id` must be a positive integer.',
            'mal_id.min' => 'The `mal_id` must be at least 1.',
        ]);

        $malId = (int) $validated['mal_id'];
        $result = $this->api->get('/v4/anime/'.$malId);

        if ($result['status'] === 404) {
            return Response::error("No anime found with mal_id {$malId}.");
        }

        if ($result['status'] !== 200) {
            return Response::error(V4ApiClient::errorMessage($result, "Unable to fetch anime with mal_id {$malId}."));
        }

        $data = data_get($result['body'], 'data');

        if (!is_array($data) || $data === []) {
            return Response::error("No anime found with mal_id {$malId}.");
        }

        return Response::structured($data);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'mal_id' => $schema->integer()
                ->description('MyAnimeList id of the anime (for example 1 for Cowboy Bebop).')
                ->min(1)
                ->required(),
        ];
    }
}
