<?php

namespace App\Concerns;

use Spatie\Enum\Enum;

trait MediaFilters
{
    public function filterByMaxScore(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        // if the client specifies the "max" possible value, ignore it, in that case they want everything included
        // https://github.com/jikan-me/jikan-rest/issues/309
        if (floatval($value) == 10) {
            return $query;
        }
        return $query->where("score", "<=", floatval($value));
    }

    public function filterByMinScore(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        // if the client specifies the "max" possible value, ignore it, in that case they want everything included
        // https://github.com/jikan-me/jikan-rest/issues/309
        if (floatval($value) == 0) {
            return $query;
        }
        return $query->where("score", ">=", floatval($value));
    }

    public function filterByScore(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where("score", floatval($value));
    }

    public function filterByStatus(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, Enum $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where("status", $value->label);
    }

    public function filterByGenres(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (!is_string($value) || empty($value)) {
            return $query;
        }
        $genres = explode(',', $value);

        foreach ($genres as $genreItem) {
            $genre = (int) $genreItem;
            // here we need a nested where clause
            // this logically looks like: (genre = x OR demographic = x OR theme = x)
            // so: (any other where clauses from before) AND (genre = x OR demographic = x OR theme = x)
            $query = $query->where(function ($q) use ($genre) {
                return $q->where('genres.mal_id', $genre)
                    ->orWhere('demographics.mal_id', $genre)
                    ->orWhere('themes.mal_id', $genre)
                    ->orWhere('explicit_genres.mal_id', $genre);
            });
        }

        return $query;
    }

    public function filterByGenresExclude(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (!is_string($value) || empty($value)) {
            return $query;
        }
        $genres = explode(',', $value);

        foreach ($genres as $genreItem) {
            $genre = (int) $genreItem;
            $query = $query
                ->where('genres.mal_id', '!=', $genre)
                ->where('demographics.mal_id', '!=', $genre)
                ->where('themes.mal_id', '!=', $genre)
                ->where('explicit_genres.mal_id', '!=', $genre);
        }

        return $query;
    }

    public function filterBySfw(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, bool $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        // call scopeExceptItemsWithAdultRating method via $query->exceptItemsWithAdultRating()
        /** @noinspection PhpUndefinedMethodInspection */
        return $value ? $query->exceptItemsWithAdultRating() : $query;
    }

    public function filterByUnapproved(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, ?bool $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return !$value ? $query->where("approved", true) : $query;
    }

    public function filterByKids(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, ?bool $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (is_null($value)) {
            return $query;
        }
        // call scopeOnlyKidsItems method via $query->onlyKidsItems()
        /** @noinspection PhpUndefinedMethodInspection */
        return $value ? $query->onlyKidsItems() : $query->exceptKidsItems();
    }
}
