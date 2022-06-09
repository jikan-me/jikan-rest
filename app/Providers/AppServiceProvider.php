<?php

namespace App\Providers;

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
use App\Producer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

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
                "name" => "Producer",
                "identifier" => "producer",
                "modelClass" => Producer::class
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
                return new SimpleSearchQueryBuilder(
                    $simpleQueryBuilder["identifier"],
                    $simpleQueryBuilder["modelClass"],
                    $searchIndexesEnabled
                );
            });
        }

        $this->app->tag(array_merge($queryBuilders, $simpleQueryBuilderAbstracts), "searchQueryBuilders");

        $this->app->singleton(SearchQueryBuilderProvider::class, function($app) {
            return new SearchQueryBuilderProvider($app->tagged("searchQueryBuilders"));
        });
    }

    /**
     * @throws \ReflectionException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @return void
     */
    private function registerMacros(): void
    {
        Collection::make($this->collectionMacros())
            ->reject(fn ($class, $macro) => Collection::hasMacro($macro))
            ->each(fn ($class, $macro) => Collection::macro($macro, app($class)()));
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
            return new $queryBuilderClass($searchIndexesEnabled);
        };
    }

    private function getSearchIndexesEnabledConfig($app): bool
    {
        return $app["config"]->get("scout.driver") != "null";
    }
}
