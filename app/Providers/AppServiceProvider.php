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
use App\Macros\CollectionOffsetGetFirst;
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
use Illuminate\Support\Env;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Laravel\Scout\Builder as ScoutBuilder;
use Typesense\LaravelTypesense\Typesense;
use App\Features;

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

        // new stuff
        $this->app->singleton(CachedScraperService::class, DefaultCachedScraperService::class);
        $this->registerModelRepositories();
        $this->registerRequestHandlers();
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
                $searchIndexesEnabled = $this->getSearchIndexesEnabledConfig($app);
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
                        $app->make(DefaultQueryBuilderService::class, [
                            static::makeSearchService($app, $searchIndexesEnabled, $repositoryInstance),
                            $app->make($searchIndexesEnabled ? ScoutBuilderPaginatorService::class : EloquentBuilderPaginatorService::class)
                        ])
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
                    $requestHandlers[] = $app->make($handlerClass, [$repositoryInstance]);
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
                    Features\MangaExternalLookupHandler::class => $unitOfWorkInstance->manga()
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

        if (Env::get("SCOUT_DRIVER") === "typesense") {
            $services[] = Typesense::class;
        }

        if (Env::get("SCOUT_DRIVER") === "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine") {
            $services[] = \Elastic\Elasticsearch\Client::class;
        }

        return $services;
    }
}
