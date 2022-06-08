<?php

namespace App\Providers;

use App\Http\QueryBuilder\AnimeSearchQueryBuilder;
use App\Http\QueryBuilder\ClubSearchQueryBuilder;
use App\Http\QueryBuilder\MangaSearchQueryBuilder;
use App\Macros\To2dArrayWithDottedKeys;
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
        $this->app->singleton(AnimeSearchQueryBuilder::class,
            $this->getQueryBuilderFactory(AnimeSearchQueryBuilder::class)
        );

        $this->app->singleton(MangaSearchQueryBuilder::class,
            $this->getQueryBuilderFactory(MangaSearchQueryBuilder::class)
        );

        $this->app->singleton(ClubSearchQueryBuilder::class,
            $this->getQueryBuilderFactory(ClubSearchQueryBuilder::class)
        );

        $this->app->tag([
            AnimeSearchQueryBuilder::class,
            MangaSearchQueryBuilder::class,
            ClubSearchQueryBuilder::class
        ], "searchQueryBuilders");

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
            $searchIndexesEnabled = $app["config"]->get("scout.driver") != "null";
            return new $queryBuilderClass($searchIndexesEnabled);
        };
    }
}
