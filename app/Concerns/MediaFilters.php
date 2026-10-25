<?php

namespace App\Concerns;

use Laravel\Scout\Builder;
use Spatie\Enum\Enum;

trait MediaFilters
{
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByMaxScore(Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        // if the client specifies the "max" possible value, ignore it, in that case they want everything included
        // https://github.com/jikan-me/jikan-rest/issues/309
        if (floatval($value) == 10) {
            return $query;
        }

        if (is_scout_query_builder($query)) {
            if (array_key_exists('score', $query->wheres)) {
                $minScoreValue = $query->wheres['score'][1];
                $maxScoreValue = floatval($value);

                return $query->where('score', ['[', $minScoreValue, '..', $maxScoreValue, ']']);
            }

            return $query->where('score', ['<=', floatval($value)]);
        }

        return $query->where('score', '<=', floatval($value));
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByMinScore(Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        // if the client specifies the "max" possible value, ignore it, in that case they want everything included
        // https://github.com/jikan-me/jikan-rest/issues/309
        if (floatval($value) == 0) {
            return $query;
        }

        if (is_scout_query_builder($query)) {
            if (array_key_exists('score', $query->wheres)) {
                $maxScoreValue = $query->wheres['score'][1];
                $minScoreValue = floatval($value);

                return $query->where('score', ['[', $minScoreValue, '..', $maxScoreValue, ']']);
            }

            return $query->where('score', ['>=', floatval($value)]);
        }

        return $query->where('score', '>=', floatval($value));
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByScore(Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (is_null($value) || $value === '') {
            return $query;
        }

        return $query->where('score', floatval($value));
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByStatus(Builder|\Illuminate\Database\Eloquent\Builder $query, Enum $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', $value->label);
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByGenres(Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (!is_string($value) || empty($value)) {
            return $query;
        }
        $genres = explode(',', $value);

        if (is_scout_query_builder($query)) {
            $genreFilterList = '['.implode(', ', $genres).']';
            $query = $query->where('(genres', [
                '=', $genreFilterList,
                ' || demographics:=', $genreFilterList,
                ' || themes:=', $genreFilterList,
                ' || explicit_genres:=', $genreFilterList,
                ')',
            ]);
        } else {
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
        }

        return $query;
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByGenresExclude(Builder|\Illuminate\Database\Eloquent\Builder $query, mixed $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (!is_string($value) || empty($value)) {
            return $query;
        }
        $genres = explode(',', $value);

        if (is_scout_query_builder($query)) {
            $genreFilterList = '['.implode(', ', $genres).']';
            $query = $query->where('(genres', [
                '!=', $genreFilterList,
                ' && demographics:!=', $genreFilterList,
                ' && themes:!=', $genreFilterList,
                ' && explicit_genres:!=', $genreFilterList,
                ')',
            ]);
        } else {
            foreach ($genres as $genreItem) {
                $genre = (int) $genreItem;
                $query = $query
                    ->where('genres.mal_id', '!=', $genre)
                    ->where('demographics.mal_id', '!=', $genre)
                    ->where('themes.mal_id', '!=', $genre)
                    ->where('explicit_genres.mal_id', '!=', $genre);
            }
        }

        return $query;
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterBySfw(Builder|\Illuminate\Database\Eloquent\Builder $query, bool $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        // call scopeExceptItemsWithAdultRating method via $query->exceptItemsWithAdultRating()
        return $value ? $query->exceptItemsWithAdultRating() : $query;
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByUnapproved(Builder|\Illuminate\Database\Eloquent\Builder $query, ?bool $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return !$value ? $query instanceof Builder ? $query->where('approved', 'true') : $query->where('approved', true) : $query;
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByKids(Builder|\Illuminate\Database\Eloquent\Builder $query, ?bool $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (is_null($value)) {
            return $query;
        }

        // call scopeOnlyKidsItems method via $query->onlyKidsItems()
        return $value ? $query->onlyKidsItems() : $query->exceptKidsItems();
    }
}
