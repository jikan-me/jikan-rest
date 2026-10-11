<?php

namespace App;

/**
 * Mongo-backed searchable model base. Attributes are magic (from the collection);
 * documented below are the shared ones the app reads.
 *
 * @property int $mal_id
 */
abstract class JikanApiSearchableModel extends JikanApiModel
{
    use JikanSearchable;

    /**
     * @return string[]
     */
    abstract public function typesenseQueryBy(): array;

    /**
     * The Typesense schema to be created (native Laravel Scout TypesenseEngine
     * contract — the engine fills 'name' if absent).
     *
     * @return array<string, mixed>
     */
    public function typesenseCollectionSchema(): array
    {
        return $this->getCollectionSchema();
    }

    /**
     * Native Laravel Scout TypesenseEngine search parameters. The engine merges
     * these over its defaults, so providing query_by here lets
     * SCOUT_DRIVER=typesense work out of the box (no model-settings config
     * needed). Weights/sort stay wired through the legacy hooks when a model
     * overrides them.
     *
     * @return array<string, mixed>
     */
    public function typesenseSearchParameters(): array
    {
        $parameters = ['query_by' => implode(',', $this->typesenseQueryBy())];

        $weights = $this->getTypeSenseQueryByWeights();
        if ($weights !== null) {
            $parameters['query_by_weights'] = $weights;
        }

        $sortBy = $this->getSearchIndexSortBy();
        if ($sortBy !== null) {
            // legacy shape: [['field' => 'x', 'direction' => 'desc'], ...]
            $parameters['sort_by'] = implode(',', array_map(
                function (array $sort): string {
                    return (string) ($sort['field'] ?? (string) key($sort))
                        .':'.(string) ($sort['direction'] ?? reset($sort));
                },
                $sortBy
            ));
        }

        return $parameters;
    }

    /**
     * The Typesense schema to be created.
     *
     * @return array<string, mixed>
     */
    public function getCollectionSchema(): array
    {
        $titleAttributeName = $this->getTitleAttributeName();

        return [
            'name' => $this->searchableAs(),
            'fields' => [
                [
                    'name' => '.*',
                    'type' => 'auto',
                ],
                [
                    'name' => $titleAttributeName,
                    'type' => 'string',
                    'sort' => true,
                    'optional' => false,
                ],
            ],
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return strtolower($this->table).'_index'.(app()->environment('testing') ? '_testing' : '');
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->mal_id;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return 'mal_id';
    }

    /**
     * Returns what weights to use on query_by fields.
     * https://typesense.org/docs/0.23.0/api/documents.html#search-parameters
     */
    public function getTypeSenseQueryByWeights(): ?string
    {
        return null;
    }

    /**
     * Returns which fields the search index should sort on when searching
     */
    /**
     * @return array<int, array<string, string>>|null
     */
    public function getSearchIndexSortBy(): ?array
    {
        return null;
    }

    protected function simplifyStringForSearch($val): string
    {
        if (! $val) {
            return '';
        }

        return preg_replace('/[^[:alnum:][:space:]]/u', ' ', $val) ?? '';
    }

    public function getTitleAttributeName(): string
    {
        return 'title';
    }
}
