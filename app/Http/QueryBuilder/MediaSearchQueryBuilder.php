<?php

namespace App\Http\QueryBuilder;

use Illuminate\Support\Collection;
use App\Http\QueryBuilder\Traits\StatusResolver;
use App\Http\QueryBuilder\Traits\TypeResolver;
use App\IsoDateFormatter;

abstract class MediaSearchQueryBuilder extends SearchQueryBuilder
{
    use IsoDateFormatter, StatusResolver, TypeResolver;

    private array $mediaParameterNames = ["score", "sfw", "genres", "genres_exclude", "min_score", "max_score",
        "start_date", "end_date", "status", "type"];

    const ORDER_BY = [
        'mal_id' => 'mal_id',
        'title' => 'title',
        'score' => 'score',
        'scored_by' => 'scored_by',
        'rank' => 'rank',
        'popularity' => 'popularity',
        'members' => 'members',
        'favorites' => 'favorites'
    ];

    protected function getParameterNames(): array
    {
        $parameterNames = parent::getParameterNames();
        return array_merge($parameterNames, $this->mediaParameterNames);
    }

    protected function sanitizeParameters(Collection $parameters): Collection
    {
        $parameters = parent::sanitizeParameters($parameters);

        if (!$parameters->offsetExists("score")) {
            $parameters["score"] = 0;
        }

        $parameters["status"] = $this->mapStatus($parameters->get("status"));
        $parameters["type"] = $this->mapType($parameters->get("type"));

        return $parameters;
    }

    private function filterByGenre(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, int $genre, $exclude = false): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $builder->where(function ($query) use ($genre, $exclude) {
            return $exclude ?
                $query
                    ->orWhere('genres.mal_id', $genre)
                    ->orWhere('demographics.mal_id', $genre)
                    ->orWhere('themes.mal_id', $genre)
                    ->orWhere('explicit_genres.mal_id', $genre)
                :
                $query
                    ->where('genres.mal_id', '!=', $genre)
                    ->where('demographics.mal_id', '!=', $genre)
                    ->where('themes.mal_id', '!=', $genre)
                    ->where('explicit_genres.mal_id', '!=', $genre);
        });
    }

    private function filterByGenres(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, string $genres, $exclude = false): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $genres = explode(',', $genres);
        foreach ($genres as $genre) {
            if (empty($genre)) {
                continue;
            }

            $genre = (int) $genre;

            $builder = $this->filterByGenre($builder, $genre, $exclude);
        }

        return $builder;
    }

    protected function buildQuery(Collection $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $builder = $results;
        $start_date = $requestParameters->get("start_date");
        $end_date = $requestParameters->get("end_date");
        $score = $requestParameters->get("score");
        $min_score = $requestParameters->get("min_score");
        $max_score = $requestParameters->get("max_score");
        $genres = $requestParameters->get("genres");
        $genresExclude = $requestParameters->get("genres_exclude");
        $sfw = $requestParameters->get("sfw");
        $type = $requestParameters->get("type");
        $status = $requestParameters->get("status");

        if (!is_null($start_date)) {
            $builder = $this->filterByStartDate($builder, $this->formatIsoDateTime($start_date));
        }

        if (!is_null($end_date)) {
            $builder = $this->filterByEndDate($builder, $this->formatIsoDateTime($end_date));
        }

        if (!is_null($type)) {
            $builder = $builder
                ->where('type', $type);
        }

        if (!is_null($status)) {
            $builder = $builder
                ->where('status', $status);
        }

        if (!is_null($score)) {
            $score = (float)$score;

            $builder = $builder
                ->where('score', '>=', $score);
        }

        if (!is_null($min_score)) {
            $min_score = (float)$min_score;

            $builder = $builder
                ->where('score', '>=', $min_score);
        }

        if (!is_null($max_score)) {
            $max_score = (float)$max_score;

            $builder = $builder
                ->where('score', '<=', $max_score);
        }

        if (!is_null($genres)) {
            $builder = $this->filterByGenres($builder, $genres);
        }

        if (!is_null($genresExclude)) {
            $builder = $this->filterByGenres($builder, $genresExclude, true);
        }

        if (!is_null($sfw)) {
            $builder = $builder
                ->where('rating', '!=', $this->getAdultRating());
        }

        return $builder;
    }

    protected function getOrderByFieldMap(): array
    {
        return self::ORDER_BY;
    }

    protected abstract function filterByStartDate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $startDate): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder;

    protected abstract function filterByEndDate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $endDate): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder;

    protected abstract function getAdultRating(): string;
}
