<?php

namespace App\Mcp\Support;

/**
 * Shapes the compact cards returned by the MCP tools.
 *
 * The field lists are deliberately small: MCP tool output is meant to stay
 * bounded and cheap for an agent to read, while the values themselves come
 * verbatim from the v4 payload.
 */
final class CompactCard
{
    /**
     * @var array<int, string>
     */
    public const ANIME_FIELDS = [
        'mal_id',
        'url',
        'images',
        'title',
        'title_english',
        'title_japanese',
        'type',
        'source',
        'episodes',
        'status',
        'airing',
        'duration',
        'rating',
        'score',
        'scored_by',
        'rank',
        'popularity',
        'members',
        'favorites',
        'season',
        'year',
    ];

    /**
     * @var array<int, string>
     */
    public const MANGA_FIELDS = [
        'mal_id',
        'url',
        'images',
        'titles',
        'title',
        'title_english',
        'title_japanese',
        'type',
        'chapters',
        'volumes',
        'status',
        'publishing',
        'published',
        'score',
        'scored_by',
        'rank',
        'popularity',
        'members',
        'favorites',
    ];

    /**
     * @var array<int, string>
     */
    public const CHARACTER_FIELDS = [
        'mal_id',
        'name',
        'name_kanji',
        'nicknames',
        'favorites',
        'url',
    ];

    /**
     * @param  array<string, mixed>  $anime
     * @param  array<int, string>|null  $fields  null = the default card fields
     * @return array<string, mixed>
     */
    public static function anime(array $anime, ?array $fields = null): array
    {
        if ($fields === null) {
            return self::pick($anime, self::ANIME_FIELDS);
        }

        $requested = [];

        foreach ($fields as $field) {
            if (in_array($field, self::ANIME_FIELDS, true) && !in_array($field, $requested, true)) {
                $requested[] = $field;
            }
        }

        // Keep ids on the card so a multi-entry comparison stays identifiable.
        if (!in_array('mal_id', $requested, true)) {
            array_unshift($requested, 'mal_id');
        }

        return self::pick($anime, $requested);
    }

    /**
     * @param  array<string, mixed>  $manga
     * @return array<string, mixed>
     */
    public static function manga(array $manga): array
    {
        return self::pick($manga, self::MANGA_FIELDS);
    }

    /**
     * @param  array<string, mixed>  $character
     * @return array<string, mixed>
     */
    public static function character(array $character): array
    {
        return self::pick($character, self::CHARACTER_FIELDS);
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    private static function pick(array $source, array $fields): array
    {
        $card = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $source)) {
                $card[$field] = $source[$field];
            }
        }

        return $card;
    }
}
