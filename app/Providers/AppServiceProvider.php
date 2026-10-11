<?php

namespace App\Providers;

use App\Contracts\AnimeRepository;
use App\Contracts\CachedScraperService;
use App\Contracts\CharacterRepository;
use App\Contracts\ClubRepository;
use App\Contracts\MagazineRepository;
use App\Contracts\MangaRepository;
use App\Contracts\Mediator;
use App\Contracts\PeopleRepository;
use App\Contracts\ProducerRepository;
use App\Contracts\Repository;
use App\Contracts\RequestHandler;
use App\Contracts\UnitOfWork;
use App\Contracts\UserRepository;
use App\Macros\CollectionOffsetGetFirst;
use App\Macros\ResponseJikanCacheFlags;
use App\Macros\To2dArrayWithDottedKeys;
use App\Mixins\ScoutBuilderMixin;
use App\Repositories\AnimeGenresRepository;
use App\Repositories\DefaultAnimeRepository;
use App\Repositories\DefaultCharacterRepository;
use App\Repositories\DefaultClubRepository;
use App\Repositories\DefaultMagazineRepository;
use App\Repositories\DefaultMangaRepository;
use App\Repositories\DefaultPeopleRepository;
use App\Repositories\DefaultProducerRepository;
use App\Repositories\DefaultUserRepository;
use App\Repositories\MangaGenresRepository;
use App\Services\DefaultBuilderPaginatorService;
use App\Services\DefaultCachedScraperService;
use App\Services\DefaultPrivateFieldMapperService;
use App\Services\DefaultQueryBuilderService;
use App\Services\DefaultScoutSearchService;
use App\Services\ElasticScoutSearchService;
use App\Services\EloquentBuilderPaginatorService;
use App\Services\MongoSearchService;
use App\Services\PrivateFieldMapperService;
use App\Services\QueryBuilderPaginatorService;
use App\Services\ScoutBuilderPaginatorService;
use App\Services\ScoutSearchService;
use App\Services\SearchEngineSearchService;
use App\Services\SearchService;
use App\Services\TypeSenseScoutSearchService;
use App\Support\CacheOptions;
use App\Support\DefaultMediator;
use App\Support\JikanConfig;
use App\Support\JikanUnitOfWork;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Jikan\MyAnimeList\MalClient;
use Laravel\Scout\Builder as ScoutBuilder;
use App\Features;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @throws \ReflectionException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerMacros();
        // the registration of request handlers should happen after the load of all service providers.
        $this->registerRequestHandlers();

        // Application-level rate limit for the public v4 API (P0: no throttling existed).
        RateLimiter::for('v4', function () {
            $perMinute = $this->app->environment('testing')
                ? 10_000
                : (int) (config('jikan.rate_limit_per_minute') ?: 240);

            return Limit::perMinute($perMinute)
                ->by((string) (request()->ip() ?? 'local'));
        });
    }

    /**
     * Register any application services.
     *
     * @throws \ReflectionException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @return void
     */
    public function register(): void
    {
        // Custom exception handler (JSON rendering lives in App\Exceptions\Handler).
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );

        // Scraper client + parser + serializer, previously bound in bootstrap/app.php.
        $httpClient = \Symfony\Component\HttpClient\HttpClient::create([
            'timeout' => (float) config('jikan.source_timeout'),
        ]);
        $this->app->instance('HttpClient', $httpClient);
        $this->app->instance('JikanParser', new MalClient($httpClient));
        $this->app->instance('SerializerV4', SerializerFactory::createV4());

        $this->app->singleton(JikanConfig::class, fn() => new JikanConfig(config("jikan")));
        $this->app->singleton(\App\Contracts\SearchAnalyticsService::class,
            ! $this->app->environment('testing') ? \App\Services\DefaultSearchAnalyticsService::class :
                \App\Services\DummySearchAnalyticsService::class);
        $this->app->alias(JikanConfig::class, "jikan-config");
        // cache options class is used to share the request scope level cache settings
        $this->app->singleton(CacheOptions::class);
        $this->app->singleton(CachedScraperService::class, DefaultCachedScraperService::class);
        $this->app->singleton(PrivateFieldMapperService::class, DefaultPrivateFieldMapperService::class);
        $this->app->bind(QueryBuilderPaginatorService::class, DefaultBuilderPaginatorService::class);
        if (static::getSearchIndexDriver($this->app) === "typesense") {
            $this->app->singleton(
                \Typesense\Client::class,
                fn () => new \Typesense\Client(config('scout.typesense.client-settings'))
            );
            $this->app->singleton(\App\Services\TypesenseCollectionDescriptor::class);
        }
        $this->registerModelRepositories();
    }

    private function getSearchService(Repository $repository): SearchService
    {
        if ($this->getSearchIndexesEnabledConfig($this->app)) {
            $scoutDriver = static::getSearchIndexDriver($this->app);
            $serviceClass = match ($scoutDriver) {
                "typesense" => TypeSenseScoutSearchService::class,
                // experimental
                "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine" => ElasticScoutSearchService::class,
                default => DefaultScoutSearchService::class
            };

            $scoutSearchService = $this->app->make($serviceClass, [
                "repository" => $repository,
            ]);
            $result = new SearchEngineSearchService($scoutSearchService, $repository);
        }
        else {
            $result = new MongoSearchService($repository);
        }

        return $result;
    }

    private function registerModelRepositories()
    {
        // note: We deliberately not included here any of the GenreRepository implementations.
        //       We don't want to bind them to an abstract symbol.
        $repositories = [
            AnimeRepository::class => DefaultAnimeRepository::class,
            MangaRepository::class => DefaultMangaRepository::class,
            CharacterRepository::class => DefaultCharacterRepository::class,
            ClubRepository::class => DefaultClubRepository::class,
            MagazineRepository::class => DefaultMagazineRepository::class,
            ProducerRepository::class => DefaultProducerRepository::class,
            PeopleRepository::class => DefaultPeopleRepository::class,
            UserRepository::class => DefaultUserRepository::class,
        ];

        foreach ($repositories as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }

        $this->app->singleton(AnimeGenresRepository::class);
        $this->app->singleton(MangaGenresRepository::class);

        $this->app->singleton(UnitOfWork::class, JikanUnitOfWork::class);
    }

    private function registerRequestHandlers()
    {
        /*
         * This bit is about a "mediator" pattern for handling requests.
         * Handlers are resolved lazily: we build a map of
         *   request (DTO) class => factory closure
         * once per worker process and inject it into the mediator. A single
         * request only ever constructs the handler it dispatches, instead of
         * all ~65 handler graphs per request.
         */
        $this->app->bind(Mediator::class, DefaultMediator::class);

        $this->app->singleton(
            'jikan-mediator-handler-factories',
            fn (Application $app) => $this->buildRequestHandlerFactories($app)
        );

        $this->app->when(DefaultMediator::class)
            ->needs('$handlerFactories')
            ->give(fn () => $this->app->make('jikan-mediator-handler-factories'));
    }

    /**
     * Build the request DTO class => handler factory map.
     *
     * Each factory builds a *fresh* handler on every call (handlers hold
     * request-scoped state), so it must be executed per request, not shared.
     *
     * @return array<string, \Closure>
     */
    private function buildRequestHandlerFactories(Application $app): array
    {
        $unitOfWorkInstance = $app->make(UnitOfWork::class);
        $factories = [];

        $register = function (string $handlerClass, callable $argsBuilder) use (&$factories, $app) {
            // requestClass() is an instance method, so peek at one throwaway
            // instance to learn the DTO this handler dispatches, then keep only
            // a lazy factory for real (per-request) construction.
            $requestClass = $app->make($handlerClass, $argsBuilder())->requestClass();
            $factories[$requestClass] = fn () => $app->make($handlerClass, $argsBuilder());
        };

        $searchRequestHandlersDescriptors = [
            Features\AnimeSearchHandler::class => $unitOfWorkInstance->anime(),
            Features\MangaSearchHandler::class => $unitOfWorkInstance->manga(),
            Features\CharacterSearchHandler::class => $unitOfWorkInstance->characters(),
            Features\PeopleSearchHandler::class => $unitOfWorkInstance->people(),
            Features\ClubSearchHandler::class => $unitOfWorkInstance->clubs(),
            Features\MagazineSearchHandler::class => $unitOfWorkInstance->magazines(),
            Features\ProducerSearchHandler::class => $unitOfWorkInstance->producers(),
        ];

        foreach ($searchRequestHandlersDescriptors as $handlerClass => $repositoryInstance) {
            $register($handlerClass, fn () => [
                "queryBuilderService" => $app->make(DefaultQueryBuilderService::class, [
                    "searchService" => $this->getSearchService($repositoryInstance)
                ]),
            ]);
        }

        $queryTopItemsDescriptors = [
            Features\QueryTopAnimeItemsHandler::class => $unitOfWorkInstance->anime(),
            Features\QueryTopMangaItemsHandler::class => $unitOfWorkInstance->manga(),
            Features\QueryTopCharactersHandler::class => $unitOfWorkInstance->characters(),
            Features\QueryTopPeopleHandler::class => $unitOfWorkInstance->people(),
        ];

        foreach ($queryTopItemsDescriptors as $handlerClass => $repositoryInstance) {
            $register($handlerClass, fn () => [
                $repositoryInstance,
                // top queries don't use the search engine, so it's enough for them to use eloquent paginator
                $app->make(EloquentBuilderPaginatorService::class),
            ]);
        }

        // request handlers which only depend on a repository instance
        $requestHandlersWithOnlyRepositoryDependency = [
            Features\AnimeGenreListHandler::class => $unitOfWorkInstance->animeGenres(),
            Features\MangaGenreListHandler::class => $unitOfWorkInstance->mangaGenres(),
        ];

        foreach ($requestHandlersWithOnlyRepositoryDependency as $handlerClass => $repositoryInstance) {
            $register($handlerClass, fn () => ["repository" => $repositoryInstance]);
        }

        // request handlers which are fetching data through the jikan library from MAL, and caching the result.
        $requestHandlersWithScraperService = [
            Features\AnimeFullLookupHandler::class => $unitOfWorkInstance->anime(),
            Features\AnimeLookupHandler::class => $unitOfWorkInstance->anime(),
            Features\UserSearchHandler::class => $unitOfWorkInstance->documents("common"),
            Features\QueryTopReviewsHandler::class => $unitOfWorkInstance->documents("common"),
            Features\UserByIdLookupHandler::class => $unitOfWorkInstance->documents("common"),
            Features\AnimeCharactersLookupHandler::class => $unitOfWorkInstance->documents("anime_characters_staff"),
            Features\AnimeStaffLookupHandler::class => $unitOfWorkInstance->documents("anime_characters_staff"),
            Features\AnimeEpisodesLookupHandler::class => $unitOfWorkInstance->documents("anime_episodes"),
            Features\AnimeEpisodeLookupHandler::class => $unitOfWorkInstance->documents("anime_episode"),
            Features\AnimeNewsLookupHandler::class => $unitOfWorkInstance->documents("anime_news"),
            Features\AnimeForumLookupHandler::class => $unitOfWorkInstance->documents("anime_forum"),
            Features\AnimeVideosLookupHandler::class => $unitOfWorkInstance->documents("anime_videos"),
            Features\AnimeVideosEpisodesLookupHandler::class => $unitOfWorkInstance->documents("anime_videos_episodes"),
            Features\AnimePicturesLookupHandler::class => $unitOfWorkInstance->documents("anime_pictures"),
            Features\AnimeStatsLookupHandler::class => $unitOfWorkInstance->documents("anime_stats"),
            Features\AnimeMoreInfoLookupHandler::class => $unitOfWorkInstance->documents("anime_moreinfo"),
            Features\AnimeRecommendationsLookupHandler::class => $unitOfWorkInstance->documents("anime_recommendations"),
            Features\AnimeReviewsLookupHandler::class => $unitOfWorkInstance->documents("anime_reviews"),
            Features\AnimeRelationsLookupHandler::class => $unitOfWorkInstance->anime(),
            Features\AnimeExternalLookupHandler::class => $unitOfWorkInstance->anime(),
            Features\AnimeStreamingLookupHandler::class => $unitOfWorkInstance->anime(),
            Features\AnimeThemesLookupHandler::class => $unitOfWorkInstance->anime(),
            Features\AnimeUserUpdatesLookupHandler::class => $unitOfWorkInstance->documents("anime_userupdates"),
            Features\CharacterLookupHandler::class => $unitOfWorkInstance->characters(),
            Features\CharacterFullLookupHandler::class => $unitOfWorkInstance->characters(),
            Features\CharacterAnimeLookupHandler::class => $unitOfWorkInstance->characters(),
            Features\CharacterMangaLookupHandler::class => $unitOfWorkInstance->characters(),
            Features\CharacterVoicesLookupHandler::class => $unitOfWorkInstance->characters(),
            Features\CharacterPicturesLookupHandler::class => $unitOfWorkInstance->documents("characters_pictures"),
            Features\ClubLookupHandler::class => $unitOfWorkInstance->clubs(),
            Features\ClubMembersLookupHandler::class => $unitOfWorkInstance->documents("clubs_members"),
            Features\ClubStaffLookupHandler::class => $unitOfWorkInstance->clubs(),
            Features\ClubRelationsLookupHandler::class => $unitOfWorkInstance->clubs(),
            Features\MangaCharactersLookupHandler::class => $unitOfWorkInstance->documents("manga_characters"),
            Features\MangaNewsLookupHandler::class => $unitOfWorkInstance->documents("manga_news"),
            Features\MangaForumLookupHandler::class => $unitOfWorkInstance->documents("manga_forum"),
            Features\MangaPicturesLookupHandler::class => $unitOfWorkInstance->documents("manga_pictures"),
            Features\MangaStatsLookupHandler::class => $unitOfWorkInstance->documents("manga_stats"),
            Features\MangaMoreInfoLookupHandler::class => $unitOfWorkInstance->documents("manga_moreinfo"),
            Features\MangaRecommendationsLookupHandler::class => $unitOfWorkInstance->documents("manga_recommendations"),
            Features\MangaUserUpdatesLookupHandler::class => $unitOfWorkInstance->documents("manga_userupdates"),
            Features\MangaReviewsLookupHandler::class => $unitOfWorkInstance->documents("manga_reviews"),
            Features\MangaLookupHandler::class => $unitOfWorkInstance->manga(),
            Features\MangaFullLookupHandler::class => $unitOfWorkInstance->manga(),
            Features\MangaRelationsLookupHandler::class => $unitOfWorkInstance->manga(),
            Features\MangaExternalLookupHandler::class => $unitOfWorkInstance->manga(),
            Features\PersonLookupHandler::class => $unitOfWorkInstance->people(),
            Features\PersonAnimeLookupHandler::class => $unitOfWorkInstance->people(),
            Features\PersonFullLookupHandler::class => $unitOfWorkInstance->people(),
            Features\PersonMangaLookupHandler::class => $unitOfWorkInstance->people(),
            Features\PersonVoicesLookupHandler::class => $unitOfWorkInstance->people(),
            Features\PersonPicturesLookupHandler::class => $unitOfWorkInstance->documents("people_pictures"),
            Features\ProducerLookupHandler::class => $unitOfWorkInstance->producers(),
            Features\ProducerFullLookupHandler::class => $unitOfWorkInstance->producers(),
            Features\ProducerExternalLookupHandler::class => $unitOfWorkInstance->producers(),
            Features\QueryAnimeRecommendationsHandler::class => $unitOfWorkInstance->documents("recommendations"),
            Features\QueryMangaRecommendationsHandler::class => $unitOfWorkInstance->documents("recommendations"),
            Features\QueryAnimeReviewsHandler::class => $unitOfWorkInstance->documents("reviews"),
            Features\QueryMangaReviewsHandler::class => $unitOfWorkInstance->documents("reviews"),
            Features\QueryAnimeSeasonListHandler::class => $unitOfWorkInstance->documents("season_archive"),
            Features\UserFullLookupHandler::class => $unitOfWorkInstance->users(),
            Features\UserProfileLookupHandler::class => $unitOfWorkInstance->users(),
            Features\UserStatisticsLookupHandler::class => $unitOfWorkInstance->users(),
            Features\UserFavoritesLookupHandler::class => $unitOfWorkInstance->users(),
            Features\UserUpdatesLookupHandler::class => $unitOfWorkInstance->users(),
            Features\UserAboutLookupHandler::class => $unitOfWorkInstance->users(),
            Features\UserHistoryLookupHandler::class => $unitOfWorkInstance->documents("users_history"),
            Features\UserFriendsLookupHandler::class => $unitOfWorkInstance->documents("users_friends"),
            Features\UserReviewsLookupHandler::class => $unitOfWorkInstance->documents("users_reviews"),
            Features\UserRecommendationsLookupHandler::class => $unitOfWorkInstance->documents("users_recommendations"),
            Features\UserClubsLookupHandler::class => $unitOfWorkInstance->documents("users_clubs"),
            Features\UserExternalLookupHandler::class => $unitOfWorkInstance->users(),
            Features\QueryRecentlyOnlineUsersHandler::class => $unitOfWorkInstance->documents("users_recently_online"),
            Features\QueryRecentlyAddedEpisodesHandler::class => $unitOfWorkInstance->documents("watch"),
            Features\QueryPopularEpisodesHandler::class => $unitOfWorkInstance->documents("watch"),
            Features\QueryRecentlyAddedPromoVideosHandler::class => $unitOfWorkInstance->documents("watch"),
            Features\QueryPopularPromoVideosHandler::class => $unitOfWorkInstance->documents("watch"),
            Features\QueryAnimeListOfUserHandler::class => $unitOfWorkInstance->documents("users_animelist"),
            Features\QueryMangaListOfUserHandler::class => $unitOfWorkInstance->documents("users_mangalist")
        ];

        foreach ($requestHandlersWithScraperService as $handlerClass => $repositoryInstance) {
            $register($handlerClass, fn () => [
                "scraperService" => $app->make(DefaultCachedScraperService::class, [
                    "repository" => $repositoryInstance,
                    "jikan" => $app->get("JikanParser"),
                    "serializer" => $app->get("SerializerV4"),
                ]),
            ]);
        }

        // automatically resolvable dependencies or no dependencies at all
        $requestHandlersWithNoDependencies = [
            Features\QueryRandomAnimeHandler::class,
            Features\QueryRandomMangaHandler::class,
            Features\QueryRandomCharacterHandler::class,
            Features\QueryRandomPersonHandler::class,
            Features\QueryRandomUserHandler::class,
            Features\QueryAnimeSchedulesHandler::class,
            Features\QueryCurrentAnimeSeasonHandler::class,
            Features\QuerySpecificAnimeSeasonHandler::class,
            Features\QueryUpcomingAnimeSeasonHandler::class
        ];

        foreach ($requestHandlersWithNoDependencies as $handlerClass) {
            $register($handlerClass, fn () => []);
        }

        return $factories;
    }

    private function registerMacros(): void
    {
        Collection::make($this->collectionMacros())
            ->reject(fn ($class, $macro) => Collection::hasMacro($macro))
            ->each(fn ($class, $macro) => Collection::macro($macro, app($class)()));

        Response::macro("addJikanCacheFlags", app(ResponseJikanCacheFlags::class)());
        JsonResponse::macro("addJikanCacheFlags", app(ResponseJikanCacheFlags::class)());

        ScoutBuilder::mixin(new ScoutBuilderMixin());
    }

    private function collectionMacros(): array
    {
        return [
            "to2dArrayWithDottedKeys" => To2dArrayWithDottedKeys::class,
            "offsetGetFirst" => CollectionOffsetGetFirst::class
        ];
    }

    private function getSearchIndexesEnabledConfig($app): bool
    {
        return $this->getSearchIndexDriver($app) != "null";
    }

    private static function getSearchIndexDriver($app): string
    {
        return $app["config"]->get("scout.driver");
    }
}
