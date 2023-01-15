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
use App\Dto\QueryTopPeopleCommand;
use App\Features\AnimeEpisodeLookupHandler;
use App\Features\AnimeEpisodesLookupHandler;
use App\Features\AnimeExternalLookupHandler;
use App\Features\AnimeForumLookupHandler;
use App\Features\AnimeGenreListHandler;
use App\Features\AnimeMoreInfoLookupHandler;
use App\Features\AnimeNewsLookupHandler;
use App\Features\AnimePicturesLookupHandler;
use App\Features\AnimeRecommendationsLookupHandler;
use App\Features\AnimeRelationsLookupHandler;
use App\Features\AnimeReviewsLookupHandler;
use App\Features\AnimeSearchHandler;
use App\Features\AnimeCharactersLookupHandler;
use App\Features\AnimeStaffLookupHandler;
use App\Features\AnimeStatsLookupHandler;
use App\Features\AnimeStreamingLookupHandler;
use App\Features\AnimeThemesLookupHandler;
use App\Features\AnimeVideosEpisodesLookupHandler;
use App\Features\AnimeVideosLookupHandler;
use App\Features\CharacterSearchHandler;
use App\Features\ClubSearchHandler;
use App\Features\MagazineSearchHandler;
use App\Features\MangaGenreListHandler;
use App\Features\MangaSearchHandler;
use App\Features\PeopleSearchHandler;
use App\Features\ProducerSearchHandler;
use App\Features\QueryAnimeHandler;
use App\Features\QueryFullAnimeHandler;
use App\Features\QueryTopAnimeItemsHandler;
use App\Features\QueryTopCharactersHandler;
use App\Features\QueryTopMangaItemsHandler;
use App\Features\QueryTopReviewsHandler;
use App\Features\UserByIdLookupHandler;
use App\Features\UserSearchHandler;
use App\GenreAnime;
use App\GenreManga;
use App\Http\QueryBuilder\AnimeSearchQueryBuilder;
use App\Http\QueryBuilder\CharacterSearchQueryBuilder;
use App\Http\QueryBuilder\ClubSearchQueryBuilder;
use App\Http\QueryBuilder\PeopleSearchQueryBuilder;
use App\Http\QueryBuilder\SimpleSearchQueryBuilder;
use App\Http\QueryBuilder\MangaSearchQueryBuilder;
use App\Http\QueryBuilder\TopAnimeQueryBuilder;
use App\Http\QueryBuilder\TopMangaQueryBuilder;
use App\Macros\To2dArrayWithDottedKeys;
use App\Magazine;
use App\Mixins\ScoutBuilderMixin;
use App\Producers;
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
use App\Services\ScoutBuilderPaginatorService;
use App\Services\ScoutSearchService;
use App\Services\SearchEngineSearchService;
use App\Services\TypeSenseScoutSearchService;
use App\Support\DefaultMediator;
use App\Support\JikanUnitOfWork;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Laravel\Scout\Builder as ScoutBuilder;
use Typesense\LaravelTypesense\Typesense;

class AppServiceProvider extends ServiceProvider
{
    private \ReflectionClass $simpleSearchQueryBuilderClassReflection;

