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
use App\Http\Middleware\EndpointCacheTtlMiddleware;
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
use App\Services\DefaultCachedScraperService;
use App\Services\DefaultQueryBuilderService;
use App\Services\DefaultScoutSearchService;
use App\Services\ElasticScoutSearchService;
use App\Services\EloquentBuilderPaginatorService;
use App\Services\MongoSearchService;
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
use Laravel\Lumen\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Env;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Laravel\Scout\Builder as ScoutBuilder;
use Typesense\LaravelTypesense\Typesense;
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
        $this->app->singleton(JikanConfig::class, fn() => new JikanConfig(config("jikan")));
        // cache options class is used to share the request scope level cache settings
        $this->app->singleton(CacheOptions::class);
        $this->app->singleton(CachedScraperService::class, DefaultCachedScraperService::class);
        if ($this->getSearchIndexesEnabledConfig($this->app)) {
            $this->app->bind(QueryBuilderPaginatorService::class, ScoutBuilderPaginatorService::class);
        } else {
            $this->app->bind(QueryBuilderPaginatorService::class, EloquentBuilderPaginatorService::class);
        }
        $this->registerModelRepositories();
        $this->registerRequestHandlers();
    }

    private function getSearchService(Repository $repository): SearchService
    {
        if ($this->getSearchIndexesEnabledConfig($this->app)) {
            $scoutDriver = static::getSearchIndexDriver($this->app);
            $serviceClass = match ($scoutDriver) {
                "typesense" => TypeSenseScoutSearchService::class,
                "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine" => ElasticScoutSearchService::class,
                default => DefaultScoutSearchService::class
            };

            $scoutSearchService = new $serviceClass($repository);
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
         */
        $this->app->singleton(Mediator::class, DefaultMediator::class);
        /*
         * Contextual binding for the mediator.
         * Each request is represented as a data transfer object, and spatie/laravel-data package's service provider
         * registers them in the ioc container. For each request there is a request handler.
         * Validation for requests is specified in the DTOs.
         * Querying/Filtering entirely happens on the model side.
         * The lines below explicitly define the mapping between request handlers and repositories.
         * Repositories are just a bit of abstraction over models. They are making unit testing easier.
         */
        $this->app->when(DefaultMediator::class)
            ->needs(RequestHandler::class)
            ->give(function (Application $app) {
                /**
                 * @var UnitOfWork $unitOfWorkInstance
                 */
                $unitOfWorkInstance = $app->make(UnitOfWork::class);
                $searchRequestHandlersDescriptors = [
                    Features\AnimeSearchHandler::class => $unitOfWorkInstance->anime(),
                    Features\MangaSearchHandler::class => $unitOfWorkInstance->manga(),
                    Features\CharacterSearchHandler::class => $unitOfWorkInstance->characters(),
                    Features\PeopleSearchHandler::class => $unitOfWorkInstance->people(),
                    Features\ClubSearchHandler::class => $unitOfWorkInstance->clubs(),
                    Features\MagazineSearchHandler::class => $unitOfWorkInstance->magazines(),
                    Features\ProducerSearchHandler::class => $unitOfWorkInstance->producers(),
                ];
                $requestHandlers = [];
                foreach ($searchRequestHandlersDescriptors as $handlerClass => $repositoryInstance) {
                    $requestHandlers[] = $app->make($handlerClass, [
                        "queryBuilderService" => new DefaultQueryBuilderService(
                            $this->getSearchService($repositoryInstance),
                            $app->make(QueryBuilderPaginatorService::class)
                        )
                    ]);
                }

                $queryTopItemsDescriptors = [
                    Features\QueryTopAnimeItemsHandler::class => $unitOfWorkInstance->anime(),
                    Features\QueryTopMangaItemsHandler::class => $unitOfWorkInstance->manga(),
                    Features\QueryTopCharactersHandler::class => $unitOfWorkInstance->characters(),
                    Features\QueryTopPeopleHandler::class => $unitOfWorkInstance->people(),
                ];

                foreach ($queryTopItemsDescriptors as $handlerClass => $repositoryInstance) {
                    $requestHandlers[] = $app->make($handlerClass, [
                        $repositoryInstance,
                        // top queries don't use the search engine, so it's enough for them to use eloquent paginator
                        $app->make(EloquentBuilderPaginatorService::class)
                    ]);
                }

                // request handlers which only depend on a repository instance
                $requestHandlersWithOnlyRepositoryDependency = [
                    Features\AnimeGenreListHandler::class => $unitOfWorkInstance->animeGenres(),
                    Features\MangaGenreListHandler::class => $unitOfWorkInstance->mangaGenres(),
                ];

                foreach ($requestHandlersWithOnlyRepositoryDependency as $handlerClass => $repositoryInstance) {
                    $requestHandlers[] = $app->make($handlerClass, ["repository" => $repositoryInstance]);
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
                    Features\QueryRecentlyOnlineUsersHandler::class => $unitOfWorkInstance->documents("users_recently_online")
                ];

                foreach ($requestHandlersWithScraperService as $handlerClass => $repositoryInstance) {
                    $jikan = $app->make(MalClient::class);
                    $serializer = $app->make("SerializerV4");
                    $scraperService = $app->make(DefaultCachedScraperService::class,
                        ["repository" => $repositoryInstance, "jikan" => $jikan, "serializer" => $serializer]);
                    $requestHandlers[] = $app->make($handlerClass, [
                        "scraperService" => $scraperService
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
                    $requestHandlers[] = $this->app->make($handlerClass);
                }

                return $requestHandlers;
            });
    }

    /**
     * @throws \ReflectionException
     * @throws BindingResolutionException
     * @return void
     */
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

    private function getQueryBuilderFactory($queryBuilderClass): \Closure
    {
        return function($app) use($queryBuilderClass) {
            $searchIndexesEnabled = $this->getSearchIndexesEnabledConfig($app);
            return new $queryBuilderClass($searchIndexesEnabled, $app->make(ScoutSearchService::class));
        };
    }

    private function getSearchIndexesEnabledConfig($app): bool
    {
        return $this->getSearchIndexDriver($app) != "null";
    }

    private static function getSearchIndexDriver($app): string
    {
        return $app["config"]->get("scout.driver");
    }

    public static function servicesToWarm(): array
    {
        // todo: test again with roadrunner -- specific issue: typesense driver not loaded in time
        $services = [
            ScoutSearchService::class,
            UnitOfWork::class,
            CachedScraperService::class
        ];

        if (Env::get("SCOUT_DRIVER") === "typesense") {
            $services[] = Typesense::class;
        }

        if (Env::get("SCOUT_DRIVER") === "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine") {
            $services[] = \Elastic\Elasticsearch\Client::class;
        }

        if (Env::get("SCOUT_DRIVER") !== "none" && Env::get("SCOUT_DRIVER")) {
            $services[] = ScoutBuilderPaginatorService::class;
        } else {
            $services[] = EloquentBuilderPaginatorService::class;
        }

        return $services;
    }
}
