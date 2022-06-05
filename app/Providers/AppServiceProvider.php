<?php

namespace App\Providers;

use App\Http\QueryBuilder\AnimeSearchQueryBuilder;
use App\Http\QueryBuilder\MangaSearchQueryBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(AnimeSearchQueryBuilder::class, function($app) {
            $searchIndexesEnabled = $app["config"]->get("scout.driver") != "null";
            return new AnimeSearchQueryBuilder($searchIndexesEnabled);
        });

        $this->app->singleton(MangaSearchQueryBuilder::class, function($app) {
            $searchIndexesEnabled = $app["config"]->get("scout.driver") != "null";
            return new MangaSearchQueryBuilder($searchIndexesEnabled);
        });

        $this->app->tag([AnimeSearchQueryBuilder::class, MangaSearchQueryBuilder::class], "searchQueryBuilders");

        $this->app->singleton(SearchQueryBuilderProvider::class, function($app) {
            return new SearchQueryBuilderProvider($app->tagged("searchQueryBuilders"));
        });
    }
}
