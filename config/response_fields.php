<?php

/*
|--------------------------------------------------------------------------
| Opt-in response field selection
|--------------------------------------------------------------------------
|
| `?fields=` is additive and opt-in: when absent, responses are byte-identical
| to the v4 contract. Only top-level keys are selectable. Unknown keys are a
| 400 validation error, so typos do not silently return the full object.
|
*/

return [
    'routes' => [
        'v4.anime.main' => 'anime',
        'v4.search.anime' => 'anime',
        'v4.manga.main' => 'manga',
        'v4.search.manga' => 'manga',
        'v4.character.main' => 'character',
        'v4.search.character' => 'character',
    ],

    'resources' => [
        'anime' => [
            'mal_id', 'url', 'images', 'trailer', 'approved', 'titles', 'title',
            'title_english', 'title_japanese', 'title_synonyms', 'type', 'source',
            'episodes', 'status', 'airing', 'aired', 'duration', 'rating', 'score',
            'scored_by', 'rank', 'popularity', 'members', 'favorites', 'synopsis',
            'background', 'season', 'year', 'broadcast', 'producers', 'licensors',
            'studios', 'genres', 'explicit_genres', 'themes', 'demographics',
        ],
        'manga' => [
            'mal_id', 'url', 'images', 'approved', 'titles', 'title', 'title_english',
            'title_japanese', 'title_synonyms', 'type', 'chapters', 'volumes', 'status',
            'publishing', 'published', 'score', 'scored', 'scored_by', 'rank',
            'popularity', 'members', 'favorites', 'synopsis', 'background', 'authors',
            'serializations', 'genres', 'explicit_genres', 'themes', 'demographics',
        ],
        'character' => [
            'mal_id', 'url', 'images', 'name', 'name_kanji', 'nicknames', 'favorites', 'about',
        ],
    ],
];
