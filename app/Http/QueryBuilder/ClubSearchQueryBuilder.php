<?php
namespace App\Http\QueryBuilder;

use App\Club;
use App\Http\QueryBuilder\Traits\TypeResolver;
use Illuminate\Support\Collection;

class ClubSearchQueryBuilder extends SearchQueryBuilder
{
    use TypeResolver;

    protected string $displayNameFieldName = "title";
    protected array $parameterNames = ["category", "type"];

    /**
     * @OA\Schema(
     *   schema="club_search_query_type",
     *   description="Club Search Query Type",
     *   type="string",
     *   enum={"public","private","secret"}
     * )
     */
    const MAP_TYPES = [
        'public' => 'public',
        'private' => 'private',
        'secret' => 'secret'
    ];

    /**
     * @OA\Schema(
     *   schema="club_search_query_category",
     *   description="Club Search Query Category",
     *   type="string",
     *   enum={
     *      "anime","manga","actors_and_artists","characters",
     *      "cities_and_neighborhoods","companies","conventions","games",
     *      "japan","music","other","schools"
     *   }
     * )
     */
    const MAP_CATEGORY = [
        'anime' => 'Anime',
        'manga' => 'Manga',
        'actors_and_artists' => 'Actors & Artists',
        'characters' => 'Characters',
        'cities_and_neighborhoods' => 'Cities & Neighborhoods',
        'companies' => 'Companies',
        'conventions' => 'Conventions',
        'games' => 'Games',
        'japan' => 'Japan',
        'music' => 'Music',
        'other' => 'Other',
        'schools' => 'Schools'
    ];

    /**
     * @OA\Schema(
     *   schema="club_search_query_orderby",
     *   description="Club Search Query OrderBy",
     *   type="string",
     *   enum={"mal_id","title","members_count","pictures_count","created"}
     * )
     */
    const ORDER_BY = [
        'mal_id', 'title', 'members_count', 'pictures_count', 'created'
    ];

    protected function getModelClass(): object|string
    {
        return Club::class;
    }

    protected function sanitizeParameters($parameters): Collection
    {
        $parameters = parent::sanitizeParameters($parameters);
        $parameters["category"] = $this->mapCategory($parameters["category"]);
        $parameters["type"] = $this->mapType($parameters["type"]);

        return $parameters;
    }

    protected function buildQuery(Collection $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $results;
    }

    protected function getOrderByFieldMap(): array
    {
        return self::ORDER_BY;
    }

    public function getIdentifier(): string
    {
        return "club";
    }

    protected function getTypeMap(): array
    {
        return self::MAP_TYPES;
    }

    /**
     * @param string|null $category
     * @return string|null
     */
    private function mapCategory(?string $category = null) : ?string
    {
        $category = strtolower($category);

        return self::MAP_CATEGORY[$category] ?? null;
    }
}
