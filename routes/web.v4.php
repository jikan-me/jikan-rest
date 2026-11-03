<?php

use App\Http\Controllers\V4DB\AnimeController;
use App\Http\Controllers\V4DB\CharacterController;
use App\Http\Controllers\V4DB\ClubController;
use App\Http\Controllers\V4DB\GenreController;
use App\Http\Controllers\V4DB\MagazineController;
use App\Http\Controllers\V4DB\MangaController;
use App\Http\Controllers\V4DB\PersonController;
use App\Http\Controllers\V4DB\ProducerController;
use App\Http\Controllers\V4DB\RandomController;
use App\Http\Controllers\V4DB\RecommendationsController;
use App\Http\Controllers\V4DB\ReviewsController;
use App\Http\Controllers\V4DB\ScheduleController;
use App\Http\Controllers\V4DB\SearchController;
use App\Http\Controllers\V4DB\SeasonController;
use App\Http\Controllers\V4DB\TopController;
use App\Http\Controllers\V4DB\UserController;
use App\Http\Controllers\V4DB\WatchController;
use App\Providers\SourceHeartbeatProvider;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'author_url' => 'https://github.com/irfan-dahir',
        'discord_url' => 'http://discord.jikan.moe',
        'version' => config('app.version'),
        'parser_version' => JIKAN_PARSER_VERSION,
        'website_url' => 'https://jikan.moe',
        'documentation_url' => 'https://docs.api.jikan.moe/',
        'github_url' => 'https://github.com/jikan-me/jikan-rest',
        'parser_github_url' => 'https://github.com/jikan-me/jikan',
        'production_api_url' => rtrim((string) config('app.url'), '/').'/v4/',
        'status_url' => 'https://status.jikan.moe',
        'myanimelist_heartbeat' => [
            'status' => SourceHeartbeatProvider::getHeartbeatStatus(),
            'score' => SourceHeartbeatProvider::getHeartbeatScore(),
            'down' => SourceHeartbeatProvider::isFailoverEnabled(),
            'last_downtime' => SourceHeartbeatProvider::getLastDowntime(),
        ],
    ]);
})->name('v4.root');

Route::get('/anime', [SearchController::class, 'anime'])->name('v4.search.anime');

Route::group(
    [
        'prefix' => 'anime/{id}',
        // Must live in the group attributes: `group(...)->where()` is a silent no-op.
        'where' => ['id' => '[0-9]+'],
    ],
    function () {
        Route::get('/', [AnimeController::class, 'main'])->name('v4.anime.main');

        Route::get('/full', [AnimeController::class, 'full'])->name('v4.anime.full');

        Route::get('/characters', [AnimeController::class, 'characters'])->name('v4.anime.characters');

        Route::get('/staff', [AnimeController::class, 'staff'])->name('v4.anime.staff');

        Route::get('/episodes', [AnimeController::class, 'episodes'])->name('v4.anime.episodes');

        Route::get('/episodes/{episodeId}', [AnimeController::class, 'episode'])
            ->name('v4.anime.episode')
            ->where('episodeId', '[0-9]+');

        Route::get('/news', [AnimeController::class, 'news'])->name('v4.anime.news');

        Route::get('/forum', [AnimeController::class, 'forum'])->name('v4.anime.forum');

        Route::get('/videos', [AnimeController::class, 'videos'])->name('v4.anime.videos');

        Route::get('/videos/episodes', [AnimeController::class, 'videosEpisodes'])->name('v4.anime.videosEpisodes');

        Route::get('/pictures', [AnimeController::class, 'pictures'])->name('v4.anime.pictures');

        Route::get('/statistics', [AnimeController::class, 'stats'])->name('v4.anime.stats');

        Route::get('/moreinfo', [AnimeController::class, 'moreInfo'])->name('v4.anime.moreInfo');

        Route::get('/recommendations', [AnimeController::class, 'recommendations'])->name('v4.anime.recommendations');

        Route::get('/userupdates', [AnimeController::class, 'userupdates'])->name('v4.anime.userupdates');

        Route::get('/reviews', [AnimeController::class, 'reviews'])->name('v4.anime.reviews');

        Route::get('/relations', [AnimeController::class, 'relations'])->name('v4.anime.relations');

        Route::get('/themes', [AnimeController::class, 'themes'])->name('v4.anime.themes');

        Route::get('/external', [AnimeController::class, 'external'])->name('v4.anime.external');

        Route::get('/streaming', [AnimeController::class, 'streaming'])->name('v4.anime.streaming');
    }
);

Route::get('/manga', [SearchController::class, 'manga'])->name('v4.search.manga');

