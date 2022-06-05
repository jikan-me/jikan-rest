<?php

namespace App\Http\QueryBuilder;

use App\IsoDateFormatter;

abstract class MediaSearchQueryBuilder extends SearchQueryBuilder
{
    use IsoDateFormatter;

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

    /**
     * @param string|null $status
     * @return string|null
     */
    public function mapStatus(?string $status = null): ?string
    {
        $status = strtolower($status);

        return $this->getStatusMap()[$status] ?? null;
    }

    /**
     * @param string|null $type
     * @return string|null
     */
    public function mapType(?string $type = null): ?string
    {
        $type = strtolower($type);

        return $this->getTypeMap()[$type] ?? null;
    }

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

    private function filterByGenre(\Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder $builder, int $genre, $exclude = false): \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder
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

    private function filterByGenres(\Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder $builder, string $genres, $exclude = false): \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder
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

    protected function buildQuery(array $requestParameters, \Jenssegers\Mongodb\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder
    {
        $builder = $results;
        extract($requestParameters);

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

    protected abstract function filterByStartDate(\Jenssegers\Mongodb\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $startDate): \Jenssegers\Mongodb\Eloquent\Builder|\Laravel\Scout\Builder;

    protected abstract function filterByEndDate(\Jenssegers\Mongodb\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $endDate): \Jenssegers\Mongodb\Eloquent\Builder|\Laravel\Scout\Builder;

    protected abstract function getStatusMap(): array;

    protected abstract function getTypeMap(): array;

    protected abstract function getAdultRating(): string;
}
