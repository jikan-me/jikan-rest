<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\CompactCard;
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

#[Name('compare_anime')]
#[Description('Fetch 2-5 anime entries by MyAnimeList id in one call and return compact cards for easy comparison. Optionally restrict the card fields. Read-only.')]
#[IsReadOnly]
#[IsIdempotent]
final class CompareAnimeTool extends Tool
{
    private const MIN_IDS = 2;

    private const MAX_IDS = 5;

    public function __construct(
        private readonly V4ApiClient $api,
    ) {}

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:'.self::MIN_IDS, 'max:'.self::MAX_IDS],
            'ids.*' => ['integer', 'min:1', 'distinct'],
            'fields' => ['sometimes', 'array', 'min:1', 'max:20'],
            'fields.*' => ['string', 'max:50'],
        ], [
            'ids.required' => 'Provide 2-5 MyAnimeList ids in `ids`.',
            'ids.array' => 'The `ids` argument must be an array of positive integers.',
            'ids.min' => 'The `ids` array must contain at least 2 ids.',
            'ids.max' => 'The `ids` array must contain at most 5 ids.',
            'ids.*.integer' => 'Every entry of `ids` must be a positive integer.',
            'ids.*.min' => 'Every entry of `ids` must be at least 1.',
            'ids.*.distinct' => 'The `ids` array must not contain duplicate ids.',
            'fields.array' => 'The `fields` argument must be an array of field names.',
            'fields.min' => 'The `fields` array must contain at least 1 field name.',
            'fields.max' => 'The `fields` array must contain at most 20 field names.',
            'fields.*.string' => 'Every entry of `fields` must be a string.',
        ]);

        $ids = [];

        foreach ((array) $validated['ids'] as $id) {
            $ids[] = (int) $id;
        }

        $ids = array_slice(array_values(array_unique($ids)), 0, self::MAX_IDS);

        // Defensive: schema/validation already enforce this, but the upstream
        // request must never be built from an unbounded or too-small id set.
        if (count($ids) < self::MIN_IDS) {
            return Response::error('Provide at least 2 distinct MyAnimeList ids in `ids`.');
        }

        $fields = null;

        if (array_key_exists('fields', $validated) && is_array($validated['fields'])) {
            $fields = array_values(array_filter($validated['fields'], 'is_string'));
        }

        $result = $this->api->get('/v4/anime', [
            'ids' => implode(',', $ids),
        ]);

        if ($result['status'] !== 200) {
            return Response::error(V4ApiClient::errorMessage($result, 'Unable to fetch the requested anime entries.'));
        }

        $data = data_get($result['body'], 'data');

        if (!is_array($data)) {
            return Response::error('Unexpected response from the v4 anime catalog.');
        }

        $byId = [];

        foreach ($data as $entry) {
            if (is_array($entry) && isset($entry['mal_id'])) {
                $byId[(int) $entry['mal_id']] = $entry;
            }
        }

        $missing = array_values(array_filter($ids, fn (int $id): bool => !isset($byId[$id])));

        if ($missing !== []) {
            return Response::error('No anime found with mal_id: '.implode(', ', $missing).'.');
        }

        $items = array_map(
            fn (int $id): array => CompactCard::anime($byId[$id], $fields),
            $ids,
        );

        return Response::structured([
            'count' => count($items),
            'items' => $items,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()
                ->description('2-5 distinct MyAnimeList ids to compare, in the order they should be returned.')
                ->items($schema->integer()->min(1))
                ->unique()
                ->min(self::MIN_IDS)
                ->max(self::MAX_IDS)
                ->required(),

            'fields' => $schema->array()
                ->description('Optional compact-card fields to keep (for example ["title", "score"]). Unknown fields are ignored; mal_id is always kept.')
                ->items($schema->string()->max(50))
                ->min(1)
                ->max(20),
        ];
    }
}