Route::group(
    [
        'prefix' => 'manga/{id}',
        // Must live in the group attributes: `group(...)->where()` is a silent no-op.
        'where' => ['id' => '[0-9]+'],
    ],
    function () {
        Route::get('/', [MangaController::class, 'main'])->name('v4.manga.main');

        Route::get('/full', [MangaController::class, 'full'])->name('v4.manga.full');

        Route::get('/characters', [MangaController::class, 'characters'])->name('v4.manga.characters');

        Route::get('/news', [MangaController::class, 'news'])->name('v4.manga.news');

        Route::get('/forum', [MangaController::class, 'forum'])->name('v4.manga.forum');

        Route::get('/pictures', [MangaController::class, 'pictures'])->name('v4.manga.pictures');

        Route::get('/statistics', [MangaController::class, 'stats'])->name('v4.manga.stats');

        Route::get('/moreinfo', [MangaController::class, 'moreInfo'])->name('v4.manga.moreInfo');

        Route::get('/recommendations', [MangaController::class, 'recommendations'])->name('v4.manga.recommendations');

        Route::get('/userupdates', [MangaController::class, 'userupdates'])->name('v4.manga.userupdates');

        Route::get('/reviews', [MangaController::class, 'reviews'])->name('v4.manga.reviews');

        Route::get('/relations', [MangaController::class, 'relations'])->name('v4.manga.relations');

        Route::get('/external', [MangaController::class, 'external'])->name('v4.manga.external');
    }
);

Route::get('/characters', [SearchController::class, 'character'])->name('v4.search.character');

Route::group(
    [
        'prefix' => 'characters/{id}',
        // Must live in the group attributes: `group(...)->where()` is a silent no-op.
        'where' => ['id' => '[0-9]+'],
    ],
    function () {
        Route::get('/', [CharacterController::class, 'main'])->name('v4.character.main');

        Route::get('/full', [CharacterController::class, 'full'])->name('v4.character.full');

        Route::get('/anime', [CharacterController::class, 'anime'])->name('v4.character.anime');

        Route::get('/voices', [CharacterController::class, 'voices'])->name('v4.character.voices');

        Route::get('/manga', [CharacterController::class, 'manga'])->name('v4.character.manga');

        Route::get('/pictures', [CharacterController::class, 'pictures'])->name('v4.character.pictures');
    }
);

Route::get('/people', [SearchController::class, 'people'])->name('v4.search.people');
Route::group(
    [
        'prefix' => 'people/{id}',
        // Must live in the group attributes: `group(...)->where()` is a silent no-op.
        'where' => ['id' => '[0-9]+'],
    ],
    function () {
        Route::get('/', [PersonController::class, 'main'])->name('v4.person.main');

        Route::get('/full', [PersonController::class, 'full'])->name('v4.person.full');

        Route::get('/anime', [PersonController::class, 'anime'])->name('v4.person.anime');

        Route::get('/voices', [PersonController::class, 'voices'])->name('v4.person.voices');

        Route::get('/manga', [PersonController::class, 'manga'])->name('v4.person.manga');

        Route::get('/pictures', [PersonController::class, 'pictures'])->name('v4.person.pictures');
    }
);

Route::group(
    [
        'prefix' => 'seasons',
    ],
    function () {
        Route::get('/', [SeasonController::class, 'archive'])->name('v4.season.archive');

        Route::get('/now', [SeasonController::class, 'now'])->name('v4.season.now');

        Route::get('/upcoming', [SeasonController::class, 'later'])->name('v4.season.later');

        Route::get('/{year}/{season}', [SeasonController::class, 'main'])->name('v4.season.main');
    }
);

Route::get('schedules/{filter?}', [ScheduleController::class, 'main'])
    ->name('v4.schedule.main')
    ->where('filter', '[A-Za-z]+');

Route::get('/producers', [SearchController::class, 'producers'])->name('v4.search.producers');

Route::group(
    [
        'prefix' => 'producers/{id}',
        // Must live in the group attributes: `group(...)->where()` is a silent no-op.
        'where' => ['id' => '[0-9]+'],
    ],
    function () {
        Route::get('/', [ProducerController::class, 'main'])->name('v4.producer.main');

        Route::get('/full', [ProducerController::class, 'full'])->name('v4.producer.full');

        Route::get('/external', [ProducerController::class, 'external'])->name('v4.producer.external');
    }
);

Route::group(
    [
        'prefix' => 'magazines',
    ],
    function () {
        Route::get('/', [MagazineController::class, 'main'])->name('v4.magazine.main');
    }
);

