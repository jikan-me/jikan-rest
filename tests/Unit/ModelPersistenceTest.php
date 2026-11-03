<?php

namespace Tests\Unit;

use App\Anime;
use App\Manga;
use App\Repositories\DefaultAnimeRepository;
use App\Repositories\DefaultMangaRepository;
use Tests\TestCase;

/**
 * The scrape -> parse -> persist store path, exercised directly. This is the
 * boundary the earlier indexer 500s lived in: v5-parsed anime/manga carry
 * `external_links` / `streaming_links`, and strict-model `$fillable` must
 * accept them or every such scrape 500s at insert (had: 20k+ failed ids).
 */
final class ModelPersistenceTest extends TestCase
{
    public function test_anime_persists_v5_external_and_streaming_links(): void
    {
        app(DefaultAnimeRepository::class)->insert([
            'mal_id' => 990001,
            'url' => 'https://myanimelist.net/anime/990001/X',
            'title' => 'X',
            'titles' => [],
            'images' => [],
            'score' => 8.0,
            'approved' => true,
            'external_links' => [['name' => 'Official', 'url' => 'https://example.com/official']],
            'streaming_links' => [['name' => 'Crunchyroll', 'url' => 'https://crunchyroll.com/x']],
        ]);

        $row = Anime::where('mal_id', 990001)->first();
        $this->assertNotNull($row, 'anime doc should persist');
        $this->assertSame('https://example.com/official', $row->external_links[0]['url'] ?? null);
        $this->assertSame('https://crunchyroll.com/x', $row->streaming_links[0]['url'] ?? null);
    }

    public function test_manga_persists_v5_external_links(): void
    {
        app(DefaultMangaRepository::class)->insert([
            'mal_id' => 990002,
            'url' => 'https://myanimelist.net/manga/990002/Y',
            'title' => 'Y',
            'images' => [],
            'approved' => true,
            'external_links' => [['name' => 'Kodansha', 'url' => 'https://kodansha.com/y']],
        ]);

        $row = Manga::where('mal_id', 990002)->first();
        $this->assertNotNull($row, 'manga doc should persist');
        $this->assertSame('https://kodansha.com/y', $row->external_links[0]['url'] ?? null);
    }
}
