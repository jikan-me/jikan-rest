<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Contracts\UnitOfWork;
use App\Profile;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

/**
 * DatabaseRepository::insert() fill()s the scraped attributes into the model, so any
 * key missing from $fillable is dropped silently (and throws under strict models).
 * Profile used to lose `last_updates` and `external_links` that way.
 */
class ProfileInsertTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    public function testInsertKeepsLastUpdatesAndExternalLinks(): void
    {
        $this->app->make(UnitOfWork::class)->users()->insert([
            'mal_id' => 777,
            'username' => 'Example',
            'internal_username' => 'example',
            'request_hash' => 'request:users:example',
            'last_updates' => ['anime' => [['entry' => ['mal_id' => 1]]], 'manga' => []],
            'external_links' => [['name' => 'Blog', 'url' => 'https://example.org']],
        ]);

        $profile = Profile::query()->where('mal_id', 777)->first();

        $this->assertNotNull($profile);
        $this->assertSame('Blog', $profile->external_links[0]['name']);
        $this->assertSame(1, $profile->last_updates['anime'][0]['entry']['mal_id']);
    }
}