Route::group(
    [
        'prefix' => 'users',
    ],
    function () {
        Route::get('/', [SearchController::class, 'users'])->name('v4.search.users');

        Route::get('/recentlyonline', [UserController::class, 'recentlyOnline'])->name('v4.user.recentlyOnline');

        Route::get('/userbyid/{id}', [SearchController::class, 'userById'])
            ->name('v4.search.userById')
            ->where('id', '[0-9]+');

        Route::group(
            [
                'prefix' => '/{username}',
            ],
            function () {
                Route::get('/', [UserController::class, 'profile'])->name('v4.user.profile');

                Route::get('/full', [UserController::class, 'full'])->name('v4.user.full');

                Route::get('/statistics', [UserController::class, 'statistics'])->name('v4.user.statistics');

                Route::get('/favorites', [UserController::class, 'favorites'])->name('v4.user.favorites');

                Route::get('/userupdates', [UserController::class, 'userupdates'])->name('v4.user.userupdates');

                Route::get('/about', [UserController::class, 'about'])->name('v4.user.about');

                Route::get('/history/{type?}', [UserController::class, 'history'])
                    ->name('v4.user.history')
                    ->where('type', '[A-Za-z]+');

                Route::get('/friends', [UserController::class, 'friends'])->name('v4.user.friends');

                if (config('jikan.disable_user_lists') === false) {
                    Route::get('/animelist/{status?}', [UserController::class, 'animelist'])
                        ->name('v4.user.animelist')
                        ->where('status', '[A-Za-z]+');

                    Route::get('/mangalist/{status?}', [UserController::class, 'mangalist'])
                        ->name('v4.user.mangalist')
                        ->where('status', '[A-Za-z]+');
                }

                Route::get('/recommendations', [UserController::class, 'recommendations'])->name('v4.user.recommendations');

                Route::get('/reviews', [UserController::class, 'reviews'])->name('v4.user.reviews');

                Route::get('/clubs', [UserController::class, 'clubs'])->name('v4.user.clubs');

                Route::get('/external', [UserController::class, 'external'])->name('v4.user.external');
            }
        );
    }
);

Route::group(
    [
        'prefix' => 'genres',
    ],
    function () {

        Route::get('/anime', [GenreController::class, 'anime'])->name('v4.genre.anime');

        Route::get('/manga', [GenreController::class, 'manga'])->name('v4.genre.manga');
    }
);

Route::group(
    [
        'prefix' => 'top',
    ],
    function () {
        Route::get('/anime', [TopController::class, 'anime'])->name('v4.top.anime');

        Route::get('/manga', [TopController::class, 'manga'])->name('v4.top.manga');

        Route::get('/characters', [TopController::class, 'characters'])->name('v4.top.characters');

        Route::get('/people', [TopController::class, 'people'])->name('v4.top.people');

        Route::get('/reviews', [TopController::class, 'reviews'])->name('v4.top.reviews');
    }
);

Route::get('/clubs', [SearchController::class, 'clubs'])->name('v4.search.clubs');

Route::group(
    [
        'prefix' => 'clubs',
    ],
    function () {

        Route::get('/{id}', [ClubController::class, 'main'])
            ->name('v4.club.main')
            ->where('id', '[0-9]+');

        Route::get('/{id}/members', [ClubController::class, 'members'])
            ->name('v4.club.members')
            ->where('id', '[0-9]+');

        Route::get('/{id}/staff', [ClubController::class, 'staff'])
            ->name('v4.club.staff')
            ->where('id', '[0-9]+');

        Route::get('/{id}/relations', [ClubController::class, 'relations'])
            ->name('v4.club.relations')
            ->where('id', '[0-9]+');
    }
);

Route::group(
    [
        'prefix' => 'reviews',
    ],
    function () {

        Route::get('/anime', [ReviewsController::class, 'anime'])->name('v4.reviews.anime');

        Route::get('/manga', [ReviewsController::class, 'manga'])->name('v4.reviews.manga');
    }
);

Route::group(
    [
        'prefix' => 'recommendations',
    ],
    function () {
        Route::get('/anime', [RecommendationsController::class, 'anime'])->name('v4.recommendations.anime');

        Route::get('/manga', [RecommendationsController::class, 'manga'])->name('v4.recommendations.manga');
    }
);

Route::group(
    [
        'prefix' => 'watch',
    ],
    function () {
        Route::get('/episodes', [WatchController::class, 'recentEpisodes'])->name('v4.watch.recentEpisodes');

        Route::get('/episodes/popular', [WatchController::class, 'popularEpisodes'])->name('v4.watch.popularEpisodes');

        Route::get('/promos', [WatchController::class, 'recentPromos'])->name('v4.watch.recentPromos');

        Route::get('/promos/popular', [WatchController::class, 'popularPromos'])->name('v4.watch.popularPromos');
    }
);

Route::group(
    [
        'prefix' => 'random',
    ],
    function () {
        Route::get('/anime', [RandomController::class, 'anime'])->name('v4.random.anime');

        Route::get('/manga', [RandomController::class, 'manga'])->name('v4.random.manga');

        Route::get('/characters', [RandomController::class, 'characters'])->name('v4.random.characters');

        Route::get('/people', [RandomController::class, 'people'])->name('v4.random.people');

        Route::get('/users', [RandomController::class, 'users'])->name('v4.random.users');
    }
);