    /**
     * @throws \ReflectionException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerMacros();
        $this->simpleSearchQueryBuilderClassReflection = new \ReflectionClass(SimpleSearchQueryBuilder::class);
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
//        $this->app->singleton(ScoutSearchService::class, function($app) {
//            $scoutDriver = $this->getSearchIndexDriver($app);
//
//            return match ($scoutDriver) {
//                "typesense" => new TypeSenseScoutSearchService(),
//                "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine" => new ElasticScoutSearchService(),
//                default => new DefaultScoutSearchService()
//            };
//        });

        // todo: remove SearchQueryBuilders

        $queryBuilders = [
            AnimeSearchQueryBuilder::class,
            MangaSearchQueryBuilder::class,
            ClubSearchQueryBuilder::class,
            CharacterSearchQueryBuilder::class,
            PeopleSearchQueryBuilder::class,
            TopAnimeQueryBuilder::class,
            TopMangaQueryBuilder::class
        ];

        foreach($queryBuilders as $queryBuilderClass) {
            $this->app->singleton($queryBuilderClass,
                $this->getQueryBuilderFactory($queryBuilderClass)
            );
        }

        $simpleQueryBuilderAbstracts = [];
        $simpleQueryBuilders = [
            [
                "name" => "GenreAnime",
                "identifier" => "genre_anime",
                "modelClass" => GenreAnime::class
            ],
            [
                "name" => "GenreManga",
                "identifier" => "genre_manga",
                "modelClass" => GenreManga::class
            ],
            [
                "name" => "Producers",
                "identifier" => "producers",
                "modelClass" => Producers::class,
                "orderByFields" => ["mal_id", "count", "favorites", "established"]
            ],
            [
                "name" => "Magazine",
                "identifier" => "magazine",
                "modelClass" => Magazine::class
            ]
        ];

        foreach ($simpleQueryBuilders as $simpleQueryBuilder) {
            $abstractName = SimpleSearchQueryBuilder::class . $simpleQueryBuilder["name"];
            $simpleQueryBuilderAbstracts[] = $abstractName;
            $this->app->singleton($abstractName, function($app) use($simpleQueryBuilder) {
                $searchIndexesEnabled = $this->getSearchIndexesEnabledConfig($app);

                $ctorArgs = [
                    $simpleQueryBuilder["identifier"],
                    $simpleQueryBuilder["modelClass"],
                    $searchIndexesEnabled,
                    $app->make(ScoutSearchService::class)
                ];
                if (array_key_exists("orderByFields", $simpleQueryBuilder)) {
                    $ctorArgs[] = $simpleQueryBuilder["orderByFields"];
                }
                return $this->simpleSearchQueryBuilderClassReflection->newInstanceArgs($ctorArgs);
            });
        }

        $this->app->tag(array_merge($queryBuilders, $simpleQueryBuilderAbstracts), "searchQueryBuilders");

        $this->app->singleton(SearchQueryBuilderProvider::class, function($app) {
            return new SearchQueryBuilderProvider($app->tagged("searchQueryBuilders"));
        });
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
        $this->app->singleton(CachedScraperService::class, DefaultCachedScraperService::class);
    }

    private function registerRequestHandlers()
    {
        /*
         * This bit is about a "mediator" pattern for handling requests.
         */
        $this->app->singleton(Mediator::class, DefaultMediator::class);
        /*
         * Each request is represented as a data transfer object, and spatie/laravel-data package's service provider
         * registers them in the ioc container. For each request there is a request handler.
         * Validation for requests is specified in the DTOs.
         * Querying/Filtering entirely happens on the model side.
         * The lines below explicitly define the mapping between request handlers and repositories.
         * Repositories are just a bit of abstraction over models.
         */
        $this->app->when(DefaultMediator::class)
            ->needs(RequestHandler::class)
            ->give(function (Application $app) {
                $searchIndexesEnabled = $this->getSearchIndexesEnabledConfig($app);
                /**
                 * @var UnitOfWork $unitOfWorkInstance
                 */
                $unitOfWorkInstance = $app->make(UnitOfWork::class);
                $searchRequestHandlersDescriptors = [
                    AnimeSearchHandler::class => $unitOfWorkInstance->anime(),
                    MangaSearchHandler::class => $unitOfWorkInstance->manga(),
                    CharacterSearchHandler::class => $unitOfWorkInstance->characters(),
                    PeopleSearchHandler::class => $unitOfWorkInstance->people(),
                    ClubSearchHandler::class => $unitOfWorkInstance->clubs(),
                    MagazineSearchHandler::class => $unitOfWorkInstance->magazines(),
                    ProducerSearchHandler::class => $unitOfWorkInstance->producers(),
                ];
                $requestHandlers = [];
                foreach ($searchRequestHandlersDescriptors as $handlerClass => $repositoryInstance) {
                    $requestHandlers[] = $app->make($handlerClass, [
                        $app->make(DefaultQueryBuilderService::class, [
                            static::makeSearchService($app, $searchIndexesEnabled, $repositoryInstance),
                            $app->make($searchIndexesEnabled ? ScoutBuilderPaginatorService::class : EloquentBuilderPaginatorService::class)
                        ])
                    ]);
                }

                $queryTopItemsDescriptors = [
                    QueryTopAnimeItemsHandler::class => $unitOfWorkInstance->anime(),
                    QueryTopMangaItemsHandler::class => $unitOfWorkInstance->manga(),
                    QueryTopCharactersHandler::class => $unitOfWorkInstance->characters(),
                    QueryTopPeopleCommand::class => $unitOfWorkInstance->people(),
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
                    AnimeGenreListHandler::class => $unitOfWorkInstance->animeGenres(),
                    MangaGenreListHandler::class => $unitOfWorkInstance->mangaGenres(),
                ];

                foreach ($requestHandlersWithOnlyRepositoryDependency as $handlerClass => $repositoryInstance) {
                    $requestHandlers[] = $app->make($handlerClass, [$repositoryInstance]);
                }

                // request handlers which are fetching data through the jikan library from MAL, and caching the result.
                $requestHandlersWithScraperService = [
                    QueryFullAnimeHandler::class => $unitOfWorkInstance->anime(),
                    QueryAnimeHandler::class => $unitOfWorkInstance->anime(),
                    UserSearchHandler::class => $unitOfWorkInstance->documents("common"),
                    QueryTopReviewsHandler::class => $unitOfWorkInstance->documents("common"),
                    UserByIdLookupHandler::class => $unitOfWorkInstance->documents("common"),
                    AnimeCharactersLookupHandler::class => $unitOfWorkInstance->documents("anime_characters_staff"),
                    AnimeStaffLookupHandler::class => $unitOfWorkInstance->documents("anime_characters_staff"),
                    AnimeEpisodesLookupHandler::class => $unitOfWorkInstance->documents("anime_episodes"),
                    AnimeEpisodeLookupHandler::class => $unitOfWorkInstance->documents("anime_episode"),
                    AnimeNewsLookupHandler::class => $unitOfWorkInstance->documents("anime_news"),
                    AnimeForumLookupHandler::class => $unitOfWorkInstance->documents("anime_forum"),
                    AnimeVideosLookupHandler::class => $unitOfWorkInstance->documents("anime_videos"),
                    AnimeVideosEpisodesLookupHandler::class => $unitOfWorkInstance->documents("anime_videos_episodes"),
                    AnimePicturesLookupHandler::class => $unitOfWorkInstance->documents("anime_pictures"),
                    AnimeStatsLookupHandler::class => $unitOfWorkInstance->documents("anime_stats"),
                    AnimeMoreInfoLookupHandler::class => $unitOfWorkInstance->documents("anime_moreinfo"),
                    AnimeRecommendationsLookupHandler::class => $unitOfWorkInstance->documents("anime_recommendations"),
                    AnimeReviewsLookupHandler::class => $unitOfWorkInstance->documents("anime_reviews"),
                    AnimeRelationsLookupHandler::class => $unitOfWorkInstance->anime(),
                    AnimeExternalLookupHandler::class => $unitOfWorkInstance->anime(),
                    AnimeStreamingLookupHandler::class => $unitOfWorkInstance->anime(),
                    AnimeThemesLookupHandler::class => $unitOfWorkInstance->anime(),
                ];

                foreach ($requestHandlersWithScraperService as $handlerClass => $repositoryInstance) {
                    $jikan = $app->make(MalClient::class);
                    $serializer = $app->make("SerializerV4");
                    $requestHandlers[] = $app->make($handlerClass, [
                        $app->make(DefaultCachedScraperService::class,
                            [$repositoryInstance, $jikan, $serializer])
                    ]);
                }

                $requestHandlersWithNoDependencies = [
                ];

                foreach ($requestHandlersWithNoDependencies as $handlerClass) {
                    $requestHandlers[] = $this->app->make($handlerClass);
                }

                return $requestHandlers;
            });
    }

    /**
     * Creates a search service instance.
     * Search service knows how to do a full-text search on the database query builder instance.
     * @throws BindingResolutionException
     */
    private static function makeSearchService(Application $app, bool $searchIndexesEnabled, Repository $repositoryInstance)
    {
        return $searchIndexesEnabled ? $app->make( SearchEngineSearchService::class, [
            static::makeScoutSearchService($app, $repositoryInstance), $repositoryInstance
        ]) : $app->make(MongoSearchService::class, [$repositoryInstance]);
    }

    /**
     * Creates a scout search service instance.
     * Scout search service knows about the configured search engine's implementation details.
     * E.g. per search request configuration.
     * @throws BindingResolutionException
     */
    private static function makeScoutSearchService(Application $app, Repository $repositoryInstance)
    {
        // todo: cache result
        $scoutDriver = static::getSearchIndexDriver($app);
        $serviceClass = match ($scoutDriver) {
            "typesense" => TypeSenseScoutSearchService::class,
            "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine" => ElasticScoutSearchService::class,
            default => DefaultScoutSearchService::class
        };

        return $app->make($serviceClass, [$repositoryInstance]);
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

        ScoutBuilder::mixin(new ScoutBuilderMixin());
    }

    private function collectionMacros(): array
    {
        return [
            "to2dArrayWithDottedKeys" => To2dArrayWithDottedKeys::class
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
        $services = [
            ScoutSearchService::class,
            AnimeSearchQueryBuilder::class,
            MangaSearchQueryBuilder::class,
            ClubSearchQueryBuilder::class,
            CharacterSearchQueryBuilder::class,
            PeopleSearchQueryBuilder::class,
            TopAnimeQueryBuilder::class,
            TopMangaQueryBuilder::class
        ];

        if (env("SCOUT_DRIVER") === "typesense") {
            $services[] = Typesense::class;
        }

        if (env("SCOUT_DRIVER") === "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine") {
            $services[] = \Elastic\Elasticsearch\Client::class;
        }

        return $services;
    }
}
