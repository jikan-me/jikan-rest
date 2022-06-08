<?php

namespace App\Http\QueryBuilder;

use App\Http\QueryBuilder\Traits\StatusResolver;
use App\Http\QueryBuilder\Traits\TypeResolver;
use App\IsoDateFormatter;

abstract class MediaSearchQueryBuilder extends SearchQueryBuilder
{
    use IsoDateFormatter, StatusResolver, TypeResolver;

    private array $mediaParameterNames = ["score", "sfw", "genres", "genres_exclude", "min_score", "max_score",
        "start_date", "end_date", "status"];

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

    protected function sanitizeParameters($parameters): array
    {
        $parameters = parent::sanitizeParameters($parameters);

        if (!array_key_exists("score", $parameters) || empty($parameters["score"])) {
            $parameters["score"] = 0;
        }

        $parameters["status"] = $this->mapStatus($parameters["status"]);
        $parameters["type"] = $this->mapType($parameters["type"]);

        return $parameters;
    }

    private function filterByGenre(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, int $genre, $exclude = false): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $builder->where(function ($query) use ($genre, $exclude) {
            $operator = $exclude ? '!=' : null;
            return $query
                ->where('genres.mal_id', $operator, $genre)
                ->where('demographics.mal_id', $operator, $genre)
                ->where('themes.mal_id', $operator, $genre)
                ->where('explicit_genres.mal_id', $operator, $genre);
        });
    }

    private function filterByGenres(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, string $genres, $exclude = false): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $genres = explode(',', $genres);
        foreach ($genres as $genre) {
            if (empty($genre)) {
                continue;
            }

            $genre = (int)$genre;

            $builder = $this->filterByGenre($builder, $genre, $exclude);
        }

        return $builder;
    }

    protected function buildQuery(array $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $builder = $results;
        extract($requestParameters);

        if (!is_null($start_date)) {
            $builder = $this->filterByStartDate($builder, $this->formatIsoDateTime($start_date));
        }

        if (!is_null($end_date)) {
            $builder = $this->filterByEndDate($builder, $this->formatIsoDateTime($end_date));
        }

        if ($score !== 0) {
            $score = (float)$score;

            $builder = $builder
                ->where('score', '>=', $score);
        }

        if ($min_score !== null) {
            $min_score = (float)$min_score;

            $builder = $builder
                ->where('score', '>=', $min_score);
        }

        if ($max_score !== null) {
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
                ->where('type', '!=', $this->getAdultRating());
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
